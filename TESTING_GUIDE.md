# Testing Guide for SMTP Email System

## Prerequisites
- DABS system must be installed and running
- Administrator account access
- Database migration must be completed (run migrate_email_settings.php)

## Test 1: Database Migration

**Objective**: Verify email_settings table was created successfully

**Steps**:
1. Access `http://your-domain/migrate_email_settings.php` (as admin)
2. Verify success message appears
3. Check database for `email_settings` table:
   ```sql
   SELECT * FROM email_settings;
   ```
4. Confirm default row exists with id=1

**Expected Result**: Table exists with default settings (smtp_enabled=0)

---

## Test 2: Admin Panel - Email Settings UI

**Objective**: Verify email settings interface loads correctly

**Steps**:
1. Log in as administrator
2. Navigate to Admin Panel
3. Click on "Email Settings" tab
4. Verify all form fields are visible:
   - Enable SMTP checkbox
   - SMTP Host, Port, Encryption
   - Authentication checkbox
   - Username/Password fields
   - From Email/Name fields
   - Test Email section

**Expected Result**: All fields display correctly with default values loaded

---

## Test 3: Save Email Settings (PHP mail() mode)

**Objective**: Verify settings can be saved without SMTP enabled

**Steps**:
1. In Email Settings tab
2. Keep SMTP disabled (checkbox unchecked)
3. Set From Email: `test@example.com`
4. Set From Name: `Test Sender`
5. Click "Save Settings"
6. Reload page and verify settings persist

**Expected Result**: 
- Success message appears
- Settings are saved to database
- Values persist after reload

---

## Test 4: Configure SMTP Settings

**Objective**: Verify SMTP configuration can be saved

**Steps**:
1. In Email Settings tab
2. Enable SMTP checkbox
3. Verify SMTP fields become visible
4. Enter test SMTP settings:
   - Host: `smtp.mailtrap.io` (or your test SMTP)
   - Port: `587`
   - Encryption: `TLS`
   - Enable Authentication
   - Username: `your-username`
   - Password: `your-password`
5. Click "Save Settings"

**Expected Result**: 
- Success message displays
- Settings saved successfully
- SMTP section remains visible

---

## Test 5: Send Test Email (PHP mail())

**Objective**: Test email sending with PHP mail() function

**Steps**:
1. Disable SMTP in settings
2. Save settings
3. Enter valid test email address
4. Click "Send Test Email"
5. Check test email inbox

**Expected Result**:
- Success or appropriate error message appears
- Email log updated in logs/email_log.txt
- Email received (if mail() is configured on server)

---

## Test 6: Send Test Email (SMTP)

**Objective**: Test email sending via SMTP

**Steps**:
1. Configure valid SMTP settings
2. Save settings
3. Enter valid test email address
4. Click "Send Test Email"
5. Check test email inbox

**Expected Result**:
- Success message appears
- Test email received with:
  - Correct subject line
  - Configuration details in body
  - Proper formatting
- Email log shows successful send

---

## Test 7: Invalid SMTP Configuration

**Objective**: Verify error handling for invalid SMTP settings

**Steps**:
1. Enable SMTP
2. Enter invalid settings:
   - Host: `invalid.smtp.server`
   - Port: `587`
3. Save settings
4. Try to send test email

**Expected Result**:
- Error message appears
- Email log shows failure
- No crash or unexpected behavior

---

## Test 8: Email Report Generation

**Objective**: Verify daily briefing emails use new system

**Steps**:
1. Create a daily briefing with activities
2. Navigate to email report section
3. Add recipient email addresses
4. Send daily briefing report
5. Check recipient inbox

**Expected Result**:
- PDF report generated
- Email sent via configured method (SMTP or mail())
- PDF attached to email
- Proper formatting and content

---

## Test 9: Email Configuration Security

**Objective**: Verify password security

**Steps**:
1. Save SMTP settings with password
2. Reload the page
3. Check password field - should be empty (placeholder)
4. Check database directly:
   ```sql
   SELECT smtp_password FROM email_settings WHERE id=1;
   ```

**Expected Result**:
- Password field shows placeholder on reload
- Database shows base64 encoded password (not plain text)
- Password not visible in browser dev tools

---

## Test 10: Non-Admin Access

**Objective**: Verify email settings are admin-only

**Steps**:
1. Log out
2. Log in as non-admin user
3. Try to access admin panel
4. Try direct access to ajax_admin.php?action=get_email_settings

**Expected Result**:
- Access denied message
- Non-admin users cannot view or modify settings

---

## Test 11: Log Viewing

**Objective**: Verify email activity is logged

**Steps**:
1. Send several test emails
2. Navigate to Log Viewer tab
3. Select email_log.txt
4. Click "Load Log"

**Expected Result**:
- Email log displays
- Shows timestamps in UK format
- Shows send attempts and results
- Properly formatted entries

---

## Common Issues and Solutions

### Issue: "Table not found" errors
**Solution**: Run migrate_email_settings.php to create the table

### Issue: Test emails not received
**Solutions**:
- Check spam/junk folder
- Verify SMTP credentials
- Check server firewall rules
- Review email_log.txt for errors

### Issue: Gmail authentication fails
**Solutions**:
- Use App Password instead of regular password
- Enable "Less secure app access" (not recommended)
- Check if 2FA is enabled

### Issue: Settings not saving
**Solutions**:
- Check database permissions
- Verify admin authentication
- Check browser console for JavaScript errors
- Review PHP error logs

---

## Performance Testing

### Load Test: Multiple Recipients
1. Create email report with 10+ recipients
2. Send report
3. Verify all recipients receive email
4. Check server load and response time

### Load Test: Large Attachments
1. Generate report with many activities
2. Verify large PDF sends successfully
3. Check email delivery time

---

## Security Checklist

- [ ] Only admins can access email settings
- [ ] SMTP passwords are not visible in UI
- [ ] Passwords are encoded in database
- [ ] AJAX endpoints validate admin role
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities in email content
- [ ] Email logs don't expose sensitive data
- [ ] Migration script requires admin access (web) or CLI

---

## Acceptance Criteria

All tests should pass with the following criteria:
- ✅ Database table created successfully
- ✅ Admin panel UI loads and functions correctly
- ✅ Settings can be saved and persist
- ✅ Test emails send successfully via both methods
- ✅ Error handling works appropriately
- ✅ Security measures are in place
- ✅ Email logging functions correctly
- ✅ Daily briefing reports use new email system
- ✅ No PHP or JavaScript errors in logs
- ✅ Non-admin users cannot access email settings
