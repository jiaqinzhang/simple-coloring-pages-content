<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
global $wpdb;
$rows = $wpdb->get_results("SELECT ID, post_title, post_name, post_status, post_date FROM {$wpdb->posts} WHERE post_type='coloring_topic' AND post_title LIKE '%Valentine%'");
foreach ($rows as $row) {
    $child_count = count(get_posts(['post_type'=>'coloring_page','post_status'=>'any','posts_per_page'=>-1,'meta_key'=>'scp_topic_id','meta_value'=>$row->ID,'fields'=>'ids']));
    echo "ID {$row->ID} | {$row->post_title} | slug={$row->post_name} | status={$row->post_status} | date={$row->post_date} | children=$child_count\n";
}
