<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$posts = get_posts([ 'post_type' => 'coloring_topic', 'posts_per_page' => 3, 'post_status' => 'publish' ]);
foreach ($posts as $p) {
    echo "=== " . $p->post_title . " (ID " . $p->ID . ") ===\n";
    echo "thumb_url: " . get_post_meta($p->ID, 'scp_thumb_url', true) . "\n";
    $pages = get_post_meta($p->ID, 'scp_pages', true);
    echo "page count: " . (is_array($pages) ? count($pages) : 0) . "\n";
    if (is_array($pages) && count($pages) > 0) {
        echo "first page png_url: " . ($pages[0]['png_url'] ?? 'MISSING') . "\n";
        echo "first page pdf_url: " . ($pages[0]['pdf_url'] ?? 'MISSING') . "\n";
    }
    echo "\n";
}
