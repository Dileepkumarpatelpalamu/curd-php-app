# CRUD PHP AJAX APP

This repository contains a **Core PHP + MySQL CRUD application** implemented using **AJAX**.
The complete functionality is handled using **only two PHP files** for simplicity and clarity.

GitHub Repository: [https://github.com/Dileepkumarpatelpalamu/curd-php-app](https://github.com/Dileepkumarpatelpalamu/curd-php-app)

---

## 📌 Project Overview

This project demonstrates how to perform **Create, Read, Update, and Delete (CRUD)** operations using:

* AJAX for asynchronous requests
* jQuery Validation for client-side validation
* PHP for server-side validation
* MySQL as the database
* Image upload and update functionality
* Pagination and search filter

The project is intentionally kept lightweight by using a **single AJAX controller file**.

---

## 🧰 Tech Stack

* Core PHP (Procedural)
* MySQL
* AJAX (jQuery)
* jQuery Validation Plugin
* HTML5, CSS3, JavaScript

---

## 📁 Project Structure

```
curd-php-app/
├── index.php        // Frontend UI + AJAX + jQuery Validation
├── get_ajax.php     // Backend controller (CRUD, validation, pagination)
├── table.sql        // Database table structure
├── uploads/         // Uploaded images
```

---

## ✨ Features

* AJAX-based CRUD operations (no page reload)
* Client-side form validation using jQuery Validation
* Server-side validation using PHP
* Image upload on insert and update
* Pagination using MySQL LIMIT & OFFSET
* Search filter with LIKE query
* Secure database queries using prepared statements

---

## 🧪 Client-Side Validation (jQuery)

```javascript
$("#userForm").validate({
  rules: {
    name: { required: true, minlength: 3 },
    email: { required: true, email: true }
  },
  messages: {
    name: "Name must be at least 3 characters",
    email: "Enter a valid email address"
  }
});
```

---

## 🛡 Server-Side Validation (PHP)

```php
if (empty($_POST['name'])) {
    echo "Name is required";
    exit;
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format";
    exit;
}
```

Server-side validation is mandatory even when client-side validation is used.

---

## 📄 Pagination

```php
$limit = 5;
$page = $_POST['page'] ?? 1;
$offset = ($page - 1) * $limit;

SELECT * FROM users LIMIT $limit OFFSET $offset;
```

Pagination improves performance and user experience.

---

## 🔍 Search Filter

```php
$search = $_POST['search'] ?? '';

SELECT * FROM users 
WHERE name LIKE '%$search%' OR email LIKE '%$search%';
```

Search works together with pagination using AJAX.

---

## 🔐 Prepared Statements (Security)

```php
$stmt = $conn->prepare("INSERT INTO users (name, email, image) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $image);
$stmt->execute();
```

Prepared statements prevent SQL injection.

---

## 🚀 How to Run the Project

1. Install **XAMPP** and start Apache & MySQL
2. Clone the repository:

```bash
git clone https://github.com/Dileepkumarpatelpalamu/curd-php-app.git
```

3. Move project to:

```
C:/xampp/htdocs/
```

4. Create a database and import `table.sql`
5. Open browser:

```
http://localhost/curd-php-app/index.php
```

## 👨‍💻 Author

**Dileep Kumar Patel**
PHP Backend Developer
GitHub: [https://github.com/Dileepkumarpatelpalamu](https://github.com/Dileepkumarpatelpalamu)
