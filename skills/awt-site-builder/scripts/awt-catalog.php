<?php
/**
 * What this AWT site has: version, blocks, patterns, design presets and settings.
 *
 * Read-only. Run it with WP-CLI from the WordPress folder:
 *
 *   wp eval-file awt-catalog.php                 Summary of everything
 *   wp eval-file awt-catalog.php block awt/tile  One block: attributes, parents, children
 *   wp eval-file awt-catalog.php pattern awt/hero  One pattern's ready-to-use markup
 *   wp eval-file awt-catalog.php icons chart     Icon names for awt/icon and iconName, by keyword
 *   wp eval-file awt-catalog.php settings        AWT Settings, as JSON
 *   wp eval-file awt-catalog.php page 123        A page's status, hash and last edit
 *
 * Everything comes from the installed version, so it is always current.
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI output.

$mode = isset( $args[0] ) ? $args[0] : 'summary';
$arg  = isset( $args[1] ) ? $args[1] : '';

$awt_line = static function ( $text ) {
	echo $text . "\n";
};

$awt_block_types = static function () {
	$out = array();
	foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
		if ( 0 === strpos( $name, 'awt/' ) ) {
			$out[ $name ] = $type;
		}
	}
	ksort( $out );
	return $out;
};

$theme = wp_get_theme();
$awt   = ( 'awt' === $theme->get_template() );

if ( 'summary' === $mode ) {
	$awt_line( 'Site: ' . home_url() . '  (WordPress ' . get_bloginfo( 'version' ) . ')' );
	$awt_line( 'Theme: ' . $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) . ( $awt ? '' : '  <- AWT is NOT the active theme' ) );
	$plugin_version = defined( 'AWT_BLOCKS_VERSION' ) ? AWT_BLOCKS_VERSION : '';
	if ( ! $plugin_version ) {
		foreach ( get_option( 'active_plugins', array() ) as $file ) {
			if ( 0 === strpos( $file, 'awt-blocks' ) || false !== strpos( $file, '/awt-blocks.php' ) ) {
				$data           = get_plugin_data( WP_PLUGIN_DIR . '/' . $file, false, false );
				$plugin_version = $data['Version'];
			}
		}
	}
	$awt_line( 'AWT Blocks plugin: ' . ( $plugin_version ? $plugin_version : 'not active' ) );
	$awt_line( 'Front page: ' . ( 'page' === get_option( 'show_on_front' ) ? 'page ' . get_option( 'page_on_front' ) : 'latest posts' ) );

	$awt_line( "\nBLOCKS (name: title. description)" );
	foreach ( $awt_block_types() as $name => $type ) {
		$where = '';
		if ( ! empty( $type->parent ) ) {
			$where = '  [only inside ' . implode( ', ', $type->parent ) . ']';
		} elseif ( ! empty( $type->ancestor ) ) {
			$where = '  [only within ' . implode( ', ', $type->ancestor ) . ']';
		}
		$awt_line( "- $name: {$type->title}. {$type->description}$where" );
	}

	$awt_line( "\nPATTERNS (name: title. description)" );
	foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $p ) {
		if ( 0 !== strpos( $p['name'], 'awt' ) ) {
			continue;
		}
		$desc = isset( $p['description'] ) ? $p['description'] : '';
		$awt_line( "- {$p['name']}: {$p['title']}. $desc" );
	}

	$settings = wp_get_global_settings();
	$awt_line( "\nPRESETS (use the slug in block attributes)" );
	$sizes = isset( $settings['typography']['fontSizes']['theme'] ) ? $settings['typography']['fontSizes']['theme'] : array();
	$awt_line( 'Font sizes: ' . implode( ', ', wp_list_pluck( $sizes, 'slug' ) ) );
	$space = isset( $settings['spacing']['spacingSizes']['theme'] ) ? $settings['spacing']['spacingSizes']['theme'] : array();
	$awt_line( 'Spacing: ' . implode( ', ', wp_list_pluck( $space, 'slug' ) ) );
	$colors = isset( $settings['color']['palette']['theme'] ) ? $settings['color']['palette']['theme'] : array();
	$awt_line( 'Colors: ' . implode( ', ', wp_list_pluck( $colors, 'slug' ) ) );
	$layout = isset( $settings['layout'] ) ? $settings['layout'] : array();
	$awt_line( 'Content width: ' . ( isset( $layout['contentSize'] ) ? $layout['contentSize'] : '?' ) . ', wide width: ' . ( isset( $layout['wideSize'] ) ? $layout['wideSize'] : '?' ) );

	$templates = array();
	foreach ( get_block_templates( array(), 'wp_template' ) as $t ) {
		if ( 'page' === $t->slug || 0 === strpos( $t->slug, 'page-' ) ) {
			$templates[] = $t->slug;
		}
	}
	$awt_line( 'Page templates: ' . implode( ', ', $templates ) );
	return;
}

if ( 'block' === $mode ) {
	$type = WP_Block_Type_Registry::get_instance()->get_registered( $arg );
	if ( ! $type ) {
		$awt_line( "No block called \"$arg\". Run without arguments to list them." );
		exit( 1 );
	}
	// Child blocks are documented on their top-level block's page.
	$docs_for = $type->name;
	while ( ( $up = WP_Block_Type_Registry::get_instance()->get_registered( $docs_for ) ) && ( ! empty( $up->parent ) || ! empty( $up->ancestor ) ) ) {
		$next = ! empty( $up->parent ) ? $up->parent[0] : $up->ancestor[0];
		if ( 0 !== strpos( $next, 'awt/' ) || $next === $docs_for ) {
			break;
		}
		$docs_for = $next;
	}
	echo wp_json_encode(
		array(
			'name'          => $type->name,
			'title'         => $type->title,
			'description'   => $type->description,
			'attributes'    => $type->attributes,
			'parent'        => $type->parent,
			'ancestor'      => $type->ancestor,
			'allowedBlocks' => isset( $type->allowed_blocks ) ? $type->allowed_blocks : null,
			'supports'      => $type->supports,
			'example'       => $type->example,
			'docs'          => 'Allowed values for each attribute: https://useawt.com/blocks/' . substr( $docs_for, 4 ) . '/',
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . "\n";
	return;
}

if ( 'pattern' === $mode ) {
	$p = WP_Block_Patterns_Registry::get_instance()->get_registered( $arg );
	if ( ! $p ) {
		$awt_line( "No pattern called \"$arg\". Run without arguments to list them." );
		exit( 1 );
	}
	echo $p['content'] . "\n";
	return;
}

if ( 'icons' === $mode ) {
	$files = glob( WP_PLUGIN_DIR . '/*/build/shared/icon-manifest.json' );
	if ( ! $files ) {
		$awt_line( 'No icon list found. Is the AWT Blocks plugin installed?' );
		exit( 1 );
	}
	$icons = json_decode( file_get_contents( $files[0] ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$icons = isset( $icons['iconsByName'] ) ? $icons['iconsByName'] : array();
	$query = strtolower( $arg );
	$found = 0;
	foreach ( $icons as $name => $icon ) {
		$haystack = strtolower( $name . ' ' . $icon['label'] . ' ' . implode( ' ', isset( $icon['aliases'] ) ? $icon['aliases'] : array() ) );
		if ( '' === $query || false !== strpos( $haystack, $query ) ) {
			$awt_line( "$name  ({$icon['label']})" );
			if ( ++$found >= 40 ) {
				$awt_line( '... more. Search with a more specific word.' );
				break;
			}
		}
	}
	if ( ! $found ) {
		$awt_line( "No icon matches \"$arg\". " . count( $icons ) . ' icons exist; try another word.' );
	}
	return;
}

if ( 'settings' === $mode ) {
	if ( ! function_exists( 'AWT\Theme\Settings\all' ) ) {
		$awt_line( 'AWT Settings are not available. Is the AWT theme active?' );
		exit( 1 );
	}
	$all = \AWT\Theme\Settings\all();
	// Custom code can be long; show its size, not its body.
	array_walk_recursive(
		$all,
		static function ( &$value, $key ) {
			if ( is_string( $value ) && strlen( $value ) > 300 ) {
				$value = '(' . strlen( $value ) . ' characters)';
			}
		}
	);
	echo wp_json_encode( $all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n";
	return;
}

if ( 'page' === $mode ) {
	$post = get_post( (int) $arg );
	if ( ! $post ) {
		$awt_line( "No post with ID $arg." );
		exit( 1 );
	}
	$awt_line( "ID {$post->ID} ({$post->post_type}, {$post->post_status}): {$post->post_title}" );
	$awt_line( 'URL: ' . get_permalink( $post ) );
	$editor = get_userdata( (int) get_post_meta( $post->ID, '_edit_last', true ) );
	$awt_line( 'Last edited: ' . $post->post_modified . ( $editor ? ' by ' . $editor->display_name : '' ) );
	$awt_line( 'Content hash (md5): ' . md5( $post->post_content ) );
	$awt_line( 'Template: ' . ( get_page_template_slug( $post ) ? get_page_template_slug( $post ) : 'default' ) );
	$revisions = wp_get_post_revisions( $post->ID, array( 'posts_per_page' => 1 ) );
	$awt_line( 'Newest revision: ' . ( $revisions ? key( $revisions ) : 'none' ) );
	$autosave = wp_get_post_autosave( $post->ID );
	if ( $autosave && $autosave->post_modified > $post->post_modified ) {
		$awt_line( 'WARNING: someone has unsaved changes in the editor (autosave ' . $autosave->ID . ', ' . $autosave->post_modified . ').' );
	}
	return;
}

$awt_line( "Unknown mode \"$mode\". Use: summary, block <name>, pattern <name>, icons <word>, settings, page <id>." );
exit( 1 );
