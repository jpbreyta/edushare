<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "edushare";


$admin_name = 'Admin User';
$admin_email = 'edushareadmin@edu.ph';
$admin_password = 'admin123';
$admin_role = 'admin';

try {

    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    $check_stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $check_stmt->execute([$admin_email]);

    if ($check_stmt->rowCount() > 0) {
        echo "An account with this email already exists. Choose a different email or update the existing account.";
    } else {

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, created_at) VALUES (?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$admin_name, $admin_email, $hashed_password, $admin_role]);

        if ($result) {
            echo "Admin account created successfully!\n";
            echo "Email: $admin_email\n";
            echo "Password: $admin_password (keep this secure)\n";
        } else {
            echo "Failed to create admin account.";
        }
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
