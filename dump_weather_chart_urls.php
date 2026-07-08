<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$topic = get_page_by_path( 'weather-chart-with-sun-and-rain-coloring-pages', OBJECT, 'coloring_topic' );
$children = get_posts( array(
	'post_type'      => 'coloring_page',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'meta_key'       => 'scp_topic_id',
	'meta_value'     => $topic->ID,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
) );
foreach ( $children as $c ) {
	echo get_post_meta( $c->ID, 'scp_pdf_url', true ) . "\n";
}
