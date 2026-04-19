<?php
// create_admin.php - run this ONCE to create/update admin user
require_once __DIR__ . '/config.php';

// CHANGE THESE IF YOU WANT DIFFERENT ADMIN LOGIN
$email = '';   // admin login email
$plainPassword = '';      // admin login password

try {
    // Hash the password using this server's PHP
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Create admins table if not exists (safety)
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Insert or update admin row
    $sql = "INSERT INTO admins (email, password_hash)
            VALUES (:email, :hash)
            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':hash'  => $hash,
    ]);

    echo "<h2>✅ Admin user created/updated successfully.</h2>";
    echo "<p><b>Email:</b> {$email}</p>";
    echo "<p><b>Password:</b> {$plainPassword}</p>";
    echo "<p>You can now log in using this email and password.</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
