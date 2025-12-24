# User Manual: Authentication

## 2.1 Overview

The Authentication module ensures secure access to the Ergon Site system. It handles user login, password management, and session security.

## 2.2 Logging In

**Procedure:**
1. Open your web browser and navigate to the Ergon Site login page.
2. Enter your **Username** (usually your email address or employee ID).
3. Enter your **Password**.
4. Click **Login**.

**Expected Outcome:**
- If credentials are correct, you'll be redirected to the dashboard.
- If incorrect, an error message will appear. After multiple failed attempts, your account may be temporarily locked.

## 2.3 Password Requirements

Passwords must meet the following criteria:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character (!@#$%^&*)

## 2.4 Forgot Password

If you forget your password:
1. On the login page, click **Forgot Password?**.
2. Enter your registered email address.
3. Click **Send Reset Link**.
4. Check your email for a password reset link.
5. Follow the link to create a new password.

*Note: Reset links expire after 24 hours for security.*

## 2.5 Changing Password

To change your password regularly:
1. After logging in, click on your profile avatar in the top right.
2. Select **Change Password**.
3. Enter your **Current Password**.
4. Enter the **New Password** (meeting requirements).
5. Confirm the **New Password**.
6. Click **Update Password**.

## 2.6 Multi-Factor Authentication (MFA)

If enabled by your administrator:
1. After entering username and password, you'll receive a code via email or SMS.
2. Enter the code in the provided field.
3. Click **Verify**.

## 2.7 Session Management

- **Auto Logout:** Inactive sessions automatically log out after 30 minutes.
- **Concurrent Sessions:** Only one active session per user is allowed.
- **Logout:** Always click **Logout** when finished to secure your account.

## 2.8 Account Lockout

After 5 failed login attempts, your account will be locked for 15 minutes. Contact your administrator to unlock it manually.

## 2.9 Security Best Practices

- Never share your credentials.
- Use a strong, unique password.
- Enable MFA if available.
- Log out when using public computers.
- Report suspicious activity immediately.

## 2.10 Troubleshooting Login Issues

### Common Problems
**Q: I can't log in despite correct credentials.**  
A: Check if Caps Lock is on, or if your account is locked due to failed attempts.

**Q: Forgot password link not received.**  
A: Check spam/junk folder. Ensure email address is correct.

**Q: Session expired unexpectedly.**  
A: Sessions auto-expire after 30 minutes of inactivity. Log in again.

**Q: Account locked.**  
A: Wait 15 minutes or contact admin for manual unlock.

### Browser Issues
- Clear browser cache and cookies.
- Try incognito/private mode.
- Update browser to latest version.

## 2.11 Advanced Authentication Features

### Single Sign-On (SSO)
If configured, use your corporate credentials to log in seamlessly.

### API Authentication
For integrations, use API keys or OAuth tokens. Contact admin for setup.

### Biometric Login
Future feature: Fingerprint or face recognition for quick access.

## 2.12 Account Recovery

1. Contact HR or IT support with your employee ID.
2. Provide verification details (date of birth, etc.).
3. Admin will reset your password.

## 2.13 Audit and Logs

All login attempts are logged for security. Admins can review access history.

## 2.14 Compliance

The system complies with GDPR, HIPAA, and other privacy standards for authentication data.