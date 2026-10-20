# Moris — Personal Training Website (Master Project Brief)

## Owner
Morteza — freelance personal trainer, KL Malaysia. Brand name: **Moris**.
13+ years coaching. Services: 1-on-1 PT, weight training, bodyweight, boxing.
Goals: local KL clients + worldwide online clients.

## Stack (mandatory)
- PHP 8.x (no Laravel/Symfony — plain PHP for cPanel shared hosting)
- MySQL (PDO)
- HTML5, CSS3, minimal vanilla JavaScript
- Compatible with cPanel shared hosting (no Node on server)

## Local environment (mandatory for dev)
- **MAMP** at `/Applications/MAMP/` — used for ALL local development (no Docker/Homebrew).
- PHP: `/Applications/MAMP/bin/php/php8.3.30/bin/php`
- MySQL 5.7 client: `/Applications/MAMP/Library/bin/mysql57/bin/mysql`
- MySQL: host `127.0.0.1`, port `8889`, user `root`, pass `root`
- Start servers via the MAMP app (Start Servers) so MySQL stays alive.
- `includes/config.php` uses `DB_PORT` 8889 locally; cPanel uses 3306 (see config.example.php).

## Design (luxurious Tiffany · black · white)
- Clean cool-white base for broad appeal; black type for authority; Tiffany `#0ABAB5` accents
- Titles / headings: **DM Serif Display** (luxurious); body / UI / descriptions: **Albert Sans** (premium readable); Persian: **Vazirmatn**
- PDO always uses `SET NAMES utf8mb4` so Persian text never shows as broken code letters
- Prefer plain ASCII punctuation in English copy (apostrophe ', hyphen -) for clean display
- Sticky glass header; black primary CTAs that hover to Tiffany; refined package cards
- Soft Tiffany hero wash (or photo hero overlay); dark footer with Tiffany accent mark
- Avoid loud gym-red / all-black themes — premium coach look that suits mixed audiences
- Mobile-first, responsive; RTL resets letter-spacing / uppercase for Persian

## Languages
- English (LTR) and Persian (RTL)
- URL pattern: `/en/...` and `/fa/...`
- All CMS content: `_en` and `_fa` fields
- Language switcher in header
- Persian pages use `dir="rtl"`

## Public pages
1. Home — hero, intro, featured packages, blog preview, gallery preview, CTA
2. About — CMS-driven
3. Services — CMS-driven
4. Packages/Pricing — exact fixed prices from database
5. Gallery — categorized masonry grid, lightbox
6. Blog — list + single post
7. Contact — form → MySQL + email notification

## Admin panel (`/admin`)
- Secure login (password_hash / password_verify), session-based
- Dashboard with basic stats
- CRUD: Pages, Packages, Blog posts, Blog categories, Gallery images, Gallery categories
- View/mark read: Contact messages
- Settings: contact email, phone, WhatsApp, social links, tagline
- **NO hardcoded business content in PHP templates**

## Content rule (critical)
ALL business content must be database-driven and editable in admin:
- Page text (Home, About, Services) — EN + FA
- Packages: name, price, currency, description, features, active flag
- Blog posts AND blog categories — full CRUD
- Gallery images AND gallery categories — full CRUD
- Site settings: notification email, phone, WhatsApp, social URLs, tagline
- NEVER hardcode prices, categories, contact email, or page copy in PHP/HTML
- Demo rows in `sql/schema.sql` are placeholders only

## Database tables
`admin_users`, `site_settings`, `pages`, `packages`, `blog_categories`, `blog_posts`, `gallery_categories`, `gallery_items`, `contact_messages`

## Security basics
- Prepared statements (PDO)
- `htmlspecialchars` on output
- CSRF tokens on admin forms
- Image upload validation (type + size)
- `config.php` not committed with real passwords

## File structure
```
moris-website/
├── index.php
├── en/  fa/
├── admin/
├── includes/  (config.php, db.php, functions.php, auth.php — later)
├── assets/css/  assets/js/  assets/images/uploads/
├── sql/schema.sql
├── PROJECT.md
├── README.md
└── DEPLOYMENT.md
```

## Out of scope for v1
- Client login portal, booking calendar, payment gateway
- React, Vue, WordPress

## Build steps
1. ✅ Folder structure, config, schema.sql, README
2. ✅ Admin login + install.php (first admin user) + dashboard
3. ✅ Pages CMS (Home, About, Services — EN + FA)
4. ✅ Packages admin (add/edit/delete, prices, EN + FA)
5. ✅ Public site: nav menu, Home, About, Services, Packages (DB-driven, EN + FA)
6. ✅ Contact form (EN + FA, CSRF, honeypot, email) + admin inbox
7. ✅ Gallery: admin upload + categories; public masonry + lightbox + filter
8. ✅ Blog: admin posts + categories + featured image; public list + single post (EN + FA)
9. ✅ Settings admin (contact, social, tagline, default lang, hero image); UI polish; DEPLOYMENT.md

## Settings (admin-managed, site_settings table)
- site_name, tagline_en/fa, default_language
- contact_email (enquiry notifications), contact_phone, contact_whatsapp
- instagram_url, youtube_url (footer icons auto-appear when set)
- hero_image (optional homepage hero background with dark overlay)

## Development rules
- One feature per step; minimal diffs
- Simple commented code for a beginner
- Explain how to test after each step
- User provides real photos/text via admin after CMS is ready

## Rich text editor (content fields)
- Long content fields (page content, blog content) use a WYSIWYG editor —
  the user does NOT hand-write HTML.
- Editor: TinyMCE 7 self-hosted GPL build via jsDelivr CDN (no API key).
- Reusable include: `admin/_editor.php` (loads TinyMCE + `assets/js/admin-editor.js`).
- Markup contract: `<textarea class="rich-ltr">` for English/LTR,
  `<textarea class="rich-rtl" dir="rtl">` for Persian/RTL.
- Editor writes clean HTML back to the textarea; public pages output it as HTML.
- Requires internet for the CDN; falls back to a plain textarea if offline.
- Reuse `admin/_editor.php` in future content forms (blog posts, etc.).

## Deployment
See `DEPLOYMENT.md` — Dan handles cPanel upload.
