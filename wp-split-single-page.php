<?php
/**
 * Plugin Name:     WP Split Single Page
 * Plugin URI:      https://github.com/sectsect/wp-split-single-page
 * Description:     Supply some functions and Pagination for split single page for each array of custom field without <!--nextpage--> on your template.
 * Author:          SECT INTERACTIVE AGENCY
 * Author URI:      https://www.ilovesect.com/
 * Version:         1.3.0
 *
 * @package         WP_Split_Single_Page
 */

/**
 * Determine if last character of permalink ends with a slash.
 *
 * @return boolean     "description".
 */
function is_perm_trailingslash() {
	if ( get_option( 'permalink_structure' ) != '' ) {
		$laststr = substr( get_option( 'permalink_structure' ), -1 );
		if ( '/' === $laststr ) {
			$return = true;
		} else {
			$return = false;
		}
	} else {
		$return = false;
	}

	return $return;
}

/**
 * Add slash before page num.
 *
 * @param [type] $link "description".
 */
function add_slash_before_page_num( $link ) {
	if ( ! is_perm_trailingslash() ) {
		$link = str_replace( '%#%', '/%#%', $link );
	}

	return $link;
}

/**
 * Add slash before page num from any.
 *
 * @param [type] $num "description".
 */
function add_slash_before_page_num_from_any( $num ) {
	if ( ! is_perm_trailingslash() ) {
		$num = '/' . $num;
	}

	return $num;
}

/**
 * Single Page Num Links.
 *
 * @param  string $args "description".
 * @return html       "description".
 */
