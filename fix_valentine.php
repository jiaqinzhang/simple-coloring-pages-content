<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

// Delete the old topic (435) - only has flat legacy data, no individual SEO pages.
$deleted = wp_delete_post(435, true);
echo "Deleted old topic 435: " . ($deleted ? 'OK' : 'FAILED') . "\n";

// Rename the new topic (5141) to reclaim the clean slug.
$updated = wp_update_post([
    'ID' => 5141,
    'post_name' => 'valentines-day-coloring-pages',
]);
echo "Renamed topic 5141 slug: " . ($updated ? 'OK' : 'FAILED') . "\n";

$check = get_post(5141);
echo "Final slug: " . $check->post_name . "\n";
