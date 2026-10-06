# Cookie Consent Plugin – Testing Guide (Concise)

This guide explains how to quickly verify that ATR Cookie Notice respects user consent.

## Quick Reference (Frontend only)
Note: The `window.scb` API exists only on frontend pages (not in wp-admin).

```javascript
// Check consent
window.scb.getConsent()

// Clear consent (reloads page)
window.scb.clearConsent()

// Check specific type
window.scb.consentGivenFor('analytics')

// Save consent
window.scb.saveConsent({ essential: true, analytics: true, marketing: false, ts: Date.now() })

// Show banner if no consent is stored
window.scb.forceShow() // If consent exists, use clearConsent() first
```

### Important notes
- `window.scb` is not available in `wp-admin`; open a public page to test
- `forceShow()` shows the banner only when no consent exists
- `clearConsent()` removes consent and reloads the page
- If `window.scb` is undefined, ensure the plugin is active, the banner is enabled, and you are on a frontend page

## Prerequisites
- Ensure the plugin is active
- Configure settings as desired (position, colors, categories)
- Optional: turn on “Enable Debug Mode” for developer helpers

## Quick Functional Test
1. Open your site in a private/incognito window
2. You should see the banner on first visit
3. Click Accept All → banner closes; reload may occur
4. Clear consent and try again with Preferences → custom selection

## Essential Console Commands
Open DevTools Console (F12):

Note: The `window.scb` API is available on frontend pages only (not in wp-admin). Run these tests on public pages.

```javascript
// Current consent object (or null if none)
window.scb.getConsent()

// Clear consent and reload shows banner again
window.scb.clearConsent() // triggers reload

// Save consent explicitly
window.scb.saveConsent({ essential: true, analytics: true, marketing: false, ts: Date.now() })
```

## Consent Scenarios to Verify
1) Accept All
```javascript
window.scb.saveConsent({ essential: true, analytics: true, marketing: true, ts: Date.now() })
```
Expected: non‑essential tracking allowed.

2) Essential Only (Reject)
```javascript
window.scb.saveConsent({ essential: true, analytics: false, marketing: false, ts: Date.now() })
```
Expected: analytics/marketing blocked.

3) Custom
```javascript
window.scb.saveConsent({ essential: true, analytics: true, marketing: false, ts: Date.now() })
```
Expected: analytics allowed, marketing blocked.

## Network Check (optional)
Use DevTools → Network. After giving consent:
- Look for requests to your analytics/marketing providers
- Before consent, these requests should not fire or should be blocked

## Debug Mode (optional)
If “Enable Debug Mode” is ON, a helper exists:
```javascript
// Simulate typical tracking calls for verification (no data is sent)
window.scb.testTracking()
```
Note: `testTracking()` is available only when Debug Mode is enabled.

## Troubleshooting (quick)
- Banner not showing: `window.scb.clearConsent()` then reload
- Consent not saved: check Console for errors, retry in a fresh private window
- Tracking fires without consent: review theme/plugins for hardcoded scripts that ignore consent

## Quick Checklist
- Banner displays for first‑time visitors
- Accept All closes banner and persists
- Preferences save custom selection and persist across reloads
- Tracking aligns with chosen consent levels

## Admin Tools: Purge Caches & Refresh Assets

If you change settings or don’t see updated JS/CSS on the frontend due to caching:

1. In wp-admin, open the plugin settings page
2. Click “Purge caches and refresh assets” (Tools block at top)
3. Reload the frontend page in a private window
4. Confirm public assets include a cache-busting version like `?ver=1.0.0-1699999999`
5. Re-test banner and consent behavior

# Cookie Consent Plugin Testing Guide

This guide provides comprehensive testing methods to verify that the ATR Cookie Notice plugin properly complies with user consent selections.

## 🧪 Testing Cookie Consent Compliance

### **1. Browser Developer Tools Testing**

#### **Console Testing (Easiest Method):**
Open your browser's Developer Tools (F12) and test these commands:

Note: The `window.scb` API exists only on frontend pages (not in wp-admin). Open a public page when running these commands.

```javascript
// Check current consent status
window.scb.getConsent()

// Check specific consent types
window.scb.consentGivenFor('analytics')
window.scb.consentGivenFor('marketing')

// Test tracking functionality
window.scb.testTracking()

// Debug consent cookies (function exists but currently empty)
window.scb.debugConsent()

// Test GTM detection (function exists but currently empty)
window.scb.debugGTM()
```

