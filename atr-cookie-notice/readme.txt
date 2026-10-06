=== ATR Cookie Notice ===
Contributors: yehudaT
Donate link: https://www.paypal.com/donate/?cmd=_s-xclick&hosted_button_id=T6VTA75GTS3YA&ssrt=1736764301031
Tags: cookies, privacy, consent, israeli law, ammendment 13
Requires at least: 5.0
Tested up to: 6.9.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cookie consent banner aligned with Israel's Privacy Protection Law (Amendment 13).

== Description ==

**ATR Cookie Notice** is a powerful and user-friendly cookie consent management plugin designed to help WordPress websites align with the Israeli Privacy Protection Law (PPL), including Amendment 13, and related privacy best practices. The plugin provides a customizable cookie banner with granular consent options for different types of cookies.

### Key Features

* **Israel Privacy Law Aligned** - Oriented to Israel's Privacy Protection Law (PPL), including Amendment 13
* **Granular Cookie Control** - Separate consent options for Essential, Analytics, and Marketing cookies
* **Customizable Design** - Full control over colors, positioning, and styling with WordPress color pickers
* **Live Preview** - Real-time floating preview that updates as you customize settings
* **Customizable Content** - Full control over banner text, buttons, and footer HTML with token support
* **Multiple Banner Positions** - Bottom, top, or overlay positioning options
* **Auto-hide Functionality** - Optional automatic banner dismissal
* **Responsive Design** - Works perfectly on all devices and screen sizes
* **RTL Support** - Proper right-to-left language support
* **Multilingual Support** - Built-in Hebrew and English translations
* **Developer Friendly** - Extensive hooks and filters for customization
* **Privacy Policy Integration** - Built-in privacy policy management with customizable footer
* **Cookie Expiry Management** - Configurable consent duration
* **Debug Mode** - Development tools for testing and troubleshooting
* **Consent Modes** - Choose between Full (blocking non-essential until consent) and Simple (informational only)
* **Tools** - One-click "Purge caches and refresh assets" with cache busting, styling reset to defaults

### Cookie Categories

The plugin manages three main cookie categories:

1. **Essential Cookies** - Always enabled, required for basic website functionality
2. **Analytics Cookies** - Optional, for website analytics and performance monitoring
3. **Marketing Cookies** - Optional, for advertising and marketing purposes

### Customization Options

* **Visual Customization** - WordPress color pickers for primary color, text color, background color with live preview
* **Position Control** - Choose from bottom, top, or center overlay positioning
* **Behavior Settings** - Auto-hide delay, cookie expiry duration
* **Custom CSS** - Add your own styling rules
* **Default States** - Set default consent states for each cookie category
* **Content Customization** - Customize banner text, buttons, and footer HTML with token support ({site_name}, {privacy_url}, {privacy_link})
* **Styling Presets** - Light, Dark, Minimal, High Contrast presets for quick setup

### Privacy & Compliance

* **Consent Management** - Proper consent tracking and storage
* **Privacy Policy** - Built-in privacy policy templates in Hebrew and English
* **Data Protection** - Secure handling of user consent data
* **Regulatory Alignment** - Oriented to Israel's Privacy Protection Law (PPL), including Amendment 13 (not legal advice)

### Developer Features

* **WordPress Coding Standards** - Follows WPCS and best practices
* **Extensive Hooks** - Customize behavior with WordPress actions and filters
* **Clean Code** - Well-documented, maintainable codebase
* **Performance Optimized** - Minimal impact on site performance

