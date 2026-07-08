<?php
/**
 * Imports coloring_topic + coloring_page posts from the regenerated JSON data.
 *
 * Reads every import_*.json file in /content/import_data/ (relative to this
 * script's directory) and, for each topic:
 *   - finds or creates the coloring_topic post + assigns its topic_category term
 *   - deletes any existing coloring_page children (safe re-run) then re-creates
 *     them in order, wiring scp_topic_id + menu_order.
 *
 * Usage: wp eval-file import_coloring_pages.php --path=... --allow-root
 * Optional env var IMPORT_ONLY_TOPIC=Cat limits the run to one topic (by
 * matching topic_title), for safe testing before a full run.
 */
if ( ! defined( 'WP_CLI' ) ) { echo 'Must run via wp-cli'; exit(1); }

$data_dir = __DIR__ . '/import_data';
$only_topic = getenv( 'IMPORT_ONLY_TOPIC' );

$files = glob( $data_dir . '/import_*.json' );
if ( ! $files ) {
	echo "No import_*.json files found in $data_dir\n";
	exit(1);
}

$topics_created = 0;
$topics_updated = 0;
$pages_created = 0;

foreach ( $files as $file ) {
	$topics = json_decode( file_get_contents( $file ), true );
	if ( ! is_array( $topics ) ) {
		echo "Skipping unreadable file: $file\n";
		continue;
	}

	foreach ( $topics as $topic_data ) {
		if ( $only_topic && stripos( $topic_data['topic_title'], $only_topic ) === false ) {
			continue;
		}

		// Find or create the topic_category term.
		$term = term_exists( $topic_data['category'], 'topic_category' );
		if ( ! $term ) {
			$term = wp_insert_term( $topic_data['category'], 'topic_category' );
		}
		$term_id = is_wp_error( $term ) ? 0 : (int) $term['term_id'];

		// Find or create the coloring_topic post by slug.
		$existing = get_page_by_path( $topic_data['topic_slug'], OBJECT, 'coloring_topic' );
		if ( $existing ) {
			$topic_id = $existing->ID;
			$topics_updated++;
		} else {
			$topic_id = wp_insert_post( array(
				'post_type'   => 'coloring_topic',
				'post_title'  => $topic_data['topic_title'],
				'post_name'   => $topic_data['topic_slug'],
				'post_status' => 'publish',
			) );
			if ( is_wp_error( $topic_id ) || ! $topic_id ) {
				echo "FAILED to create topic: {$topic_data['topic_title']}\n";
				continue;
			}
			$topics_created++;
		}

		if ( $term_id ) {
			wp_set_post_terms( $topic_id, array( $term_id ), 'topic_category' );
		}
		update_post_meta( $topic_id, 'scp_age_range', $topic_data['age_range'] );

		// Wipe any previously-imported children for this topic (safe re-run).
		$old_children = get_posts( array(
			'post_type'      => 'coloring_page',
			'posts_per_page' => -1,
			'meta_key'       => 'scp_topic_id',
			'meta_value'     => $topic_id,
			'fields'         => 'ids',
		) );
		foreach ( $old_children as $old_id ) {
			wp_delete_post( $old_id, true );
		}

		// Also populate scp_pages on the topic (legacy field the topic-hub
		// template falls back to; keeps "Download All" + thumb consistent).
		$legacy_pages = array();

		foreach ( $topic_data['pages'] as $i => $page ) {
			$page_id = wp_insert_post( array(
				'post_type'   => 'coloring_page',
				'post_title'  => $page['h1_title'],
				'post_name'   => $page['page_slug'],
				'post_status' => 'publish',
				'menu_order'  => $i,
			) );
			if ( is_wp_error( $page_id ) || ! $page_id ) {
				echo "FAILED to create page: {$page['h1_title']}\n";
				continue;
			}

			update_post_meta( $page_id, 'scp_topic_id', $topic_id );
			update_post_meta( $page_id, 'scp_meta_description', $page['meta_description'] );
			update_post_meta( $page_id, 'scp_intro', $page['page_intro'] );
			update_post_meta( $page_id, 'scp_vocabulary', $page['vocabulary'] );
			update_post_meta( $page_id, 'scp_fun_fact', $page['fun_fact'] );
			update_post_meta( $page_id, 'scp_alt_text', $page['alt_text'] );
			update_post_meta( $page_id, 'scp_png_url', $page['png_url'] );
			update_post_meta( $page_id, 'scp_pdf_url', $page['pdf_url'] );
			update_post_meta( $page_id, 'scp_thumb_url', $page['thumb_url'] );

			$legacy_pages[] = array(
				'title'     => $page['h1_title'],
				'alt'       => $page['alt_text'],
				'thumb_url' => $page['thumb_url'],
				'png_url'   => $page['png_url'],
				'pdf_url'   => $page['pdf_url'],
			);

			$pages_created++;
		}

		update_post_meta( $topic_id, 'scp_pages', $legacy_pages );
		update_post_meta( $topic_id, 'scp_thumb_url', $legacy_pages[0]['thumb_url'] ?? '' );
	}
}

echo "Topics created: $topics_created\n";
echo "Topics updated (already existed): $topics_updated\n";
echo "Pages created: $pages_created\n";