#### **Network Tab Testing:**
1. Open Network tab in DevTools
2. Clear browser data (cookies, localStorage)
3. Reload page → Should see banner
4. Accept different consent options
5. Watch for tracking requests:
   - **No consent**: Should see blocked/404 requests
   - **Analytics consent**: Should see Google Analytics requests
   - **Marketing consent**: Should see Facebook Pixel requests

### **2. Cookie Consent Scenarios Testing**

#### **Test Case 1: Accept All**
```javascript
// In console, simulate accepting all
window.scb.saveConsent({
  essential: true,
  analytics: true,
  marketing: true,
  ts: Date.now()
})
// Reload page - should see no banner, all tracking active
```

#### **Test Case 2: Reject Non-Essential**
```javascript
// In console, simulate rejecting non-essential
window.scb.saveConsent({
  essential: true,
  analytics: false,
  marketing: false,
  ts: Date.now()
})
// Reload page - should see no banner, no tracking
```

#### **Test Case 3: Custom Selection**
```javascript
// In console, simulate custom selection
window.scb.saveConsent({
  essential: true,
  analytics: true,
  marketing: false,
  ts: Date.now()
})
// Reload page - should see no banner, analytics only
```

### **3. Visual Testing Methods**

#### **Banner Behavior:**
1. **Clear all data** → Banner should appear
2. **Click "Accept All"** → Banner should disappear, page should reload
3. **Click "Reject"** → Banner should disappear, no reload
4. **Click "Preferences"** → Settings form should toggle
5. **Custom selection** → Form should work, banner should close

#### **Tracking Script Behavior:**
1. **Before consent**: Check if tracking scripts are blocked
2. **After consent**: Check if appropriate scripts are activated

### **4. Real Tracking Script Testing**

#### **Google Analytics Test:**
```javascript
// Before consent (should be undefined/blocked)
console.log('gtag available:', typeof window.gtag === 'function')
console.log('dataLayer:', window.dataLayer)

// After analytics consent (should work)
// Check if gtag() function is available and working
```

#### **Facebook Pixel Test:**
```javascript
// Before consent (should be undefined/blocked)
console.log('fbq available:', typeof window.fbq === 'function')

// After marketing consent (should work)
// Check if fbq() function is available
```

### **4.1. Comprehensive Data Collection Testing**

After verifying that tracking functions are working, here's how to test that **actual data collection** is functioning:

#### **🧪 Built-in Testing Functions**

The plugin includes comprehensive testing tools. In your browser console, run:

```javascript
// Test if tracking functions are working (not blocked)
window.scb.testTracking();

// Check current blocking status
window.scb.isTrackingBlocked();

// Note: debugConsent() and debugGTM() functions exist but are currently empty
// They are kept for compatibility but don't provide debug output
```

#### **📊 Google Analytics Data Collection Testing**

##### **Real-time Reports Verification:**
1. Go to **Google Analytics** → **Reports** → **Real-time** → **Events**
2. Perform actions on your site (click buttons, scroll, navigate)
3. You should see real-time data appearing in the reports

##### **Console Testing for GA:**
```javascript
// Test GA event tracking
gtag('event', 'test_event', {
  event_category: 'cookie_consent_test',
  event_label: 'user_clicked_accept_all'
});

// Test GA pageview
gtag('config', 'GA_MEASUREMENT_ID', {
  page_title: 'Test Page',
  page_location: window.location.href
});

// Check if gtag is working
console.log('gtag function type:', typeof gtag);
console.log('gtag function source:', gtag.toString().substring(0, 100));
```

#### **🏷️ Google Tag Manager Data Collection Testing**

##### **Preview Mode Testing:**
1. Go to **GTM** → **Preview** button
2. Enter your website URL
3. Perform actions and see real-time tag firing
4. Verify that tags are firing according to consent level

##### **Console Testing for GTM:**
```javascript
// Test dataLayer pushes
dataLayer.push({
  event: 'test_event',
  eventCategory: 'cookie_consent',
  eventAction: 'accept_all',
  eventLabel: 'test_label'
});

// Check dataLayer contents
console.log('dataLayer length:', dataLayer.length);
console.log('dataLayer contents:', dataLayer);

// Test if dataLayer.push is working
console.log('dataLayer.push function:', typeof dataLayer.push);
```

