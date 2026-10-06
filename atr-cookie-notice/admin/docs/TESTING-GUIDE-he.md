# מדריך בדיקת תוסף עוגיות (תמציתי)

מדריך זה מסביר כיצד לוודא במהירות ש‑ATR Cookie Notice מכבד את בחירת ההסכמה של המשתמש.

## רפרנס מהיר (פרונטאנד בלבד)
הערה: ה‑`window.scb` קיים רק בעמודי פרונטאנד (לא בתוך wp-admin).

```javascript
// בדוק הסכמה
window.scb.getConsent()

// נקה הסכמה (מטעין מחדש את הדף)
window.scb.clearConsent()

// בדוק סוג ספציפי
window.scb.consentGivenFor('analytics')

// שמור הסכמה
window.scb.saveConsent({ essential: true, analytics: true, marketing: false, ts: Date.now() })

// הצג באנר אם אין הסכמה שמורה
window.scb.forceShow() // אם קיימת הסכמה, השתמש תחילה ב‑clearConsent()
```

### הערות חשובות
- `window.scb` לא זמין בתוך `wp-admin`; פתח עמוד ציבורי לבדיקות
- `forceShow()` מציג באנר רק כאשר אין הסכמה קיימת
- `clearConsent()` מסיר הסכמה ומרענן את הדף
- אם `window.scb` לא מוגדר, ודא שהתוסף פעיל, הבאנר מופעל, ואת/ה בעמוד פרונטאנד

## דרישות מוקדמות
- ודאו שהתוסף פעיל
- הגדירו את ההגדרות הרצויות (מיקום, צבעים, קטגוריות)
- אופציונלי: הפעילו "מצב דיבוג" למפתח/ת

## בדיקת פונקציונליות מהירה
1. פתחו את האתר בחלון פרטי/אנונימי
2. בביקור ראשון הבאנר אמור להופיע
3. לחצו "קבל הכל" → הבאנר נסגר; ייתכן ריענון
4. נקו הסכמה ונסו שוב עם "העדפות" → בחירה מותאמת

## פקודות קונסול חיוניות
פתחו את קונסול כלי המפתח (F12):

הערה: ה‑`window.scb` זמין רק בעמודי פרונטאנד (לא בתוך wp-admin). הריצו את הבדיקות בעמוד ציבורי באתר.

```javascript
// אובייקט הסכמה נוכחי (או null אם אין)
window.scb.getConsent()

// ניקוי הסכמה וריענון להצגת הבאנר שוב
window.scb.clearConsent() // מפעיל ריענון

// שמירת הסכמה מפורשת
window.scb.saveConsent({ essential: true, analytics: true, marketing: false, ts: Date.now() })
```

## תרחישי הסכמה לבדיקה
1) קבל הכל
```javascript
window.scb.saveConsent({ essential: true, analytics: true, marketing: true, ts: Date.now() })
```
צפוי: מעקב לא חיוני מותר.

2) חיוני בלבד (דחייה)
```javascript
window.scb.saveConsent({ essential: true, analytics: false, marketing: false, ts: Date.now() })
```
צפוי: אנליטיקה/שיווק חסומים.

3) מותאם אישית
```javascript
window.scb.saveConsent({ essential: true, analytics: true, marketing: false, ts: Date.now() })
```
צפוי: אנליטיקה מותרת, שיווק חסום.

## בדיקת רשת (אופציונלי)
כלי מפתח → כרטיסיית Network. לאחר נתינת הסכמה:
- חפשו בקשות לספקי אנליטיקה/שיווק
- לפני הסכמה, בקשות אלה לא אמורות לצאת או אמורות להיחסם

## מצב דיבוג (אופציונלי)
אם "מצב דיבוג" פעיל, זמין מסייע:
```javascript
// סימולציית קריאות מעקב טיפוסיות (ללא שליחת נתונים)
window.scb.testTracking()
```
הערה: `testTracking()` זמין רק כשמצב דיבוג פעיל.

