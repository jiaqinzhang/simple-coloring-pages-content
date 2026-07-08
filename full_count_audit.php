<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$topics = get_posts(['post_type'=>'coloring_topic','posts_per_page'=>-1,'post_status'=>'publish']);
echo "Auditing " . count($topics) . " topics...\n\n";

$mismatches = 0;
foreach ($topics as $t) {
    $legacy = get_post_meta($t->ID, 'scp_pages', true);
    $legacy_count = is_array($legacy) ? count($legacy) : 0;

    $real_pub = get_posts(['post_type'=>'coloring_page','post_status'=>'publish','posts_per_page'=>-1,'meta_key'=>'scp_topic_id','meta_value'=>$t->ID,'fields'=>'ids']);
    $real_any = get_posts(['post_type'=>'coloring_page','post_status'=>'any','posts_per_page'=>-1,'meta_key'=>'scp_topic_id','meta_value'=>$t->ID,'fields'=>'ids']);

    if ($legacy_count !== count($real_pub) || count($real_pub) !== count($real_any)) {
        $mismatches++;
        echo "MISMATCH: {$t->post_title} (ID {$t->ID}) legacy=$legacy_count real_publish=" . count($real_pub) . " real_any=" . count($real_any) . "\n";
    }
}
echo "\nTotal mismatches: $mismatches / " . count($topics) . "\n";