#### **📱 Facebook Pixel Data Collection Testing**

##### **Facebook Pixel Helper Extension:**
1. Install "Facebook Pixel Helper" Chrome extension
2. Browse your site and see pixel fires in real-time
3. Verify pixel events are firing after marketing consent

##### **Console Testing for Facebook Pixel:**
```javascript
// Test Facebook Pixel
fbq('track', 'PageView');
fbq('track', 'Lead', {
  content_name: 'Cookie Consent Test',
  content_category: 'Test Event'
});

// Check if fbq is working
console.log('fbq function type:', typeof fbq);
console.log('fbq function source:', fbq.toString().substring(0, 100));
```

#### **🌐 Network Tab Verification**

1. **Open Developer Tools** → **Network tab**
2. **Filter by domain:**
   - `google-analytics.com` (GA requests)
   - `googletagmanager.com` (GTM requests)
   - `facebook.com` (Facebook Pixel requests)
3. **Perform actions** on your site
4. **Look for successful requests** (200 status codes)

**Expected Results:**
- **Before consent**: Blocked/404 requests or no requests
- **After consent**: Successful 200 status requests to tracking services

#### **🎯 Real-world Testing Scenarios**

##### **Complete User Journey Test:**
```javascript
// 1. Clear consent and test from scratch
window.scb.clearConsent();
location.reload();

// 2. Accept all cookies in the banner
// 3. Navigate to different pages
// 4. Click buttons/links
// 5. Fill out forms
// 6. Check if data appears in GA/GTM real-time reports
```

##### **Specific Event Testing:**
```javascript
// Test various user interactions
document.querySelector('a').click(); // Test link clicks
window.scrollTo(0, 100); // Test scroll tracking
document.querySelector('form').submit(); // Test form submissions

// Test custom events
gtag('event', 'custom_event', {
  event_category: 'user_interaction',
  event_action: 'button_click',
  event_label: 'test_button'
});
```

#### **🔧 Advanced Debug Mode**

Enable more verbose logging for troubleshooting:

```javascript
// Enable debug mode for tracking
window.gtag_debug = true;
window.fbq_debug = true;

// Check function availability and status
console.log('=== Tracking Function Status ===');
console.log('gtag function:', typeof gtag);
console.log('ga function:', typeof ga);
console.log('fbq function:', typeof fbq);
console.log('dataLayer:', dataLayer);

// Check if functions are our restored versions
console.log('gtag is blocked:', gtag.toString().includes('SCB: gtag() blocked'));
console.log('ga is blocked:', ga.toString().includes('SCB: ga() blocked'));
console.log('fbq is blocked:', fbq.toString().includes('SCB: fbq() blocked'));
```

#### **📱 Cross-Platform Testing**

- **Test on mobile devices** (different user agents)
- **Test in incognito/private mode**
- **Test with different browsers** (Chrome, Firefox, Safari, Edge)
- **Test with different consent levels** on each platform

#### **✅ Expected Results After Accepting Cookies**

You should see these **console logs:**
```
SCB: Restoring tracking functions...
SCB: gtag() function restored
SCB: ga() function restored
SCB: fbq() function restored
SCB: dataLayer.push() function restored
SCB: Tracking functions restoration completed
```

**Note:** The actual plugin code doesn't include these specific console log messages. The tracking functions are restored silently when consent is given.

**Network requests:**
- ✅ Successful calls to Google Analytics
- ✅ Successful calls to Facebook Pixel
- ✅ Successful calls to GTM

**Real-time data:**
- ✅ Pageviews appearing in GA real-time
- ✅ Events firing in GTM preview
- ✅ Pixel fires in Facebook Pixel Helper

#### **🚨 Troubleshooting Data Collection Issues**

If data collection isn't working after consent:

```javascript
// 1. Check consent status
const consent = window.scb.getConsent();
console.log('Current consent:', consent);

// 2. Check if functions are still blocked
const blockingStatus = window.scb.isTrackingBlocked();
console.log('Blocking status:', blockingStatus);

// 3. Test individual functions
gtag('event', 'test');
ga('send', 'event', 'test');
fbq('track', 'PageView');

// 4. Check for JavaScript errors in console
// 5. Verify network requests are going through
```

