<?php
/**
 * Dumps a JSON manifest for the 396 topics still missing scp_pdf_all_url:
 * { topic_slug (without -coloring-pages suffix), page_pdf_urls (ordered
 * by menu_order) }. A separate Python job reads this, downloads each
 * page's individual PDF, merges them, and writes the result into the
 * wp_data volume -- this script only reads, does not write anything.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$topics = get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => -1 ) );

$manifest = array();
foreach ( $topics as $t ) {
	$existing = get_post_meta( $t->ID, 'scp_pdf_all_url', true );
	if ( $existing ) continue; // already has one, skip

	$children = get_posts( array(
		'post_type'      => 'coloring_page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => 'scp_topic_id',
		'meta_value'     => $t->ID,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );
	if ( ! $children ) continue;

	$pdf_urls = array();
	foreach ( $children as $c ) {
		$url = get_post_meta( $c->ID, 'scp_pdf_url', true );
		if ( $url ) $pdf_urls[] = $url;
	}
	if ( ! $pdf_urls ) continue;

	$slug = preg_replace( '/-coloring-pages$/', '', $t->post_name );
	$manifest[] = array(
		'topic_id'   => $t->ID,
		'slug'       => $slug,
		'pdf_urls'   => $pdf_urls,
	);
}

file_put_contents( '/content/repo/pdf_merge_manifest.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
echo "Manifest written: " . count( $manifest ) . " topics\n";
$total_pages = array_sum( array_map( function( $m ) { return count( $m['pdf_urls'] ); }, $manifest ) );
echo "Total individual PDFs to merge: $total_pages\n";
