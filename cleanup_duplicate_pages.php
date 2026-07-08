<?php
/**
 * One-time cleanup: deletes ALL coloring_page posts (any status), scoped
 * strictly to that post type. Does not touch coloring_topic, pages, or
 * anything else. Run this before re-running import_coloring_pages.php to
 * clear the duplicate-slug mess left by the pre-fix wipe-query bug.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$all = get_posts([
    'post_type' => 'coloring_page',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'fields' => 'ids',
]);

echo "Found " . count($all) . " coloring_page posts to delete.\n";

$deleted = 0;
foreach ($all as $id) {
    if (wp_delete_post($id, true)) {
        $deleted++;
    }
}

echo "Deleted: $deleted\n";

$remaining = count(get_posts([
    'post_type' => 'coloring_page',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'fields' => 'ids',
]));
echo "Remaining coloring_page posts: $remaining\n";
