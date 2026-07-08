<?php
/**
 * Retry manifest for the single topic that failed on the first
 * merge_pdfs.py pass ("Stream has ended unexpectedly" -- a transient
 * network hiccup during a ~5262-download run, not a systemic bug).
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$slug = 'weather-chart-with-sun-and-rain-coloring-pages';
$topic = get_page_by_path( $slug, OBJECT, 'coloring_topic' );
if ( ! $topic ) { echo "Topic not found: $slug\n"; exit(1); }

$children = get_posts( array(
	'post_type'      => 'coloring_page',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'meta_key'       => 'scp_topic_id',
	'meta_value'     => $topic->ID,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
) );

// Cache-bust: one of these files was recently overwritten on origin after
// being served with a truncated body, and Cloudflare had already cached
// the broken response (Cf-Cache-Status: HIT on the old 65536-byte file).
// Appending a throwaway query string forces a fresh origin fetch.
$cb = time();
$pdf_urls = array();
foreach ( $children as $c ) {
	$url = get_post_meta( $c->ID, 'scp_pdf_url', true );
	if ( $url ) $pdf_urls[] = $url . '?cb=' . $cb;
}

$manifest = array( array(
	'topic_id' => $topic->ID,
	'slug'     => 'weather-chart-with-sun-and-rain',
	'pdf_urls' => $pdf_urls,
) );

if ( ! is_dir( '/content/output' ) ) mkdir( '/content/output', 0777, true );
file_put_contents( '/content/output/pdf_merge_manifest.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
echo "Retry manifest written: 1 topic, " . count( $pdf_urls ) . " pages\n";