Perfect for businesses, agencies, and developers who need a reliable cookie consent solution aligned with Israeli privacy law that can be easily customized to match any website design.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/atr-cookie-notice` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **Settings > ATR Cookie Notice** in the WordPress admin menu to configure the plugin
4. Check **Enable Cookie Banner** to show the cookie notice to visitors
5. Customize the banner appearance, behavior, and cookie categories as needed
6. Use the live preview to see changes in real-time
7. Click **Save Settings** to apply your changes
8. The cookie banner will appear on your website frontend

== Frequently Asked Questions ==

= Do I need to configure anything after installation? =

The plugin works out of the box with default settings, but we recommend customizing the appearance and cookie categories to match your website's design and compliance needs. Visit the plugin settings page (Settings > ATR Cookie Notice) to configure colors, positioning, content, and cookie categories.

= Does this plugin help with Israel privacy law compliance? =

ATR Cookie Notice is designed to help websites align with Israel's Privacy Protection Law (including Amendment 13). It provides granular consent options, consent tracking, and privacy policy resources. It is not legal advice—verify your requirements with a legal professional.

= Can I customize the appearance of the cookie banner? =

Absolutely! The plugin offers extensive customization options including WordPress color pickers with live preview, positioning, custom CSS, and styling presets (Light, Dark, Minimal, High Contrast). You can also customize the banner text, buttons, and footer HTML using tokens like {site_name}, {privacy_url}, and {privacy_link}.

= What are the available content tokens? =

You can use the following tokens in custom banner text and footer HTML:
* {site_name} - Your website name
* {privacy_url} - URL of your Privacy Policy page
* {privacy_link} - Full anchor tag linking to Privacy Policy
* [ok_button] - OK button (Simple mode)
* [accept_all_button] - Accept All button (Full mode)
* [reject_button] - Reject button (Full mode)
* [preferences_button] - Preferences button (Full mode)
* [privacy_link] - Privacy Policy link (Simple mode)

= Does the plugin support multiple languages? =

Yes, the plugin includes built-in support for Hebrew and English with proper RTL layout support. Additional translations can be added through the WordPress translation system.

= Can I set different default states for cookie categories? =

Yes, you can configure whether analytics and marketing cookies are enabled or disabled by default. Essential cookies are always enabled as they're required for basic website functionality.

= How long does the plugin remember user consent? =

By default, consent is remembered for 365 days, but this can be customized in the plugin settings. Users can change their preferences at any time.

= Is there a way to test the plugin before going live? =

Yes, the plugin includes a debug mode that provides detailed information in the browser console, making it easy to test and troubleshoot during development.

= The banner or updated assets don't appear on some pages =

First, clear your page cache/CDN as usual (and any optimizer caches). If the issue persists, use the plugin Tools on the settings page:

1. Go to Settings > ATR Cookie Notice
2. Click "Purge caches and refresh assets" (adds a cache-busting timestamp to CSS/JS and triggers safe cache purges)
3. Reload the frontend in a private window; asset URLs should include `?ver={pluginVersion}-{timestamp}`

= The banner breaks after updating or using cache/optimization plugins =

If using cache, minification, or JS optimization plugins, you may need to exclude the plugin's JavaScript files from:
* Minify/Combine
* Defer/Delay loading
* Async loading
* CDN delivery

Exclude these files:
* `/wp-content/plugins/atr-cookie-notice/public/js/atr-cookie-notice-public.js`
* `/wp-content/plugins/atr-cookie-notice/admin/js/atr-cookie-notice-admin.js`

After making changes, use the "Purge caches and refresh assets" tool and test in a private browser window.

= Can I customize a preset style? =

Yes, but you must save the preset first before making custom changes. If you select a preset style (Light, Dark, Minimal, or High Contrast) and want to customize it further in the Styling & Appearance tab, follow these steps:

1. Select your desired preset from the dropdown
2. Click **Save Settings** to apply the preset
3. Make your custom changes in the Styling & Appearance tab
4. Click **Save Settings** again to save your customizations

If you make changes without saving the preset first, you'll lose the preset values.

= Can I reset styling settings to defaults? =

Yes! In the Tools section of the plugin settings, you'll find a "Reset Styling to defaults" button that resets only the Styling & Appearance settings while keeping all other configurations intact. A confirmation prompt prevents accidental resets.

== Screenshots ==

1. Cookie banner displayed at the bottom of the website with consent options for different cookie categories
2. Plugin settings page showing the General Settings tab with banner configuration options
3. Cookie Categories settings tab showing analytics and marketing cookie options
4. Styling & Appearance settings tab with WordPress color pickers and live preview
5. Content customization tab with customizable banner text, buttons, and footer HTML
6. Advanced Options tab with debug mode and cookie expiry settings
7. Tools tab with cache purge and styling reset options
8. Privacy Policy documentation page with built-in templates

== Changelog ==

= 1.2.0 =
* New: Customizable banner content - full control over banner text, buttons, and footer HTML
* New: Content tokens support ({site_name}, {privacy_url}, {privacy_link}, button placeholders)
* New: WordPress color pickers with live preview in Styling & Appearance tab
* New: Floating live preview that follows active field and updates in real-time
* New: Styling presets (Light, Dark, Minimal, High Contrast) for quick setup
* New: "Reset Styling to defaults" tool with confirmation prompt (resets only styling settings)
* New: Cache reminder notice after saving settings with direct "Purge caches" button
* New: Sticky live preview on Styling tab (follows scrolling)
* Improvement: Moved plugin settings under WordPress Settings menu (Settings > ATR Cookie Notice)
* Improvement: Better RTL layout support with proper CSS positioning
* Improvement: Cache/optimization compatibility warnings and JS exclusion guidance in Tools tab
* Improvement: Advanced JavaScript optimization when Simple mode is active - unnecessary functions return early without executing (better cache compatibility)
* Improvement: Unique CSS classes matching IDs for all front-end elements
* Improvement: Security enhancements - ABSPATH checks, index.php files in all directories
* Improvement: CSS compliance - class-only selectors (no ID selectors)
* Improvement: Proper HTML sanitization with wp_kses for custom content
* Improvement: Multiple customization filters for banner HTML (atr_cookie_notice_text_html_simple, atr_cookie_notice_footer_html_full, etc.)

= 1.1.0 =
* New: Consent Mode setting — choose between Full (blocking non-essential until consent) and Simple (informational only)
* New: Simple mode banner with OK button; no blocking or script changes
* New: Tools → "Purge caches and refresh assets" (adds asset-buster to CSS/JS and triggers safe cache purges)
* Improvement: Advanced JS no-ops when Simple mode is active (helps with caches)
* Fix: Ensure localized settings (e.g., autoHideDelay) are read correctly

= 1.0.0 =
* Initial release
* GDPR compliant cookie consent management
* Granular cookie category control (Essential, Analytics, Marketing)
* Customizable banner design and positioning
* Multiple banner positions (bottom, top, overlay)
* Auto-hide functionality with configurable delay
* Responsive design for all devices
* Hebrew and English language support
* Privacy policy integration
* Custom CSS support
* Debug mode for development
* WordPress Coding Standards compliance
* Extensive customization options
* Developer-friendly hooks and filters

== Upgrade Notice ==

= 1.2.0 =
Major update with customizable banner content, WordPress color pickers, live preview, styling presets, and enhanced cache compatibility. Settings moved to Settings > ATR Cookie Notice. Import/Export feature temporarily removed. Recommended for all users.

= 1.1.1 =
New: Friendly review notice (after 14 days), translatable, nonce-protected dismiss
Fix: Hebrew readme/PO alignment; added missing readme strings for GlotPress import
Tweak: Description clarifies Full vs Simple modes

= 1.1.0 =
New: Consent Mode setting – choose between Full (blocking non‑essential until consent) and Simple (informational only)
New: Simple mode banner with OK button; no blocking or script changes
New: Tools → “Purge caches and refresh assets” (adds asset‑buster to CSS/JS and triggers safe cache purges)
Improvement: Advanced JS no‑ops when Simple mode is active (helps with caches)
Fix: Ensure localized settings (e.g., autoHideDelay) are read correctly

= 1.0.0 =
Initial release
GDPR compliant cookie consent management
Granular cookie category control (Essential, Analytics, Marketing)
Customizable banner design and positioning
Multiple banner positions (bottom, top, overlay)
Auto-hide functionality with configurable delay
Responsive design for all devices
Hebrew and English language support
Privacy policy integration
Custom CSS support
Debug mode for development
WordPress Coding Standards compliance
Extensive customization options
Developer-friendly hooks and filters

== Liability Statement ==

This plugin is provided "as is" without warranties of any kind, express or implied. It is intended to help site owners align cookie consent UX and behavior with Israel's Privacy Protection Law (including Amendment 13). It does not constitute legal advice, nor does it guarantee compliance. You are solely responsible for verifying your legal obligations and configuring the plugin to meet your specific requirements. The author and contributors are not liable for any damages, losses, or non-compliance resulting from the use of this plugin.
