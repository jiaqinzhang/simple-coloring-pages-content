<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$upload_dir = wp_upload_dir();
$target = $upload_dir['basedir'] . '/2026/07';
$files = scandir($target);
$files = array_values(array_diff($files, ['.', '..']));
$prefixes = [];
foreach ($files as $f) {
    if (preg_match('/^([a-z0-9]+(?:-[a-z0-9]+)*)-\d+_/', $f, $m)) {
        $prefixes[$m[1]] = true;
    }
}
$list = array_keys($prefixes);
sort($list);
echo "Total unique prefixes: " . count($list) . "\n";
echo implode(", ", $list) . "\n";

// which ones have a published post whose slug matches (roughly)
$published = get_posts(['post_type'=>'coloring_topic','posts_per_page'=>-1,'post_status'=>'publish','fields'=>'ids']);
$pub_slugs = [];
foreach ($published as $id) {
    $pub_slugs[] = get_post_field('post_name', $id);
}
echo "\nPublished post slugs (" . count($pub_slugs) . "):\n";
sort($pub_slugs);
echo implode(", ", $pub_slugs) . "\n";
