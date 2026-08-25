/**
 * CAST Studio mail relay — paste this into Google Apps Script.
 *
 * Setup:
 * 1. https://script.google.com → New project → name it CAST Mail
 * 2. Replace Code.gs with this file
 * 3. Project Settings → Script properties:
 *      MAIL_SECRET = (same value you will put on Render as GOOGLE_APPS_SCRIPT_SECRET)
 * 4. Deploy → New deployment → Type: Web app
 *      Execute as: Me
 *      Who has access: Anyone
 * 5. Copy the Web app URL into Render:
 *      GOOGLE_APPS_SCRIPT_URL = (the /exec URL)
 *      GOOGLE_APPS_SCRIPT_SECRET = (same MAIL_SECRET)
 *      MAIL_FROM_ADDRESS = your Gmail
 *      MAIL_FROM_NAME = CAST Studio
 * 6. Save Render env → Manual Deploy
 *
 * Emails send FROM the Google account that owns this script.
 */
function doPost(e) {
  const secret = PropertiesService.getScriptProperties().getProperty('MAIL_SECRET');
  let payload = {};

  try {
    payload = JSON.parse((e.postData && e.postData.contents) || '{}');
  } catch (err) {
    return json_({ ok: false, error: 'Invalid JSON' });
  }

  if (!secret || payload.secret !== secret) {
    return json_({ ok: false, error: 'Unauthorized' });
  }

  const to = String(payload.to || '').trim();
  const subject = String(payload.subject || 'CAST Studio').trim();
  const html = String(payload.html || payload.text || '').trim();
  const text = String(payload.text || html.replace(/<[^>]+>/g, ' ')).trim();
  const fromName = String(payload.from_name || 'CAST Studio').trim();

  if (!to || !html) {
    return json_({ ok: false, error: 'Missing to or body' });
  }

  GmailApp.sendEmail(to, subject, text, {
    htmlBody: html,
    name: fromName,
  });

  return json_({ ok: true });
}

function doGet() {
  return json_({ ok: true, service: 'cast-mail' });
}

function json_(data) {
  return ContentService
    .createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