## פתרון בעיות (מהיר)
- באנר לא מופיע: `window.scb.clearConsent()` ואז רענון
- הסכמה לא נשמרת: בדקו שגיאות בקונסול, נסו חלון פרטי חדש
- מעקב יוצא ללא הסכמה: בדקו ערכת/תוספים שמזריקים סקריפטים תוך התעלמות מהסכמה

## צ'קליסט מהיר
- באנר מוצג למשתמש/ת חדשה
- "קבל הכל" סוגר באנר ונשמר
- "העדפות" שומר בחירה מותאמת ונשמרת בין ריענונים
- המעקב תואם לרמות ההסכמה שנבחרו

# מדריך בדיקת תוסף הסכמה לעוגיות

מדריך זה מספק שיטות בדיקה מקיפות לוודא שתוסף ATR Cookie Notice עומד בהתאם לבחירות ההסכמה של המשתמש.

## 🧪 בדיקת עמידה בהסכמה לעוגיות

### **1. בדיקה באמצעות כלי מפתח הדפדפן**

#### **בדיקת קונסול (השיטה הקלה ביותר):**
פתח את כלי המפתח של הדפדפן (F12) ובדוק את הפקודות הבאות:

הערה: ה‑`window.scb` קיים רק בעמודי פרונטאנד (לא בתוך wp-admin). יש לפתוח עמוד ציבורי בעת הרצת פקודות אלה.

```javascript
// בדוק מצב הסכמה נוכחי
window.scb.getConsent()

// בדוק סוגי הסכמה ספציפיים
window.scb.consentGivenFor('analytics')
window.scb.consentGivenFor('marketing')

// בדוק פונקציונליות מעקב
window.scb.testTracking()

// דיבוג עוגיות הסכמה (פונקציה קיימת אך כרגע ריקה)
window.scb.debugConsent()

// בדוק זיהוי GTM (פונקציה קיימת אך כרגע ריקה)
window.scb.debugGTM()
```

#### **בדיקת כרטיסיית רשת:**
1. פתח כרטיסיית רשת בכלי המפתח
2. נקה נתוני דפדפן (עוגיות, localStorage)
3. רענן דף → צריך לראות באנר
4. קבל אפשרויות הסכמה שונות
5. צפה בבקשות מעקב:
   - **ללא הסכמה**: צריך לראות בקשות חסומות/404
   - **הסכמה לאנליטיקה**: צריך לראות בקשות Google Analytics
   - **הסכמה לשיווק**: צריך לראות בקשות Facebook Pixel

### **2. בדיקת תרחישי הסכמה לעוגיות**

#### **מקרה בדיקה 1: קבל הכל**
```javascript
// בקונסול, סימולציה של קבלת הכל
window.scb.saveConsent({
  essential: true,
  analytics: true,
  marketing: true,
  ts: Date.now()
})
// רענן דף - צריך לראות ללא באנר, כל המעקב פעיל
```

#### **מקרה בדיקה 2: דחה לא חיוני**
```javascript
// בקונסול, סימולציה של דחיית לא חיוני
window.scb.saveConsent({
  essential: true,
  analytics: false,
  marketing: false,
  ts: Date.now()
})
// רענן דף - צריך לראות ללא באנר, ללא מעקב
```

#### **מקרה בדיקה 3: בחירה מותאמת אישית**
```javascript
// בקונסול, סימולציה של בחירה מותאמת אישית
window.scb.saveConsent({
  essential: true,
  analytics: true,
  marketing: false,
  ts: Date.now()
})
// רענן דף - צריך לראות ללא באנר, אנליטיקה בלבד
```

### **3. שיטות בדיקה ויזואלית**

#### **התנהגות באנר:**
1. **נקה כל הנתונים** → באנר צריך להופיע
2. **לחץ "קבל הכל"** → באנר צריך להיעלם, דף צריך להיטען מחדש
3. **לחץ "דחה"** → באנר צריך להיעלם, ללא טעינה מחדש
4. **לחץ "העדפות"** → טופס הגדרות צריך להתחלף
5. **בחירה מותאמת אישית** → טופס צריך לעבוד, באנר צריך להיסגר

