<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

echo "=== Topics with 0 pages ===\n";
$topics = get_posts(['post_type'=>'coloring_topic','posts_per_page'=>-1,'post_status'=>'publish','fields'=>'ids']);
$empty_topics = 0;
foreach ($topics as $tid) {
    $count = count(get_posts(['post_type'=>'coloring_page','posts_per_page'=>-1,'meta_key'=>'scp_topic_id','meta_value'=>$tid,'fields'=>'ids']));
    if ($count === 0) {
        $empty_topics++;
        if ($empty_topics <= 5) echo "  - " . get_the_title($tid) . " (ID $tid)\n";
    }
}
echo "Total topics with 0 pages: $empty_topics / " . count($topics) . "\n\n";

echo "=== Orphaned coloring_page posts (topic_id points to nothing) ===\n";
$pages = get_posts(['post_type'=>'coloring_page','posts_per_page'=>-1,'post_status'=>'any','fields'=>'ids']);
$orphans = 0;
foreach ($pages as $pid) {
    $tid = (int) get_post_meta($pid, 'scp_topic_id', true);
    if (!$tid || get_post_status($tid) === false) {
        $orphans++;
        if ($orphans <= 5) echo "  - " . get_the_title($pid) . " (ID $pid, topic_id=$tid)\n";
    }
}
echo "Total orphaned pages: $orphans / " . count($pages) . "\n\n";

echo "=== coloring_page posts missing key meta (png_url or pdf_url empty) ===\n";
$missing_meta = 0;
foreach ($pages as $pid) {
    $png = get_post_meta($pid, 'scp_png_url', true);
    $pdf = get_post_meta($pid, 'scp_pdf_url', true);
    if (!$png || !$pdf) {
        $missing_meta++;
        if ($missing_meta <= 5) echo "  - " . get_the_title($pid) . " (ID $pid) png=" . ($png?:'EMPTY') . " pdf=" . ($pdf?:'EMPTY') . "\n";
    }
}
echo "Total pages missing png/pdf: $missing_meta / " . count($pages) . "\n\n";

echo "=== Empty categories (0 published topics) ===\n";
$cats = get_terms(['taxonomy'=>'topic_category','hide_empty'=>false]);
foreach ($cats as $c) {
    if ($c->count === 0) echo "  - " . $c->name . " (0 topics)\n";
}

echo "\n=== Total counts ===\n";
echo "coloring_topic (publish): " . wp_count_posts('coloring_topic')->publish . "\n";
echo "coloring_page (publish): " . wp_count_posts('coloring_page')->publish . "\n";
echo "coloring_page (future): " . wp_count_posts('coloring_page')->future . "\n";
