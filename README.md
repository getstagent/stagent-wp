=== Stagent ===
Contributors: stagent
Tags: stagent, bookings, artists, events, djs
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 0.2.4
Requires PHP: 7.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Stagent is a lightweight WordPress plugin to display bookings from Stagent on your website using a simple shortcode.

== Description ==
Stagent is a lightweight WordPress plugin designed to display bookings from the Stagent API on your website. Easily integrate your event bookings by fetching teams and bookings directly from Stagent, and display both upcoming and past events using the [stagent_bookings] shortcode. With built-in AJAX pagination, customizable display options (including dark mode, toggle buttons, and canceled bookings), and transient caching for performance, Stagent makes event management seamless.

**Features include:**
- **API Integration**: Securely fetch teams and bookings using your Stagent API key.
- **Shortcode Driven**: Use [stagent_bookings] to embed booking lists anywhere, with options for team, artists, show type, per page, canceled bookings, past/upcoming toggles, and dark mode.
- **Booking Widget**: Add the Stagent Booking Widget in a few clicks.
- **Load More**: Smooth, incremental loading of bookings without page reloads.
- **Transient Caching**: Improve performance with built-in caching.
- **Display Options**: Configure dark mode, toggle past/upcoming visibility, show canceled events, and set items per page via shortcode or settings.
- **Developer Friendly**: Extend functionality with hooks and a clean codebase.
- **Future Proof**: Designed to evolve with additional booking features.

== Installation ==
= Manual Installation =
1. Download the plugin zip file.
2. In your WordPress admin dashboard, navigate to Plugins > Add New > Upload Plugin.
3. Upload the ‘stagent.zip’ file and click "Install Now."
4. Activate the plugin.
5. Go to Settings > Stagent to configure your API key, default team, and display options. Note: The "Refresh" link requires a valid nonce for security.
6. Insert the [stagent_bookings] shortcode into posts or pages to display bookings.

= FTP Installation =
1. Unzip the ‘stagent.zip’ file.
2. Upload the resulting folder to the /wp-content/plugins/ directory on your server.
3. Activate the plugin from the WordPress dashboard.
4. Configure settings and insert the shortcode as described above.

== Frequently Asked Questions ==
= How do I obtain my Stagent API key? =
Log in to your Stagent account and navigate to Account > API Tokens to generate a personal access token.

= How do I activate the Stagent plugin? =
Copy your personal access token from Stagent and paste it into the Settings > Stagent page in your WordPress admin.

= How do I add the bookings list? =
The easiest way is to create your shortcode using the Shortcode Generator located in the **Settings > Stagent** page under the "Shortcode Generator" tab. Want to do it manually? Use the `[stagent_bookings]` shortcode to display the bookings list. Customize it with these attributes:
- `team="id"`: Filter bookings by a specific team ID.
- `artists="id,id"`: Comma-separated list of artist IDs to filter bookings.
- `show="all|upcoming|past"`: Control which bookings to display:
  - `all` (default): Upcoming and past bookings.
  - `upcoming`: Upcoming bookings only.
  - `past`: Past bookings only.
- `per_page="number"`: Number of bookings per page (default: 5).
- `canceled="true|false"`: Show canceled bookings:
  - `false` (default): Hide canceled bookings.
  - `true`: Include canceled bookings.
- `show_past="true|false"`: Show or hide the "Past" and "Upcoming" toggle buttons:
  - `true` (default): Show toggle buttons (if past bookings are enabled in settings).
  - `false`: Hide toggle buttons, showing only the selected `show` type.
- `dark_mode="true|false"`: Enable or disable dark mode for the bookings list:
  - `false` (default): Use light mode unless enabled in settings.
  - `true`: Enable dark mode for this shortcode instance.

= Example: Basic Usage =
`[stagent_bookings]`
Displays bookings for the default team, showing 5 upcoming bookings with toggle buttons (if past enabled), excluding canceled ones, in light mode.

= Example: Specific Team =
`[stagent_bookings team="d1e2f3"]`
Shows bookings for team ID "d1e2f3".

= Example: Filter by Artists =
`[stagent_bookings team="d1e2f3" artists="a1b2c3,a4b5c6"]`
Filters bookings for team "d1e2f3" to artists "a1b2c3" and "a4b5c6".

= Example: Past Bookings Only =
`[stagent_bookings show="past"]`
Displays past bookings for the default team.

= Example: Show Canceled Bookings =
`[stagent_bookings canceled="true"]`
Includes canceled bookings.

= Example: Custom Per Page =
`[stagent_bookings per_page="10"]`
Shows 10 bookings per page.

= Example: Hide Toggle Buttons =
`[stagent_bookings show="upcoming" show_past="false"]`
Shows only upcoming bookings without toggle buttons.

= Example: Enable Dark Mode =
`[stagent_bookings dark_mode="true"]`
Displays bookings in dark mode, overriding settings.

= Example: Full Control =
`[stagent_bookings team="d4e5f6" artists="a1b2c3,a4b5c6" show="upcoming" per_page="15" canceled="true" show_past="false" dark_mode="true"]`
Shows 15 upcoming bookings (including canceled) for team "d4e5f6" and artists "a1b2c3", "a4b5c6", without toggle buttons, in dark mode.

= Where can I find the IDs? =
- Team IDs: Found in the URL: stagent.com/app/teams/ID.
- Artist IDs: Found in the roster URL: stagent.com/app/teams/team-id/roster/ID.

= How can I customize which bookings are displayed? =
Use the shortcode attributes above. For further customization, apply CSS to the booking list classes or use the plugin’s filter hooks.

= Can I disable caching? =
Yes, set STAGENT_DEVELOPMENT_MODE to true in wp-config.php to bypass caching:
define('STAGENT_DEVELOPMENT_MODE', true);

= What is dark mode? =
Enable dark mode via the dark_mode="true" shortcode attribute or in Settings > Stagent to apply a dark theme to the bookings list display.

== Changelog ==
= v0.2.4 =
- **Added**: Option to enable/disable 'Powered by Stagent'.
- - **Improved**: Optimized and minified CSS.

= v0.2.3 =
- **Fixed**: Corrected minimum required versions.

= v0.2.2 =
- **Improved**: Store widget script as URL and enqueue it.
- **Fixed**: Add element existence checks in JS.

= v0.2.1 =
- **Improved**: Applied feedback from WordPress.org for Plugin Directory listing.

= v0.2.0 =
- **Added**: New shortcode attributes `show_past="true|false"` to control visibility of "Past" and "Upcoming" toggle buttons (defaults to `true` if past bookings are enabled in settings).
- **Added**: New shortcode attribute `dark_mode="true|false"` to enable/disable dark mode per shortcode instance (overrides settings, defaults to `false`).
- **Added**: New shortcode generator located in the **Settings > Stagent** page under the "Shortcode Generator" tab.
- **Enhanced**: Added nonce security, migrated inline JS to `stagent-admin.js`, refined `stagent_booking_widget` validation, and streamlined shortcode generator UI for better usability and compliance.
- **Improved**: Enhanced template security with sanitization/escaping, added type safety, updated README with full `$atts` docs, fixed filename mismatches and 403 errors, and ensured widget display consistency.

= v0.1.0 =
- Initial release.

== Upgrade Notice ==
= 0.2.0 =
Upgrade for new shortcode options (`show_past`, `dark_mode`), a new shortcode generator and enhanced security. Update your shortcodes if using new attributes.

= 0.1.0 =
Initial release of Stagent for WordPress.

== License ==
Stagent is released under the GPLv2 or later. See <http://www.gnu.org/licenses/gpl-2.0.html> for details.