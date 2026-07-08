<?php
/**
 * P11: dumps a random sample of real pdf_url/png_url values (single pages
 * + topic bundle PDFs) plus a sample of SEO title/meta pairs (P13) so an
 * external curl pass can verify downloads resolve and titles/metas look
 * unique/non-stuffed.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

echo "=== SAMPLE: 15 random coloring_page pdf_url/png_url ===\n";
$pages = get_posts( array( 'post_type' => 'coloring_page', 'post_status' => 'publish', 'posts_per_page' => 15, 'orderby' => 'rand' ) );
foreach ( $pages as $p ) {
	$pdf = get_post_meta( $p->ID, 'scp_pdf_url', true );
	$png = get_post_meta( $p->ID, 'scp_png_url', true );
	echo "$pdf\n$png\n";
}

echo "\n=== SAMPLE: 10 random coloring_topic pdf_all_url ===\n";
$topics = get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => 10, 'orderby' => 'rand' ) );
foreach ( $topics as $t ) {
	$pdf_all = get_post_meta( $t->ID, 'scp_pdf_all_url', true );
	echo "$pdf_all\n";
}

echo "\n=== SAMPLE: 12 random topic titles + meta (P13 uniqueness check) ===\n";
$topics2 = get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => 12, 'orderby' => 'rand' ) );
foreach ( $topics2 as $t ) {
	echo "TITLE: " . get_the_title( $t ) . "\n";
}

echo "\n=== SAMPLE: 12 random coloring_page titles + meta_description (P13) ===\n";
$pages2 = get_posts( array( 'post_type' => 'coloring_page', 'post_status' => 'publish', 'posts_per_page' => 12, 'orderby' => 'rand' ) );
foreach ( $pages2 as $p ) {
	$desc = get_post_meta( $p->ID, 'scp_meta_description', true );
	echo "TITLE: " . get_the_title( $p ) . "\n";
	echo "META: $desc\n\n";
}
