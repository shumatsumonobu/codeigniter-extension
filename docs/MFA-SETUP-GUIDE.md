# Multi-Factor Authentication (MFA) Setup Guide

This guide explains how to set up and use Multi-Factor Authentication (MFA) in the CodeIgniter Extension demo application.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Database Setup](#database-setup)
3. [First-Time Login](#first-time-login)
4. [MFA Setup Process](#mfa-setup-process)
5. [Login with MFA](#login-with-mfa)
6. [Backup Codes](#backup-codes)
7. [Account Recovery](#account-recovery)
8. [MFA Settings Management](#mfa-settings-management)

---

## Prerequisites

- PHP 8.0 or higher
- MySQL/MariaDB database
- Google Authenticator app (or any TOTP-compatible app)
  - [iOS App Store](https://apps.apple.com/app/google-authenticator/id388497605)
  - [Google Play Store](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2)

---

## Database Setup

Run the following SQL to set up the database with MFA support:

```sql
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `sample_db` DEFAULT CHARACTER SET utf8mb4;
USE `sample_db`;

-- User table with MFA columns
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('admin', 'member') NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `name` varchar(30) NOT NULL,
  `mfa_secret` varchar(64) DEFAULT NULL COMMENT 'TOTP secret key for MFA',
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether MFA is enabled',
  `backup_codes` text DEFAULT NULL COMMENT 'JSON array of hashed backup codes',
  `recovery_hash` varchar(255) DEFAULT NULL COMMENT 'Hash for account recovery token',
  `recovery_expires` int(10) unsigned DEFAULT NULL COMMENT 'Recovery token expiration timestamp',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ukAccountEmail` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Session table with primary key
CREATE TABLE IF NOT EXISTS `session` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Or use the provided `demo/init.sql` file:

```bash
mysql -u root -p < demo/init.sql
```

---

## First-Time Login

### Step 1: Access the Login Page

Navigate to the login page at `http://your-domain/users/login`.

![Login Page](screenshots/01-login-page.png)

### Step 2: Enter Credentials

Enter your email and password:
- **Email**: Your registered email address
- **Password**: Your password

### Step 3: Submit the Form

Click the "Sign In" button to proceed.

---

## MFA Setup Process

When MFA is not yet enabled, you will be redirected to the MFA setup page after login.

### Step 1: View the QR Code

The MFA setup page displays a QR code and your secret key.

![MFA Setup Page](screenshots/03-mfa-setup.png)

### Step 2: Scan QR Code

Open your Google Authenticator app and:
1. Tap the **+** button
2. Select **Scan a QR code**
3. Point your camera at the QR code on screen

**Alternative**: Manually enter the secret key shown below the QR code.

### Step 3: Enter Verification Code

Enter the 6-digit code displayed in your authenticator app into the verification field.

### Step 4: Save Backup Codes

After successful verification, you will receive 10 backup codes. **Save these codes securely!**

- Click **Download** to save as a text file
- Click **Print** to print a physical copy
- Click **Copy** to copy to clipboard

Each backup code can only be used once.

### Step 5: Complete Setup

Click "Continue to Dashboard" to finish the MFA setup process.

---

## Login with MFA

When MFA is enabled, you'll need to provide a verification code after entering your password.

### Step 1: Enter Password

Log in with your email and password as usual.

### Step 2: Enter MFA Code

You will be redirected to the MFA verification page.

![MFA Verify Page](screenshots/02-mfa-verify.png)

Enter the 6-digit code from your Google Authenticator app.

### Step 3: Alternative: Use Backup Code

If you don't have access to your authenticator app, you can use one of your backup codes instead.

---

## Backup Codes

Backup codes provide emergency access if you lose your authenticator device.

### Using a Backup Code

1. On the MFA verification page, enter a backup code instead of the TOTP code
2. The backup code format is: `XXXX-XXXX` (8 characters with hyphen)
3. Each code can only be used **once**

### Regenerating Backup Codes

If you're running low on backup codes:
1. Go to **MFA Settings**
2. Click **Regenerate Backup Codes**
3. Save the new codes securely
4. Previous codes will be invalidated

---

## Account Recovery

If you've lost access to both your authenticator app and backup codes:

### Step 1: Access Recovery Page

Navigate to `http://your-domain/users/mfa-recovery`.

![MFA Recovery Page](screenshots/05-mfa-recovery.png)

### Step 2: Enter Your Email

Enter the email address associated with your account.

### Step 3: Check Your Email

A recovery link will be sent to your email address. The link expires in 1 hour.

### Step 4: Complete Recovery

Click the recovery link in your email to disable MFA and regain access to your account.

**Note**: After recovery, you should set up MFA again for security.

---

## MFA Settings Management

Manage your MFA settings from the settings page.

![MFA Settings Page](screenshots/04-mfa-settings.png)

### Available Options

- **Enable MFA**: Set up MFA if not already enabled
- **Disable MFA**: Turn off MFA (requires current TOTP code)
- **Regenerate Backup Codes**: Generate new backup codes
- **View Remaining Codes**: Check how many backup codes you have left

---

## Troubleshooting

### Code Not Working

1. **Time Sync**: Ensure your phone's time is synchronized correctly
2. **Correct Account**: Make sure you're using the code from the correct account in Google Authenticator
3. **Try Again**: TOTP codes change every 30 seconds - wait for a new code

### Lost Authenticator Access

1. Use a backup code to log in
2. If no backup codes remain, use the account recovery feature

### QR Code Not Displaying

1. Clear your browser cache
2. Try a different browser
3. Manually enter the secret key shown below the QR code

---

## Security Best Practices

1. **Store backup codes offline** - Print them or save in a secure password manager
2. **Never share your secret key** - Treat it like a password
3. **Use a secure device** - Only install Google Authenticator on trusted devices
4. **Enable MFA everywhere** - Use MFA for all accounts that support it
5. **Update backup codes** - Regenerate codes if you've used several

---

## Technical Details

### TOTP Implementation

- **Algorithm**: HMAC-SHA1
- **Digits**: 6
- **Period**: 30 seconds
- **Secret Length**: 20 bytes (Base32 encoded)

### Backup Codes

- **Count**: 10 codes per generation
- **Format**: 8 alphanumeric characters with hyphen (XXXX-XXXX)
- **Storage**: Bcrypt hashed in database
- **Usage**: Single-use, removed after successful verification

### Recovery Token

- **Length**: 32 bytes (hex encoded)
- **Expiration**: 1 hour
- **Storage**: SHA-256 hashed in database

---

## API Reference

For developers integrating MFA:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/users/mfa-status` | GET | Check user's MFA status |
| `/api/users/mfa-setup` | POST | Initialize MFA setup |
| `/api/users/mfa-verify-setup` | POST | Verify code and complete setup |
| `/api/users/mfa-verify-login` | POST | Verify code during login |
| `/api/users/mfa-disable` | POST | Disable MFA |
| `/api/users/mfa-regenerate-backup-codes` | POST | Generate new backup codes |
| `/api/users/mfa-request-recovery` | POST | Request recovery token |
| `/api/users/mfa-verify-recovery` | POST | Verify recovery token |

---

## Support

For issues or questions:
- GitHub: [takuya-motoshima/codeigniter-extension](https://github.com/takuya-motoshima/codeigniter-extension)
- Issues: [GitHub Issues](https://github.com/takuya-motoshima/codeigniter-extension/issues)
