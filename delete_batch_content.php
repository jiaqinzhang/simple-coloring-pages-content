<?php
/**
 * Explicit, user-confirmed batch deletion.
 *
 * FULL_TOPIC_SLUGS: deletes the coloring_topic post AND every coloring_page
 * child under it.
 *
 * SINGLE_PAGE_SLUGS: deletes only the named coloring_page (matched by
 * [topic_slug, page_slug] pair so there's no risk of a slug collision
 * across topics) -- the parent topic and its other pages are untouched.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$FULL_TOPIC_SLUGS = array(
	'autumn-corn-maze-scene-coloring-pages',
	'spring-kite-flying-scene-coloring-pages',
	'columbus-day-sailing-ship-coloring-pages',
	'tide-pool-with-small-sea-creatures-coloring-pages',
	'ordinal-numbers-first-second-third-coloring-pages',
	'camel-coloring-pages',
	'punctuation-marks-chart-coloring-pages',
	'flying-magic-carpet-with-tassels-coloring-pages',
	'flying-pegasus-with-feathered-wings-coloring-pages',
	'rainforest-canopy-coloring-pages',
	'sand-dunes-coloring-pages',
	'sparkling-crystal-gemstone-coloring-pages',
	'deep-canyon-coloring-pages',
	'spinning-tornado-coloring-pages',
	'lightning-bolt-coloring-pages',
	'ocean-wave-coloring-pages',
	'spring-rainbow-after-rain-coloring-pages',
	'crab-coloring-pages',
	'flamingo-coloring-pages',
	'simple-addition-math-illustration-coloring-pages',
	'helicopter-coloring-pages',
	'circus-performer-balancing-on-a-ball-coloring-pages',
);

// [topic_slug, page_slug]
$SINGLE_PAGE_SLUGS = array(
	array( 'friendly-ghost-floating-with-a-happy-face-coloring-pages', 'waving-friendly-ghost-floating-with-a-happy-face-coloring-page' ),
	array( 'friendly-ghost-floating-with-a-happy-face-coloring-pages', 'sleepy-friendly-ghost-floating-with-a-happy-face-coloring-page' ),
	array( 'shapes-coloring-pages', 'circle-shapes-coloring-page' ),
	array( 'shapes-coloring-pages', 'triangle-shapes-coloring-page' ),
	array( 'shapes-coloring-pages', 'smiling-2-shapes-coloring-page' ),
	array( 'spring-coloring-pages', 'resting-spring-coloring-page' ),
	array( 'spring-coloring-pages', 'watering-spring-coloring-page' ),
	array( 'spring-coloring-pages', 'flying-spring-coloring-page' ),
	array( 'summer-coloring-pages', 'coloring-scene-7-summer-coloring-page' ),
	array( 'summer-coloring-pages', 'coloring-scene-6-summer-coloring-page' ),
	array( 'summer-coloring-pages', 'coloring-scene-1-summer-coloring-page' ),
	array( 'autumn-nature-walk-scene-coloring-pages', 'smiling-autumn-nature-walk-scene-coloring-page' ),
	array( 'autumn-nature-walk-scene-coloring-pages', 'coloring-scene-6-autumn-nature-walk-scene-coloring-page' ),
	array( 'autumn-nature-walk-scene-coloring-pages', 'resting-autumn-nature-walk-scene-coloring-page' ),
	array( 'autumn-nature-walk-scene-coloring-pages', 'winking-autumn-nature-walk-scene-coloring-page' ),
);

$topics_deleted = 0;
$topic_children_deleted = 0;
$topics_not_found = array();

foreach ( $FULL_TOPIC_SLUGS as $slug ) {
	$topic = get_page_by_path( $slug, OBJECT, 'coloring_topic' );
	if ( ! $topic ) {
		$topics_not_found[] = $slug;
		continue;
	}
	$children = get_posts( array(
		'post_type'      => 'coloring_page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => 'scp_topic_id',
		'meta_value'     => $topic->ID,
		'fields'         => 'ids',
	) );
	foreach ( $children as $cid ) {
		wp_delete_post( $cid, true );
		$topic_children_deleted++;
	}
	wp_delete_post( $topic->ID, true );
	$topics_deleted++;
	echo "Deleted topic: $slug (" . count( $children ) . " child pages)\n";
}

$pages_deleted = 0;
$pages_not_found = array();

foreach ( $SINGLE_PAGE_SLUGS as $pair ) {
	list( $topic_slug, $page_slug ) = $pair;
	$topic = get_page_by_path( $topic_slug, OBJECT, 'coloring_topic' );
	if ( ! $topic ) {
		$pages_not_found[] = "$topic_slug / $page_slug (topic not found)";
		continue;
	}
	$page = get_page_by_path( $page_slug, OBJECT, 'coloring_page' );
	if ( ! $page ) {
		$pages_not_found[] = "$topic_slug / $page_slug (page not found)";
		continue;
	}
	$page_topic_id = (int) get_post_meta( $page->ID, 'scp_topic_id', true );
	if ( $page_topic_id !== $topic->ID ) {
		$pages_not_found[] = "$topic_slug / $page_slug (topic_id mismatch, skipped for safety)";
		continue;
	}
	wp_delete_post( $page->ID, true );
	$pages_deleted++;
	echo "Deleted page: $topic_slug / $page_slug\n";
}

echo "\n=== SUMMARY ===\n";
echo "Full topics deleted: $topics_deleted (plus $topic_children_deleted child pages)\n";
echo "Topics not found: " . count( $topics_not_found ) . "\n";
foreach ( $topics_not_found as $t ) echo "  - $t\n";
echo "Individual pages deleted: $pages_deleted\n";
echo "Pages not found/skipped: " . count( $pages_not_found ) . "\n";
foreach ( $pages_not_found as $p ) echo "  - $p\n";
