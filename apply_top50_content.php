<?php
/**
 * P2: attaches the generated 300-500 word body + varied FAQ to the top 50
 * highest-traffic coloring_topic posts. Reads top50_content.json (keyed by
 * bare topic name, e.g. "Dog") and matches against post_title
 * "{Topic} Coloring Pages". In-place meta update only -- no post
 * delete/recreate.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$json_path = __DIR__ . '/top50_content.json';
$data = json_decode( file_get_contents( $json_path ), true );
if ( ! is_array( $data ) ) {
	echo "Failed to read/parse $json_path\n";
	exit(1);
}

$updated = 0;
$not_found = array();

foreach ( $data as $topic_name => $entry ) {
	$expected_title = $topic_name . ' Coloring Pages';
	$posts = get_posts( array(
		'post_type'      => 'coloring_topic',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'title'          => $expected_title,
	) );
	if ( ! $posts ) {
		$not_found[] = $expected_title;
		continue;
	}
	$post_id = $posts[0]->ID;
	update_post_meta( $post_id, 'scp_topic_body', $entry['body'] );
	update_post_meta( $post_id, 'scp_topic_faq', $entry['faq'] );
	$updated++;
}

echo "Updated: $updated\n";
echo "Not found: " . count( $not_found ) . "\n";
foreach ( $not_found as $t ) echo "  - $t\n";
