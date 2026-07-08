<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$slug = 'swirled-candy-cane-coloring-pages';
$topic = get_page_by_path($slug, OBJECT, 'coloring_topic');

if (!$topic) {
    echo "Topic not found: $slug\n";
    exit(0);
}

echo "Found topic: ID {$topic->ID} | {$topic->post_title}\n";

$children = get_posts([
    'post_type' => 'coloring_page',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_key' => 'scp_topic_id',
    'meta_value' => $topic->ID,
    'fields' => 'ids',
]);
echo "Children to delete: " . count($children) . "\n";

$deleted_children = 0;
foreach ($children as $cid) {
    if (wp_delete_post($cid, true)) $deleted_children++;
}
echo "Deleted children: $deleted_children\n";

$deleted_topic = wp_delete_post($topic->ID, true);
echo "Deleted topic: " . ($deleted_topic ? 'OK' : 'FAILED') . "\n";
