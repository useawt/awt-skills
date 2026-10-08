<?php
/**
 * Publish or schedule a draft the owner has approved, then confirm it is live.
 *
 *   wp eval-file awt-publish.php id=123 expect=<md5>
 *   wp eval-file awt-publish.php id=123 expect=<md5> at="2026-10-20 09:00"
 *
 * Run it only after the owner has said yes to this page. `expect` is the content
 * hash of the version they reviewed (`awt-catalog.php page 123`): if anyone
 * changed the page since, nothing is published. `at` is in the site's own time
 * zone (awt-catalog.php prints it); WordPress then publishes the page at that
 * time. A page that still has placeholders like [Price] is refused.
 *
 * Before publishing it runs awt-check.php and refuses on any error. It changes
 * only the status and the date, never the content. Afterwards it loads the live
 * page the way a visitor does and says whether the new content is there.
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

$id     = isset( $opts['id'] ) ? (int) $opts['id'] : 0;
$target = $id ? get_post( $id ) : null;
if ( ! $target ) {
	$fail( 'Give the page: id=123 expect=<md5>' );
}
if ( ! in_array( $target->post_type, array( 'page', 'post' ), true ) ) {
	$fail( "ID $id is a {$target->post_type}, not a page or post." );
}
if ( 'publish' === $target->post_status ) {
	echo "✓ Already published: " . get_permalink( $target ) . "\n";
	exit( 0 );
}
if ( 'trash' === $target->post_status ) {
	$fail( "ID $id is in the trash. Ask the owner whether to restore it." );
}
if ( empty( $opts['expect'] ) ) {
	$fail( "Publishing needs expect=<md5> of the version the owner approved. Run: wp eval-file awt-catalog.php page $id" );
}
if ( md5( $target->post_content ) !== $opts['expect'] ) {
	$fail( 'The page changed after the owner reviewed it (hash is now ' . md5( $target->post_content ) . '). Show them the current version and ask again.' );
}
$autosave = wp_get_post_autosave( $target->ID );
if ( $autosave && $autosave->post_modified > $target->post_modified ) {
	$fail( 'Someone has unsaved changes open in the editor (autosave ' . $autosave->ID . '). Ask the owner to save or discard them first.' );
}
$title = trim( $target->post_title );
if ( '' === $title || 'Draft' === $title ) {
	$fail( "The page has no real title yet. Agree one with the owner, then: wp post update $id --post_title=\"...\"" );
}

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

$left = awt_skill_placeholders( $target->post_content );
if ( $left ) {
	$fail( 'The page still has placeholders: ' . implode( ', ', $left ) . '. Ask the owner for the real text first.' );
}

$error_count = 0;
foreach ( awt_skill_check( $target->post_content, $target ) as $issue ) {
	echo "{$issue[0]}  {$issue[1]}\n";
	$error_count += ( 'ERROR' === $issue[0] ) ? 1 : 0;
}
if ( $error_count ) {
	$fail( "$error_count errors. Nothing was published." );
}

$change = array( 'ID' => $target->ID );
if ( ! empty( $opts['at'] ) ) {
	try {
		$when = new DateTimeImmutable( $opts['at'], wp_timezone() );
	} catch ( Exception $e ) {
		$fail( "\"{$opts['at']}\" is not a date and time. Use the form 2026-10-20 09:00." );
	}
	if ( $when->getTimestamp() <= time() + 60 ) {
		$fail( "{$opts['at']} is not in the future (site time zone: " . wp_timezone_string() . ').' );
	}
	$change['post_status']   = 'future';
	$change['post_date']     = $when->format( 'Y-m-d H:i:s' );
	$change['post_date_gmt'] = get_gmt_from_date( $change['post_date'] );
} else {
	$change['post_status']   = 'publish';
	$change['post_date']     = current_time( 'mysql' );
	$change['post_date_gmt'] = current_time( 'mysql', true );
}
$change['edit_date'] = true;

// wp_update_post() re-slashes the stored fields itself, and nothing here
// carries content, so the attribute escapes are untouched.
$result = wp_update_post( $change, true );
if ( is_wp_error( $result ) ) {
	$fail( 'WordPress refused: ' . $result->get_error_message() );
}
update_post_meta( $target->ID, '_edit_last', get_current_user_id() );
clean_post_cache( $target->ID );
$stored = get_post( $target->ID );

if ( md5( $stored->post_content ) !== $opts['expect'] ) {
	$fail( "The status changed, but so did the content. Check it with awt-check.php post=$id." );
}

if ( 'future' === $stored->post_status ) {
	echo "✓ Scheduled ID $id for {$stored->post_date} (" . wp_timezone_string() . '): ' . get_permalink( $stored ) . "\n";
	echo "WordPress publishes it at that time. On a quiet site it can be a few minutes late.\n";
	exit( 0 );
}

echo "✓ Published ID $id: " . get_permalink( $stored ) . "\n";
$wanted = $target->post_name ? $target->post_name : sanitize_title( $title );
if ( $stored->post_name !== $wanted && preg_match( '/^' . preg_quote( $wanted, '/' ) . '-\d+$/', $stored->post_name ) ) {
	echo "WARN  The address ends in \"{$stored->post_name}\", so another page or post already uses that slug. Tell the owner.\n";
}
list( $level, $message ) = awt_skill_verify_live( $stored );
echo ( 'OK' === $level ? '✓ ' : 'WARN  ' ) . $message . "\n";
