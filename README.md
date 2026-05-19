# Library Management System

A professional, full-featured Library Management System built with **PHP 8+**, **MySQL**, **Bootstrap 5**, and **Vanilla JavaScript**. Follows clean MVC-like architecture with strong separation of concerns.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)

---

## Features

### Core Modules
- **Authentication** — Login/logout with session management, role-based access (Admin/Librarian)
- **Dashboard** — Stats overview with total books, borrowed, available, members, overdue counts
- **Book Management** — Full CRUD, search by title/author/ISBN, filter by category, pagination
- **Member Management** — Full CRUD, search, active/inactive status toggle
- **Borrow/Return System** — Borrow books, return with automatic fine calculation, overdue detection
- **Fine Calculation** — Automatic fine based on overdue days ($1.00/day default)

### Security
- CSRF token protection on all forms
- Password hashing with `password_hash()` / `password_verify()`
- PDO prepared statements (SQL injection prevention)
- XSS prevention via output escaping (`htmlspecialchars`)
- Session timeout & regeneration
- Input sanitization & validation

### UI/UX
- Modern dark glassmorphism design
- Fully responsive (mobile-friendly)
- Bootstrap 5 with custom premium styling
- Animated stat cards & page transitions
- Flash messages (success/error/warning)
- Delete confirmation modals
- Pagination on all list views

---

## Tech Stack

| Layer      | Technology            |
|------------|-----------------------|
| Backend    | PHP 8+ (Pure, no framework) |
| Database   | MySQL 8.0+            |
| Frontend   | HTML5, CSS3, JavaScript |
| CSS        | Bootstrap 5.3 + Custom CSS |
| DB Access  | PDO (prepared statements) |

---

## Project Structure

```
ipp/
├── app/
│   ├── controllers/       # AuthController, BookController, MemberController, etc.
│   ├── models/             # User, Book, Member, BorrowTransaction, Fine, Category
│   ├── services/           # BorrowService, FineService (business logic)
│   ├── middleware/          # AuthMiddleware (auth & CSRF protection)
│   ├── helpers/             # functions.php, flash.php (reusable utilities)
│   └── validators/          # BookValidator, MemberValidator
├── config/
│   ├── app.php             # Application constants & configuration
│   └── database.php        # PDO database singleton
├── public/
│   ├── index.php           # Front controller (entry point)
│   ├── .htaccess           # URL rewriting
│   └── assets/
│       ├── css/style.css   # Custom styles
│       └── js/app.js       # Client-side JavaScript
├── routes/
│   └── web.php             # Route definitions
├── views/
│   ├── layouts/            # header.php, footer.php, sidebar.php, navbar.php
│   ├── auth/               # login.php
│   ├── dashboard/          # index.php
│   ├── books/              # index, create, edit, show
│   ├── students/           # index, create, edit
│   └── transactions/       # index, borrow, return
├── sql/
│   └── library_management.sql  # Full database schema + seed data
├── storage/                # File storage directory
└── README.md
```

---

## Installation & Setup

### Prerequisites
- PHP 8.0+
- MySQL 8.0+
- Apache with `mod_rewrite` enabled (XAMPP/LAMPP/WAMP)

### Step 1: Clone / Copy Project
Copy the project folder into your web server's document root:
- **XAMPP (Windows):** `C:\xampp\htdocs\ipp\`
- **LAMPP (Linux):** `/opt/lampp/htdocs/ipp/`

### Step 2: Create Database
Open **phpMyAdmin** or MySQL CLI and run:
```sql
SOURCE /path/to/ipp/sql/library_management.sql;
```
Or import the file `sql/library_management.sql` via phpMyAdmin.

### Step 3: Configure Database Connection
Edit `config/database.php` if your MySQL credentials differ:
```php
private const DB_HOST = 'localhost';
private const DB_NAME = 'library_management';
private const DB_USER = 'root';
private const DB_PASS = '';        // Set your password here
private const DB_PORT = 3306;
```

### Step 4: Configure Base URL
Edit `config/app.php` and set the base URL to match your setup:
```php
define('BASE_URL', '/ipp/public');
```

### Step 5: Enable mod_rewrite
Make sure Apache's `mod_rewrite` is enabled:
- **XAMPP:** Uncomment `LoadModule rewrite_module` in `httpd.conf`
- **Also set** `AllowOverride All` for the htdocs directory

### Step 6: Access the Application
Navigate to: `http://localhost/ipp/public/login`

---

## Default Login Credentials

| Role      | Username   | Password    |
|-----------|------------|-------------|
| Admin     | `admin`    | `password`  |
| Librarian | `librarian`| `password`  |

---

## Borrow Rules (Configurable)

| Setting              | Default  | Config Location     |
|----------------------|----------|---------------------|
| Borrow Duration      | 14 days  | `config/app.php`    |
| Fine Per Day         | $1.00    | `config/app.php`    |
| Max Books Per Member | 5        | `config/app.php`    |

---

## Key Design Decisions

1. **Front Controller Pattern** — All requests route through `public/index.php`
2. **Singleton Database** — Single PDO connection via `Database::getInstance()`
3. **Service Layer** — Business logic in Services, not Controllers
4. **Reusable Layouts** — Header/footer/sidebar/navbar as partials
5. **CSRF Protection** — Token-based validation on every POST request
6. **Flash Messages** — Session-based alerts that survive redirects

---

## License

This project is for educational purposes. Feel free to use and modify.
