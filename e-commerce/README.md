# E‑Commerce (PHP)

A minimal PHP e‑commerce scaffold to get you started quickly on XAMPP/WAMP.

## Features
- Simple project structure with assets, pages, includes, admin, config
- Basic router via index.php?page=...
- Shared header/footer and navbar
- Starter CSS/JS and placeholder pages

## Requirements
- PHP 8.0+
- MySQL 8.0+
- XAMPP/WAMP (local server)

## Getting Started
1. Copy this folder into your server root (XAMPP htdocs).
   - Current path: c:\\xampp\\htdocs\\e-commerce (keep as-is).
2. Start Apache/MySQL from XAMPP.
3. Create a database (e.g., ecommerce_db).
4. Update config/db.php credentials if needed (XAMPP defaults: user root, password empty).
5. Visit in browser:
   - If served via Apache: http://localhost/e-commerce/
   - If using PHP built-in server: http://127.0.0.1:8000/

## Notes
- This is a scaffold; wire up real product, cart, auth, and admin logic next.
- Replace placeholder images under assets/images with real assets.

## Structure
assets/
  css/style.css
  js/main.js
  images/
config/db.php
includes/
  header.php
  footer.php
  navbar.php
pages/
  home.php
  product.php
  cart.php
  checkout.php
  login.php
  register.php
admin/
  dashboard.php
  add_product.php
  edit_product.php
  view_orders.php
  manage_users.php
uploads/
index.php
README.md
SETUP_GUIDE.md