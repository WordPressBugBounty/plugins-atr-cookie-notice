# ATR Cookie Notice

Cookie consent management for WordPress.

## Features

- **Cookie Consent Banner**: Displays when users first visit the site
- **Granular Consent Options**: Users can choose between Essential, Analytics, and Marketing cookies
- **Consent Storage**: User preferences are stored locally and respected across visits
- **Multi-language Support**: The plugin is available in English and Hebrew. Other languages are possible to be added in the standard Wordpress translation process.

## Configuration

### General Settings
- Enable/disable the cookie banner
- Consent Mode:
  - Full (default) – Blocks non‑essential scripts (analytics/marketing) until consent. Shows buttons: “Accept All”, “Reject Non‑Essential”, and “Preferences” for granular choices. Restores tracking only for the categories the user approved.
  - Simple – An informational banner with an “OK” button and a Privacy Policy link. Does not block or modify any third‑party scripts.
  - Recommendation: Choose “Full” when you must block non‑essential tracking until consent; choose “Simple” when you only need transparent notice without blocking.
- Set banner position (bottom, top, overlay)
- Configure auto-hide delay

### Cookie Categories
- Show/hide Analytics and Marketing categories
- Defaults:
  - Essential: always required (cannot be disabled)
  - Analytics: shown, unchecked by default
  - Marketing: shown, unchecked by default

### Styling & Appearance
- Comprehensive styling via CSS variables:
  - Colors: primary, secondary, text, background, link, button (primary/secondary) text/bg
  - Typography: font family, base size, weights, uppercase buttons
  - Layout & spacing: radius, paddings, gaps, banner/modal max width, direction, alignment
  - Overlay & stacking: overlay color/opacity, z-index, shadow preset
  - Custom CSS (appended after computed styles)
- Live Preview in admin updates instantly as you edit fields (no save required).
- “Force override theme styles” toggle adds scoped resets and high-specificity rules to win against aggressive theme CSS.
- Styling reset: Tools include a “Reset Styling to defaults” action that restores only Styling & Appearance fields.

### Content (Safe HTML)
- Override banner text (`.scb-text`) and primary actions (`.scb-actions`) for both modes.
- Fields:
  - Simple: “Simple Mode — Text HTML”, “Simple Mode — Actions HTML”
  - Full: “Full Mode — Text HTML”, “Full Mode — Actions HTML”
- Leave empty to keep the default, fully translated content.
- Allowed tags: `a`, `button`, `strong`, `em`, `span`, `br`, `p`
  - Allowed attributes: `href`, `rel`, `target`, `class`, `id`, `type`, and `style` (on `span`)
- Tokens:
  - `{site_name}`, `{privacy_url}`
  - `{privacy_link}` → full anchor with the Privacy Policy page title, e.g. `<a href="…" target="_blank" rel="noopener" role="link">Privacy Policy Title</a>`
  - Simple actions: `[ok_button]`, `[privacy_link]`
  - Full actions: `[accept_all_button]`, `[reject_button]`, `[preferences_button]`
- Footer (Full mode):
  - Field: “Full Mode — Footer HTML”
  - Tokens: `{privacy_url}`
  - Default (when empty): a Privacy Policy link
- Safety: required button IDs are enforced. If missing, defaults are appended:
  - Simple: `#scb-btn-ok`
  - Full: `#scb-btn-accept-all`, `#scb-btn-reject`, `#scb-btn-custom`
- Filters (for developers, after token replacement):
  - `atr_cookie_notice_text_html_simple`, `atr_cookie_notice_actions_html_simple`
  - `atr_cookie_notice_text_html_full`, `atr_cookie_notice_actions_html_full`

### Localization
- Privacy Policy link in the banner points to the page defined under WordPress Settings → Privacy (the plugin does not manage that page’s content).
- For admin documentation only, sample Privacy Policy content is shown based on the admin user’s locale (Hebrew/English).
- Translations follow standard WordPress i18n. Additional languages can be added only via the official WordPress translation system (translate.wordpress.org) for the text domain `atr-cookie-notice`.

