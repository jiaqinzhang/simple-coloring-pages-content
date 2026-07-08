<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

// Lion topic id lookup
$lion = get_page_by_path('lion-coloring-pages', OBJECT, 'coloring_topic');
echo "Lion topic ID: " . ($lion ? $lion->ID : 'NOT FOUND') . "\n\n";

if ($lion) {
    $all = get_posts([
        'post_type' => 'coloring_page',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_key' => 'scp_topic_id',
        'meta_value' => $lion->ID,
        'orderby' => 'post_name',
        'order' => 'ASC',
    ]);
    echo "Total coloring_page children (any status) for Lion: " . count($all) . "\n\n";
    foreach ($all as $p) {
        $intro = get_post_meta($p->ID, 'scp_intro', true);
        $has_bug = (strpos($intro, 'practice Fine') !== false) ? 'OLD/BUGGY' : 'fixed';
        echo "ID {$p->ID} | status={$p->post_status} | slug={$p->post_name} | date={$p->post_date} | $has_bug\n";
    }
}
