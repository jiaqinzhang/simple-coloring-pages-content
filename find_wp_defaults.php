<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
global $wpdb;

echo "=== Sample Page ===\n";
$rows = $wpdb->get_results("SELECT ID, post_title, post_status, post_type FROM {$wpdb->posts} WHERE post_title='Sample Page'");
foreach ($rows as $r) echo "ID {$r->ID} | {$r->post_title} | {$r->post_status} | {$r->post_type}\n";

echo "\n=== Hello World post ===\n";
$rows = $wpdb->get_results("SELECT ID, post_title, post_status, post_type FROM {$wpdb->posts} WHERE post_title LIKE '%Hello world%'");
foreach ($rows as $r) echo "ID {$r->ID} | {$r->post_title} | {$r->post_status} | {$r->post_type}\n";

echo "\n=== Default comment ===\n";
$comments = $wpdb->get_results("SELECT comment_ID, comment_author, comment_content FROM {$wpdb->comments} LIMIT 5");
foreach ($comments as $c) echo "ID {$c->comment_ID} | {$c->comment_author} | " . substr($c->comment_content,0,60) . "\n";

echo "\n=== All published 'page' post_type entries ===\n";
$rows = $wpdb->get_results("SELECT ID, post_title, post_name, post_status FROM {$wpdb->posts} WHERE post_type='page'");
foreach ($rows as $r) echo "ID {$r->ID} | {$r->post_title} | slug={$r->post_name} | {$r->post_status}\n";

echo "\n=== Site tagline/description ===\n";
echo "blogdescription: " . get_option('blogdescription') . "\n";