#### **התנהגות סקריפטי מעקב:**
1. **לפני הסכמה**: בדוק אם סקריפטי מעקב חסומים
2. **אחרי הסכמה**: בדוק אם הסקריפטים המתאימים מופעלים

### **4. בדיקת סקריפטי מעקב אמיתיים**

#### **בדיקת Google Analytics:**
```javascript
// לפני הסכמה (צריך להיות undefined/חסום)
console.log('gtag זמין:', typeof window.gtag === 'function')
console.log('dataLayer:', window.dataLayer)

// אחרי הסכמה לאנליטיקה (צריך לעבוד)
// בדוק אם פונקציית gtag() זמינה ועובדת
```

#### **בדיקת Facebook Pixel:**
```javascript
// לפני הסכמה (צריך להיות undefined/חסום)
console.log('fbq זמין:', typeof window.fbq === 'function')

// אחרי הסכמה לשיווק (צריך לעבוד)
// בדוק אם פונקציית fbq() זמינה
```

### **4.1. בדיקת איסוף נתונים מקיפה**

לאחר וידוא שפונקציות המעקב עובדות, כך תבדוק ש**איסוף נתונים אמיתי** פועל:

#### **🧪 פונקציות בדיקה מובנות**

התוסף כולל כלי בדיקה מקיפים. בקונסול הדפדפן, הרץ:

```javascript
// בדוק אם פונקציות מעקב עובדות (לא חסומות)
window.scb.testTracking();

// בדוק מצב חסימה נוכחי
window.scb.isTrackingBlocked();

// הערה: פונקציות debugConsent() ו-debugGTM() קיימות אך כרגע ריקות
// הן נשמרות לתאימות אך לא מספקות פלט דיבוג
```

#### **📊 בדיקת איסוף נתונים Google Analytics**

##### **אימות דוחות בזמן אמת:**
1. עבור ל-**Google Analytics** → **דוחות** → **זמן אמת** → **אירועים**
2. בצע פעולות באתר שלך (לחץ על כפתורים, גלול, ניווט)
3. צריך לראות נתונים בזמן אמת מופיעים בדוחות

##### **בדיקת קונסול עבור GA:**
```javascript
// בדוק מעקב אירועי GA
gtag('event', 'test_event', {
  event_category: 'cookie_consent_test',
  event_label: 'user_clicked_accept_all'
});

// בדוק pageview של GA
gtag('config', 'GA_MEASUREMENT_ID', {
  page_title: 'דף בדיקה',
  page_location: window.location.href
});

// בדוק אם gtag עובד
console.log('סוג פונקציית gtag:', typeof gtag);
console.log('מקור פונקציית gtag:', gtag.toString().substring(0, 100));
```

#### **🏷️ בדיקת איסוף נתונים Google Tag Manager**

##### **בדיקת מצב תצוגה מקדימה:**
1. עבור ל-**GTM** → כפתור **תצוגה מקדימה**
2. הזן את כתובת האתר שלך
3. בצע פעולות וראה תגיות פועלות בזמן אמת
4. וודא שתגיות פועלות לפי רמת ההסכמה

##### **בדיקת קונסול עבור GTM:**
```javascript
// בדוק דחיפות dataLayer
dataLayer.push({
  event: 'test_event',
  eventCategory: 'cookie_consent',
  eventAction: 'accept_all',
  eventLabel: 'test_label'
});

// בדוק תוכן dataLayer
console.log('אורך dataLayer:', dataLayer.length);
console.log('תוכן dataLayer:', dataLayer);

// בדוק אם dataLayer.push עובד
console.log('פונקציית dataLayer.push:', typeof dataLayer.push);
```

#### **📱 בדיקת איסוף נתונים Facebook Pixel**

