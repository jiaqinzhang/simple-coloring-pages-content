<?php
/**
 * P12 follow-up: exhaustive (not sampled) English-quality scan across
 * every coloring_page and coloring_topic text field currently live in
 * the DB. Checks: repeated words, double spaces, a/an agreement,
 * doubled punctuation, missing space after punctuation, trailing
 * comma-before-period artifacts. Read-only -- reports only, does not
 * write. Designed to run once and produce a fix-list for a follow-up
 * targeted patch script.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$VOWEL_SOUND_EXCEPTIONS = array( 'university', 'unicorn', 'unique', 'user', 'usual', 'european', 'one', 'once' );
$SILENT_H_WORDS = array( 'hour', 'honest', 'honor' );

function scp_check_text( $text, $field_label, $post_id, $post_title, &$issues, $VOWEL_SOUND_EXCEPTIONS, $SILENT_H_WORDS ) {
	if ( ! $text || ! is_string( $text ) ) return;

	// 1. Repeated word ("the the", "cute cute").
	if ( preg_match( '/\b(\w+)(\s+\1)+\b/i', $text, $m ) ) {
		$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'repeated_word', 'detail' => $m[0] );
	}

	// 2. Double space.
	if ( strpos( $text, '  ' ) !== false ) {
		$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'double_space', 'detail' => '' );
	}

	// 3. "a" before a vowel-sound word (should be "an"), skipping known exceptions.
	if ( preg_match_all( '/\ba\s+([aeiouAEIOU]\w*)/', $text, $matches ) ) {
		foreach ( $matches[1] as $word ) {
			$lw = strtolower( $word );
			if ( in_array( $lw, $VOWEL_SOUND_EXCEPTIONS, true ) ) continue;
			$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'a_an_error', 'detail' => "a $word" );
		}
	}
	// 4. "an" before a consonant-sound word (should be "a"), skipping silent-h exceptions.
	if ( preg_match_all( '/\ban\s+([b-df-hj-np-tv-zB-DF-HJ-NP-TV-Z]\w*)/', $text, $matches ) ) {
		foreach ( $matches[1] as $word ) {
			$lw = strtolower( $word );
			if ( in_array( $lw, $SILENT_H_WORDS, true ) ) continue;
			$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'an_a_error', 'detail' => "an $word" );
		}
	}

	// 5. Doubled punctuation (,, .. ,. etc), excluding ellipsis "..." on purpose.
	if ( preg_match( '/[,.]{2,}/', $text, $m ) && $m[0] !== '...' ) {
		$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'doubled_punctuation', 'detail' => $m[0] );
	}

	// 6. Space before punctuation.
	if ( preg_match( '/\s+[,.;:!?]/', $text, $m ) ) {
		$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'space_before_punctuation', 'detail' => trim( $m[0] ) );
	}

	// 7. Comma immediately before a period (truncation artifact).
	if ( strpos( $text, ',.' ) !== false ) {
		$issues[] = array( 'post_id' => $post_id, 'title' => $post_title, 'field' => $field_label, 'type' => 'comma_before_period', 'detail' => '' );
	}
}

$issues = array();

echo "Scanning coloring_page posts...\n";
$page_ids = get_posts( array( 'post_type' => 'coloring_page', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$page_fields = array( 'scp_intro', 'scp_meta_description', 'scp_alt_text', 'scp_fun_fact' );
foreach ( $page_ids as $id ) {
	$title = get_the_title( $id );
	foreach ( $page_fields as $field ) {
		$val = get_post_meta( $id, $field, true );
		scp_check_text( $val, $field, $id, $title, $issues, $VOWEL_SOUND_EXCEPTIONS, $SILENT_H_WORDS );
	}
}
echo "  checked " . count( $page_ids ) . " pages\n";

echo "Scanning coloring_topic posts...\n";
$topic_ids = get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$topic_fields = array( 'scp_intro', 'scp_topic_body' );
foreach ( $topic_ids as $id ) {
	$title = get_the_title( $id );
	foreach ( $topic_fields as $field ) {
		$val = get_post_meta( $id, $field, true );
		scp_check_text( $val, $field, $id, $title, $issues, $VOWEL_SOUND_EXCEPTIONS, $SILENT_H_WORDS );
	}
}
echo "  checked " . count( $topic_ids ) . " topics\n";

$by_type = array();
foreach ( $issues as $i ) {
	$by_type[ $i['type'] ][] = $i;
}

echo "\n=== TOTAL ISSUES: " . count( $issues ) . " ===\n";
foreach ( $by_type as $type => $items ) {
	echo "\n--- $type: " . count( $items ) . " ---\n";
	foreach ( array_slice( $items, 0, 10 ) as $it ) {
		echo "  [{$it['post_id']}] {$it['title']} ({$it['field']}): {$it['detail']}\n";
	}
	if ( count( $items ) > 10 ) echo "  ... and " . ( count( $items ) - 10 ) . " more\n";
}

if ( ! is_dir( '/content/output' ) ) mkdir( '/content/output', 0777, true );
file_put_contents( '/content/output/english_scan_issues.json', json_encode( $issues, JSON_PRETTY_PRINT ) );
echo "\nFull issue list written to english_scan_issues.json\n";
