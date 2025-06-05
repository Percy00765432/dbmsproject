
# 🚇 Metro Card Management

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

---

## 📋 Description
Metro Card Management is a web-based system to **track and manage metro card transactions**.  
It handles card issuing, recharges, fare calculations, penalties, and transaction analytics for metro commuters.

---

## 🛠️ Technologies Used
- **XAMPP** (Apache + MySQL Server)
- **PHP** (Backend)
- **MySQL** (Database)
- **JavaScript** (Frontend scripting)
- **CSS** (Styling)

---

## 🚀 Features
- User registration and login
- Issue and manage metro cards
- Entry/exit tracking and fare calculation
- Recharge cards online
- Admin panel for user and transaction management
- Penalty system for invalid travel
- Daily analytics for total rides and revenue

---

## 📂 Project Structure

```
/css/                -> Stylesheets
/includes/           -> PHP include/configuration files
/js/                 -> JavaScript scripts

admin.php            -> Admin Dashboard Page
dashboard.php        -> User Dashboard Page
get_card_details.php -> Fetch Metro Card Details (AJAX/Backend)
index.php            -> Login Page
logout.php           -> Logout functionality
register.php         -> User Registration Page
station.php          -> Station Management Page
metro.sql            -> SQL database schema
README.md            -> Project Instructions
profile.php          -> Profile Editing Page
```

---

## 🛠️ Setup Instructions

### 1. Install XAMPP
- Download and install [XAMPP](https://www.apachefriends.org/index.html).

### 2. Clone the Repository
```bash
git clone https://github.com/Percy00765432/dbmsproject.git
```

### 3. Move Project to XAMPP Directory
- Copy the project folder into `C:/xampp/htdocs/`.

### 4. Set Up the Database
- Start **Apache** and **MySQL** in XAMPP Control Panel.
- Open **phpMyAdmin** at `http://localhost/phpmyadmin/`.
- Create a new database named:
  ```
  metro
  ```
- Import the SQL file:
  - Navigate to the database → Import → Choose the file `database/metro.sql`.
  - Click **Go** to execute.

> 📄 **Note:**  
> Default Admin Account:  
> - **Email:** `admin@metro.com`  
> - **Password:** `password`

### 5. Configure Database Connection
- Open your PHP config file (inside `/includes/` folder).
- Ensure the database connection settings:
  ```php
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "metro";
  ```

### 6. Run the Application
Open your browser and visit:
```
http://localhost/dbmsproject/index.php
```

---

## 🛤️ Database Overview

| Table         | Description                              |
|---------------|------------------------------------------|
| users         | Stores users (admin, operator, passenger)|
| metro_cards   | Issued metro cards and their status      |
| stations      | Metro station details with zones         |
| transactions  | Tracks each ride's entry, exit, and fare |
| fare_rules    | Fares between different zones            |
| recharges     | Recharge history of metro cards          |
| penalties     | Penalty records for misuse               |
| analytics     | Daily ride and revenue statistics        |

---

## 👨‍💻 Author
### Prathik K B - 1RUA24CSE0327
### Raghav Raina - 1RUA24CSE0352
### Raaahul S Nayar - 1RUA24CSE0346
---

