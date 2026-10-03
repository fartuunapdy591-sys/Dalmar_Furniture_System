# Dalmar Furniture and House Interior Management System

A complete web-based management system (Laravel + MySQL) built for a
furniture and house interior business. It includes: Dashboard, Products,
Categories, Customers, Orders (with Order Details), Payments, Sales Reports
(charts), Users, and Settings — with role-based access control.

## Tech Stack
- **Backend:** Laravel 10 (PHP 8.1+)
- **Frontend:** Blade, HTML5, CSS3, JavaScript, Bootstrap 5, Chart.js
- **Database:** MySQL

## Installation

1. Install Composer dependencies:
   ```bash
   composer install
   ```

2. Create your `.env` file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   (On Windows, use `copy .env.example .env` instead of `cp`.)

3. Create a MySQL database named `dalmar_furniture` (or any name you like),
   then update your `.env` file accordingly:
   ```
   DB_DATABASE=dalmar_furniture
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Run the migrations and seed sample data:
   ```bash
   php artisan migrate --seed
   ```

5. Start the development server:
   ```bash
   php artisan serve
   ```

6. Visit `http://127.0.0.1:8000` — you will land on the Sign In page.

## Demo Login Credentials

| Role          | Email                    | Password  |
|---------------|--------------------------|-----------|
| Administrator | admin@dalmar.com         | password  |
| Sales Manager | sales@dalmar.com         | password  |
| Salesperson   | salesperson@dalmar.com   | password  |
| Accountant    | accountant@dalmar.com    | password  |

## Database Schema

| Table          | Description                                     |
|----------------|--------------------------------------------------|
| `users`        | System users and their roles                      |
| `categories`   | Product categories                                |
| `products`     | Products and stock levels                         |
| `customers`    | Customer records                                  |
| `orders`       | Customer orders                                   |
| `order_items`  | Line items belonging to each order                |
| `payments`     | Payment records for each order                    |
| `settings`     | Store-wide configuration (name, currency, etc.)   |

## Modules

- **Login** — secure session-based authentication with hashed passwords
- **Dashboard** — key metrics plus live charts (sales overview, sales by category)
- **Products** — add, edit, delete, and search products; automatic stock status
- **Categories** — manage product categories
- **Customers** — customer records and order history
- **Orders** — create multi-item orders, view order details, print invoice, update status
- **Payments** — payment method and status tracked per order
- **Sales Reports** — total sales, average order value, sales trend and category charts
- **Users** — manage system users and roles (Admin only)
- **Settings** — company info, currency, timezone, and notification preferences (Admin only)

## Core Features Covered

- All pages are fully functional (not static mockups)
- All data is persisted in a MySQL database
- Full Create, Read, Update, Delete (CRUD) support across every module
- Search and filtering on Products, Customers, and Orders
- Role-based access control (Admin, Sales Manager, Salesperson, Accountant)
- Dashboard statistics generated from real database queries

## Possible Future Enhancements

- Multi-warehouse inventory support
- Online payment gateway integration (Stripe, PayPal, etc.)
- Companion mobile application
- PDF/Excel export for sales reports
