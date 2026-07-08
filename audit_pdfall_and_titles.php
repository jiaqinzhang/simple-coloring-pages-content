<?php
/**
 * Follow-up audit triggered by a random sample showing 8/10 topics with a
 * blank scp_pdf_all_url, plus a widespread title-casing issue (small words
 * capitalized, and a "Bird'S Nest" broken-possessive case). Quantifies
 * both across the full published set before deciding on a fix.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

global $wpdb;

$topics = get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$total = count( $topics );
$blank = 0;
$non_blank_examples = array();
foreach ( $topics as $tid ) {
	$url = get_post_meta( $tid, 'scp_pdf_all_url', true );
	if ( ! $url ) {
		$blank++;
	} elseif ( count( $non_blank_examples ) < 3 ) {
		$non_blank_examples[] = get_the_title( $tid ) . ' -> ' . $url;
	}
}
echo "=== scp_pdf_all_url audit ===\n";
echo "Total topics: $total\n";
echo "Blank pdf_all_url: $blank\n";
echo "Non-blank: " . ( $total - $blank ) . "\n";
foreach ( $non_blank_examples as $e ) echo "  e.g. $e\n";

// Title-casing scan: count titles containing a capitalized small word
// that should be lowercase mid-title (And/Of/On/At/With/From/A/The/etc),
// and count the "X'S" broken-possessive pattern specifically.
$small_words = array('A','An','The','And','Or','But','Nor','For','On','At','To','In','With','From','Of','As');
$pattern = '/\b(' . implode('|', $small_words) . ')\b/';
$apostrophe_s_pattern = "/[a-z]['\x{2019}]S\b/u";

$title_case_hits = 0;
$apostrophe_hits = 0;
$examples = array();
$apostrophe_examples = array();

$all_titles = array_merge(
	get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) ),
	get_posts( array( 'post_type' => 'coloring_page', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) )
);
$checked = 0;
foreach ( $all_titles as $id ) {
	$title = get_the_title( $id );
	$checked++;
	// Only flag if a small word appears NOT at the start of the title.
	$words = explode( ' ', $title );
	for ( $i = 1; $i < count( $words ); $i++ ) {
		if ( in_array( $words[$i], $small_words, true ) ) {
			$title_case_hits++;
			if ( count( $examples ) < 8 ) $examples[] = $title;
			break;
		}
	}
	if ( preg_match( $apostrophe_s_pattern, $title ) ) {
		$apostrophe_hits++;
		if ( count( $apostrophe_examples ) < 5 ) $apostrophe_examples[] = $title;
	}
}
echo "\n=== Title-casing audit (topics + pages, $checked total titles checked) ===\n";
echo "Titles with a capitalized small word mid-title: $title_case_hits\n";
foreach ( $examples as $e ) echo "  e.g. $e\n";
echo "\nTitles with broken possessive (X'S): $apostrophe_hits\n";
foreach ( $apostrophe_examples as $e ) echo "  e.g. $e\n";
