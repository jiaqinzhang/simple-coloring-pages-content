<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$count = wp_count_posts('coloring_topic');
echo "publish count: " . ($count->publish ?? 0) . "\n";
$all = get_posts([ 'post_type' => 'coloring_topic', 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ]);
echo "total any-status: " . count($all) . "\n";
$titles = get_posts([ 'post_type' => 'coloring_topic', 'posts_per_page' => 20, 'post_status' => 'publish' ]);
foreach ($titles as $t) { echo "- " . $t->post_title . "\n"; }
echo "permalink_structure: " . get_option('permalink_structure') . "\n";
