<?php
if ( ! defined('WP_CLI') ) { echo 'Must run via wp-cli'; exit(1); }

$site_name = get_bloginfo('name') ?: 'Simple Coloring Pages';
$domain = parse_url(home_url(), PHP_URL_HOST) ?: 'simplecoloringpagesforkids.com';
$contact_email = 'hello@' . $domain;

$pages = array();

$pages['about'] = array(
	'title' => 'About Us',
	'content' => "<p>{$site_name} started with a simple goal: give parents, teachers, and kids a place to find free, original coloring pages without pop-ups, sign-ups, or paywalls.</p>
<p>Every illustration on this site is custom-made for young children — bold outlines, simple shapes, and friendly characters that are easy to color with crayons, markers, or colored pencils. We cover everything from animals and vehicles to holidays, fantasy characters, and early-learning topics like letters, numbers, and shapes.</p>
<h2>Who We're For</h2>
<p>We build pages for three kinds of visitors:</p>
<ul>
<li><strong>Parents</strong> looking for a screen-free activity at home</li>
<li><strong>Teachers</strong> who need printable worksheets and classroom activities</li>
<li><strong>Kids</strong> who just want something fun to color</li>
</ul>
<h2>How It Works</h2>
<p>Every page can be downloaded as a print-ready PDF or PNG, sized for both US Letter and A4 paper. No account, no email address, and no payment required — just download and print.</p>
<p>New coloring pages are added regularly, so check back often for new themes.</p>",
);

$pages['contact'] = array(
	'title' => 'Contact',
	'content' => "<p>Have a question, a suggestion for a new coloring page theme, or found something that isn't working right? We'd love to hear from you.</p>
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

$pages['privacy-policy'] = array(
	'title' => 'Privacy Policy',
	'content' => "<p><em>Last updated: " . date('F j, Y') . "</em></p>
<p>This Privacy Policy explains how {$site_name} (\"we\", \"us\", or \"our\"), available at {$domain}, handles information when you visit our website.</p>
<h2>Information We Collect</h2>
<p>{$site_name} does not require you to create an account, log in, or submit personal information to download or print coloring pages. We do not knowingly collect personal information directly from children.</p>
<p>Like most websites, our server and any analytics or advertising tools we use may automatically collect standard technical information, such as:</p>
<ul>
<li>Browser type and device information</li>
<li>Pages visited and time spent on the site</li>
<li>Approximate location based on IP address</li>
<li>Referring website</li>
</ul>
<h2>Cookies and Advertising</h2>
<p>We may use cookies and similar technologies to understand how visitors use the site and, where enabled, to serve advertising through third-party providers such as Google AdSense. These providers may use cookies to serve ads based on your prior visits to this or other websites.</p>
<p>You can learn more about how Google uses information from sites that use its services, and how to opt out of personalized advertising, at <a href=\"https://policies.google.com/technologies/ads\" target=\"_blank\" rel=\"noopener\">policies.google.com/technologies/ads</a>.</p>
<h2>Children's Privacy</h2>
<p>Our coloring pages are designed for children to use with a parent, guardian, or teacher, but the website itself is intended to be used and operated by adults on children's behalf. We do not knowingly collect personal information from children under 13. If you believe a child has provided us with personal information, please contact us so we can remove it.</p>
<h2>Third-Party Links</h2>
<p>Our site may contain links to third-party websites or services. We are not responsible for the privacy practices of those third parties, and we encourage you to review their privacy policies separately.</p>
<h2>Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated \"Last updated\" date.</p>
<h2>Contact Us</h2>
<p>If you have questions about this Privacy Policy, contact us at <a href=\"mailto:{$contact_email}\">{$contact_email}</a>.</p>",
);

$pages['terms'] = array(
	'title' => 'Terms of Use',
	'content' => "<p><em>Last updated: " . date('F j, Y') . "</em></p>
<p>By using {$site_name} (\"the site\"), you agree to the following terms. If you do not agree, please do not use the site.</p>
<h2>Use of Coloring Pages</h2>
<p>All coloring pages, illustrations, and printable files on this site are free to download and print for personal, family, and classroom use. You may print as many copies as you need for these purposes.</p>
<p>You may <strong>not</strong>:</p>
<ul>
<li>Resell, redistribute, or republish our coloring pages or PDFs, whether for free or for payment, on another website or platform</li>
<li>Claim our illustrations as your own original work</li>
<li>Use the artwork in commercial products for sale without our written permission</li>
</ul>
<h2>Ownership</h2>
<p>All original artwork, illustrations, and site content are the property of {$site_name} unless otherwise noted. This policy grants you permission to print and use the files as described above — it does not transfer ownership of the artwork to you.</p>
<h2>No Warranty</h2>
<p>The site and its content are provided \"as is\" without warranties of any kind. We do our best to keep downloads working correctly, but we don't guarantee the site will always be available, error-free, or uninterrupted.</p>
<h2>Limitation of Liability</h2>
<p>To the fullest extent permitted by law, {$site_name} is not liable for any indirect, incidental, or consequential damages arising from your use of the site or its content.</p>
<h2>Changes to These Terms</h2>
<p>We may update these Terms of Use from time to time. Continued use of the site after changes are posted means you accept the updated terms.</p>
<h2>Contact Us</h2>
<p>Questions about these terms? Contact us at <a href=\"mailto:{$contact_email}\">{$contact_email}</a>.</p>",
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
