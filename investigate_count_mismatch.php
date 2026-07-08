<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

foreach (['numbers-coloring-pages', 'alphabet-coloring-pages', 'vegetables-coloring-pages'] as $slug) {
    echo "=========== $slug ===========\n";
    $topic = get_page_by_path($slug, OBJECT, 'coloring_topic');
    if (!$topic) { echo "TOPIC NOT FOUND\n\n"; continue; }
    echo "Topic ID: {$topic->ID} | status={$topic->post_status}\n";

    $legacy = get_post_meta($topic->ID, 'scp_pages', true);
    echo "Legacy scp_pages count: " . (is_array($legacy) ? count($legacy) : 0) . "\n";

    // Real children matching this topic_id, ANY status
    $children_any = get_posts(['post_type'=>'coloring_page','post_status'=>'any','posts_per_page'=>-1,'meta_key'=>'scp_topic_id','meta_value'=>$topic->ID,'fields'=>'ids']);
    echo "Real children (any status) pointing to topic {$topic->ID}: " . count($children_any) . "\n";

    // Published only
    $children_pub = get_posts(['post_type'=>'coloring_page','post_status'=>'publish','posts_per_page'=>-1,'meta_key'=>'scp_topic_id','meta_value'=>$topic->ID,'fields'=>'ids']);
    echo "Real children (publish only) pointing to topic {$topic->ID}: " . count($children_pub) . "\n";

    // Now find ALL coloring_page posts whose title suggests this topic (in case scp_topic_id points elsewhere)
    global $wpdb;
    $like = '%' . $wpdb->esc_like(ucfirst(str_replace('-coloring-pages','',$slug))) . '%';
    $stray = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_title, pm.meta_value as topic_id FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='scp_topic_id' WHERE p.post_type='coloring_page' AND p.post_title LIKE %s", $like));
    echo "All coloring_page posts with matching title pattern: " . count($stray) . "\n";
    $topic_ids_seen = [];
    foreach ($stray as $s) { $topic_ids_seen[$s->topic_id] = ($topic_ids_seen[$s->topic_id] ?? 0) + 1; }
    foreach ($topic_ids_seen as $tid => $cnt) {
        $t = get_post($tid);
        echo "  -> topic_id=$tid (" . ($t ? $t->post_title . ' status=' . $t->post_status : 'MISSING TOPIC') . ") : $cnt children\n";
    }
    echo "\n";
}
