=== Portfolio Showcase ===
Contributors: Your Name
Tags: portfolio, projects, showcase, filter, shortcode
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, modern, dark-glassmorphism project portfolio showcase with category/technology filtering, AJAX filtering, unlimited shortcodes and a full custom admin panel. No page builders, no Elementor, no Gutenberg dependency required.

== Description ==

Portfolio Showcase lets you build a premium, animated project portfolio grid anywhere on your site using a simple shortcode. It ships with its own dedicated admin area — no reliance on the default WordPress post editor — for managing projects, categories, technologies and unlimited pre-built shortcode combinations.

**Frontend Features**

* Responsive grid: 3 columns desktop, 2 columns tablet, 1 column mobile
* Dark glassmorphism cards with soft shadows, 18px rounded corners and smooth 300ms transitions
* Technology icons displayed on each card
* AJAX category + technology filtering, no page reload
* Load-more pagination with lazy-loaded images
* Hover lift, image zoom and soft glow animations

**Admin Features**

* Projects: add / edit / delete / hide / show / search / bulk actions
* Categories: full CRUD with usage counts
* Technologies: full CRUD with icon upload (SVG, PNG, JPG, JPEG, WEBP)
* Shortcode generator: build unlimited `[estel_portfolio]` variations by category, technology, or both, with copy / preview / delete
* Assets only load on pages where the shortcode is actually used

**Shortcode Examples**

`[estel_portfolio]`
`[estel_portfolio category="wordpress"]`
`[estel_portfolio category="wordpress,laravel"]`
`[estel_portfolio technology="react,nodejs"]`
`[estel_portfolio category="business" technology="laravel"]`

**Performance & Security**

* No CSS/JS loaded globally — only on pages containing the shortcode
* All output escaped, all input sanitized
* Nonce verification and capability checks on every action
* No direct file access on any PHP file

== Installation ==

1. Upload the `portfolio-showcase` folder to `/wp-content/plugins/`, or install the ZIP directly via Plugins → Add New → Upload Plugin.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Go to the new "Portfolio Showcase" menu to add Categories, Technologies and Projects.
4. Place the `[estel_portfolio]` shortcode (or any generated variation) on any page or post.

== Frequently Asked Questions ==

= Does this plugin require Elementor or Gutenberg? =

No. It works with any theme and any page builder simply by pasting the shortcode into a text/HTML block.

= Can I show only specific categories or technologies? =

Yes, use the Shortcodes screen in the admin panel to generate a custom shortcode, or write the attributes manually as shown above.

= What image formats are supported for technology icons? =

SVG, PNG, JPG, JPEG and WEBP, up to 2MB. Uploaded SVGs are sanitized before being stored.

= Is this plugin multisite compatible? =

Yes.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
