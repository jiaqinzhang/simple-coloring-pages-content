<?php
/**
 * P3 targeted content patch: updates scp_meta_description / scp_intro /
 * scp_alt_text on EXISTING coloring_page posts (matched by parent topic +
 * post_name/slug) from the corrected import_data/*.json. Does NOT delete
 * or recreate any post -- this is a safe in-place meta update, unlike the
 * full wipe-and-reimport used for the original import.
 *
 * Also refreshes the matching entry inside the topic's legacy scp_pages
 * meta array (used as a fallback by some templates) so both stay in sync.
 *
 * Only touches topics present in AFFECTED_TOPICS (species/vehicle pose
 * fixes + the Numbers/Alphabet grammar fixes) -- leaves every other topic
 * untouched.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$data_dir = __DIR__ . '/import_data';
$files = glob($data_dir . '/import_*.json');

$AFFECTED_TOPICS = array(
	// Animals (species pose fix)
	'Giraffe','Zebra','Horse','Deer','Camel','Cow','Pig','Sheep','Goat','Elephant','Rhino','Hippo',
	'Kangaroo','Chicken','Duck','Eagle','Parrot','Flamingo','Peacock','Owl','Penguin','Dolphin','Whale',
	'Shark','Seahorse','Turtle','Frog','Crocodile','Snake','Bee','Ladybug','Butterfly','Snail','Octopus',
	'Crab','Bat',
	// Vehicles (ground-action fix)
	'Rocket Ship','Spaceship','Ufo','Submarine','Sailboat','Cruise Ship','Yacht','Ferry Boat','Kayak',
	'Jet Ski','Hot Air Balloon','Blimp','Biplane','Hovercraft',
	// Grammar fixes
	'Numbers','Alphabet',
);

$pages_updated = 0;
$pages_not_found = 0;
$topics_touched = array();

foreach ($files as $file) {
	$topics = json_decode(file_get_contents($file), true);
	if (!is_array($topics)) continue;

	foreach ($topics as $topic_data) {
		$bare_title = preg_replace('/\s+Coloring Pages$/i', '', $topic_data['topic_title']);
		if (!in_array($bare_title, $AFFECTED_TOPICS, true)) continue;

		$topic = get_page_by_path($topic_data['topic_slug'], OBJECT, 'coloring_topic');
		if (!$topic) {
			echo "TOPIC NOT FOUND: {$topic_data['topic_title']} (slug {$topic_data['topic_slug']})\n";
			continue;
		}

		$legacy_pages = get_post_meta($topic->ID, 'scp_pages', true);
		if (!is_array($legacy_pages)) $legacy_pages = array();
		$legacy_by_title = array();
		foreach ($legacy_pages as $i => $lp) {
			$legacy_by_title[$lp['title']] = $i;
		}

		$topic_touched = false;

		foreach ($topic_data['pages'] as $page_data) {
			$child = get_page_by_path($page_data['page_slug'], OBJECT, 'coloring_page');
			// Fallback: match by scp_topic_id + post_name directly (get_page_by_path
			// can be unreliable for non-hierarchical CPTs using custom rewrites).
			if (!$child) {
				$found = get_posts(array(
					'post_type'      => 'coloring_page',
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'name'           => $page_data['page_slug'],
					'meta_key'       => 'scp_topic_id',
					'meta_value'     => $topic->ID,
				));
				$child = $found ? $found[0] : null;
			}
			if (!$child) {
				$pages_not_found++;
				echo "PAGE NOT FOUND: {$topic_data['topic_title']} / {$page_data['page_slug']}\n";
				continue;
			}

			update_post_meta($child->ID, 'scp_meta_description', $page_data['meta_description']);
			update_post_meta($child->ID, 'scp_intro', $page_data['page_intro']);
			update_post_meta($child->ID, 'scp_alt_text', $page_data['alt_text']);
			$pages_updated++;
			$topic_touched = true;

			if (isset($legacy_by_title[$page_data['h1_title']])) {
				$idx = $legacy_by_title[$page_data['h1_title']];
				$legacy_pages[$idx]['alt'] = $page_data['alt_text'];
			}
		}

		if ($topic_touched) {
			update_post_meta($topic->ID, 'scp_pages', $legacy_pages);
			$topics_touched[] = $topic_data['topic_title'];
		}
	}
}

echo "\nPages updated: $pages_updated\n";
echo "Pages not found: $pages_not_found\n";
echo "Topics touched: " . count($topics_touched) . "\n";
foreach ($topics_touched as $t) echo "  - $t\n";
