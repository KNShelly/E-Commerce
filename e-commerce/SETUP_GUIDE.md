# 🧱 E‑COMMERCE PROJECT SETUP GUIDE

## 📌 Overview
This guide helps you set up the local PHP e‑commerce scaffold on XAMPP/WAMP.

## ⚙ 1. System Requirements
### Software
- XAMPP or WAMP (local server environment)
- PHP 8.0 or later
- MySQL 8.0 or later
- VS Code (or preferred code editor)
- Git (for version control)

---

## 🗂 2. Folder Structure
Create the following directory inside your server root.

For XAMPP:
`C:\xampp\htdocs\e-commerce`

```
e-commerce/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   └── db.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── pages/
│   ├── home.php
│   ├── product.php
│   ├── cart.php
│   ├── checkout.php
│   ├── login.php
│   └── register.php
├── admin/
│   ├── dashboard.php
│   ├── add_product.php
│   ├── edit_product.php
│   ├── view_orders.php
│   └── manage_users.php
├── uploads/
├── index.php
├── README.md
└── SETUP_GUIDE.md
```

## 🚀 3. Run Locally
- Start Apache and MySQL in XAMPP.
- Open: `http://localhost/e-commerce/`
- Alternatively, from the project folder you can run the PHP dev server:
  - `php -S 127.0.0.1:8000`
  - Then open: `http://127.0.0.1:8000/`

## 🔧 4. Database
- Create a database: `ecommerce_db`
- Update credentials in `config/db.php` as needed.
- Default XAMPP credentials are `root` (no password).

## ✅ 5. Next Steps
- Implement product CRUD and connect to the DB.
- Build cart, checkout, and order flow.
- Add authentication and admin access control.
- Replace placeholder assets with real images/content.