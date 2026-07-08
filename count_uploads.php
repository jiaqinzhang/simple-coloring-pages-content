<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }
$upload_dir = wp_upload_dir();
$base = $upload_dir['basedir'];
echo "basedir: $base\n";

function count_files_recursive($dir) {
    $total = 0;
    $bytes = 0;
    $exts = [];
    $items = @scandir($dir);
    if ($items === false) return [0, 0, []];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            list($t, $b, $e) = count_files_recursive($path);
            $total += $t;
            $bytes += $b;
            foreach ($e as $ext => $c) { $exts[$ext] = ($exts[$ext] ?? 0) + $c; }
        } else {
            $total++;
            $bytes += filesize($path);
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            $exts[$ext] = ($exts[$ext] ?? 0) + 1;
        }
    }
    return [$total, $bytes, $exts];
}

list($total, $bytes, $exts) = count_files_recursive($base);
echo "TOTAL FILES: $total\n";
echo "TOTAL SIZE: " . round($bytes / 1024 / 1024, 2) . " MB\n";
echo "By extension:\n";
foreach ($exts as $ext => $c) { echo "  .$ext: $c\n"; }

// also list subdirectories of uploads/2026/07 if exists
$target = $base . '/2026/07';
if (is_dir($target)) {
    $files = scandir($target);
    $files = array_values(array_diff($files, ['.', '..']));
    echo "\nFiles in 2026/07: " . count($files) . "\n";
    // unique topic prefixes (before first dash-number)
    $prefixes = [];
    foreach ($files as $f) {
        if (preg_match('/^([a-z0-9]+(?:-[a-z0-9]+)*)-\d+_/', $f, $m)) {
            $prefixes[$m[1]] = true;
        }
    }
    echo "Unique topic-prefix count: " . count($prefixes) . "\n";
}
