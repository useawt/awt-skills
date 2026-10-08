<?php
/**
 * Check block markup before it goes on a page, or check a page that is already there.
 *
 *   wp eval-file awt-check.php file=/path/to/page.html
 *   wp eval-file awt-check.php post=123
 *
 * Read-only. Prints ERROR and WARN lines and exits 1 when there is an error.
 *
 * What it checks:
 *  - every block is opened and closed, and its name exists on this site
 *  - attributes exist and have the right type; with awt-allowed-values.json
 *    beside this script, values outside the documented list are flagged too
 *  - blocks sit inside the parents they need, and parents accept them
 *  - core blocks have the HTML shape the editor expects
 *  - attribute escapes are intact
 *  - the rendered page: balanced <div>s, heading order, alt text,
 *    links and buttons with a name, unique ids
 *
 * It cannot prove the editor will accept every block. Opening the page in the
 * editor is the final check when you have a browser.
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI output.

if ( ! function_exists( 'awt_skill_check' ) ) {

	/**
	 * Check markup. Returns a list of [ 'ERROR'|'WARN', message ].
	 *
	 * @param string       $content Block markup.
	 * @param WP_Post|null $post    The page it belongs to, if any (for rendering and the template).
	 * @param string       $template Page template slug, when there is no post yet.
	 * @return array
	 */
	function awt_skill_check( $content, $post = null, $template = '' ) {
		$issues   = array();
		$registry = WP_Block_Type_Registry::get_instance();
		$add      = static function ( $level, $msg ) use ( &$issues ) {
			$issues[] = array( $level, $msg );
		};

		// 1. Delimiters: every opener has its closer, in order. Same pattern as
		// WordPress's own block parser.
		$stack = array();
		preg_match_all( '/<!--\s+(\/)?wp:([a-z][a-z0-9_-]*\/)?([a-z][a-z0-9_-]*)\s+({(?:(?:[^}]+|}+(?=})|(?!}\s+\/?-->).)*+)?}\s+)?(\/)?-->/s', $content, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
		foreach ( $m as $d ) {
			$name    = ( $d[2][0] ? $d[2][0] : 'core/' ) . $d[3][0];
			$line    = substr_count( substr( $content, 0, $d[0][1] ), "\n" ) + 1;
			$closer  = '/' === $d[1][0];
			if ( ! empty( $d[4][0] ) && false !== strpos( $d[4][0], '--' ) ) {
				$add( 'WARN', "Line $line: $name has a raw -- in its attributes. WordPress writes it as \\u002d\\u002d; a value containing --> breaks the block." );
			}
			$selfend = isset( $d[5] ) && '/' === $d[5][0];
			if ( $closer ) {
				$open = array_pop( $stack );
				if ( null === $open || $open[0] !== $name ) {
					$add( 'ERROR', "Line $line: closing $name, but the open block is " . ( $open ? "{$open[0]} (line {$open[1]})" : 'none' ) . '.' );
					return $issues;
				}
			} elseif ( ! $selfend ) {
				$stack[] = array( $name, $line );
			}
		}
		foreach ( $stack as $open ) {
			$add( 'ERROR', "Line {$open[1]}: {$open[0]} is never closed." );
		}
		if ( $stack ) {
			return $issues;
		}

		// 2. Damaged escapes: u003c without its backslash shows as text on the page.
		if ( preg_match( '/(?<!\\\\)u00[0-9a-f]{2}/', $content, $bad ) ) {
			$add( 'ERROR', "An attribute escape lost its backslash (\"{$bad[0]}\"). Write the content with wp_slash() or the save script, never with raw SQL." );
		}

		// 3. Each block: name, attributes, nesting, HTML shape.
		$allowed = array();
		$values  = __DIR__ . '/awt-allowed-values.json';
		if ( is_readable( $values ) ) {
			$decoded = json_decode( file_get_contents( $values ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$allowed = isset( $decoded['values'] ) ? $decoded['values'] : array();
		}
		$generic = array( 'className', 'style', 'lock', 'metadata', 'anchor', 'align', 'layout', 'fontSize', 'fontFamily', 'textColor', 'backgroundColor', 'gradient', 'borderColor', 'textAlign' );
		$walk    = static function ( $blocks, $chain ) use ( &$walk, $registry, $add, $generic, $allowed ) {
			foreach ( $blocks as $block ) {
				$name = $block['blockName'];
				if ( null === $name ) {
					if ( trim( wp_strip_all_tags( $block['innerHTML'] ) ) !== '' || preg_match( '/<(img|iframe|video|table)/i', $block['innerHTML'] ) ) {
						$add( 'WARN', 'HTML outside any block: "' . substr( trim( preg_replace( '/\s+/', ' ', $block['innerHTML'] ) ), 0, 60 ) . '". The editor turns it into a Classic block. Wrap it in a block.' );
					}
					continue;
				}
				$where = $chain ? end( $chain ) : 'top level';
				$type  = $registry->get_registered( $name );
				if ( ! $type ) {
					$add( 'ERROR', "$name does not exist on this site (inside $where)." );
					continue;
				}

				if ( ! empty( $type->parent ) && ! in_array( $where, $type->parent, true ) ) {
					$add( 'ERROR', "$name must sit directly inside " . implode( ' or ', $type->parent ) . ", not $where." );
				}
				if ( ! empty( $type->ancestor ) && ! array_intersect( $type->ancestor, $chain ) ) {
					$add( 'ERROR', "$name must sit somewhere inside " . implode( ' or ', $type->ancestor ) . '.' );
				}
				if ( $chain ) {
					$ptype = $registry->get_registered( $where );
					if ( $ptype && ! empty( $ptype->allowed_blocks ) && ! in_array( $name, $ptype->allowed_blocks, true ) ) {
						$add( 'ERROR', "$where does not accept $name. It accepts: " . implode( ', ', $ptype->allowed_blocks ) . '.' );
					}
				}

				$schema = is_array( $type->attributes ) ? $type->attributes : array();
				foreach ( $block['attrs'] as $key => $value ) {
					if ( ! isset( $schema[ $key ] ) ) {
						if ( ! in_array( $key, $generic, true ) ) {
							$add( 'WARN', "$name has no attribute \"$key\". The editor drops it." );
						}
						continue;
					}
					$def = $schema[ $key ];
					if ( isset( $def['source'] ) ) {
						$add( 'WARN', "$name reads \"$key\" from its HTML, so the value in the comment is ignored. Put it in the HTML." );
						continue;
					}
					if ( isset( $allowed[ $name ][ $key ] ) && is_scalar( $value ) && ! in_array( (string) $value, $allowed[ $name ][ $key ], true ) ) {
						$add( 'WARN', "$name: \"$key\" is \"$value\", which is not one of: " . implode( ', ', array_map( static function ( $v ) { return '' === $v ? '(empty)' : $v; }, $allowed[ $name ][ $key ] ) ) . '.' );
					}
					if ( isset( $def['type'] ) && 'rich-text' !== $def['type'] ) {
						$valid = rest_validate_value_from_schema( $value, array_intersect_key( $def, array_flip( array( 'type', 'enum', 'items', 'properties' ) ) ), $key );
						if ( is_wp_error( $valid ) ) {
							$add( 'ERROR', "$name: " . $valid->get_error_message() );
						}
					}
				}

				$html = trim( $block['innerHTML'] );
				$tag  = static function ( $pattern ) use ( $html ) {
					return (bool) preg_match( $pattern, $html );
				};
				switch ( $name ) {
					case 'core/heading':
						$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;
						if ( ! $tag( '/^<h' . $level . '\b[^>]*class="[^"]*\bwp-block-heading\b/' ) ) {
							$add( 'WARN', "core/heading level $level should be <h$level class=\"wp-block-heading\">. The editor may call it invalid." );
						}
						break;
					case 'core/paragraph':
						if ( ! $tag( '/^<p[\s>]/' ) ) {
							$add( 'WARN', 'core/paragraph should be one <p> element.' );
						}
						break;
					case 'core/list':
						$list = empty( $block['attrs']['ordered'] ) ? 'ul' : 'ol';
						if ( ! $tag( '/^<' . $list . '\b[^>]*class="[^"]*\bwp-block-list\b/' ) ) {
							$add( 'WARN', "core/list should be <$list class=\"wp-block-list\">." );
						}
						break;
					case 'core/list-item':
						if ( ! $tag( '/^<li[\s>]/' ) ) {
							$add( 'WARN', 'core/list-item should be one <li> element.' );
						}
						break;
					case 'core/image':
						if ( ! $tag( '/^<figure\b[^>]*class="[^"]*\bwp-block-image\b/' ) ) {
							$add( 'WARN', 'core/image should be <figure class="wp-block-image ...">.' );
						}
						break;
				}

				if ( $block['innerBlocks'] ) {
					$walk( $block['innerBlocks'], array_merge( $chain, array( $name ) ) );
				}
			}
		};
		$walk( parse_blocks( $content ), array() );

		// 4. The rendered page.
		$previous = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		if ( $post ) {
			$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );
		}
		try {
			$rendered = do_blocks( $content );
		} catch ( Throwable $e ) {
			$add( 'ERROR', 'Rendering failed: ' . $e->getMessage() );
			return $issues;
		} finally {
			$GLOBALS['post'] = $previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			wp_reset_postdata();
		}

		$open_divs  = preg_match_all( '/<div[\s>]/i', $rendered );
		$close_divs = preg_match_all( '/<\/div>/i', $rendered );
		if ( $open_divs !== $close_divs ) {
			$add( 'ERROR', "The rendered page opens $open_divs <div>s and closes $close_divs. Some block's HTML is broken." );
		}

		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="utf-8"?><body>' . $rendered . '</body>' );
		libxml_clear_errors();
		$xp = new DOMXPath( $doc );

		$template = $template ? $template : ( $post ? get_page_template_slug( $post ) : '' );
		$has_title = ( 'page-no-title' !== $template );
		$last      = $has_title ? 1 : 0;
		foreach ( $xp->query( '//h1|//h2|//h3|//h4|//h5|//h6' ) as $h ) {
			$level = (int) substr( $h->nodeName, 1 );
			$text  = trim( preg_replace( '/\s+/', ' ', $h->textContent ) );
			if ( 1 === $level && $has_title ) {
				$add( 'WARN', "Heading 1 \"$text\": the page title is already the heading 1. Use heading 2, or the \"Page without title\" template." );
			} elseif ( 0 === $last && $level > 1 ) {
				$add( 'WARN', "Heading $level \"$text\" comes first, but this page has no title heading. Make the first heading a heading 1." );
			} elseif ( $level > $last + 1 ) {
				$add( 'WARN', "Heading $level \"$text\" skips a level (the one before is heading $last)." );
			}
			$last = $level;
		}

		foreach ( $xp->query( '//img[not(@alt)]' ) as $img ) {
			$add( 'ERROR', 'Image with no alt attribute: ' . $img->getAttribute( 'src' ) . '. Describe it, or use alt="" if it is decorative.' );
		}

		$named = static function ( $el ) use ( $xp ) {
			if ( trim( $el->textContent ) !== '' || trim( $el->getAttribute( 'aria-label' ) ) !== '' || $el->getAttribute( 'aria-labelledby' ) ) {
				return true;
			}
			return $xp->query( './/img[normalize-space(@alt)!=""]|.//svg[@aria-label]', $el )->length > 0;
		};
		foreach ( $xp->query( '//a[@href]' ) as $a ) {
			if ( ! $named( $a ) ) {
				$add( 'ERROR', 'Link with no text: ' . $a->getAttribute( 'href' ) . '.' );
			}
		}
		foreach ( $xp->query( '//button' ) as $b ) {
			if ( ! $named( $b ) ) {
				$add( 'ERROR', 'Button with no text or aria-label.' );
			}
		}

		foreach ( awt_skill_placeholders( $content ) as $placeholder ) {
			$add( 'WARN', "Placeholder $placeholder is still in the page. The owner fills it in before it is published." );
		}

		$ids = array();
		foreach ( $xp->query( '//*[@id]' ) as $el ) {
			$ids[ $el->getAttribute( 'id' ) ][] = 1;
		}
		foreach ( $ids as $id => $n ) {
			if ( count( $n ) > 1 ) {
				$add( 'ERROR', 'The id "' . $id . '" is used ' . count( $n ) . ' times. Ids must be unique.' );
			}
		}

		return $issues;
	}

	/**
	 * Placeholders like [Price] or [Customer quote] left in the content.
	 *
	 * @param string $content Block markup.
	 * @return string[]
	 */
	function awt_skill_placeholders( $content ) {
		preg_match_all( '/\[[A-Z][A-Za-z ]{1,40}\]/', wp_strip_all_tags( do_blocks( $content ) ), $found );
		return array_values( array_unique( $found[0] ) );
	}

	/**
	 * Load a published page the way a visitor does and confirm it shows this content.
	 * Returns [ 'OK'|'WARN', message ].
	 *
	 * The fingerprint is the longest run of plain words inside one element of the
	 * rendered content: punctuation is left out because the live page curls quotes
	 * and dashes, and it never spans two elements, so a plain text search of the
	 * page's HTML finds it too.
	 *
	 * @param WP_Post $post A published post or page.
	 * @return array
	 */
	function awt_skill_verify_live( $post ) {
		$url             = get_permalink( $post );
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		// Tags become spaces, so words in neighbouring elements never run together.
		$flatten = static function ( $html ) {
			$html = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', ' ', $html );
			$text = html_entity_decode( preg_replace( '/<[^>]+>/', ' ', $html ), ENT_QUOTES, 'UTF-8' );
			return trim( preg_replace( '/\s+/u', ' ', $text ) );
		};
		$rendered = do_blocks( $post->post_content );
		wp_reset_postdata();
		$pieces = html_entity_decode( preg_replace( '/<[^>]+>/', "\n", preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', "\n", $rendered ) ), ENT_QUOTES, 'UTF-8' );
		preg_match_all( '/[\p{L}\p{N}][\p{L}\p{N} ]{18,}[\p{L}\p{N}]/u', preg_replace( '/[ \t]+/u', ' ', $pieces ), $runs );
		if ( ! $runs[0] ) {
			return array( 'WARN', "Published. The page has too little text to check automatically: open $url and look at it." );
		}
		usort(
			$runs[0],
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);
		$finger = trim( mb_substr( $runs[0][0], 0, 60 ) );

		$fetch = static function ( $address ) use ( $finger, $flatten ) {
			$response = wp_remote_get(
				$address,
				array(
					'timeout'    => 20,
					// Some hosts block requests that do not look like a browser.
					'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36',
				)
			);
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}
			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $code ) {
				return "status $code";
			}
			return false !== mb_strpos( $flatten( wp_remote_retrieve_body( $response ) ), $finger );
		};

		$plain = $fetch( $url );
		if ( true === $plain ) {
			return array( 'OK', "The live page shows the new content: $url" );
		}
		$fresh = $fetch( add_query_arg( 'awt-check', time(), $url ) );
		if ( true === $fresh ) {
			return array( 'WARN', "The page is published, but visitors still get an old copy from the page cache. Clear the cache (see connecting.md), then check $url again." );
		}
		if ( is_string( $plain ) ) {
			return array( 'WARN', "Published, but the server could not load its own page ($plain). Open $url yourself and look for: \"$finger\"" );
		}
		return array( 'WARN', "Published, but the live page does not show the new content. Open $url and look for: \"$finger\"" );
	}
}

if ( defined( 'AWT_SKILL_CHECK_LIBRARY' ) ) {
	return;
}

$opts = array();
foreach ( isset( $args ) ? $args : array() as $a ) {
	$parts             = explode( '=', $a, 2 );
	$opts[ $parts[0] ] = isset( $parts[1] ) ? $parts[1] : '';
}

$target = null;
if ( isset( $opts['post'] ) ) {
	$target = get_post( (int) $opts['post'] );
	if ( ! $target ) {
		echo "No post with ID {$opts['post']}.\n";
		exit( 1 );
	}
	$content = $target->post_content;
} elseif ( isset( $opts['file'] ) ) {
	if ( ! is_readable( $opts['file'] ) ) {
		echo "Cannot read {$opts['file']}. Give the full path; ~ is not expanded here, so use \$HOME.\n";
		exit( 1 );
	}
	$content = file_get_contents( $opts['file'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
} else {
	echo "Usage: wp eval-file awt-check.php file=<path> [template=page-no-title]  or  post=<id>\n";
	exit( 1 );
}

$issues = awt_skill_check( $content, $target, isset( $opts['template'] ) ? $opts['template'] : '' );
$error_count = 0;
foreach ( $issues as $issue ) {
	echo "{$issue[0]}  {$issue[1]}\n";
	$error_count += ( 'ERROR' === $issue[0] ) ? 1 : 0;
}
$blocks = preg_match_all( '/<!--\s+wp:/', $content );
echo ( $error_count ? '✗' : '✓' ) . " $blocks blocks, $error_count errors, " . ( count( $issues ) - $error_count ) . " warnings.\n";
exit( $error_count ? 1 : 0 );
