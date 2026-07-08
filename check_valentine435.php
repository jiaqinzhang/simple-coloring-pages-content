<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$pages = get_post_meta(435, 'scp_pages', true);
echo "scp_pages count: " . (is_array($pages) ? count($pages) : 0) . "\n";
if (is_array($pages) && count($pages) > 0) {
    echo "first page: " . json_encode($pages[0]) . "\n";
}
echo "terms: ";
$terms = get_the_terms(435, 'topic_category');
if ($terms && !is_wp_error($terms)) { foreach($terms as $t) echo $t->name . " "; }
echo "\n";
