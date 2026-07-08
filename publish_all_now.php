<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$future_posts = get_posts([
    'post_type' => 'coloring_page',
    'post_status' => 'future',
    'posts_per_page' => -1,
    'fields' => 'ids',
]);

echo "Found " . count($future_posts) . " scheduled posts to publish now.\n";

$now = current_time('mysql');
$now_gmt = current_time('mysql', true);

$updated = 0;
foreach ($future_posts as $id) {
    $result = wp_update_post([
        'ID' => $id,
        'post_status' => 'publish',
        'post_date' => $now,
        'post_date_gmt' => $now_gmt,
    ]);
    if ($result && ! is_wp_error($result)) $updated++;
}

echo "Published: $updated\n";

$still_future = count(get_posts(['post_type'=>'coloring_page','post_status'=>'future','posts_per_page'=>-1,'fields'=>'ids']));
$total_publish = count(get_posts(['post_type'=>'coloring_page','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids']));
echo "Remaining future: $still_future\n";
echo "Total published: $total_publish\n";