##### **תוסף Facebook Pixel Helper:**
1. התקן תוסף "Facebook Pixel Helper" של Chrome
2. גלוש באתר שלך וראה פיקסלים פועלים בזמן אמת
3. וודא שאירועי פיקסל פועלים אחרי הסכמה לשיווק

##### **בדיקת קונסול עבור Facebook Pixel:**
```javascript
// בדוק Facebook Pixel
fbq('track', 'PageView');
fbq('track', 'Lead', {
  content_name: 'בדיקת הסכמה לעוגיות',
  content_category: 'אירוע בדיקה'
});

// בדוק אם fbq עובד
console.log('סוג פונקציית fbq:', typeof fbq);
console.log('מקור פונקציית fbq:', fbq.toString().substring(0, 100));
```

#### **🌐 אימות כרטיסיית רשת**

1. **פתח כלי מפתח** → **כרטיסיית רשת**
2. **סנן לפי דומיין:**
   - `google-analytics.com` (בקשות GA)
   - `googletagmanager.com` (בקשות GTM)
   - `facebook.com` (בקשות Facebook Pixel)
3. **בצע פעולות** באתר שלך
4. **חפש בקשות מוצלחות** (קודי סטטוס 200)

**תוצאות צפויות:**
- **לפני הסכמה**: בקשות חסומות/404 או ללא בקשות
- **אחרי הסכמה**: בקשות מוצלחות עם קוד סטטוס 200 לשירותי מעקב

#### **🎯 תרחישי בדיקה בעולם האמיתי**

##### **בדיקת מסע משתמש מלא:**
```javascript
// 1. נקה הסכמה ובדוק מההתחלה
window.scb.clearConsent();
location.reload();

// 2. קבל את כל העוגיות בבאנר
// 3. ניווט לדפים שונים
// 4. לחץ על כפתורים/קישורים
// 5. מלא טפסים
// 6. בדוק אם נתונים מופיעים בדוחות זמן אמת של GA/GTM
```

##### **בדיקת אירועים ספציפיים:**
```javascript
// בדוק אינטראקציות משתמש שונות
document.querySelector('a').click(); // בדוק לחיצות על קישורים
window.scrollTo(0, 100); // בדוק מעקב גלילה
document.querySelector('form').submit(); // בדוק שליחת טפסים

// בדוק אירועים מותאמים אישית
gtag('event', 'custom_event', {
  event_category: 'user_interaction',
  event_action: 'button_click',
  event_label: 'test_button'
});
```

#### **🔧 מצב דיבוג מתקדם**

הפעל לוגים מפורטים יותר לפתרון בעיות:

```javascript
// הפעל מצב דיבוג למעקב
window.gtag_debug = true;
window.fbq_debug = true;

// בדוק זמינות וסטטוס פונקציות
console.log('=== סטטוס פונקציות מעקב ===');
console.log('פונקציית gtag:', typeof gtag);
console.log('פונקציית ga:', typeof ga);
console.log('פונקציית fbq:', typeof fbq);
console.log('dataLayer:', dataLayer);

// בדוק אם פונקציות הן הגרסאות המשוחזרות שלנו
console.log('gtag חסום:', gtag.toString().includes('SCB: gtag() blocked'));
console.log('ga חסום:', ga.toString().includes('SCB: ga() blocked'));
console.log('fbq חסום:', fbq.toString().includes('SCB: fbq() blocked'));
```

#### **📱 בדיקה חוצת פלטפורמות**

- **בדוק במכשירים ניידים** (user agents שונים)
- **בדוק במצב גלישה בסתר/פרטי**
- **בדוק בדפדפנים שונים** (Chrome, Firefox, Safari, Edge)
- **בדוק עם רמות הסכמה שונות** בכל פלטפורמה

#### **✅ תוצאות צפויות אחרי קבלת עוגיות**

