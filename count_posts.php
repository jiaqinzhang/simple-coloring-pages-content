<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$count = wp_count_posts('coloring_topic');
echo "publish: " . ($count->publish ?? 0) . "\n";
echo "draft: " . ($count->draft ?? 0) . "\n";
echo "pending: " . ($count->pending ?? 0) . "\n";
echo "trash: " . ($count->trash ?? 0) . "\n";

$all = get_posts([ 'post_type' => 'coloring_topic', 'posts_per_page' => -1, 'post_status' => 'publish', 'fields' => 'ids' ]);
$total_pages = 0;
$missing_meta = 0;
foreach ($all as $id) {
    $pages = get_post_meta($id, 'scp_pages', true);
    if (is_array($pages)) { $total_pages += count($pages); }
    else { $missing_meta++; }
}
echo "published topics: " . count($all) . "\n";
echo "total scp_pages across all topics: $total_pages\n";
echo "topics missing scp_pages meta: $missing_meta\n";

// last modified topic
$latest = get_posts(['post_type'=>'coloring_topic','posts_per_page'=>5,'post_status'=>'publish','orderby'=>'date','order'=>'DESC']);
echo "\nMost recently created topics:\n";
foreach ($latest as $p) { echo "- {$p->post_title} (" . $p->post_date . ")\n"; }
