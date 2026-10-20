# Moris — cPanel Deployment Guide

For Dan (or anyone deploying to shared hosting).

> **Ubuntu VPS?** Use [DEPLOYMENT-UBUNTU.md](DEPLOYMENT-UBUNTU.md) instead (Nginx + PHP-FPM + MySQL).

## 1. Upload files

Upload all project files to `public_html/` (or subdomain folder) via File Manager or FTP.

Ensure these folders are writable for uploads (chmod 755 or 775 as host requires):

- `assets/images/uploads/`

## 2. Create MySQL database

In cPanel → **MySQL Databases**:

1. Create database (e.g. `cpaneluser_moris`)
2. Create database user with strong password
3. Add user to database with **ALL PRIVILEGES**

## 3. Import schema

cPanel → **phpMyAdmin** → select database → **Import** → upload `sql/schema.sql`

## 4. Configure site

On the server, edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cpaneluser_moris');
define('DB_USER', 'cpaneluser_morisuser');
define('DB_PASS', 'your_real_password');
define('DEBUG_MODE', false);  // IMPORTANT on production
```

## 5. Create admin user

Visit `https://yourdomain.com/admin/install.php` once to create the admin account.

**Then immediately:**
- Delete `admin/install.php` from the server
- Confirm `includes/.installed` exists (created automatically)
- Set `DEBUG_MODE` to `false` in `config.php`

See [SECURITY.md](SECURITY.md) for the full security checklist.

## 6. Email for contact form

In cPanel, create an email account (e.g. `hello@yourdomain.com`).

Set `contact_email` in admin **Settings** (or in `site_settings` table) to that address.

PHP `mail()` often works on cPanel; if not, configure SMTP in a later update.

## 7. SSL

cPanel → **SSL/TLS Status** → enable Let's Encrypt for the domain.

## 8. PHP version

cPanel → **Select PHP Version** → choose **PHP 8.1** or **8.2**, enable extensions:

- pdo_mysql
- mbstring
- fileinfo (for image uploads)

## Checklist

- [ ] Files uploaded
- [ ] Database created and schema imported
- [ ] `config.php` updated, `DEBUG_MODE` false
- [ ] Admin user created via install
- [ ] `uploads/` folder writable
- [ ] SSL enabled
- [ ] Contact form test message received

## Local path note

If the site runs in a subfolder, set `BASE_PATH` in `config.php` (e.g. `'/moris'`).