צריך לראות את **לוגי הקונסול** הבאים:
```
SCB: משחזר פונקציות מעקב...
SCB: פונקציית gtag() שוחזרה
SCB: פונקציית ga() שוחזרה
SCB: פונקציית fbq() שוחזרה
SCB: פונקציית dataLayer.push() שוחזרה
SCB: שחזור פונקציות מעקב הושלם
```

**הערה:** הקוד האמיתי של התוסף לא כולל הודעות לוג קונסול ספציפיות אלה. פונקציות המעקב משוחזרות בשקט כאשר ניתנת הסכמה.

**בקשות רשת:**
- ✅ קריאות מוצלחות ל-Google Analytics
- ✅ קריאות מוצלחות ל-Facebook Pixel
- ✅ קריאות מוצלחות ל-GTM

**נתונים בזמן אמת:**
- ✅ Pageviews מופיעים בזמן אמת של GA
- ✅ אירועים פועלים בתצוגה מקדימה של GTM
- ✅ פיקסלים פועלים ב-Facebook Pixel Helper

#### **🚨 פתרון בעיות איסוף נתונים**

אם איסוף נתונים לא עובד אחרי הסכמה:

```javascript
// 1. בדוק מצב הסכמה
const consent = window.scb.getConsent();
console.log('הסכמה נוכחית:', consent);

// 2. בדוק אם פונקציות עדיין חסומות
const blockingStatus = window.scb.isTrackingBlocked();
console.log('מצב חסימה:', blockingStatus);

// 3. בדוק פונקציות בודדות
gtag('event', 'test');
ga('send', 'event', 'test');
fbq('track', 'PageView');

// 4. בדוק שגיאות JavaScript בקונסול
// 5. וודא שבקשות רשת עוברות
```

**בעיות נפוצות:**
- פונקציות משוחזרות אך עדיין לא עובדות → בדוק שגיאות JavaScript
- בקשות רשת נכשלות → בדוק בעיות CORS או חסימה
- נתונים בזמן אמת לא מופיעים → בדוק תצורת GA/GTM
- פיקסלים לא מופיעים → בדוק תוסף Facebook Pixel Helper

### **5. סקריפט בדיקה אוטומטי**

צור קובץ בדיקה להרצת כל התרחישים:

```javascript
// test-consent.js - הרץ זאת בקונסול
function testConsentCompliance() {
  console.log('🧪 בודק עמידה בהסכמה לעוגיות...')
  
  // בדיקה 1: ללא הסכמה
  console.log('\n📋 בדיקה 1: ללא הסכמה')
  window.scb.clearConsent()
  console.log('הסכמה נוקתה, באנר צריך להופיע בטעינה מחדש')
  
  // בדיקה 2: חיוני בלבד
  console.log('\n📋 בדיקה 2: חיוני בלבד')
  window.scb.saveConsent({
    essential: true,
    analytics: false,
    marketing: false,
    ts: Date.now()
  })
  console.log('הסכמה חיונית נשמרה, אין מעקב צריך לעבוד')
  
  // בדיקה 3: הסכמה לאנליטיקה
  console.log('\n📋 בדיקה 3: הסכמה לאנליטיקה')
  window.scb.saveConsent({
    essential: true,
    analytics: true,
    marketing: false,
    ts: Date.now()
  })
  console.log('הסכמה לאנליטיקה נשמרה, GA צריך לעבוד, FB לא')
  
  // בדיקה 4: הסכמה מלאה
  console.log('\n📋 בדיקה 4: הסכמה מלאה')
  window.scb.saveConsent({
    essential: true,
    analytics: true,
    marketing: true,
    ts: Date.now()
  })
  console.log('הסכמה מלאה נשמרה, כל המעקב צריך לעבוד')
}

// הרץ את הבדיקה
testConsentCompliance()
```

### **6. מה לחפש**

#### **✅ אינדיקטורים להצלחה:**
- באנר מופיע למשתמשים חדשים
- באנר נעלם אחרי הסכמה
- אין באנר בביקורים חוזרים (עם הסכמה תקפה)
- סקריפטי מעקב עובדים לפי רמת ההסכמה
- טופס הגדרות מתחלף כראוי
- הסכמה נשמרת בין טעינות דף

