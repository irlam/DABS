# SMTP Email System Setup

## Overview
This document describes the setup process for the new SMTP email system in DABS.

## Database Migration

Before using the SMTP email system, you need to create the `email_settings` table in your database.

### Option 1: Using phpMyAdmin or MySQL Client

1. Connect to your MySQL database
2. Select the `k87747_dabs` database
3. Run the SQL script from `database/email_settings.sql`

### Option 2: Using the command line

```bash
mysql -h 10.35.233.124 -u k87747_dabs -p k87747_dabs < database/email_settings.sql
```

## Configuration

After running the migration:

1. Log in to DABS as an administrator
2. Navigate to **Admin Panel** > **Email Settings** tab
3. Configure your SMTP settings:
   - **Enable SMTP**: Toggle on to use SMTP instead of PHP mail()
   - **SMTP Host**: Your SMTP server (e.g., smtp.gmail.com)
   - **SMTP Port**: Usually 587 for TLS, 465 for SSL
   - **Encryption**: Select TLS (recommended) or SSL
   - **Authentication**: Enable if your SMTP server requires it
   - **Username/Password**: Your SMTP credentials
   - **From Email/Name**: Default sender information

4. Save the settings
5. Send a test email to verify configuration

## Common SMTP Providers

### Gmail
- Host: smtp.gmail.com
- Port: 587 (TLS) or 465 (SSL)
- Note: Use an App Password, not your regular Gmail password
- How to create App Password: https://support.google.com/accounts/answer/185833

### Office 365
- Host: smtp.office365.com
- Port: 587 (TLS)

### SendGrid
- Host: smtp.sendgrid.net
- Port: 587 (TLS) or 465 (SSL)
- Username: apikey
- Password: Your SendGrid API key

### Mailgun
- Host: smtp.mailgun.org
- Port: 587 (TLS) or 465 (SSL)

## Features

- **Flexible Configuration**: Choose between SMTP or PHP mail()
- **Secure Storage**: SMTP passwords are encrypted in the database
- **PHPMailer Integration**: Uses industry-standard PHPMailer library
- **Test Functionality**: Send test emails to verify configuration
- **Detailed Logging**: All email activity is logged to email_log.txt

## Troubleshooting

If emails are not sending:

1. Check the email logs at `logs/email_log.txt`
2. Verify SMTP credentials are correct
3. Ensure firewall allows outbound connections on SMTP ports
4. For Gmail, enable "Less secure app access" or use App Password
5. Check spam/junk folders for test emails
6. Verify SPF/DKIM records for your domain

## Security Notes

- SMTP passwords are base64 encoded in the database (not fully encrypted)
- For production use, consider using environment variables for credentials
- Only administrators can view and modify email settings
- Test emails log the sender's username and timestamp
