<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

// Delete ALL existing coloring_topic posts (both old demo placeholders and any
// partial preview-batch imports) so we get one clean, consistent dataset.
$all = get_posts([ 'post_type' => 'coloring_topic', 'posts_per_page' => -1, 'post_status' => 'any' ]);
foreach ( $all as $p ) {
    wp_delete_post( $p->ID, true );
    echo "DELETED: " . $p->post_title . "\n";
}

// Prevent the theme's demo-content.php seeder from ever re-running.
update_option( 'scp_demo_seeded', 1 );

echo "RESET DONE, " . count($all) . " old posts removed\n";
