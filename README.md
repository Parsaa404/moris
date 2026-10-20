# Moris Website

Personal training website for **Moris** (Morteza) — PHP + MySQL, built for cPanel shared hosting.

## What this project is

- Bilingual site: **English** + **Persian (RTL)**
- **Admin panel** to edit all content (pages, packages, blog, gallery, settings)
- Contact form → database + email
- Minimal white, professional design

## Requirements

- PHP 8.0+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+

Local development uses **MAMP** (installed at `/Applications/MAMP/`).

## Local development with MAMP

MAMP bundles PHP 8.3 and MySQL 5.7/8.0. Default MySQL: user `root`, pass `root`, port `8889`.
`includes/config.php` is already set for this.

### 1. Start MAMP servers (keeps MySQL running)

Open the **MAMP** app (`/Applications/MAMP.app`) and click **Start Servers**.
This runs Apache (port 8888) and MySQL (port 8889) persistently in the background.

> Starting MySQL only from a one-off terminal command will not keep it alive —
> use the MAMP app so the server stays up while you work.

### 2. Create the database and import the schema (first time only)

```bash
MYSQL=/Applications/MAMP/Library/bin/mysql57/bin/mysql
$MYSQL -u root -proot -P 8889 -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS moris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$MYSQL -u root -proot -P 8889 -h 127.0.0.1 moris_db < sql/schema.sql
```

Or use MAMP's phpMyAdmin: create `moris_db` → Import → choose `sql/schema.sql`.

### 3. Run the site

Use MAMP's PHP built-in server from the project root:

**Easiest:** double-click **`Start Moris Website.command`** on your Desktop (or in the project folder).

Or from Terminal in the project folder:

```bash
/Applications/MAMP/bin/php/php8.3.30/bin/php -S localhost:8000 router.php
```

Always run from the **project root** (the folder that contains `en/`, `fa/`, `admin/`).
If the server starts from the wrong folder, Safari shows **Not Found** for every link.

### Local links

| Page | URL |
|------|-----|
| English home | http://localhost:8000/en/ |
| Persian home | http://localhost:8000/fa/ |
| **Admin login** | http://localhost:8000/admin/login.php |

Visitors do not log in — only you use **admin login** to edit the site.

On phone/tablet, tap the **☰ menu** (top right) to see Home, About, Services, etc.

> MAMP Apache (`http://localhost:8888/...`) only works if the project is in `htdocs/` and URLs include the folder name. Prefer port **8000** + `start-local.command` to avoid confusion.

## Project structure

```
moris-website/
├── index.php              # Redirect to default language
├── en/                    # English public pages
├── fa/                    # Persian public pages (RTL)
├── admin/                 # Admin panel (password protected)
├── includes/
│   ├── config.php         # DB credentials (not in git)
│   ├── config.example.php # Template for config
│   ├── db.php             # PDO connection
│   └── functions.php      # Helpers
├── assets/
│   ├── css/
│   ├── js/
│   └── images/uploads/    # Gallery & blog uploads
└── sql/
    └── schema.sql         # Database tables + demo data
```

## Build progress

| Step | Status | Description |
|------|--------|-------------|
| 1 | ✅ Done | Scaffold, config, database schema |
| 2 | ✅ Done | Admin login, installer, dashboard |
| 3 | ✅ Done | Pages CMS (Home, About, Services — EN + FA) |
| 4 | ✅ Done | Packages admin (add/edit/delete, prices) |
| 5 | ✅ Done | Public site: nav, Home, About, Services, Packages |
| 6 | ✅ Done | Contact form + admin inbox |
| 7 | ✅ Done | Gallery: upload, categories, masonry + lightbox |
| 8 | ✅ Done | Blog: posts, categories, featured images, public views |
| 9 | ✅ Done | Settings (contact/social/hero), UI polish, deployment docs |

## After install

All content is managed in **admin** — demo packages and text in `schema.sql` are placeholders. Update prices, pages, categories, and settings there.

## Deployment

- [DEPLOYMENT.md](DEPLOYMENT.md) — cPanel shared hosting  
- [DEPLOYMENT-UBUNTU.md](DEPLOYMENT-UBUNTU.md) — Ubuntu + Nginx + MySQL

## Master brief for Cursor

See [PROJECT.md](PROJECT.md) — paste or reference in new Cursor chats so the AI stays consistent.
