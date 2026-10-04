# 🛡️ Secure Employee Management System (EMS)

A security-focused Web Application built with **PHP 8.x** and **MySQL / MariaDB**, specifically designed to demonstrate secure coding practices and robust mitigations against **OWASP Top 10** vulnerabilities.

---

## 🎯 Project Overview
Unlike standard CRUD applications that only focus on business logic, **Secure EMS** is built from the ground up with a **Defense-in-Depth** architecture. Every input, output, file transaction, and session state is validated, sanitized, and hardened against common web attack vectors.

---

## 🛡️ Key Security Implementations

### 1. SQL Injection (SQLi) Mitigation
* **Method:** PDO Prepared Statements with Parameter Binding (`$pdo->prepare()`).
* **Mechanism:** Strictly decouples SQL queries from user input data, rendering classic attacks like `' OR '1'='1` harmless text strings. `PDO::ATTR_EMULATE_PREPARES` is disabled to enforce true server-side prepared statements.

### 2. Cross-Site Scripting (XSS) Protection
* **Method:** Context-aware Output Encoding using `htmlspecialchars()`.
* **Mechanism:** Converts sensitive HTML characters (`<`, `>`, `&`, `"`, `'`) into harmless HTML entities before rendering in the DOM, preventing stored & reflected malicious script executions.

### 3. Cross-Site Request Forgery (CSRF) Defense
* **Method:** Cryptographically Secure Anti-CSRF Tokens (`hash_equals()` validation).
* **Mechanism:** Generates a unique, high-entropy 32-byte token (`random_bytes(32)`) per user session to validate all state-changing HTTP POST requests.

### 4. Secure Authentication & Session Management
* **Password Hashing:** BCRYPT algorithm via `password_hash()` and verified with `password_verify()`.
* **Anti-Brute Force Protection:** Tracks and limits failed login attempts by client IP address in the database, triggering a temporary lockout after 5 consecutive failures.
* **Session Hardening:** 
  * Invokes `session_regenerate_id(true)` upon successful authentication to prevent Session Fixation.
  * `HttpOnly` and `SameSite=Strict` flags set on session cookies to prevent cookie theft via XSS.

### 5. Multi-Layered Secure File Upload Architecture
* **Magic Bytes Inspection:** Validates true MIME types using `finfo_file()` rather than relying solely on file extensions or client headers.
* **Randomized File Naming:** Files are renamed to high-entropy 16-byte random hex hashes (`bin2hex(random_bytes(16))`) to prevent path traversal and file collisions.
* **Execution Prevention:** Deployed `.htaccess` configuration inside `/uploads` disabling Apache script handlers (`SetHandler none`, `Options -ExecCGI`) to stop webshell / Remote Code Execution (RCE) attempts.

### 6. HTTP Security Headers
* Injected standard security headers: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and `Content-Security-Policy`.

---

## 🚀 Tech Stack
* **Backend:** PHP 8.x
* **Database:** MySQL / MariaDB (PDO Driver)
* **Web Server:** Apache (XAMPP / Standalone)
* **Frontend:** Bootstrap 5 (Dark Theme)

---

## 💻 Installation & Setup

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) (with PHP 8.x and MySQL / MariaDB) or equivalent web server environment.
* Git installed on your system.

### Step-by-Step Setup

1. **Clone the repository** to your local web server root (e.g., `C:\xampp\htdocs\`):
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Alfred-Sam/secure-ems.git
   ```

2. **Start Apache & MySQL** in the XAMPP Control Panel.

3. **Import the Database**:
   * Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
   * Create a database named `secure_ems` (or import `database.sql` directly).
   * Or run via terminal:
     ```bash
     mysql -u root -p secure_ems < database.sql
     ```

4. **Verify Database Configuration**:
   * Check [config/database.php](file:///c:/xampp/htdocs/secure-ems/config/database.php) and adjust MySQL username and password if required (default is `root` with no password).

5. **Initialize Admin Account** (Optional if imported via SQL):
   * Visit: `http://localhost/secure-ems/setup_user.php`

6. **Open the Application**:
   * Visit: `http://localhost/secure-ems/login.php`
   * **Default Credentials:**
     * **Username:** `admin`
     * **Password:** `admin123`

---

## 📁 Project Structure

```
secure-ems/
│
├── config/
│   └── database.php          # Secure PDO database connection
│
├── includes/
│   └── security_headers.php   # Centralized HTTP security headers
│
├── uploads/                  # Upload directory for avatars
│   ├── .htaccess             # Execution prevention policy
│   └── .gitkeep
│
├── database.sql              # Database schema and initial seeds
├── login.php                 # Anti-brute force secure login page
├── dashboard.php             # XSS & CSRF protected employee management
├── upload_avatar.php         # Multi-layer secure file upload handler
├── logout.php                # Session destruction and cleanup
├── setup_user.php            # Admin account generator utility
├── README.md                 # English documentation
└── README_ID.md              # Indonesian documentation
```

---

## 📄 License
This project is open-source and available under the [MIT License](LICENSE).