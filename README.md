# ![Alt text](images/logo.jpg "SECT") WP Split Single Page <span style="font-size: 18px;">- For Each Array of custom field -</span>

### Supply some functions and Pagination for split single page for each array of custom field without `<!--nextpage-->` on your template.

#### Installation
- - -
 1. `cd /path-to-your/wp-content/plugins/`
 2. `git clone git@github.com:sectsect/wp-split-single-page.git`
 3. Activate the plugin through the 'Plugins' menu in WordPress.

 That's it:ok_hand:

#### functions
- - -
* `is_single_paged($num)`	- Detect the specific split page. (`boolean`)

* `single_paginate($args)`	- Output the pagination. (Based on `paginate_links()` [Codex](https://codex.wordpress.org/Function_Reference/paginate_links))  

	##### Default Arguments
	```
<?php
	$args = array(
		'base'               => get_the_permalink() . '%#%/',	// (is_preview()) get_the_permalink() . '&paged=%#%'
		'format'             => get_the_permalink() . '%#%/',	// (is_preview()) get_the_permalink() . '&paged=%#%'
		'total'              => 1,
		'current'            => 0,
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
?>
	```
	##### NOTE:
	`'base'` and `'format'` Silence is golden 👍

### Usage Example
- - -

#### single.php
NOTE: Split the page every two arrays.

	<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<article>
		<h1><?php the_title(); ?></h1>

		<?php if (is_single_paged(1)): ?>
			<section>
				something...
			</section>
		<?php endif; ?>

		<?php
			$fields    = CFS()->get('section');
			$fields    = array_chunk($fields, 2);
			if(!is_preview()){
				$paged   = (get_query_var('page')) ? get_query_var('page') : 1;
			}else{
				$pagenum = $_GET['paged'];
				$paged   = ($pagenum) ? $pagenum : 1;
			}
			$key       = $paged - 1;    // "-1" For Array's key
			$pagecount = count($fields);
			$fields    = $fields[$key];
			foreach ($fields as $field):
		?>
			<section>
				<?php if ($field['h2']): ?>
					<h2><?php echo $field['h2']; ?></h2>
				<?php endif; ?>

				<?php if ($field['h3']): ?>
					<h3><?php echo $field['h3']; ?></h3>
				<?php endif; ?>

				<?php if ($field['text']): ?>
					<?php echo $field['text']; ?>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>

		<section class="pagenation">
			<?php
				if(function_exists('single_paginate')){
					$args = array(
						'total'    => $pagecount,
						'current'  => $paged
					);
					single_paginate($args);
				}
			?>
		</section>
	</article>

	<?php endwhile; endif; ?>

	<?php get_footer(); ?>


### Change log  
 * **1.0.0** - Initial Release

### License
See [LICENSE](https://github.com/sectsect/wp-split-single-page/blob/master/LICENSE) file.
