# Mini PHP Framework 🚀

A tiny Laravel-like framework built with:

- PHP 8 Attributes
- PSR-4 Autoloading
- DI Container
- Attribute Router
- Minimal HTTP layer

---

## Install

```bash
composer install
composer dump-autoload
```

---

## Run

```bash
php -S localhost:8000 -t public
```

---

## Routes

- GET / → MainController
- GET /user/123 → UserController
```