<?php
/**
 * Whether AWT can be installed or updated on this site, and how.
 *
 *   wp eval-file awt-install-check.php
 *   wp eval-file awt-install-check.php manifest=$HOME/awt-skill/awt.json
 *
 * Read-only: it changes nothing. It reads the list of AWT releases that
 * useawt.com publishes, compares it with this site, and ends with one line:
 *
 *   RESULT: READY        The commands to install, update or switch on AWT
 *                        follow, with what they change and how to undo them.
 *   RESULT: UP TO DATE   The newest AWT is installed and active.
 *   RESULT: STOP         Something has to change first. It says what.
 *
 * Run the printed commands only after the owner says yes. They install only
 * from AWT's own releases on GitHub; the script refuses any other address.
 * `manifest=` reads the release list from a file instead, for a server that
 * cannot reach useawt.com (see installing.md).
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI output.

$opts = array();
foreach ( isset( $args ) ? $args : array() as $a ) {
	$parts             = explode( '=', $a, 2 );
	$opts[ $parts[0] ] = isset( $parts[1] ) ? $parts[1] : '';
}

if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$show   = static function ( $label, $value ) {
	printf( "%-12s %s\n", $label . ':', $value );
};
$finish = static function ( $word, array $lines ) {
	echo "\nRESULT: $word\n";
	foreach ( $lines as $l ) {
		echo $l . "\n";
	}
	exit( 'STOP' === $word ? 1 : 0 );
};

// Only AWT's own release zips, the same rule AWT's updater applies.
$is_package    = static function ( $url, $file ) {
	return is_string( $url )
		&& 1 === preg_match( '#^https://github\.com/useawt/awt-(theme|blocks)/releases/download/v[0-9.]+/' . preg_quote( $file, '#' ) . '$#', $url );
};
$release_notes = static function ( $version ) {
	return "https://github.com/useawt/awt-theme/releases/tag/v$version and https://github.com/useawt/awt-blocks/releases/tag/v$version";
};

// --- The releases ---------------------------------------------------------

$manifest = null;
$problem  = '';
if ( isset( $opts['manifest'] ) ) {
	$file = $opts['manifest'];
	if ( ! is_readable( $file ) ) {
		$problem = "Cannot read $file. Give the full path; ~ is not expanded.";
	} else {
		$manifest = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
	}
} else {
	$response = wp_remote_get(
		'https://useawt.com/updates/v1/awt.json',
		// Sends nothing about the site, like AWT's own update check.
		array(
			'timeout'    => 15,
			'user-agent' => 'AWT',
		)
	);
	if ( is_wp_error( $response ) ) {
		$problem = 'This server cannot reach useawt.com (' . $response->get_error_message() . '), so it probably cannot download AWT either. Follow "When the server cannot download" in installing.md.';
	} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$problem = 'useawt.com answered ' . wp_remote_retrieve_response_code( $response ) . ' for the release list.';
	} else {
		$manifest = json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
if ( ! $problem && ( ! is_array( $manifest ) || 1 !== ( isset( $manifest['schemaVersion'] ) ? $manifest['schemaVersion'] : 0 ) || empty( $manifest['releases'] ) ) ) {
	$problem = 'The release list is not in the expected format.';
}

// The newest release that is not withdrawn and has both zips.
$target   = null;
$releases = $manifest && ! $problem ? $manifest['releases'] : array();
usort(
	$releases,
	static function ( $a, $b ) {
		return version_compare( $b['version'], $a['version'] );
	}
);
foreach ( $releases as $r ) {
	if ( empty( $r['hold'] )
		&& $is_package( isset( $r['plugin']['package'] ) ? $r['plugin']['package'] : '', 'awt-blocks.zip' )
		&& $is_package( isset( $r['theme']['package'] ) ? $r['theme']['package'] : '', 'awt.zip' ) ) {
		$target = $r;
		break;
	}
}
if ( ! $problem && ! $target ) {
	$problem = 'The release list names no release that can be installed.';
}
$find_release = static function ( $version ) use ( $releases ) {
	foreach ( $releases as $r ) {
		if ( $r['version'] === $version ) {
			return $r;
		}
	}
	return null;
};

// --- This site --------------------------------------------------------------

$site_wp     = get_bloginfo( 'version' );
$needs_wp    = $manifest && ! empty( $manifest['requiresWp'] ) ? $manifest['requiresWp'] : '6.6';
$needs_php   = $manifest && ! empty( $manifest['requiresPhp'] ) ? $manifest['requiresPhp'] : '8.1';
$active      = wp_get_theme();
$all_themes  = wp_get_themes();
$all_plugins = get_plugins();

// Any AWT build, whatever its folder: the theme by its text domain, the
// blocks plugin the way awt-catalog.php finds it.
$awt_themes = array();
foreach ( $all_themes as $folder => $t ) {
	if ( 'awt' === $t->get( 'TextDomain' ) ) {
		$awt_themes[ $folder ] = $t;
	}
}
$awt_plugins = array();
foreach ( $all_plugins as $file => $data ) {
	if ( 0 === strpos( $data['TextDomain'], 'awt' ) && false !== stripos( $data['Name'], 'blocks' ) ) {
		$awt_plugins[ $file ] = $data;
	}
}

$theme_v   = isset( $awt_themes['awt'] ) ? $awt_themes['awt']->get( 'Version' ) : '';
$plugin_f  = 'awt-blocks/awt-blocks.php';
$plugin_v  = isset( $awt_plugins[ $plugin_f ] ) ? $awt_plugins[ $plugin_f ]['Version'] : '';
$theme_on  = 'awt' === $active->get_template();
$plugin_on = $plugin_v && is_plugin_active( $plugin_f );
$count     = static function ( $type, $one, $many ) {
	$n = (int) wp_count_posts( $type )->publish;
	return $n . ' ' . ( 1 === $n ? $one : $many );
};
$content   = $count( 'page', 'page', 'pages' ) . ', ' . $count( 'post', 'post', 'posts' );

$state = static function ( $version, $on ) {
	if ( ! $version ) {
		return 'not installed';
	}
	return $version . ( $on ? ', active' : ', installed but not active' );
};

echo "AWT INSTALL CHECK\n";
$show( 'Site', home_url() );
$show( 'WordPress', $site_wp . "  (AWT needs $needs_wp or newer)" );
$show( 'PHP', PHP_VERSION . "  (AWT needs $needs_php or newer)" );
$show( 'Theme now', $active->get( 'Name' ) . ' ' . $active->get( 'Version' ) . ' (folder ' . $active->get_stylesheet() . ', ' . ( wp_is_block_theme() ? 'a block theme' : 'a classic theme' ) . ')' );
$show( 'Content', "$content, published" );
$show( 'AWT theme', $state( $theme_v, $theme_on ) );
$show( 'AWT Blocks', $state( $plugin_v, $plugin_on ) );
$show( 'Newest AWT', $target ? $target['version'] . ( empty( $target['publishedAt'] ) ? '' : ', released ' . substr( $target['publishedAt'], 0, 10 ) ) : 'unknown' );

// --- Reasons to stop --------------------------------------------------------

$stops = array();
if ( $problem ) {
	$stops[] = $problem;
}
if ( is_multisite() ) {
	$stops[] = 'This is a multisite network. Installing AWT across a network is not covered here: ask the owner.';
}
if ( version_compare( $site_wp, $needs_wp, '<' ) ) {
	$stops[] = "WordPress $site_wp is too old. The owner updates WordPress to $needs_wp or newer first (Dashboard, then Updates).";
}
if ( version_compare( PHP_VERSION, $needs_php, '<' ) ) {
	$stops[] = 'PHP ' . PHP_VERSION . " is too old. The owner switches the site to PHP $needs_php or newer in the host's control panel, or asks the host to.";
}
if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
	$stops[] = 'wp-config.php turns off installing themes and plugins (DISALLOW_FILE_MODS). Ask the owner; do not change wp-config.php yourself.';
}
if ( ! wp_is_writable( get_theme_root() ) || ! wp_is_writable( WP_PLUGIN_DIR ) ) {
	$stops[] = 'This SSH user cannot write to the themes or plugins folder. Ask the owner or the host.';
}
foreach ( $awt_themes as $folder => $t ) {
	if ( 'awt' !== $folder ) {
		$stops[] = "An AWT theme is in the folder \"$folder\" (" . $t->get( 'Name' ) . ' ' . $t->get( 'Version' ) . '). Installing into "awt" could leave two copies, and the edited header, footer and templates belong to the folder that is active (now "' . $active->get_stylesheet() . '"). Ask the owner how AWT was installed before changing anything.';
	}
}
foreach ( $awt_plugins as $file => $data ) {
	if ( $plugin_f !== $file ) {
		$stops[] = "AWT's blocks are in the plugin \"$file\" ({$data['Name']} {$data['Version']}). Ask the owner how AWT was installed before changing anything.";
	}
}
if ( isset( $all_themes['awt'] ) && ! isset( $awt_themes['awt'] ) ) {
	$stops[] = 'A different theme already uses the folder "awt" (' . $all_themes['awt']->get( 'Name' ) . '). Ask the owner.';
}
if ( is_dir( WP_PLUGIN_DIR . '/awt-blocks' ) && ! $plugin_v ) {
	$stops[] = 'A folder "awt-blocks" is in the plugins folder, but it is not AWT Blocks. Ask the owner.';
}
if ( $stops ) {
	$finish( 'STOP', $stops );
}

// --- What to run ------------------------------------------------------------

$newest   = $target['version'];
$run      = array();
$changes  = array();
$undo     = array();
$breaking = array();

// The plugin first, then the theme, as useawt.com says to: the theme
// asks for the plugin the moment it is active.
if ( ! $plugin_v ) {
	$run[]     = 'wp plugin install ' . $target['plugin']['package'] . ' --activate';
	$changes[] = "AWT Blocks $newest is added and switched on.";
	$undo[]    = 'wp plugin deactivate awt-blocks';
} elseif ( version_compare( $plugin_v, $newest, '<' ) ) {
	$run[]     = 'wp plugin install ' . $target['plugin']['package'] . ' --force' . ( $plugin_on ? '' : ' --activate' );
	$changes[] = "AWT Blocks goes from $plugin_v to $newest" . ( $plugin_on ? '.' : ' and is switched on.' );
	$before    = $find_release( $plugin_v );
	$undo[]    = $before ? 'wp plugin install ' . $before['plugin']['package'] . ' --force' : "Restore the backup: $plugin_v is no longer in the release list.";
} elseif ( ! $plugin_on ) {
	$run[]     = 'wp plugin activate awt-blocks';
	$changes[] = 'AWT Blocks is switched on.';
	$undo[]    = 'wp plugin deactivate awt-blocks';
}

$switch = static function () use ( $active, $content ) {
	$lines = array(
		'The site switches from ' . $active->get( 'Name' ) . " to AWT. Every page takes AWT's look, header and footer at once, for visitors too. Pages, posts and media stay as they are ($content).",
	);
	if ( ! wp_is_block_theme() ) {
		$lines[] = $active->get( 'Name' ) . "'s menus, widgets and Customizer settings do not show in AWT. WordPress keeps them, and switching back brings them back.";
	}
	return $lines;
};

if ( ! $theme_v ) {
	$run[]   = 'wp theme install ' . $target['theme']['package'] . ' --activate';
	$changes   = array_merge( $changes, $switch() );
	$changes[] = 'From then on AWT updates itself by default, three days after each release. A version that can change how the site looks waits for the owner to install it. AWT Settings, Tools, changes this.';
	$undo[]    = 'wp theme activate ' . $active->get_stylesheet();
} else {
	if ( version_compare( $theme_v, $newest, '<' ) ) {
		$run[]     = 'wp theme install ' . $target['theme']['package'] . ' --force';
		$changes[] = "The AWT theme goes from $theme_v to $newest. AWT Settings and every edit made in the Site Editor are kept.";
		$before    = $find_release( $theme_v );
		$undo[]    = $before ? 'wp theme install ' . $before['theme']['package'] . ' --force' : "Restore the backup: $theme_v is no longer in the release list.";
	}
	if ( ! $theme_on ) {
		$run[]   = 'wp theme activate awt';
		$changes = array_merge( $changes, $switch() );
		$undo[]  = 'wp theme activate ' . $active->get_stylesheet();
	}
}

// Releases that change how a site looks never install themselves; the owner
// reads what changed first.
$oldest = $theme_v && $plugin_v ? ( version_compare( $theme_v, $plugin_v, '<' ) ? $theme_v : $plugin_v ) : ( $theme_v ? $theme_v : $plugin_v );
if ( $oldest ) {
	foreach ( array_reverse( $releases ) as $r ) {
		if ( ! empty( $r['breaking'] ) && version_compare( $r['version'], $oldest, '>' ) && version_compare( $r['version'], $newest, '<=' ) ) {
			$breaking[] = $r['version'];
		}
	}
	$listed_from = end( $releases );
	if ( $listed_from && version_compare( $oldest, $listed_from['version'], '<' ) ) {
		$changes[] = "$oldest is older than every release in the list, so this check cannot see which updates change how the site looks. Read the release notes from $oldest on before updating: https://github.com/useawt/awt-theme/releases";
	}
}
foreach ( $breaking as $v ) {
	$changes[] = "AWT $v changes how some sites look. Show the owner what changed before updating: " . $release_notes( $v );
}

if ( $run ) {
	$out = array( 'What changes (tell the owner, and wait for a yes):' );
	foreach ( $changes as $c ) {
		$out[] = "  - $c";
	}
	$out[] = 'Then back up (Safety rule 4 in SKILL.md) and run, from the WordPress folder, in this order:';
	foreach ( $run as $c ) {
		$out[] = "  $c";
	}
	$out[] = 'Afterwards run this check again: it should say UP TO DATE.';
	$out[] = 'To undo:';
	foreach ( array_reverse( $undo ) as $c ) {
		$out[] = "  $c";
	}
	$finish( 'READY', $out );
}

// --- Already done -----------------------------------------------------------

$out = array( "AWT $theme_v is installed and active. Nothing to install." );

$response = wp_remote_get(
	home_url( '/' ),
	array(
		'timeout'    => 20,
		// Some hosts block requests that do not look like a browser.
		'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36',
	)
);
if ( is_wp_error( $response ) ) {
	$out[] = 'The server could not load its own home page (' . $response->get_error_message() . '). Load ' . home_url( '/' ) . ' yourself, as step 6 of installing.md says.';
} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
	$out[] = 'WARN: the home page answers ' . wp_remote_retrieve_response_code( $response ) . '. Look at ' . home_url( '/' ) . ' before anything else.';
} elseif ( preg_match( '/critical error|Fatal error|Parse error/i', wp_remote_retrieve_body( $response ) ) ) {
	$out[] = 'WARN: the home page shows a PHP error. Undo the last change and tell the owner.';
} else {
	$out[] = 'The home page loads: ' . home_url( '/' );
}

if ( function_exists( 'AWT\Theme\Settings\get' ) ) {
	if ( ! AWT\Theme\Settings\get( 'welcome.completed' ) ) {
		$out[] = 'Next, for the owner: the welcome wizard sets the style, logo, header and text size in a few steps: ' . admin_url( 'themes.php?page=awt-settings&tab=welcome' );
	}
	$modes       = array(
		'auto'   => 'AWT updates itself',
		'notify' => 'AWT tells the owner about new versions',
		'off'    => 'update checks are off',
	);
	$update_mode = (string) AWT\Theme\Settings\get( 'updates.mode' );
	$out[]       = 'Updates: ' . ( isset( $modes[ $update_mode ] ) ? $modes[ $update_mode ] : $modes['auto'] ) . ' (AWT Settings, Tools).';
}
$finish( 'UP TO DATE', $out );
