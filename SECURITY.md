# Moris — Security overview

This document summarizes the security review of the Moris PHP + MySQL website and what was hardened.

## Already solid (before this pass)

- SQL Injection: PDO prepared statements everywhere; no raw user SQL
- Password storage: `password_hash` / `password_verify` (bcrypt)
- CSRF tokens on admin forms + contact form (`hash_equals`)
- XSS on text fields: `htmlspecialchars` via `e()`
- Image uploads: MIME allowlist + random filenames + admin-only
- No open redirects found
- No command execution / LDAP / NoSQL / JWT / GraphQL / WebSockets in this app

## Hardened in this pass

| Area | What we did |
|------|-------------|
| Security headers | `X-Frame-Options`, `nosniff`, `Referrer-Policy`, CSP, HSTS when HTTPS |
| Clickjacking | Frame protection headers |
| Session cookies | `HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS |
| Session fixation | `session_regenerate_id(true)` on login + CSRF rotate |
| Broken access control | `require_admin()` re-checks active admin in DB |
| Brute force | Login limited to 5 attempts / 15 min per IP+user |
| Rate limiting | Contact form limited to 5 messages / 10 min per IP |
| Mail injection | Strip CR/LF from subject/name/headers |
| Stored XSS (HTML) | `sanitize_html()` on page/blog/package save |
| Social URL XSS | Only `http`/`https` URLs allowed |
| Path traversal | `delete_upload()` uses `realpath()` containment |
| Upload RCE | `.htaccess` disables PHP in uploads folder |
| Info disclosure | Deny web access to `includes/`, `sql/`; no directory listing |
| Install abuse | Lock file `includes/.installed`; DB errors keep install locked |
| Password policy | Install requires 10+ chars with letters + numbers |

## Your checklist — what applies vs not

### Fixed / protected in code
SQL Injection, XSS (reflected + stored HTML sanitization), CSRF, Session fixation, Session cookie flags, Broken access control (admin), Authentication brute force, Rate limiting (login + contact), Clickjacking headers, Insecure file upload (hardened), Directory traversal (uploads delete), Sensitive path exposure, Password policy (install), Backup/schema exposure via `.htaccess`

### You / Dan must do on hosting
| Item | Action |
|------|--------|
| HTTPS / TLS / HSTS | Enable SSL in cPanel (Let's Encrypt) |
| `DEBUG_MODE` | Set `false` in production `config.php` |
| Delete `admin/install.php` | After first admin is created |
| Strong admin password | Unique, long, not reused |
| Backups | cPanel backups / database export |
| DNS / domain | Point domain to host; keep registrar lock |

### Not applicable to this site
SSRF, RCE via eval/exec, LFI/RFI (no include of user paths), XXE, LDAP, NoSQL, SSTI, Insecure deserialization, JWT, GraphQL, WebSocket, CORS API, Command injection — **not used** in this codebase.

### Needs hosting / ops (not pure PHP)
Port scan, SSL labs scan, WAF, DoS resistance, CVE/dependency scan of server packages, subdomain takeover, malware/web shell monitoring, full OWASP pen-test — ask Dan/host or a security professional for live server tests. **Do not run DoS or attack tools against production.**

## After deploy (Dan checklist)

1. `DEBUG_MODE = false`
2. Delete `admin/install.php`
3. Confirm `.htaccess` files uploaded
4. Force HTTPS in cPanel
5. Test login lockout + contact form
6. Keep `includes/config.php` private (never commit passwords)

## Local note

Local MAMP uses HTTP, so the `Secure` cookie flag stays off until HTTPS. That is expected for local development.