function single_paginate_links( $args = '' ) {
	global $wp_query, $wp_rewrite;

	// Setting up default values based on the current URL.
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$url_parts    = explode( '?', $pagenum_link );

	// Get max pages and current page out of the current query, if available.
	$total   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
	$current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

	// Append the format placeholder to the base URL.
	$pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';

	// URL base depends on permalink settings.
	$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

	$defaults = array(
		'base'               => $pagenum_link, // CKECK http://example.com/all_posts.php%_% : %_% is replaced by format (below) !
		'format'             => $format, // "?page=%#%" "%#%" is replaced by the page number !
		'total'              => $total,
		'current'            => $current,
		'show_all'           => false,
		'prev_next'          => true,
		'prev_text'          => __( '&laquo; Previous' ),
		'next_text'          => __( 'Next &raquo;' ),
		'end_size'           => 1,
		'mid_size'           => 2,
		'type'               => 'plain',
		'add_args'           => array(), // CHECK array of query args to add !
		'add_fragment'       => '',
		'before_page_number' => '',
		'after_page_number'  => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! is_array( $args['add_args'] ) ) {
		$args['add_args'] = array();
	}

	// Merge additional query vars found in the original URL into 'add_args' array.
	if ( isset( $url_parts[1] ) ) {
		// Find the format argument.
		$format       = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
		$format_query = isset( $format[1] ) ? $format[1] : '';
		wp_parse_str( $format_query, $format_args );

		// Find the query args of the requested URL.
		wp_parse_str( $url_parts[1], $url_query_args );

		// Remove the format argument from the array of query arguments, to avoid overwriting custom format.
		foreach ( $format_args as $format_arg => $format_arg_value ) {
			unset( $url_query_args[ $format_arg ] );
		}

		$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
	}

	// Who knows what else people pass in $args !
	$total = (int) $args['total'];
	if ( $total < 2 ) {
		return;
	}
	$current  = (int) $args['current'];
	$end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
	if ( $end_size < 1 ) {
		$end_size = 1;
	}
	$mid_size = (int) $args['mid_size'];
	if ( $mid_size < 0 ) {
		$mid_size = 2;
	}
	$add_args   = $args['add_args'];
	$r          = '';
	$page_links = array();
	$dots       = false;

	if ( $args['prev_next'] && $current && 1 < $current ) :
		$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );

		// If the last character of the permalink setting is anything other than a slash, add a slash !
		$link = add_slash_before_page_num( $link );

		if ( is_preview() ) {
			if ( is_perm_trailingslash() ) {
				$target = '%#%';
			} else {
				$target = '/%#%';
			}
		} else {
			$target = '%#%';
		}

		$link = str_replace( $target, $current - 1, $link );

		// Add code
		// For Plugin "Public Post Preview" !
		if ( isset( $_GET['p'] ) && isset( $_GET['_ppp'] ) ) {
			$link = str_replace( '&paged=1', '', $link );
		} else {
			if ( is_perm_trailingslash() ) {
				$target   = '/1/';
				$replaced = '/';
			} else {
				$target   = '/1';
				$replaced = '';
			}
			$link = str_replace( $target, $replaced, $link );
		}

		if ( $add_args ) {
			$link = add_query_arg( $add_args, $link );
		}
		$link .= $args['add_fragment'];

		/**
		 * Filter the paginated links for the given archive pages.
		 *
		 * @since 3.0.0
		 *
		 * @param string $link The paginated link URL.
		 */
		$page_links[] = '<a class="prev page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['prev_text'] . '</a>';
	endif;
	for ( $n = 1; $n <= $total; $n++ ) :
		if ( $n == $current ) :
			$page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . '</span>';
			$dots         = true;
		else :
			if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
				// Add code !
				if ( 1 == $n ) {
					// For Plugin "Public Post Preview" !
					if ( is_preview() ) {
						$link = str_replace( '&paged=%#%', '', $args['base'] );
					} else {
						if ( is_perm_trailingslash() ) {
							$target = '%#%/';
						} else {
							$target = '%#%';
						}
						$link = str_replace( $target, '', $args['base'] );
					}
				} else {
					$link = str_replace( '%_%', $args['format'], $args['base'] );
				}

				if ( ! is_preview() ) {
					// If the last character of the permalink setting is anything other than a slash, add a slash !
						$link = add_slash_before_page_num( $link );
				}

					$link = str_replace( '%#%', $n, $link );
				if ( $add_args ) {
					$link = add_query_arg( $add_args, $link );
				}
					$link .= $args['add_fragment'];

					/** This filter is documented in wp-includes/general-template.php */
					$page_links[] = "<a class='page-numbers' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . '</a>';
					$dots         = true;
			elseif ( $dots && ! $args['show_all'] ) :
				$page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
				$dots         = false;
			endif;
		endif;
	endfor;
	if ( $args['prev_next'] && $current && ( $current < $total || -1 == $total ) ) :
		$link = str_replace( '%_%', $args['format'], $args['base'] );

		// If the last character of the permalink setting is anything other than a slash, add a slash !
		$link = add_slash_before_page_num( $link );

		if ( is_preview() ) {
			if ( is_perm_trailingslash() ) {
				$target = '%#%';
			} else {
				$target = '/%#%';
			}
		} else {
			$target = '%#%';
		}

		$link = str_replace( $target, $current + 1, $link );

		if ( $add_args ) {
			$link = add_query_arg( $add_args, $link );
		}
		$link .= $args['add_fragment'];

		/** This filter is documented in wp-includes/general-template.php */
		$page_links[] = '<a class="next page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['next_text'] . '</a>';
	endif;
	switch ( $args['type'] ) {
		case 'array':
			return $page_links;

		case 'list':
			$r .= "<ul class='page-numbers'>\n\t<li>";
			$r .= join( "</li>\n\t<li>", $page_links );
			$r .= "</li>\n</ul>\n";
			break;

		default:
			$r = join( "\n", $page_links );
			break;
	}
	return $r;
}


/**
 * Single Paginate
 *
 * @param  string $args "description".
 * @return void
 */
