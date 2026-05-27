<h1 align="center">UpMonitor</h1>

<p align="center">
  <strong>Free, Self-Hosted Uptime Monitoring Tool</strong><br>
  Built with PHP, MySQL, and Bootstrap 5
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#usage">Usage</a> •
  <a href="#security">Security</a> •
  <a href="#contributing">Contributing</a> •
  <a href="#license">License</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-blue.svg" alt="Version">
  <img src="https://img.shields.io/badge/license-MIT-green.svg" alt="License">
  <img src="https://img.shields.io/badge/php-7.4%2B-purple.svg" alt="PHP">
  <img src="https://img.shields.io/badge/mysql-5.7%2B-orange.svg" alt="MySQL">
</p>

---

## Features

- User Authentication (Register / Login / Logout)
- Monitor Types: HTTP, HTTPS, Ping, Port
- Real-time Dashboard with Doughnut Chart
- Detailed Logs with Response Times
- Maintenance Windows (pause checks during planned downtime)
- Dark Mode with Cookie + Database persistence
- Donate Page (TRX, USDT TRC20, USDT BEP20)
- CSRF Protection on all forms
- Secure Password Hashing (bcrypt)
- Responsive Design (Bootstrap 5.3)
- Modern UI with Glassmorphism and Animations

---

## Installation

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3 or higher
- Web server (Apache, Nginx, IIS) or XAMPP/WAMP
- PHP extensions: curl, pdo, pdo_mysql, openssl, mbstring

### Step 1: Download

Download the latest release ZIP from the Releases page, or clone:

git clone https://github.com/rmoradi2019/upmonitor.git

### Step 2: Database Setup

Go to the end

### Step 3: Configuration

Edit includes/config.php with your database credentials, site URL, and timezone.

### Step 4: Cron Job Setup

Set up a cron job to run the checker every minute.

**Linux (cPanel/DirectAdmin):**

* * * * * /usr/bin/php /home/username/public_html/upmonitor/cron.php

**Windows (Task Scheduler):**

- Open Task Scheduler
- Create a task that runs php.exe with the path to cron.php as argument
- Set trigger to repeat every 1 minute

**Web Cron (testing only):**

https://yourdomain.com/upmonitor/cron.php?secret=YOUR_CRON_SECRET

(Replace YOUR_CRON_SECRET with the key you set inside cron.php)

### Step 5: Access

Open your browser and go to:

http://localhost/upmonitor

Register an account and start adding monitors.

---

## Usage

### Adding a Monitor

1. Login and go to Monitors
2. Click Add Monitor
3. Choose type (HTTP, HTTPS, Ping, or Port)
4. Enter target URL or IP
5. Save

### Maintenance Windows

- Click the wrench icon next to a monitor
- Define a time window
- During that period, checks are skipped to avoid false downtime

### Dark Mode

- Toggle with the moon/sun button in the navbar
- Preference is saved automatically

### Donate Page

Edit donate.php to replace the placeholder wallet addresses with your own.

---

## Security

- CSRF tokens on all POST forms
- Passwords hashed with bcrypt
- PDO prepared statements for all queries
- Sensitive files protected by .htaccess
- Cron endpoint secured with a secret key
- Error display disabled in production mode

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP |
| Database | MySQL / MariaDB |
| Frontend | Bootstrap 5.3, Chart.js |
| Icons | Font Awesome 6 |

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

---

## License

MIT License. See LICENSE for more information.

---

## Author

**rmoradi2019**

- GitHub: https://github.com/rmoradi2019

---

If this project helps you, please give it a star on GitHub!

---

## Donate

TRX(Tron/TRC20) : TWukNBmxLUbPVgayRsZ72u84K8yW7K9cQw

USDT(TRC20) : TWukNBmxLUbPVgayRsZ72u84K8yW7K9cQw

USDT(Bep20) : 0x53f03070E2b6157fCaF48688b3426fA131c8175B

---

## Databse Creation Commands

-- UpMonitor Database Structure
-- Run this file to create all required tables

CREATE DATABASE IF NOT EXISTS uptime_monitor;
USE uptime_monitor;

-- Users table

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    dark_mode TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Monitors table

CREATE TABLE IF NOT EXISTS monitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('http','https','ping','port') NOT NULL,
    target VARCHAR(255) NOT NULL,
    port INT NULL,
    interval_seconds INT DEFAULT 60,
    status ENUM('up','down','pending') DEFAULT 'pending',
    last_checked DATETIME NULL,
    uptime_percent DECIMAL(5,2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Monitor logs table

CREATE TABLE IF NOT EXISTS monitor_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monitor_id INT NOT NULL,
    status ENUM('up','down') NOT NULL,
    response_time INT NULL,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (monitor_id) REFERENCES monitors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maintenance windows table

CREATE TABLE IF NOT EXISTS monitor_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monitor_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (monitor_id) REFERENCES monitors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

---