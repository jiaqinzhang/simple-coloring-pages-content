<?php
/**
 * Post-processes the freshly-imported coloring_page posts (all currently
 * 'publish') into a staggered rollout:
 *   - keep the first ~1/3 of each topic's pages published now
 *   - set the remaining ~2/3 to 'future', releasing ~200/day, interleaved
 *     across categories so each day's batch is a natural mix of topics
 *     rather than one whole category at a time.
 *
 * Usage: wp eval-file schedule_publish.php --path=... --allow-root
 */
if ( ! defined( 'WP_CLI' ) ) { echo 'Must run via wp-cli'; exit(1); }

$PUBLISH_FRACTION = 1 / 3;
$PER_DAY          = 200;

// 1. Load every coloring_page post grouped by topic, in menu_order.
$all_pages = get_posts( array(
	'post_type'      => 'coloring_page',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
	'fields'         => 'ids',
) );

$by_topic = array();
foreach ( $all_pages as $page_id ) {
	$topic_id = (int) get_post_meta( $page_id, 'scp_topic_id', true );
	$by_topic[ $topic_id ][] = $page_id;
}

// 2. Decide keep-published vs to-schedule per topic; group the "to schedule"
//    pile by the topic's category for interleaving.
$by_category_queue = array(); // category_slug => [ page_id, page_id, ... ]
$kept_published     = 0;
$to_schedule_total   = 0;

foreach ( $by_topic as $topic_id => $page_ids ) {
	$count         = count( $page_ids );
	$publish_count = (int) ceil( $count * $PUBLISH_FRACTION );

	$terms = get_the_terms( $topic_id, 'topic_category' );
	$cat_slug = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : 'uncategorized';

	foreach ( $page_ids as $i => $page_id ) {
		if ( $i < $publish_count ) {
			$kept_published++;
			continue; // stays published, nothing to do
		}
		$by_category_queue[ $cat_slug ][] = $page_id;
		$to_schedule_total++;
	}
}

echo "Kept published now: $kept_published\n";
echo "To schedule: $to_schedule_total\n";
echo "Categories in rotation: " . count( $by_category_queue ) . "\n";

// 3. Round-robin interleave across categories into one global ordered list.
$interleaved = array();
$cat_keys    = array_keys( $by_category_queue );
$pointers    = array_fill_keys( $cat_keys, 0 );
$remaining   = $to_schedule_total;

while ( $remaining > 0 ) {
	foreach ( $cat_keys as $cat ) {
		$p = $pointers[ $cat ];
		if ( isset( $by_category_queue[ $cat ][ $p ] ) ) {
			$interleaved[] = $by_category_queue[ $cat ][ $p ];
			$pointers[ $cat ]++;
			$remaining--;
		}
	}
}

// 4. Chunk into daily batches starting tomorrow, set status=future + post_date.
$updated = 0;
$chunks  = array_chunk( $interleaved, $PER_DAY );
foreach ( $chunks as $day_offset => $chunk ) {
	$publish_date = date( 'Y-m-d H:00:00', strtotime( '+' . ( $day_offset + 1 ) . ' days 9 hours' ) );
	foreach ( $chunk as $page_id ) {
		wp_update_post( array(
			'ID'            => $page_id,
			'post_status'   => 'future',
			'post_date'     => $publish_date,
			'post_date_gmt' => get_gmt_from_date( $publish_date ),
		) );
		$updated++;
	}
}

echo "Scheduled: $updated pages across " . count( $chunks ) . " days\n";
echo "First scheduled batch: " . ( $chunks ? date( 'Y-m-d', strtotime( '+1 days' ) ) : 'n/a' ) . "\n";
echo "Last scheduled batch: " . ( $chunks ? date( 'Y-m-d', strtotime( '+' . count( $chunks ) . ' days' ) ) : 'n/a' ) . "\n";
