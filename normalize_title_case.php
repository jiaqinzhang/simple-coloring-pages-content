<?php
/**
 * Normalizes post_title on coloring_topic + coloring_page posts: lowercase
 * small words (articles/conjunctions/prepositions) unless they're the
 * first word, and fix the "X'S" broken-possessive-capitalization pattern.
 * Only touches post_title (display text) -- post_name/slug is untouched,
 * so no URL, routing, or the already-verified page-count logic changes.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$small_words = array( 'a', 'an', 'the', 'and', 'but', 'or', 'for', 'nor', 'on', 'at', 'to', 'in', 'with', 'from', 'of', 'as', 'into', 'onto' );

function scp_normalize_title( $title, $small_words ) {
	$words = explode( ' ', $title );
	foreach ( $words as $i => $w ) {
		if ( $i === 0 ) continue; // never touch the first word
		$lower = strtolower( $w );
		if ( in_array( $lower, $small_words, true ) ) {
			$words[ $i ] = $lower;
		}
	}
	$title = implode( ' ', $words );
	// Fix "Bird's" -> was capitalized as "Bird'S" (straight or curly apostrophe).
	$title = preg_replace_callback( "/([a-z])(['\x{2019}])([A-Z])\\b/u", function( $m ) {
		return $m[1] . $m[2] . strtolower( $m[3] );
	}, $title );
	return $title;
}

$dry_run = getenv( 'DRY_RUN' ) === '1';

$post_types = array( 'coloring_topic', 'coloring_page' );
$total_checked = 0;
$total_changed = 0;
$examples = array();

foreach ( $post_types as $pt ) {
	$ids = get_posts( array( 'post_type' => $pt, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
	foreach ( $ids as $id ) {
		$total_checked++;
		$title = get_the_title( $id );
		$new_title = scp_normalize_title( $title, $small_words );
		if ( $new_title !== $title ) {
			$total_changed++;
			if ( count( $examples ) < 15 ) {
				$examples[] = "$title  ->  $new_title";
			}
			if ( ! $dry_run ) {
				wp_update_post( array( 'ID' => $id, 'post_title' => $new_title ) );
			}
		}
	}
}

echo ( $dry_run ? "[DRY RUN] " : "" ) . "Checked: $total_checked\n";
echo ( $dry_run ? "[DRY RUN] " : "" ) . "Changed: $total_changed\n";
foreach ( $examples as $e ) echo "  $e\n";
