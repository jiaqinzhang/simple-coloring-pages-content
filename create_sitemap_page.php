<?php
/**
 * Creates the /sitemap/ WP Page so page-sitemap.php (the HTML sitemap
 * template) has a post to attach to. Content is empty -- the template
 * renders everything dynamically from live category/topic data.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$existing = get_page_by_path( 'sitemap', OBJECT, 'page' );
$post_args = array(
	'post_type'    => 'page',
	'post_title'   => 'Sitemap',
	'post_name'    => 'sitemap',
	'post_content' => '',
	'post_status'  => 'publish',
);
if ( $existing ) {
	$post_args['ID'] = $existing->ID;
	$id = wp_update_post( $post_args );
	echo "Updated: sitemap (ID $id)\n";
} else {
	$id = wp_insert_post( $post_args );
	echo "Created: sitemap (ID $id)\n";
}