#### **❌ אינדיקטורים לכישלון:**
- באנר לא מופיע למשתמשים חדשים
- באנר ממשיך להופיע אחרי הסכמה
- מעקב עובד ללא הסכמה
- טופס הגדרות לא מתחלף
- הסכמה לא נשמרת

### **7. רשימת בדיקה מהירה**

```markdown
□ נקה נתוני דפדפן
□ רענן דף → באנר מופיע
□ לחץ "קבל הכל" → באנר נעלם, דף נטען מחדש
□ רענן דף → אין באנר מופיע
□ בדוק קונסול: window.scb.getConsent() מראה הסכמה מלאה
□ בדוק כרטיסיית רשת: בקשות מעקב צריכות לעבוד
□ נקה הסכמה: window.scb.clearConsent()
□ רענן דף → באנר מופיע שוב
□ לחץ "העדפות" → טופס הגדרות מתחלף
□ בחירה מותאמת אישית → טופס עובד, הסכמה נשמרת
```

### **8. בדיקת רמות הסכמה שונות**

#### **חיוני בלבד (דחה לא חיוני):**
- ✅ האתר פועל כרגיל
- ❌ אין מעקב Google Analytics
- ❌ אין מעקב Facebook Pixel
- ❌ אין עוגיות שיווק

#### **הסכמה לאנליטיקה:**
- ✅ האתר פועל כרגיל
- ✅ מעקב Google Analytics עובד
- ❌ אין מעקב Facebook Pixel
- ❌ אין עוגיות שיווק

#### **הסכמה מלאה (קבל הכל):**
- ✅ האתר פועל כרגיל
- ✅ מעקב Google Analytics עובד
- ✅ מעקב Facebook Pixel עובד
- ✅ כל עוגיות השיווק מופעלות

### **9. פתרון בעיות נפוצות**

#### **באנר לא מופיע:**
1. בדוק אם הסכמה כבר קיימת: `window.scb.getConsent()`
2. נקה הסכמה: `window.scb.clearConsent()`
3. הכרח הצגת באנר: `window.scb.forceShow()`
4. בדוק קונסול דפדפן לשגיאות
5. וודא שהתוסף פעיל בוורדפרס

#### **טופס הגדרות לא מתחלף:**
1. בדוק אם אלמנט `scb-settings` קיים
2. וודא שקבצי CSS עובדים
3. בדוק קונסול JavaScript לשגיאות

#### **מעקב לא עובד אחרי הסכמה:**
1. וודא שהסכמה נשמרה כראוי
2. בדוק אם סקריפטי מעקב נפתחו כראוי
3. רענן דף כדי לוודא שכל השינויים נכנסים לתוקף
4. בדוק כרטיסיית רשת לבקשות מעקב

### **10. בדיקת ביצועים**

#### **השפעת זמן טעינה:**
- מדוד זמן טעינת דף עם באנר
- מדוד זמן טעינת דף ללא באנר
- באנר צריך להוסיף עומס מינימלי (<100ms)

#### **שימוש בזיכרון:**
- בדוק שימוש בזיכרון בכלי המפתח
- התוסף לא צריך לגרום לדליפות זיכרון
- נתוני הסכמה צריכים להיות קלים

---

## 📋 **פקודות עזר מהירות**

```javascript
// פקודות בדיקה חיוניות
window.scb.getConsent()                    // בדוק הסכמה נוכחית
window.scb.clearConsent()                  // נקה כל הסכמה
window.scb.forceShow()                     // הכרח הצגת באנר
window.scb.testTracking()                  // בדוק פונקציות מעקב
window.scb.debugConsent()                  // דיבוג מצב הסכמה
```

---

*לתמיכה נוספת או לדיווח על בעיות, אנא עיין בתיעוד התוסף או פנה לתמיכה.*
