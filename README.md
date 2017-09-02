# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="28" height="auto"> WP Split Single Page
[![Build Status](https://travis-ci.org/sectsect/wp-split-single-page.svg?branch=master)](https://travis-ci.org/sectsect/wp-split-single-page) [![PHP-Eye](https://php-eye.com/badge/sectsect/wp-split-single-page/tested.svg?style=flat)](https://php-eye.com/package/sectsect/wp-split-single-page) [![Latest Stable Version](https://poser.pugx.org/sectsect/wp-split-single-page/v/stable)](https://packagist.org/packages/sectsect/wp-split-single-page) [![License](https://poser.pugx.org/sectsect/wp-split-single-page/license)](https://packagist.org/packages/sectsect/wp-split-single-page)
#### \- For Each Array of custom field -

### Supply some functions and Pagination for split single page for each array of custom field without `<!--nextpage-->` on your template.

## Installation

##### 1. Clone this Repo into your `wp-content/plugins` directory.
```
$ cd /path-to-your/wp-content/plugins/
$ git clone git@github.com:sectsect/wp-split-single-page.git
```
##### 2. Activate the plugin through the 'Plugins' menu in WordPress.<br>
 That's it:ok_hand:

## Notes

- Supports `is_preview()` Page. See [Usage Example](#usage-example).
- Supports Wordpress Plugin [Public Post Preview](https://github.com/ocean90/public-post-preview)
- Supports Wordpress Plugin [CF Preview Fix](https://wordpress.org/plugins/cf-preview-fix/) for Cloudfront
:memo: You need to manually add the following two parameters to the URL output by CF Preview Fix.
```
&post_date=20171231021559&preview_time=20171231021604
```

## functions

| Function | Description |
| ------ | ----------- |
| `is_single_paged($num)`  | Detect the specific splitted page number. <br>( Return: `boolean` ) |
| `single_paginate($args)` | Get the Pagination. <br>( Based on `paginate_links()` [Codex](https://codex.wordpress.org/Function_Reference/paginate_links) ) |
| `prev_single_paged_link($pagecount, $paged, $label, $type)` | Get the Previous Split Single Page link |
| `next_single_paged_link($pagecount, $paged, $label, $type)` | Get the Next Split Single Page link |

#### `single_paginate($args)`
Default Arguments
``` php
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
    'after_page_number'  => '',
);
```
**TIP:** `'base'` and `'format'` Silence is golden 👍

#### `next_single_paged_link($pagecount, $paged, $label, $type)`
##### Parameters

* **pagecount**
`(integer)` The total number of pages.

* **paged**
`(integer)` The current page number.

* **label**
`(string)` (Optional) Link text to display.
Default: `'Next'`

* **type**
`(string)` (Optional) Controls format of the returned value.
Possible values are:
   - **'plain'** - `<a href="#" rel="next">Next</a>`
   - **'list'** - `<li class="next"><a href="#" rel="next">Next</a></li>`

   Default: `'plain'`

## Usage Example

#### single.php
NOTE: Split the page every two arrays (w/ [Custom Field Suite](https://wordpress.org/plugins/custom-field-suite/) Plugin).
``` php
<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<article>
    <h1><?php the_title(); ?></h1>

    <?php if ( is_single_paged(1) ): ?>
        <section>
            The first page only.
        </section>
    <?php endif; ?>

    <?php
        $fields = CFS()->get('section');            // Get the array of Loop-field
        $fields = array_values( (array) $fields );   // Renumbering Array Keys
        $fields = array_chunk( (array) $fields, 2 ); // array_chunk
        if ( ! is_preview() ) {
            $paged   = ( get_query_var('page') ) ? get_query_var('page') : 1;
        } else {
            if ( isset( $_GET['paged'] ) ) {
                $pagenum = (int) wp_unslash( $_GET['paged'] );
            }
            $paged   = ( $pagenum ) ? $pagenum : 1;
        }
        $key       = $paged - 1;    // "-1" For Array's key
        $pagecount = count( $fields );
        $fields    = $fields[$key];
        foreach ( $fields as $field ):
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

    <?php if ( is_single_paged($pagecount) ): ?>
        <section>
            The last page only.
        </section>
    <?php endif; ?>

    <section class="pagenation">
        <?php
            if ( function_exists('single_paginate') ) {
                $args = array(
                    'total'    => $pagecount,
                    'current'  => $paged,
                );
                single_paginate($args);
            }
        ?>
    </section>

    <section class="prev-next">
        <ul>
            <?php
                prev_single_paged_link( $pagecount, $paged, "PREV", "list" );
                next_single_paged_link( $pagecount, $paged, "NEXT", "list" );
            ?>
        </ul>
    </section>
</article>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
```

## Change log
 * **1.2.7** - Improve some codes for comparing same types
 * **1.2.6** - :bug: Fix bug in function `is_single_paged()`
 * **1.2.5** - :bug: Fix bug for navigation links when the permalink setting has no Trailing Slash
 * **1.2.4** - :bug: Fix PHP Notice for Undefined variable
 * **1.2.3** - Add PHP Unit Testing w/phpunit via TravisCI
 * **1.2.2** - Add support that permalink setting has no Trailing Slash. And Support Plugin [CF Preview Fix](https://wordpress.org/plugins/cf-preview-fix/) for Cloudfront (w/ conditions).
 * **1.2.1** - Add composer.json
 * **1.2.0** - Add Support Wordpress Plugin [Public Post Preview](https://github.com/ocean90/public-post-preview)
 * **1.1.0** - Add New functions `prev_single_paged_link()` and `next_single_paged_link()`
 * **1.0.0** - :tada: Initial Release

  See [CHANGELOG](https://github.com/sectsect/wp-split-single-page/blob/master/CHANGELOG.md) file.

## License
See [LICENSE](https://github.com/sectsect/wp-split-single-page/blob/master/LICENSE) file.
