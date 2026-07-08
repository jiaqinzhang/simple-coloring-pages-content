<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
global $wpdb;

foreach (['Number 1 %', 'Letter A %', '%Vegetable%'] as $pattern) {
    echo "=========== title LIKE '$pattern' ===========\n";
    $rows = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_name, post_status FROM {$wpdb->posts} WHERE post_type='coloring_page' AND post_title LIKE %s LIMIT 10", $pattern));
    foreach ($rows as $r) {
        $tid = get_post_meta($r->ID, 'scp_topic_id', true);
        $topic = get_post($tid);
        echo "ID {$r->ID} | {$r->post_title} | slug={$r->post_name} | status={$r->post_status} | scp_topic_id=$tid";
        echo " -> " . ($topic ? "{$topic->post_title} (status={$topic->post_status})" : "MISSING/INVALID TOPIC");
        echo "\n";
    }
    echo "\n";
}
