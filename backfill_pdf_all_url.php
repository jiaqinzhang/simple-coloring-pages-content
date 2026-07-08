<?php
/**
 * Backfills scp_pdf_all_url (and scp_pdf_all_size) for every coloring_topic
 * whose field is currently blank. The bundle PDF files already exist on
 * the server at {UPLOADS_BASE}/{topic-slug-without-"-coloring-pages"}_all.pdf
 * (confirmed via curl spot checks) -- the import script just never wrote
 * this meta field. Only writes the URL after confirming the file actually
 * returns HTTP 200, so a topic with no real bundle file is left blank and
 * reported rather than getting a link to a 404.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$uploads_base = 'https://simplecoloringpagesforkids.com/wp-content/uploads/2026/07';

$topics = get_posts( array( 'post_type' => 'coloring_topic', 'post_status' => 'publish', 'posts_per_page' => -1 ) );

$updated = 0;
$already_set = 0;
$file_missing = array();

foreach ( $topics as $t ) {
	$existing = get_post_meta( $t->ID, 'scp_pdf_all_url', true );
	if ( $existing ) {
		$already_set++;
		continue;
	}

	// Derive the bundle-file slug from the topic's own slug, stripping a
	// trailing "-coloring-pages" the same way the generator built it.
	$slug = $t->post_name;
	$slug = preg_replace( '/-coloring-pages$/', '', $slug );
	$candidate = "{$uploads_base}/{$slug}_all.pdf";

	$ctx = stream_context_create( array( 'http' => array( 'method' => 'HEAD', 'timeout' => 8 ) ) );
	$headers = @get_headers( $candidate, false, $ctx );
	$ok = $headers && strpos( $headers[0], '200' ) !== false;

	if ( $ok ) {
		update_post_meta( $t->ID, 'scp_pdf_all_url', $candidate );
		// Try to get a human-readable size from the Content-Length header.
		foreach ( $headers as $h ) {
			if ( stripos( $h, 'Content-Length:' ) === 0 ) {
				$bytes = (int) trim( substr( $h, strlen( 'Content-Length:' ) ) );
				if ( $bytes > 0 ) {
					$mb = round( $bytes / 1048576, 1 );
					update_post_meta( $t->ID, 'scp_pdf_all_size', $mb . ' MB' );
				}
			}
		}
		$updated++;
	} else {
		$file_missing[] = $t->post_title . ' (' . $slug . '_all.pdf)';
	}
}

echo "Already had pdf_all_url: $already_set\n";
echo "Backfilled: $updated\n";
echo "File not found (left blank): " . count( $file_missing ) . "\n";
foreach ( array_slice( $file_missing, 0, 20 ) as $m ) echo "  - $m\n";
if ( count( $file_missing ) > 20 ) echo "  ... and " . ( count( $file_missing ) - 20 ) . " more\n";
