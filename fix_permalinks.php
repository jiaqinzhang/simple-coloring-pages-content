<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'category_base', '' );
flush_rewrite_rules( true );
echo "permalink_structure now: " . get_option('permalink_structure') . "\n";
echo "FIXED\n";
