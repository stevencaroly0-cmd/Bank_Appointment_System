<?php
require_once 'config.php';
$message = '';
try {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count === 0) {
        $hash = password_hash('Admin@123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['System Administrator', 'admin', $hash, 'Administrator']);
        $message = 'Demo administrator created. Username: admin | Password: Admin@123';
    } else {
        $message = 'A user account already exists. Setup did not create another account.';
    }
} catch (Throwable $e) {
    $message = 'Setup failed. First import database.sql in phpMyAdmin, then reload this page. Error: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Setup Administrator</title>
    </head>
<body style="font-family:Arial;max-width:700px;margin:60px auto;line-height:1.6">
    <h2>Bank Appointment System — Initial Setup</h2>
    <p><?= e($message) ?></p>
    <p>
        <strong>Security:</strong> After successfully creating the account, delete 
        <code>setup_admin.php</code> from the project folder
    .</p>
    <p>
        <a href="login.php">Go to login</a>
    </p>
  </body>
</html>