function single_paginate( $args = '' ) {
	if ( ! is_preview() ) {
		if ( is_perm_trailingslash() ) {
			$basestr = '%#%/';
		} else {
			$basestr = '%#%';
		}
		$args1 = array(
			'base'   => get_the_permalink() . $basestr,
			'format' => get_the_permalink() . $basestr,
		);
	} else {
		// For Plugin "Public Post Preview" !
		if ( isset( $_GET['p'] ) && isset( $_GET['_ppp'] ) ) {
			$args1 = array(
				'base'   => home_url( '/' ) . '?p=' . wp_unslash( $_GET['p'] ) . '&preview=1&_ppp=' . wp_unslash( $_GET['_ppp'] ) . '&paged=%#%',
				'format' => home_url( '/' ) . '?p=' . wp_unslash( $_GET['p'] ) . '&preview=1&_ppp=' . wp_unslash( $_GET['_ppp'] ) . '&paged=%#%',
			);
		} else {
			$args1 = array(
				'base'   => get_the_permalink() . '&paged=%#%',
				'format' => get_the_permalink() . '&paged=%#%',
			);
		}
	}
	$args2 = array(
		'total'              => 1,
		'current'            => 1,
		'show_all'           => false,
		'end_size'           => 1,
		'mid_size'           => 2,
		'prev_next'          => true,
		'prev_text'          => __( '&laquo; Previous' ),
		'next_text'          => __( 'Next &raquo;' ),
		'type'               => 'list',
		'add_args'           => false,
		'add_fragment'       => '',
		'before_page_number' => '',
		'after_page_number'  => '',
	);
	$args  = array_merge( $args1, $args2, $args );
	echo single_paginate_links( $args );
}


/**
 * Get the Previous / Next Single Paged link
 *
 * @param  string $pagecount "description".
 * @param  string $paged     "description".
 * @param  string $label     "description".
 * @param  string $type      "description".
 * @return void            "description".
 */
function prev_single_paged_link( $pagecount, $paged, $label = 'Prev', $type = 'plain' ) {
	$html = '';
	if ( 'list' === $type ) {
		$html .= '<li class="prev">';
	}
	if ( 1 === (int) $paged ) {
		$html .= '<span>' . $label . '</span>';
	} else {
		$prev = (int) $paged - 1;
		if ( 1 === $prev ) {
			if ( ! is_preview() ) {
				$link = get_the_permalink();
			} else {
				// For Plugin "Public Post Preview" !
				if ( isset( $_GET['p'] ) && isset( $_GET['_ppp'] ) ) {
					$link = home_url( '/' ) . '?p=' . wp_unslash( $_GET['p'] ) . '&preview=1&_ppp=' . wp_unslash( $_GET['_ppp'] );
				} else {
					$link = get_the_permalink() . '&preview=true';
				}
				// For Plugin "CF Preview Fix" !
				if ( isset( $_GET['post_date'] ) && isset( $_GET['preview_time'] ) ) {
					$link .= '&post_date=' . wp_unslash( $_GET['post_date'] ) . '&preview_time=' . wp_unslash( $_GET['preview_time'] );
				}
			}
		} else {
			if ( ! is_preview() ) {
				// If the last character of the permalink setting is anything other than a slash, add a slash !
				$prev = add_slash_before_page_num_from_any( $prev );

				$link = get_the_permalink() . $prev;
				if ( is_perm_trailingslash() ) {
					$link .= '/';
				}
			} else {
				// For Plugin "Public Post Preview" !
				if ( isset( $_GET['p'] ) && isset( $_GET['_ppp'] ) ) {
					$link = home_url( '/' ) . '?p=' . wp_unslash( $_GET['p'] ) . '&preview=1&_ppp=' . wp_unslash( $_GET['_ppp'] ) . '&paged=' . $prev;
				} else {
					$link = get_the_permalink() . '&paged=' . $prev . '&preview=true';
				}
				// For Plugin "CF Preview Fix" !
				if ( isset( $_GET['post_date'] ) && isset( $_GET['preview_time'] ) ) {
					$link .= '&post_date=' . wp_unslash( $_GET['post_date'] ) . '&preview_time=' . wp_unslash( $_GET['preview_time'] );
				}
			}
		}
		$html .= '<a href="' . $link . '" rel="prev">' . $label . '</a>';
	}
	if ( 'list' === $type ) {
		$html .= '</li>';
	}

	if ( $html ) {
		echo $html;
	}
}
/**
 * Next Single Paged Link
 *
 * @param  string $pagecount "description".
 * @param  string $paged     "description".
 * @param  string $label     "description".
 * @param  string $type      "description".
 * @return void            "description".
 */