### Advanced Options
- Enable debug mode
- Set cookie expiry days

### Tools
- Purge caches and refresh assets: a one-click tool that:
  - Bumps an internal asset buster so public CSS/JS URLs get a new `?ver=` and bypass stale caches
  - Triggers safe purges in popular cache plugins/providers when installed
  - Redirects back with a success notice

How to use:
1. Open the plugin settings page in wp-admin
2. Click “Purge caches and refresh assets” in the Tools block at the top
3. Reload a frontend page and verify asset URLs include `?ver={pluginVersion}-{timestamp}`

### Consent Modes

#### Full (default)
- Blocks analytics and marketing trackers until consent is provided.
- Shows granular controls: Accept All, Reject Non‑Essential, Preferences with categories.
- Restores tracking only for categories the user approved.

#### Simple
- Displays a lightweight informational banner with an OK button and a Privacy Policy link.
- Does not block or modify any third‑party scripts.
- Recommended when you only need to inform users without consent gating.

#### Runtime scripts
- Only one script is loaded based on the selected mode:
  - Full: `public/js/atr-cookie-notice-public.js` (requires jQuery)
  - Simple: `public/js/atr-cookie-notice-simple.js` (no jQuery)

### Controlled Tracking Services

The plugin automatically blocks and controls these common tracking services:

**Analytics Services:**
- Google Analytics (gtag, ga functions)
- Google Tag Manager (dataLayer.push function)

**Marketing Services:**
- Facebook Pixel (fbq function)

**How it works:**
- Before consent: Tracking functions are blocked and replaced with no-op versions
- After consent: Original tracking functions are restored based on user choices
- Essential cookies: Always allowed (session management, security, preferences)
- Analytics cookies: Blocked until user consents to Analytics category
- Marketing cookies: Blocked until user consents to Marketing category

### Debug Mode (for developers)

When enabled, exposes test hooks to verify cookie blocking (no impact on users):

- JS flag: `scbSettings.enableDebug === true`
- Helper: `window.scb.testTracking()` simulates analytics/marketing calls
- Optional self-test after setup (does not alter consent)

How to test

1. Turn on "Enable Debug Mode" and save.
2. Open a frontend page and clear prior consent in console: `window.scb.clearConsent()` (page reloads).
3. Before consent, check blocking flags in console:
   - `window.gtag && window.gtag._scbBlocked` → true when blocked
   - `window.ga && window.ga._scbBlocked` → true when blocked
   - `window.fbq && window.fbq._scbBlocked` → true when blocked
4. Run `window.scb.testTracking()` (returns undefined; it's just a probe).
5. Click Accept All. Re-check the flags; they should no longer be blocked.

## Usage

On first visit:
1. Show the consent banner
2. Block analytics/marketing until consent
3. Store user choices

## Cookie Categories (reference)

### Essential
- Required for core functionality (e.g., sessions, auth, language)

### Analytics
- Example: Google Analytics, page views, performance

### Marketing
- Example: Facebook Pixel, Google Ads

## WooCommerce checkout privacy link

WooCommerce includes its own privacy policy display on checkout. This plugin does not add a separate checkbox.

How to enable the privacy link on checkout:

1. WooCommerce → Settings → Accounts & Privacy
2. Enable "Add a privacy policy link to your checkout page"
3. Ensure a Privacy Policy page is set in Settings → Privacy
4. Customize the policy text under WooCommerce → Settings → Accounts & Privacy if needed

Note: The link appears only if a Privacy Policy page is set (Settings → Privacy). For classic themes (non‑FSE), add `[privacy_policy]` under WooCommerce → Customizer → Checkout → Terms and conditions so the terms checkbox covers the privacy policy as well.

## Developer Information

- **Text Domain**: atr-cookie-notice
- **Version**: 1.1.0
- **Author**: Yehuda Tiram
- **License**: GPL-2.0+

## Support

For support and documentation, visit the plugin settings page in your WordPress admin.

## Changelog

### 1.0.0
- Initial release
- Cookie consent banner
- Multi-language support
