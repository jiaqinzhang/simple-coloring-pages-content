<?php
/**
 * Reads /content/repo/pdf_merge_result.json (written by merge_pdfs.py)
 * and writes scp_pdf_all_url + scp_pdf_all_size for every topic that
 * merged successfully.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$uploads_base = 'https://simplecoloringpagesforkids.com/wp-content/uploads/2026/07';
$results = json_decode( file_get_contents( '/content/repo/pdf_merge_result.json' ), true );

$updated = 0;
$errors = array();

foreach ( $results as $r ) {
	if ( $r['status'] !== 'ok' ) {
		$errors[] = $r['slug'] . ': ' . ( $r['error'] ?? 'unknown error' );
		continue;
	}
	$url = "{$uploads_base}/{$r['slug']}_all.pdf";
	update_post_meta( $r['topic_id'], 'scp_pdf_all_url', $url );
	update_post_meta( $r['topic_id'], 'scp_pdf_all_size', $r['size_mb'] . ' MB' );
	$updated++;
}

echo "Updated: $updated\n";
echo "Errors: " . count( $errors ) . "\n";
foreach ( array_slice( $errors, 0, 20 ) as $e ) echo "  - $e\n";
