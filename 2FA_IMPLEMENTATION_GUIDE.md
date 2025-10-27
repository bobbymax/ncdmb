# Two-Factor Authentication Implementation Guide

## ✅ Implementation Complete!

Your NCDMB Portal now has Microsoft Authenticator support for Two-Factor Authentication (2FA).

---

## 🔧 What Was Installed

### Backend (Laravel)

-   ✅ **Package**: `pragmarx/google2fa-laravel` v2.3.0
-   ✅ **Migration**: Database migration for 2FA columns
-   ✅ **Controller**: `TwoFactorController` with generate, confirm, verify, disable, status endpoints
-   ✅ **User Model**: Updated with 2FA methods
-   ✅ **Routes**: API routes for 2FA management and verification
-   ✅ **Login Logic**: Updated `AuthApiController` to check for 2FA

### Frontend (React)

-   ✅ **Package**: `qrcode.react`
-   ✅ **Component**: `TwoFactorSetup.tsx` for enabling 2FA
-   ✅ **Component**: `TwoFactorChallenge.tsx` for login verification

---

## 🚀 Next Steps (Required)

### 1. Run Database Migration

You need to run the migration to add 2FA columns to your users table:

```bash
cd /Users/bobbyekaro/Sites/portal
php artisan migrate
```

This will add the following columns to your `users` table:

-   `two_factor_secret` (encrypted)
-   `two_factor_recovery_codes` (encrypted)
-   `two_factor_confirmed_at` (timestamp)
-   `two_factor_enabled` (boolean)

### 2. Test the Installation

#### Backend Test:

```bash
php artisan tinker
```

Then in tinker:

```php
$google2fa = new \PragmaRX\Google2FA\Google2FA();
$secret = $google2fa->generateSecretKey();
echo $secret;
```

If you see a secret key, the backend is working!

#### Frontend Test:

1. Start your React dev server: `npm start`
2. Navigate to where you want to add the 2FA setup
3. Import and use the components

---

## 📱 How to Use

### For Users: Enabling 2FA

1. **Navigate to Settings/Security** (you'll need to add this to your UI)
2. **Click "Enable 2FA"**
3. **Scan QR Code** with Microsoft Authenticator app
4. **Enter Code** to confirm
5. **Save Recovery Codes** (important!)

### For Developers: Integrating the Components

#### 1. In your Settings/Profile page:

```tsx
import TwoFactorSetup from "resources/views/components/Auth/TwoFactorSetup";

// In your component:
<TwoFactorSetup />;
```

#### 2. In your Login flow:

```tsx
import TwoFactorChallenge from 'resources/views/components/Auth/TwoFactorChallenge';

// In your login component:
const [requires2FA, setRequires2FA] = useState(false);
const [userId, setUserId] = useState<number>(0);

const handleLogin = async (credentials) => {
  try {
    const response = await axios.post('/api/login', credentials);

    // Check if 2FA is required
    if (response.data.requires_2fa) {
      setRequires2FA(true);
      setUserId(response.data.user_id);
    } else {
      // Normal login success
      window.location.href = '/dashboard';
    }
  } catch (error) {
    // Handle error
  }
};

// Render:
{requires2FA ? (
  <TwoFactorChallenge
    userId={userId}
    onSuccess={() => window.location.href = '/dashboard'}
    onCancel={() => {
      setRequires2FA(false);
      setUserId(0);
    }}
  />
) : (
  // Your normal login form
)}
```

---

## 🔐 API Endpoints

### Public Endpoints (No Auth Required)

-   `POST /api/2fa/verify` - Verify 2FA code during login

### Protected Endpoints (Require Auth)

-   `GET /api/2fa/status` - Get current user's 2FA status
-   `POST /api/2fa/generate` - Generate QR code for setup
-   `POST /api/2fa/confirm` - Confirm and enable 2FA
-   `POST /api/2fa/disable` - Disable 2FA (requires password)

---

## 🧪 Testing on Localhost

2FA works perfectly on `localhost:8000` or any local URL!

### Requirements:

1. ✅ Local Laravel server running
2. ✅ Smartphone with Microsoft Authenticator installed
3. ✅ Time synchronized between devices

### Steps:

1. Start Laravel: `php artisan serve`
2. Open `http://localhost:8000` in browser
3. Navigate to 2FA setup
4. Scan QR code with phone
5. Enter code from app
6. Test login!

---

## 🔍 Troubleshooting

### Issue: "Invalid authentication code"

**Solution**: Make sure your computer and phone times are synchronized.

```bash
# Mac
sudo sntp -sS time.apple.com

# Phone
Settings → Date & Time → Use network-provided time
```

### Issue: QR code won't scan

**Solutions**:

1. Increase screen brightness
2. Use the manual entry option (shows the secret key)
3. Make QR code bigger (already set to 220px)

### Issue: Migration fails

**Solution**: Make sure your database is running and configured in `.env`

### Issue: "Class not found" errors

**Solution**: Clear Laravel cache:

```bash
php artisan clear-compiled
php artisan cache:clear
composer dump-autoload
```

---

## 📊 User Model Methods

```php
// Enable 2FA
$user->enableTwoFactorAuthentication($secret);

// Disable 2FA
$user->disableTwoFactorAuthentication();

// Get secret (decrypted)
$secret = $user->getTwoFactorSecret();

// Generate recovery codes
$codes = $user->generateRecoveryCodes();

// Get recovery codes
$codes = $user->getRecoveryCodes();

// Check if 2FA is enabled
if ($user->two_factor_enabled) {
    // ...
}
```

---

## 🎨 Customization

### Colors

The components use your project's greenish color scheme (`#137547`). To change:

**TwoFactorSetup.tsx**:

-   Line 52: Main icon color
-   Line 68-73: Feature icons

**TwoFactorChallenge.tsx**:

-   Line 58: Shield icon color

### Text/Branding

-   App name: Set in `config/app.php` → `'name' => 'NCDMB Portal'`
-   All text is easily customizable in the React components

---

## 📝 Security Notes

1. **Secrets are encrypted** in the database using Laravel's `encrypt()` function
2. **Recovery codes are one-time use** - they're removed after being used
3. **Codes expire** after 30 seconds (TOTP standard)
4. **Rate limiting**: Consider adding to prevent brute force attacks
5. **Recovery codes**: Users get 8 codes, regenerate if they run out

---

## 🔄 Future Enhancements

Consider adding:

1. **Admin override**: Allow admins to disable 2FA for locked-out users
2. **SMS backup**: SMS codes as fallback (requires SMS service)
3. **Email alerts**: Notify users when 2FA is enabled/disabled
4. **Login history**: Track 2FA login attempts
5. **Force 2FA**: Require 2FA for specific user roles

---

## 📞 Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for frontend errors
3. Use `php artisan tinker` to test backend functions
4. Verify time synchronization between devices

---

## ✨ Success Indicators

You'll know it's working when:

-   ✅ QR code appears in setup
-   ✅ Microsoft Authenticator accepts the QR code
-   ✅ Codes from app verify successfully
-   ✅ Login prompts for 2FA code
-   ✅ Recovery codes work as backup

---

## 🎉 You're All Set!

Just run the migration and you're ready to test. The implementation follows Laravel and React best practices, with Microsoft Authenticator branding throughout.

**Remember**: Always test thoroughly before deploying to production!
