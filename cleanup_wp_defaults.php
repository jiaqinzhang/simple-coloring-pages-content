<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

// Delete WordPress's default placeholder content.
$r1 = wp_delete_post(2, true); // Sample Page
echo "Deleted Sample Page (ID 2): " . ($r1 ? 'OK' : 'FAILED') . "\n";

$r2 = wp_delete_post(1, true); // Hello world! post (also cascades its comment)
echo "Deleted Hello World post (ID 1): " . ($r2 ? 'OK' : 'FAILED') . "\n";

// Set a real tagline instead of the empty default.
update_option('blogdescription', 'Free Printable Coloring Pages for Kids');
echo "Tagline set to: " . get_option('blogdescription') . "\n";

// Verify
global $wpdb;
$remaining = $wpdb->get_results("SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE ID IN (1,2)");
echo "Remaining rows with ID 1 or 2: " . count($remaining) . "\n";
