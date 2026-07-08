<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$topics = get_posts(['post_type'=>'coloring_topic','posts_per_page'=>-1,'post_status'=>'publish']);
echo "Total topics: " . count($topics) . "\n\n";

// Group by normalized title (strip all non-alnum, lowercase) to find near-duplicates
$groups = [];
foreach ($topics as $t) {
    $norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $t->post_title));
    $groups[$norm][] = $t;
}

$dupe_groups = 0;
foreach ($groups as $norm => $group) {
    if (count($group) > 1) {
        $dupe_groups++;
        echo "DUPLICATE GROUP ($norm):\n";
        foreach ($group as $t) {
            $child_count = count(get_posts(['post_type'=>'coloring_page','posts_per_page'=>-1,'post_status'=>'any','meta_key'=>'scp_topic_id','meta_value'=>$t->ID,'fields'=>'ids']));
            echo "  ID {$t->ID} | {$t->post_title} | slug={$t->post_name} | children=$child_count\n";
        }
    }
}
echo "\nTotal duplicate groups found: $dupe_groups\n";
