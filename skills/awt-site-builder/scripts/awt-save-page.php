<?php
/**
 * Save block markup to a page safely.
 *
 * New draft (the default, and the safe first step):
 *   wp eval-file awt-save-page.php file=/path/page.html title="About us" [template=page-no-title] [parent=12] [type=page] [status=draft|pending|private]
 *
 * Replace an existing page's content (only with the owner's go-ahead):
 *   wp eval-file awt-save-page.php file=/path/page.html id=123 expect=<md5>
 *
 * `expect` is the content hash you saw when you read the page
 * (`awt-catalog.php page 123`). If the page changed since then, someone else
 * edited it and nothing is written.
 *
 * Before writing, it runs awt-check.php and refuses on any error. It keeps a
 * revision of the old content, writes through wp_slash() so attribute escapes
 * survive, and checks that what was stored is exactly what you sent.
 * It never publishes a new page, and never deletes anything.
 *
 * Changes are recorded under the first administrator. Add WP-CLI's own
 * --user=<login> to record them under the owner's account instead.
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI output.

define( 'AWT_SKILL_CHECK_LIBRARY', true );
require __DIR__ . '/awt-check.php';

$opts = array();
foreach ( isset( $args ) ? $args : array() as $a ) {
	$parts             = explode( '=', $a, 2 );
	$opts[ $parts[0] ] = isset( $parts[1] ) ? $parts[1] : '';
}

$fail = static function ( $msg ) {
	echo "✗ $msg\n";
	exit( 1 );
};

if ( empty( $opts['file'] ) ) {
	$fail( 'Give the markup file: file=/path/page.html' );
}
if ( ! is_readable( $opts['file'] ) ) {
	$fail( "Cannot read {$opts['file']}. Give the full path; ~ is not expanded here, so use \$HOME." );
}
$content = file_get_contents( $opts['file'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
if ( trim( $content ) === '' ) {
	$fail( 'The markup file is empty.' );
}

$id   = isset( $opts['id'] ) ? (int) $opts['id'] : 0;
$target = $id ? get_post( $id ) : null;
if ( $id && ! $target ) {
	$fail( "No post with ID $id." );
}

// Act as an administrator so revisions record an author and capability-aware
// blocks render as they would for the owner.
if ( ! get_current_user_id() ) {
	$admins = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
		)
	);
	if ( $admins ) {
		wp_set_current_user( $admins[0]->ID );
	}
}

$template = isset( $opts['template'] ) ? $opts['template'] : ( $target ? get_page_template_slug( $target ) : '' );
$error_count   = 0;
foreach ( awt_skill_check( $content, $target, $template ) as $issue ) {
	echo "{$issue[0]}  {$issue[1]}\n";
	$error_count += ( 'ERROR' === $issue[0] ) ? 1 : 0;
}
if ( $error_count ) {
	$fail( "$error_count errors. Nothing was saved." );
}

if ( $target ) {
	if ( empty( $opts['expect'] ) ) {
		$fail( 'Replacing a page needs expect=<md5> from when you read it. Run: wp eval-file awt-catalog.php page ' . $id );
	}
	if ( md5( $target->post_content ) !== $opts['expect'] ) {
		$fail( 'The page changed since you read it (hash is now ' . md5( $target->post_content ) . '). Someone edited it. Read it again and ask the owner before overwriting.' );
	}
	$autosave = wp_get_post_autosave( $target->ID );
	if ( $autosave && $autosave->post_modified > $target->post_modified ) {
		$fail( 'Someone has unsaved changes open in the editor (autosave ' . $autosave->ID . '). Ask the owner to save or discard them first.' );
	}
	if ( md5( $target->post_content ) === md5( $content ) ) {
		echo "✓ No change: the page already has exactly this content.\n";
		exit( 0 );
	}

	// Make sure the current version exists as a revision before replacing it.
	$keep      = null;
	$revisions = wp_get_post_revisions( $target->ID, array( 'posts_per_page' => 1 ) );
	$newest    = $revisions ? reset( $revisions ) : null;
	if ( ! $newest || $newest->post_content !== $target->post_content ) {
		$keep = _wp_put_post_revision( $target );
	} else {
		$keep = $newest->ID;
	}

	$result = wp_update_post(
		wp_slash(
			array(
				'ID'           => $target->ID,
				'post_content' => $content,
			)
		),
		true
	);
	if ( is_wp_error( $result ) ) {
		$fail( 'WordPress refused the update: ' . $result->get_error_message() );
	}
	if ( isset( $opts['template'] ) ) {
		update_post_meta( $target->ID, '_wp_page_template', $opts['template'] );
	}
	update_post_meta( $target->ID, '_edit_last', get_current_user_id() );
	$saved_id = $target->ID;
	echo ( is_numeric( $keep ) && $keep ? "The old content is revision $keep. Restore it from the editor's Revisions panel if needed.\n" : "WARNING: revisions are off on this site, so the old content was not kept.\n" );
} else {
	$type   = isset( $opts['type'] ) ? $opts['type'] : 'page';
	$status = isset( $opts['status'] ) ? $opts['status'] : 'draft';
	if ( ! post_type_exists( $type ) ) {
		$fail( "There is no post type \"$type\". Use page or post." );
	}
	if ( ! in_array( $status, array( 'draft', 'pending', 'private' ), true ) ) {
		$fail( 'A new page is saved as draft, pending or private. Publish it after the owner approves.' );
	}
	$postarr = array(
		'post_type'    => $type,
		'post_status'  => $status,
		'post_title'   => isset( $opts['title'] ) ? $opts['title'] : 'Draft',
		'post_content' => $content,
		'post_parent'  => isset( $opts['parent'] ) ? (int) $opts['parent'] : 0,
		'post_author'  => get_current_user_id(),
	);
	if ( isset( $opts['template'] ) ) {
		$postarr['page_template'] = $opts['template'];
	}
	$saved_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $saved_id ) ) {
		$fail( 'WordPress refused the new page: ' . $saved_id->get_error_message() );
	}
	update_post_meta( $saved_id, '_edit_last', get_current_user_id() );
}

clean_post_cache( $saved_id );
$stored = get_post( $saved_id );
if ( md5( $stored->post_content ) !== md5( $content ) ) {
	$fail( "Saved, but the stored content differs from the file (ID $saved_id). Check it with awt-check.php post=$saved_id." );
}

echo "✓ Saved ID $saved_id ({$stored->post_status}). Content hash: " . md5( $stored->post_content ) . "\n";
echo 'View: ' . ( 'publish' === $stored->post_status ? get_permalink( $stored ) : get_preview_post_link( $stored ) ) . "\n";
echo 'Edit: ' . admin_url( 'post.php?post=' . $saved_id . '&action=edit' ) . "\n";
if ( 'publish' === $stored->post_status ) {
	echo "If the site has a page cache, clear it so visitors see the change.\n";
}
