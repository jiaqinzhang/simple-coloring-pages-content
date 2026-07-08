<?php
/**
 * P9 AdSense/child-safety compliance polish: strengthens the existing
 * Privacy Policy (explicit no-active-solicitation-of-children's-PII
 * statement) and Contact page (explicit adult-use note). Updates in place
 * by slug -- does not touch About or Terms, which already read fine.
 */
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$site_name = get_bloginfo('name') ?: 'Simple Coloring Pages';
$domain = parse_url(home_url(), PHP_URL_HOST) ?: 'simplecoloringpagesforkids.com';
$contact_email = 'hello@' . $domain;

$pages = array();

$pages['privacy-policy'] = array(
	'title' => 'Privacy Policy',
	'content' => "<p><em>Last updated: " . date('F j, Y') . "</em></p>
<p>This Privacy Policy explains how {$site_name} (\"we\", \"us\", or \"our\"), available at {$domain}, handles information when you visit our website.</p>
<h2>Who This Site Is For</h2>
<p>{$site_name} is built for parents, guardians, teachers, and other adults to browse, download, and print coloring pages on behalf of the children in their care. The site is intended to be operated by an adult; it is not designed for direct, unsupervised use by children, and no part of the site asks a child to register, log in, or create an account.</p>
<h2>Information We Collect</h2>
<p>{$site_name} does not require you to create an account, log in, or submit personal information to download or print coloring pages. We do not ask for, and do not knowingly collect, personal information from children — including names, email addresses, photos, or comments. We do not include child-facing comment forms, chat features, or public profile pages anywhere on the site.</p>
<p>Like most websites, our server and any analytics or advertising tools we use may automatically collect standard technical information, such as:</p>
<ul>
<li>Browser type and device information</li>
<li>Pages visited and time spent on the site</li>
<li>Approximate location based on IP address</li>
<li>Referring website</li>
</ul>
<h2>Cookies and Advertising</h2>
<p>We use cookies and similar technologies to operate the site and, where enabled, to serve advertising through third-party providers such as Google AdSense and to measure traffic through analytics tools. These providers may use cookies to serve ads based on your prior visits to this or other websites. Some cookies are required for the site to function correctly; we do not offer a cookie-free version of the site, but you can control or block cookies through your browser settings at any time.</p>
<p>You can learn more about how Google uses information from sites that use its services, and how to opt out of personalized advertising, at <a href=\"https://policies.google.com/technologies/ads\" target=\"_blank\" rel=\"noopener\">policies.google.com/technologies/ads</a>.</p>
<h2>Children's Privacy</h2>
<p>Our coloring pages are designed for children to use with a parent, guardian, or teacher, but the website itself is intended to be used and operated by adults on children's behalf. We do not knowingly collect personal information from children under 13, and we do not direct any marketing, account creation, or data-collection features at children. If you believe a child has provided us with personal information, please contact us so we can remove it.</p>
<h2>Third-Party Links</h2>
<p>Our site may contain links to third-party websites or services. We are not responsible for the privacy practices of those third parties, and we encourage you to review their privacy policies separately.</p>
<h2>Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated \"Last updated\" date.</p>
<h2>Contact Us</h2>
<p>If you have questions about this Privacy Policy, contact us at <a href=\"mailto:{$contact_email}\">{$contact_email}</a>.</p>",
);

$pages['contact'] = array(
	'title' => 'Contact',
	'content' => "<p>Have a question, a suggestion for a new coloring page theme, or found something that isn't working right? We'd love to hear from you.</p>
<p><em>This contact form and email address are intended for parents, teachers, and other adult visitors. If you're a young reader, please have a parent or teacher reach out on your behalf.</em></p>
<h2>Email Us</h2>
<p>You can reach us at <a href=\"mailto:{$contact_email}\">{$contact_email}</a>.</p>
<p>We try to respond to every message, though it may take a few days during busy periods.</p>
<h2>Before You Write</h2>
<p>A few quick answers that might save you a message:</p>
<ul>
<li><strong>Are the coloring pages really free?</strong> Yes — every page on this site is free to download and print for personal and classroom use.</li>
<li><strong>Can I request a theme?</strong> Absolutely, send us your idea and we'll consider it for a future update.</li>
<li><strong>Can teachers print pages for a whole class?</strong> Yes, unlimited copies are fine for classroom use.</li>
</ul>",
);

foreach ( $pages as $slug => $data ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$post_args = array(
		'post_type'    => 'page',
		'post_title'   => $data['title'],
		'post_name'    => $slug,
		'post_content' => $data['content'],
		'post_status'  => 'publish',
	);
	if ( $existing ) {
		$post_args['ID'] = $existing->ID;
		$id = wp_update_post( $post_args );
		echo "Updated: $slug (ID $id)\n";
	} else {
		$id = wp_insert_post( $post_args );
		echo "Created: $slug (ID $id)\n";
	}
}
