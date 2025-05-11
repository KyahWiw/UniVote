<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=univote_accounts;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    echo "\nPlease make sure:\n";
    echo "1. XAMPP is running\n";
    echo "2. MySQL service is started in XAMPP Control Panel\n";
    echo "3. The database 'univote_accounts' exists\n";
}
?> 