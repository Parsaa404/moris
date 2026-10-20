# Moris — Ubuntu server deployment

Deploy on **Ubuntu 22.04 or 24.04** with **Nginx**, **PHP 8.2+**, and **MySQL 8** (or MariaDB).

Replace `YOUR_DOMAIN` with your real domain (e.g. `moristraining.com`).

---

## Automatic deploy (recommended)

### A) From your Mac — upload + install in one step

1. Copy and edit config:

```bash
cd ~/Projects/moris-website
cp deploy/ubuntu-env.example deploy/ubuntu-env
nano deploy/ubuntu-env   # set MORIS_DOMAIN, MORIS_DB_PASS, MORIS_CERTBOT_EMAIL
```

2. Set SSH target and run:

```bash
export MORIS_SSH=ubuntu@YOUR_SERVER_IP
bash deploy/push-and-deploy.sh
```

### B) On the server only

Upload the project to `/var/www/moris`, then:

```bash
cd /var/www/moris
cp deploy/ubuntu-env.example deploy/ubuntu-env
nano deploy/ubuntu-env
sudo bash deploy/ubuntu-install.sh
```

The script installs packages, creates the database, imports `sql/schema.sql`, writes `includes/config.php`, configures Nginx, and optionally runs Certbot.

After install:

1. Open `https://YOUR_DOMAIN/admin/install.php` → create admin  
2. **Delete** `admin/install.php`  
3. DB password is also saved on the server: `/root/moris-deploy-credentials.txt`

---

## Manual steps (if you prefer)

### 1. Server packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server \
  php-fpm php-mysql php-mbstring php-xml php-curl php-gd php-fileinfo \
  certbot python3-certbot-nginx unzip
```

Check PHP version and FPM socket:

```bash
php -v
ls /run/php/php*-fpm.sock
```

Use that socket path in the Nginx config (see step 5).

---

## 2. MySQL database

```bash
sudo mysql
```

In the MySQL shell:

```sql
CREATE DATABASE moris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'moris_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON moris_db.* TO 'moris_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Import schema (after files are uploaded):

```bash
mysql -u moris_user -p moris_db < /var/www/moris/sql/schema.sql
```

---

## 3. Upload site files

From your Mac (project folder):

```bash
rsync -avz --exclude includes/config.php \
  --exclude assets/images/uploads/* \
  --exclude includes/.installed \
  ~/Projects/moris-website/ user@YOUR_SERVER_IP:/var/www/moris/
```

Or use SFTP (FileZilla) to upload everything **except** local-only secrets.

On the server:

```bash
sudo mkdir -p /var/www/moris
sudo chown -R www-data:www-data /var/www/moris
sudo chmod -R u+rwX,g+rwX,o+rX /var/www/moris
sudo chmod -R g+w /var/www/moris/assets/images/uploads
```

---

## 4. Configure `includes/config.php`

On the server, create `includes/config.php` from the example:

```bash
cd /var/www/moris
sudo cp includes/config.example.php includes/config.php
sudo nano includes/config.php
```

Set:

```php
define('BASE_PATH', '');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'moris_db');
define('DB_USER', 'moris_user');
define('DB_PASS', 'STRONG_PASSWORD_HERE');
define('DEBUG_MODE', false);
```

---

## 5. Nginx

```bash
sudo cp /var/www/moris/deploy/nginx-moris.conf.example /etc/nginx/sites-available/moris
sudo nano /etc/nginx/sites-available/moris
# Edit server_name and fastcgi_pass socket path

sudo ln -sf /etc/nginx/sites-available/moris /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 6. SSL (HTTPS)

```bash
sudo certbot --nginx -d YOUR_DOMAIN -d www.YOUR_DOMAIN
```

---

## 7. Create admin user

1. Open **https://YOUR_DOMAIN/admin/install.php**
2. Create your admin account
3. **Delete** `admin/install.php` on the server immediately
4. Confirm `includes/.installed` exists

---

## 8. Production checklist

- [ ] `DEBUG_MODE` is `false` in `config.php`
- [ ] Database imported and site loads EN + FA home pages
- [ ] Admin login works at `/admin/login.php`
- [ ] Contact email set in admin **Settings**
- [ ] Uploads folder writable (`assets/images/uploads/`)
- [ ] HTTPS works
- [ ] `admin/install.php` removed

---

## Live URLs (after deployment)

| Page | URL |
|------|-----|
| English home | `https://YOUR_DOMAIN/en/` |
| Persian home | `https://YOUR_DOMAIN/fa/` |
| Admin login | `https://YOUR_DOMAIN/admin/login.php` |

There is **no separate** “website login” for visitors — only **admin login** for you to edit content.

---

## Optional: Apache on Ubuntu

If you prefer Apache instead of Nginx:

```bash
sudo apt install apache2 libapache2-mod-php
sudo a2enmod rewrite headers
```

Set `DocumentRoot` to `/var/www/moris`. The project `.htaccess` in the root applies security rules; ensure `AllowOverride All` is enabled for that directory.

---

## Local vs server

| | Local Mac | Ubuntu server |
|---|-----------|---------------|
| Start site | Double-click **Start Moris Website.command** on Desktop | Nginx always on |
| Database | MAMP MySQL port **8889** | MySQL port **3306** |
| URLs | `http://localhost:8000/...` | `https://YOUR_DOMAIN/...` |

See [DEPLOYMENT.md](DEPLOYMENT.md) for **cPanel** shared hosting (alternative to Ubuntu).
