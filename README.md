# iVOTE CS — Setup Guide

## 1. Database Setup
1. Open phpMyAdmin or your MySQL client
2. Run `schema.sql` — this creates the database, all tables, and the default admin account

## 2. Configure Database Connection
Edit `/includes/db.php` and set your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'ivote_cs');
```

## 3. Deploy to Web Server
Place the entire `ivote/` folder inside your server's web root (e.g. `htdocs/` or `public_html/`).
The system expects to run at the **root** of a domain or virtual host (e.g. `http://localhost/`).

If deploying to a subdirectory (e.g. `http://localhost/ivote/`), update all `/` prefixed paths in navbar.php and shared.css.

## 4. First Login (Admin)
```
Student ID : ADM-9901
Password   : Admin@1234
```
**Change the password immediately** after first login via the database or a password-change page.

## 5. Add Your Logo
Place your logo image at `/assets/img/logo.png`.

## 6. Uploads Folder Permissions
Ensure the web server has **write permission** to `/uploads/documents/`:
```bash
chmod 755 uploads/
chmod 755 uploads/documents/
```

---

## File Structure
```
ivote/
├── schema.sql                  ← Run once in MySQL
├── index.php                   ← Guest homepage
├── login.php                   ← Login + Register
├── logout.php                  ← Session destroyer
├── pending.php                 ← Awaiting approval screen
├── about.php                   ← About the team
├── .htaccess                   ← Security + routing
│
├── includes/
│   ├── db.php                  ← Database connection
│   ├── auth.php                ← Session guards + helpers
│   ├── navbar.php              ← Shared navbar (all roles)
│   └── admin_sidebar.php       ← Admin sidebar
│
├── admin/
│   ├── dashboard.php           ← Admin overview
│   ├── verification.php        ← Approve / reject students
│   ├── accounts.php            ← View / delete voter accounts
│   ├── elections.php           ← Create / manage elections
│   └── candidates.php          ← Register candidates per position
│
├── student/
│   ├── dashboard.php           ← Student home + live results
│   ├── vote.php                ← Ballot casting page
│   └── account.php             ← Profile + document upload
│
├── assets/
│   ├── css/shared.css          ← Global styles (navbar, sidebar, etc.)
│   ├── js/shared.js            ← Dropdown + modal JS
│   └── img/logo.png            ← Place your logo here
│
└── uploads/
    ├── .htaccess               ← Blocks PHP execution in uploads
    └── documents/              ← Student COR/ID uploads (server-writable)
```

---

## User Role Matrix

| Feature                        | Guest | Student (Pending) | Student (Approved) | Admin |
|-------------------------------|-------|-------------------|--------------------|-------|
| View homepage                  | ✅    | ✅                | ✅                 | ✅    |
| View About page                | ✅    | ✅                | ✅                 | ✅    |
| Register account               | ✅    | —                 | —                  | —     |
| Log in                         | ✅    | ✅                | ✅                 | ✅    |
| View pending screen            | —     | ✅                | —                  | —     |
| Upload verification document   | —     | ✅                | ✅                 | —     |
| View student dashboard         | —     | —                 | ✅                 | —     |
| Cast vote                      | —     | —                 | ✅ (once)          | —     |
| View admin dashboard           | —     | —                 | —                  | ✅    |
| Approve / reject students      | —     | —                 | —                  | ✅    |
| Manage voter accounts          | —     | —                 | —                  | ✅    |
| Create / manage elections      | —     | —                 | —                  | ✅    |
| Add / remove candidates        | —     | —                 | —                  | ✅    |

---

## Election Flow
1. **Admin** creates an election (sets title, start/end date)
2. **Admin** adds candidates via Candidates Manager
3. Election auto-activates when start time is reached (or admin sets it manually)
4. **Approved students** see "Vote Now" banner, navigate to `/student/vote.php`
5. Students select one candidate per position and submit
6. Live vote counts appear on the dashboard
7. Admin can end the election at any time