function next_single_paged_link( $pagecount, $paged, $label = 'Next', $type = 'plain' ) {
	$html = '';
	if ( 'list' === $type ) {
		$html .= '<li class="next">';
	}
	if ( (int) $paged === (int) $pagecount ) {
		$html .= '<span>' . $label . '</span>';
	} else {
		$next = (int) $paged + 1;
		if ( ! is_preview() ) {
			// If the last character of the permalink setting is anything other than a slash, add a slash !
			$next = add_slash_before_page_num_from_any( $next );

			$link = get_the_permalink() . $next;
			if ( is_perm_trailingslash() ) {
				$link .= '/';
			}
		} else {
			// For Plugin "Public Post Preview" !
			if ( isset( $_GET['p'] ) && isset( $_GET['_ppp'] ) ) {
				$link = home_url( '/' ) . '?p=' . wp_unslash( $_GET['p'] ) . '&preview=1&_ppp=' . wp_unslash( $_GET['_ppp'] ) . '&paged=' . $next;
			} else {
				$link = get_the_permalink() . '&paged=' . $next . '&preview=true';
			}
			// For Plugin "CF Preview Fix" !
			if ( isset( $_GET['post_date'] ) && isset( $_GET['preview_time'] ) ) {
				$link .= '&post_date=' . wp_unslash( $_GET['post_date'] ) . '&preview_time=' . wp_unslash( $_GET['preview_time'] );
			}
		}
		$html .= '<a href="' . $link . '" rel="next">' . $label . '</a>';
	}
	if ( 'list' === $type ) {
		$html .= '</li>';
	}

	if ( $html ) {
		echo $html;
	}
}

/**
 * Detect Single-Paged for split single-page
 *
 * @param  [type] $page "description".
 * @return boolean       "description".
 */
function is_single_paged( $page ) {
	global $wp_query, $post;
	if ( ! is_preview() ) {
		$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
	} else {
		$pagenum = '';
		if ( isset( $_GET['paged'] ) ) {
			$pagenum = wp_unslash( $_GET['paged'] );
			$pagenum = (int) $pagenum;
		}
		$paged           = ( $pagenum ) ? $pagenum : 1;
		$wp_query->query = array(
			'p'         => $post->ID,
			'page'      => $paged,
			'preview'   => true,
			'post_type' => get_post_type(),
		);
		$wp_query->set( 'paged', $paged );
	}

	if ( is_single() && $paged === (int) $page ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Add Prev and Next Tags to Paginated Single Posts
 *
 * @param  [type] $num_pages "description".
 * @return void       "description".
 */
function add_rel_prev_next_paginated_posts( $num_pages ) {
	global $post;
	$paged = intval( get_query_var( 'paged' ) );
	if ( is_single() && ! is_preview() ) {
		$perm_link = get_permalink();
		if ( $num_pages > 1 ) {
			$page = intval( get_query_var( 'page' ) );
			if ( 0 == $page ) {
				$page = 1;
			}
			if ( ( $page > 1 ) && ( $page <= $num_pages ) ) {
				$prev_page_num = ( $page - 1 );
				if ( 2 == $page ) {
					$prev_page_num = '';
				}
				$full_url = user_trailingslashit( trailingslashit( $perm_link ) . $prev_page_num );
				echo '<link rel="prev" href="' . esc_url( $full_url ) . '" />';
			}
			if ( ( $page >= 1 ) && ( $page < $num_pages ) ) {
				$nxt_page_num = ( $page + 1 );
				$full_url     = user_trailingslashit( trailingslashit( $perm_link ) . $nxt_page_num );
				echo '<link rel="next" href="' . esc_url( $full_url ) . '" />';
			}
		}
	}
}

/**
 * A copy of rel_canonical but to allow an override on a custom tag
 *
 * @return html       "description".
 */
function rel_canonical_with_custom_tag_override() {
	if ( ! is_singular() ) {
		return;
	}
	global $post;
	global $wp_the_query;
	$id = $post->ID;
	if ( $id != $wp_the_query->get_queried_object_id() ) {
		return;
	}
	$link = get_permalink( $id );
	echo "<link rel='canonical' href='" . esc_url( $link ) . "' />\n";
}

if ( function_exists( 'rel_canonical' ) ) {
	remove_action( 'wp_head', 'rel_canonical' );
}
add_action( 'wp_head', 'rel_canonical_with_custom_tag_override' );
