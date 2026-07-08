<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
foreach ([435, 5141] as $id) {
    $p = get_post($id);
    if ($p) {
        echo "ID $id: title={$p->post_title} slug={$p->post_name} status={$p->post_status}\n";
    } else {
        echo "ID $id: DOES NOT EXIST\n";
    }
}
echo "\nAll posts with slug 'valentines-day-coloring-pages':\n";
global $wpdb;
$rows = $wpdb->get_results("SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_name='valentines-day-coloring-pages' AND post_type='coloring_topic'");
foreach ($rows as $r) echo "  ID {$r->ID} | {$r->post_title} | {$r->post_status}\n";
