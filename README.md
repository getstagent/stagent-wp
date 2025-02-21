=== Stagent ===
Tags: stagent, bookings, artists, DJs
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Stagent is a lightweight WordPress plugin designed to display bookings from the Stagent API on your website. Easily integrate your event bookings by fetching teams and events directly from Stagent, and display both upcoming and past events using a simple shortcode. With built-in AJAX pagination, customizable display options (including dark mode and canceled bookings), and transient caching for performance, Stagent makes event management seamless.

**Features include:**
* API Integration – Securely fetch teams and bookings using your Stagent API key.
* Shortcode Driven – Use the [stagent_bookings] shortcode to embed booking lists anywhere.
* Booking Widget – Implement the Stagent Booking Widget in 3 clicks.
* Load More – Enjoy smooth, incremental loading of bookings without page reloads.
* Transient Caching – Improve performance with built-in caching (with an option to disable if needed).
* Display Options – Configure dark mode, show canceled events, and more.
* Developer Friendly – Easily extend functionality with hooks and a clean, organized codebase.
* Future Proof – Designed to evolve with your needs and to integrate with additional booking features.

== Installation ==
**Manual Installation:**
1. Download the plugin zip file.
2. In your WordPress admin dashboard, navigate to Plugins > Add New > Upload Plugin.
3. Upload the ‘stagent.zip’ file and click “Install Now.”
4. Activate the plugin.
5. Go to Settings > Stagent to configure your API key, default team, and display options.
6. Insert the shortcode [stagent_bookings] into your posts or pages where you want to display bookings.

**FTP Installation:**
1. Unzip the ‘stagent.zip’ file.
2. Upload the resulting folder to the /wp-content/plugins/ directory on your server.
3. Activate the plugin from the WordPress dashboard.
4. Configure settings and insert the shortcode as described above.

== Frequently Asked Questions ==
= How do I obtain my Stagent API key? =
Log in to your Stagent account and navigate to the API section.

= How do I activate the Stagent plugin? =
Copy your personal access token and paste it into the Stagent settings page in WordPress.

= How do I add the bookings list? =
Use the shortcode `[stagent_bookings]` to display the bookings list. You can control the list with various attributes:

- `team="id"`: Specify the team ID to filter bookings by a specific team.
- `artists="id,id"`: Provide a comma-separated list of artist IDs to filter bookings by specific artists.
- `show="all|upcoming|past"`: Control which bookings are displayed:
  - `all` (*default*): Show both upcoming and past bookings.
  - `upcoming`: Show only upcoming bookings.
  - `past`: Show only past bookings.
- `canceled="false|true"`: Determine whether to show canceled bookings:
  - `false` (*default*): Hide canceled bookings.
  - `true`: Display canceled bookings.
- `per_page="{amount}"`: Set the number of bookings to display per page. Default is `5`.

**Example: the basics**
```wordpress
[stagent_bookings]
```
Displays the default bookings list with the default team, showing upcoming bookings only, hiding canceled events, and displaying 5 items per page.

**Example: show bookings for a specific team**
```wordpress
[stagent_bookings team="d1e2f3"]
```
Shows bookings only for the team with ID d1e2f3.

**Example: filter by specific artist(s)**
```wordpress
[stagent_bookings team="d1e2f3" artists="a1b2c3,a4b5c6"]
```
Displays bookings for team d1e2f3, limited to the artists with IDs a1b2c3 and a4b5c6.

**Example: show only past bookings**
```wordpress
[stagent_bookings show="past"]
```
Displays past bookings only, using the default team.

**Example: display canceled bookings**
```wordpress
[stagent_bookings canceled="true"]
```
Includes canceled bookings in the list.

**Example: increase bookings per page**
```wordpress
[stagent_bookings per_page="10"]
```
Shows 10 bookings per page instead of the default 5.

**Example: full control**
```wordpress
[stagent_bookings team="d4e5f6" artists="a1b2c3,a4b5c6,a8b8c9" show="upcoming" canceled="true" per_page="15"]
```
- Shows upcoming bookings only.
- Includes canceled bookings.
- Displays 15 bookings per page.
- Filters by team d4e5f6 and artists a1b2c3, a4b5c6 and a8b8c9.

= Where can I find the IDs? =
IDs can be found in the url: `stagent.com/app/teams/ID`, while IDs for specific artist can be found here: `stagent.com/app/teams/id/roster/ID`.

= How can I customize which bookings are displayed? =
Use the shortcode attributes to filter events. For example, use canceled="true" to show canceled bookings or omit it to hide them. Further customization can be done via CSS or by extending the plugin’s hooks.

= Can I disable caching? =
Yes. To disable caching entirely, define `STAGENT_DISABLE_CACHE` as true in your wp-config.php file:
```php
define('STAGENT_DISABLE_CACHE', true);
```

= What is dark mode? =
Stagent supports dark mode styling. Enable it on the settings page, and the bookings list display will automatically apply dark mode classes.

== Changelog ==
= 0.1.0 =
- Initial release.
- Core API integration for fetching teams and bookings.
- Shortcode [stagent_bookings] to display upcoming and past bookings.
- AJAX “Load More” functionality for incremental event loading.
- Transient caching built in (with an option to disable).
- Admin settings page for configuring API key, default team, dark mode, canceled bookings, and booking widget.
- Developer hooks and classes for future customization.

== Upgrade Notice ==
= 0.1.0 =
This is the initial release of Stagent for WordPress. Enjoy integrating your bookings and let us know your feedback.

== License ==
Stagent is released under the GPLv2 or later. For more details, see http://www.gnu.org/licenses/gpl-2.0.html