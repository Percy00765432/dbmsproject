🚇 Metro Card Management

📋 Description

Metro Card Management is a web-based system to track and manage metro card transactions.
It handles card issuing, recharges, fare calculations, penalties, and transaction analytics for metro commuters.
🛠️ Technologies Used

    XAMPP (Apache + MySQL Server)

    PHP (Backend)

    MySQL (Database)

    JavaScript (Frontend scripting)

    CSS (Styling)

🚀 Features

    User registration and login

    Issue and manage metro cards

    Entry/exit tracking and fare calculation

    Recharge cards online

    Admin panel for user and transaction management

    Penalty system for invalid travel

    Daily analytics for total rides and revenue

📂 Project Structure

/css/              -> Stylesheets
/includes/         -> PHP include/configuration files
/js/               -> JavaScript scripts

admin.php          -> Admin Dashboard Page
dashboard.php      -> User Dashboard Page
get_card_details.php -> Fetch Metro Card Details (AJAX/Backend)
index.php          -> Login Page
logout.php         -> Logout functionality
register.php       -> User Registration Page
station.php        -> Station Management Page
/database/metro.sql -> SQL database schema
README.md          -> Project Instructions

🛠️ Setup Instructions
1. Install XAMPP

    Download and install XAMPP.

2. Clone the Repository

git clone https://github.com/your-username/metro-card-management.git

3. Move Project to XAMPP Directory

    Copy the project folder into C:/xampp/htdocs/.

4. Set Up the Database

    Start Apache and MySQL in XAMPP Control Panel.

    Open phpMyAdmin at http://localhost/phpmyadmin/.

    Create a new database named:

    metro

    Import the SQL file:

        Navigate to the database → Import → Choose the file database/metro.sql.

        Click Go to execute.

    📄 Note:
    Default Admin Account:

        Email: admin@metro.com

        Password: password

5. Configure Database Connection

    Open your PHP config file (inside /includes/ folder).

    Ensure the database connection settings:

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "metro";

6. Run the Application

Open your browser and visit:

http://localhost/metro-card-management/index.php

🛢️ Database Overview
Table	Description
users	Stores users (admin, operator, passenger)
metro_cards	Issued metro cards and their status
stations	Metro station details with zones
transactions	Tracks each ride's entry, exit, and fare
fare_rules	Fares between different zones
recharges	Recharge history of metro cards
penalties	Penalty records for misuse
analytics	Daily ride and revenue statistics

📜 License

This project is licensed under the MIT License.
👨‍💻 Author

[Your Name or GitHub username]
