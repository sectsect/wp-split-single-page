# ![Alt text](images/logo.jpg "SECT") WP Split Single Page - For Each Array of custom field -

### Supply some functions and Pagination for split single page for each array of custom field without `<!--nextpage-->` on your template.

#### Installation
- - -
 1. `cd /path-to-your/wp-content/plugins/`
 2. `git clone git@github.com:sectsect/wp-split-single-page.git`
 3. Activate the plugin through the 'Plugins' menu in WordPress.<br>
 That's it:ok_hand:

#### Notes
- - -
* Supports `is_preview()` Page. See [Usage Example](#usage-example).
* Supports Wordpress Plugin [Public Post Preview](https://github.com/ocean90/public-post-preview)

#### functions
- - -

| Function | Description |
| ------ | ----------- |
| `is_single_paged($num)`  | Detect the specific split page. ( Return: `boolean` ) |
| `single_paginate($args)` | Get the Pagination. ( Based on `paginate_links()` [Codex](https://codex.wordpress.org/Function_Reference/paginate_links) ) |
| `prev_single_paged_link($pagecount, $paged, $label, $type)` | Get the Previous Split Single Page link |
| `next_single_paged_link($pagecount, $paged, $label, $type)` | Get the Next Split Single Page link |

#### `single_paginate($args)`  
Default Arguments
``` php
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
**TIP:** `'base'` and `'format'` Silence is golden 👍

#### `prev_single_paged_link()` / `next_single_paged_link()`  
Default Arguments

* **label**
(string) (Optional) Link text to display.  
Default: `'Next'`

* **type**
(string) (optional) Controls format of the returned value.  
Possible values are:
 - **'plain'** - `<a href="#" rel="next">Next</a>`
 - **'list'** - `<li class="next"><a href="#" rel="next">Next</a></li>`

 Default: `'plain'`

### Usage Example
- - -

#### single.php
NOTE: Split the page every two arrays.
``` php
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

	<section class="prev-next">
		<ul>
			<?php
				prev_single_paged_link($pagecount, $paged, "PREV", "list");
				next_single_paged_link($pagecount, $paged, "NEXT", "list");
			?>
		</ul>
	</section>
</article>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
```

### Change log  
 * **1.2.0** - Add Support Wordpress Plugin [Public Post Preview](https://github.com/ocean90/public-post-preview)
 * **1.1.0** - Add New functions `prev_single_paged_link()` and `next_single_paged_link()`
 * **1.0.0** - Initial Release

### License
See [LICENSE](https://github.com/sectsect/wp-split-single-page/blob/master/LICENSE) file.