**Common Issues:**
- Functions restored but still not working → Check for JavaScript errors
- Network requests failing → Check for CORS or blocking issues
- Real-time data not appearing → Check GA/GTM configuration
- Pixel fires not showing → Check Facebook Pixel Helper extension

### **5. Automated Testing Script**

Create a test file to run all scenarios:

```javascript
// test-consent.js - Run this in console
function testConsentCompliance() {
  console.log('🧪 Testing Cookie Consent Compliance...')
  
  // Test 1: No consent
  console.log('\n📋 Test 1: No consent')
  window.scb.clearConsent()
  console.log('Consent cleared, banner should show on reload')
  
  // Test 2: Essential only
  console.log('\n📋 Test 2: Essential only')
  window.scb.saveConsent({
    essential: true,
    analytics: false,
    marketing: false,
    ts: Date.now()
  })
  console.log('Essential consent saved, no tracking should work')
  
  // Test 3: Analytics consent
  console.log('\n📋 Test 3: Analytics consent')
  window.scb.saveConsent({
    essential: true,
    analytics: true,
    marketing: false,
    ts: Date.now()
  })
  console.log('Analytics consent saved, GA should work, FB should not')
  
  // Test 4: Full consent
  console.log('\n📋 Test 4: Full consent')
  window.scb.saveConsent({
    essential: true,
    analytics: true,
    marketing: true,
    ts: Date.now()
  })
  console.log('Full consent saved, all tracking should work')
}

// Run the test
testConsentCompliance()
```

### **6. What to Look For**

#### **✅ Success Indicators:**
- Banner appears for new users
- Banner disappears after consent
- No banner on return visits (with valid consent)
- Tracking scripts work according to consent level
- Settings form toggles properly
- Consent persists across page reloads

#### **❌ Failure Indicators:**
- Banner doesn't appear for new users
- Banner keeps showing after consent
- Tracking works without consent
- Settings form doesn't toggle
- Consent doesn't persist

### **7. Quick Test Checklist**

```markdown
□ Clear browser data
□ Reload page → Banner appears
□ Click "Accept All" → Banner disappears, page reloads
□ Reload page → No banner appears
□ Check console: window.scb.getConsent() shows full consent
□ Check Network tab: Tracking requests should work
□ Clear consent: window.scb.clearConsent()
□ Reload page → Banner appears again
□ Click "Preferences" → Settings form toggles
□ Custom selection → Form works, consent saved
```

### **8. Testing Different Consent Levels**

#### **Essential Only (Reject Non-Essential):**
- ✅ Website functions normally
- ❌ No Google Analytics tracking
- ❌ No Facebook Pixel tracking
- ❌ No marketing cookies

#### **Analytics Consent:**
- ✅ Website functions normally
- ✅ Google Analytics tracking works
- ❌ No Facebook Pixel tracking
- ❌ No marketing cookies

#### **Full Consent (Accept All):**
- ✅ Website functions normally
- ✅ Google Analytics tracking works
- ✅ Facebook Pixel tracking works
- ✅ All marketing cookies enabled

### **9. Troubleshooting Common Issues**

#### **Banner Not Appearing:**
1. Check if consent already exists: `window.scb.getConsent()`
2. Clear consent: `window.scb.clearConsent()`
3. Force show banner: `window.scb.forceShow()`
4. Check browser console for errors
5. Verify plugin is active in WordPress

#### **Settings Form Not Toggling:**
1. Check if `scb-settings` element exists
2. Verify CSS classes are working
3. Check JavaScript console for errors

#### **Tracking Not Working After Consent:**
1. Verify consent was saved correctly
2. Check if tracking scripts are properly unblocked
3. Reload page to ensure all changes take effect
4. Check Network tab for tracking requests

### **10. Performance Testing**

#### **Load Time Impact:**
- Measure page load time with banner
- Measure page load time without banner
- Banner should add minimal overhead (<100ms)

#### **Memory Usage:**
- Check memory usage in DevTools
- Plugin should not cause memory leaks
- Consent data should be lightweight

---

## 📋 **Quick Reference Commands**

```javascript
// Essential testing commands
window.scb.getConsent()                    // Check current consent
window.scb.clearConsent()                  // Clear all consent
window.scb.forceShow()                     // Force show banner
window.scb.testTracking()                  // Test tracking functions
window.scb.debugConsent()                  // Debug consent status
```

---

*For additional support or to report issues, please refer to the plugin documentation or contact support.*
