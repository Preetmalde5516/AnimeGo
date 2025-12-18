# 🎬 AnimeGo – Anime Streaming Web Application

![PHP](https://img.shields.io/badge/PHP-8.4-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.4-orange?logo=mysql)
![Apache](https://img.shields.io/badge/Apache-XAMPP-red?logo=apache)
![Status](https://img.shields.io/badge/Status-Academic%20Project-success)
![License](https://img.shields.io/badge/License-Educational-lightgrey)

AnimeGo is a **web-based anime streaming platform** developed using **PHP and MySQL**.
It allows users to explore, watch, and manage anime movies and series with a clean UI, while providing an **admin panel** for complete content control.

This project follows the **Software Development Life Cycle (SDLC)** and was developed for **academic learning purposes**.

---

## 🌟 Features

### 👤 User Features

* User Registration & Login
* Browse Anime Movies & Series
* Watch Anime Videos
* Search Anime by Title
* View Anime Details (Genre, Description, Year)
* Add / Remove from Watchlist
* Contact Admin via Contact Form

### 🛠️ Admin Features

* Secure Admin Login
* Admin Dashboard
* Manage Movies (Add / Edit / Delete)
* Manage Series & Episodes
* Manage Genres
* Manage Users
* Manage Contact Messages
* Database Management

---

## 👥 User Roles

| Role            | Permissions                                        |
| --------------- | -------------------------------------------------- |
| Admin           | Full control over users, movies, series & database |
| Registered User | Watch anime & manage watchlist                     |
| Guest User      | Browse content & register                          |

---

## 🖥️ Tech Stack

### Frontend

* HTML
* CSS
* JavaScript

### Backend

* PHP (8.4)
* MySQL (8.4.5)

### Tools & Environment

* XAMPP (Apache + MySQL)
* phpMyAdmin
* Visual Studio Code
* Browser: Chrome / Firefox

---

## 📂 Database Structure

Main tables used:

* `users`
* `movies`
* `series`
* `episodes`
* `genres`
* `movie_genres`
* `series_genres`
* `user_watchlist`
* `contact_messages`

The database is **normalized**, uses **foreign keys**, and follows **ER-diagram design**.

---

## 📸 Screenshots

### 🧑 User Side

* Home Page
* Login & Signup
* Movies Page
* Series Page
* Watchlist
* Contact Us

### 🛠️ Admin Side

* Admin Dashboard
* Manage Movies
* Manage Series
* Manage Users
* Manage Messages

> 📌 Screenshots are available in the project documentation (`/screenshots` folder or report PDF).

---

## ⚙️ Installation & Setup

Follow these steps to run the project locally:

### 1️⃣ Install XAMPP

* Download from: [https://www.apachefriends.org/](https://www.apachefriends.org/)
* Start **Apache** and **MySQL**

### 2️⃣ Clone or Download Project

```bash
git clone https://github.com/your-username/AnimeGo.git
```

OR download ZIP and extract it.

### 3️⃣ Move Project Folder

Copy the project folder to:

```text
C:\xampp\htdocs\
```

### 4️⃣ Create Database

* Open **phpMyAdmin**
* Create a database named:

```sql
animego
```

* Import the provided SQL file

### 5️⃣ Configure Database Connection

Edit `config/db.php` (or connection file):

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "animego";
```

### 6️⃣ Run the Project

Open browser and go to:

```text
http://localhost/AnimeGo
```

---

## 🔐 Security

* Password hashing (`password_hash`)
* Secure login (`password_verify`)
* Session-based authentication
* Role-based access control
* Form validation

---

## 🧪 Testing

Test cases implemented for:

* Login Module
* Signup Module
* Contact Us Module

Each test includes:

* Test Steps
* Expected Result
* Actual Result
* Status (Pass / Fail)

---

## ⚠️ Limitations

* Only one admin supported
* No 2FA or DRM
* Local video storage
* No adaptive streaming (360p/720p/1080p)

---

## 🔮 Future Enhancements

* Improved UI/UX
* User profile customization
* Continue Watching feature
* Reviews & Ratings
* Analytics dashboard
* Adaptive video streaming
* Mobile responsiveness

---

## 📚 References

* *PHP & MySQL Web Development* – Luke Welling & Laura Thomson
* *Learning PHP, MySQL & JavaScript* – Robin Nixon
* Tools: XAMPP, phpMyAdmin, VS Code
* Reference Sites:

  * kaido.to
  * hianime.ro

---

## ✅ Conclusion

AnimeGo provides a **simple, functional, and user-friendly anime streaming experience** with a complete admin panel.
It serves as a **strong foundation** for future scalability and feature expansion.
