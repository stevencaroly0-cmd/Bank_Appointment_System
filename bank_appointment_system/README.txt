BANK APPOINTMENT BOOKING MANAGEMENT SYSTEM
PHP + MySQL + AdminLTE 3 — XAMPP setup

WHAT IS INCLUDED
- Login/logout
- Dashboard appointment statistics and upcoming appointments
- Customer CRUD (create, list/search, edit, delete)
- Banking service CRUD
- Staff CRUD
- Appointment create, list/search/filter, edit, status update, delete
- Appointment overlap validation for the same staff member
- PDO prepared statements, password hashing, session regeneration, CSRF tokens
- AdminLTE 3 interface via CDN

REQUIREMENTS
- XAMPP with Apache, PHP 8+ recommended, and MySQL/MariaDB
- Internet connection for AdminLTE CDN assets (CSS/JavaScript)

INSTALLATION
1. Extract the folder "bank_appointment_system" into:
   C:\xampp\htdocs\
   The final path should be C:\xampp\htdocs\bank_appointment_system\

2. Start Apache and MySQL from XAMPP Control Panel.

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Choose Import, select database.sql from this folder, then click Import/Go.
   Alternatively, open database.sql in the SQL tab and execute it.

5. Check config.php. Default XAMPP settings are:
   database: bank_appointment_db
   username: root
   password: (blank)
   Change these values if your MySQL configuration differs.

6. Open:
   http://localhost/bank_appointment_system/setup_admin.php

7. It creates the initial administrator only if the users table is empty.
   Default demo login:
   Username: admin
   Password: Admin@123

8. Sign in at:
   http://localhost/bank_appointment_system/login.php

9. IMPORTANT: After creating the initial administrator, delete setup_admin.php.

FIRST USE
1. Open Services and add the services your branch offers.
2. Open Staff and add bank staff members.
3. Open Customers and register customers.
4. Open Appointments and create appointments.
5. Use the Dashboard and Appointment List to review records.

DATABASE NOTE
The four core project tables remain customers, services, staff, and appointments.
A fifth table, users, was added only to support secure login and role information.
This is a practical implementation addition because a working application needs user accounts.

IMPORTANT LIMITATIONS
- This is a local educational prototype, not a production-ready banking system.
- It does not process transactions, open accounts, approve loans, or connect to bank systems.
- Role names are stored and displayed, but detailed role-based permission restrictions have not yet been implemented.
- AdminLTE files load from a CDN, so an internet connection is needed for the full styling.
- Use fictional customer data while testing.
