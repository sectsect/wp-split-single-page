<?php
/*
Plugin Name: WP Split Single Page
Plugin URI: https://github.com/sectsect/wp-split-single-page
Description: Supply some functions and Pagination for split single page for each array of custom field without <!--nextpage--> on your template.
Author: SECT INTERACTIVE AGENCY
Version: 1.1.0
Author URI: https://www.ilovesect.com/
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
		'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
		'format' => $format, // ?page=%#% : %#% is replaced by the page number
		'total' => $total,
		'current' => $current,
		'show_all' => false,
		'prev_next' => true,
		'prev_text' => __('&laquo; Previous'),
		'next_text' => __('Next &raquo;'),
		'end_size' => 1,
		'mid_size' => 2,
		'type' => 'plain',
		'add_args' => array(), // array of query args to add
		'add_fragment' => '',
		'before_page_number' => '',
		'after_page_number' => ''
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! is_array( $args['add_args'] ) ) {
		$args['add_args'] = array();
	}

	// Merge additional query vars found in the original URL into 'add_args' array.
	if ( isset( $url_parts[1] ) ) {
		// Find the format argument.
		$format = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
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

	// Who knows what else people pass in $args
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
	$add_args = $args['add_args'];
	$r = '';
	$page_links = array();
	$dots = false;

	if ( $args['prev_next'] && $current && 1 < $current ) :
		$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current - 1, $link );

		// add code
		$link = str_replace( '/1/', '/', $link );

		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
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
			$page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</span>";
			$dots = true;
		else :
			if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
			//	$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );

				// add code
				if(1 == $n){
					$link = str_replace( '%#%/', '', $args['base'] );
				}else{
					$link = str_replace( '%_%', $args['format'], $args['base'] );
				}

				$link = str_replace( '%#%', $n, $link );
				if ( $add_args )
					$link = add_query_arg( $add_args, $link );
				$link .= $args['add_fragment'];

				/** This filter is documented in wp-includes/general-template.php */
				$page_links[] = "<a class='page-numbers' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</a>";
				$dots = true;
			elseif ( $dots && ! $args['show_all'] ) :
				$page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
				$dots = false;
			endif;
		endif;
	endfor;
	if ( $args['prev_next'] && $current && ( $current < $total || -1 == $total ) ) :
		$link = str_replace( '%_%', $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current + 1, $link );
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$link .= $args['add_fragment'];

		/** This filter is documented in wp-includes/general-template.php */
		$page_links[] = '<a class="next page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['next_text'] . '</a>';
	endif;
	switch ( $args['type'] ) {
		case 'array' :
			return $page_links;

		case 'list' :
			$r .= "<ul class='page-numbers'>\n\t<li>";
			$r .= join("</li>\n\t<li>", $page_links);
			$r .= "</li>\n</ul>\n";
			break;

		default :
			$r = join("\n", $page_links);
			break;
	}
	return $r;
}

/**
 * pagination
 */
function single_paginate( $args = '' ) {
	if(!is_preview()){
		$args1 = array(
			'base'           => get_the_permalink() . '%#%/',
			'format'         => get_the_permalink() . '%#%/'
		);
	}else{
		$args1 = array(
			'base'           => get_the_permalink() . '&paged=%#%',
			'format'         => get_the_permalink() . '&paged=%#%'
		);
	}
	$args2 = array(
		'total'              => $pagecount,
		'current'            => $paged,
		'show_all'           => false,
		'end_size'           => 1,
		'mid_size'           => 2,
		'prev_next'          => true,
		'prev_text'          => __('&laquo; Previous'),
		'next_text'          => __('Next &raquo;'),
		'type'               => 'list',
		'add_args'           => false,
		'add_fragment'       => '',
		'before_page_number' => '',
		'after_page_number'  => ''
	);
	$args = array_merge($args1, $args2, $args);
	echo single_paginate_links($args);
}

/**
 * Get the Previous / Next Single Paged link
 */
function prev_single_paged_link($pagecount, $paged, $label) {
	$html = '<li class="prev">';
	if($paged == 1){
		$html .= '<span>' . $label . '</span>';
	}else{
		$prev = $paged - 1;
		if($prev == 1){
			$link = get_the_permalink();
		}else{
			$link = get_the_permalink() . $prev . '/';
		}
		$html .= '<a href="' . $link . '" rel="prev">' . $label . '</a>';
	}
	$html .= '</li>';

	if($html) {
		echo $html;
	}
}
function next_single_paged_link($pagecount, $paged, $label) {
	$html = '<li class="next">';
	if($paged == $pagecount){
		$html .= '<span>' . $label . '</span>';
	}else{
		$next = $paged + 1;
		$link = get_the_permalink() . $next . '/';
		$html .= '<a href="' . $link . '" rel="next">' . $label . '</a>';
	}
	$html .= '</li>';

	if($html) {
		echo $html;
	}
}
// ▼ USAGE
// prev_single_paged_link($pagecount, $paged, "PREV");
// next_single_paged_link($pagecount, $paged, "NEXT");

/**
 * Detect Single-Paged for split single-page
 */
function is_single_paged($page){
	global $wp_query;
	if(!is_preview()){
		$paged = (get_query_var('page')) ? get_query_var('page') : 1;
	}else{
		$pagenum = $_GET['paged'];
		$paged   = ($pagenum) ? $pagenum : 1;
		$wp_query->query = array('p' => $post->ID, 'page' => $paged, 'preview' => true, 'post_type' => get_post_type() );
		$wp_query->set('paged', $paged);
	}

	if(is_single() && $paged == $page){
		return true;
	}else{
		return false;
	}
}
?>
