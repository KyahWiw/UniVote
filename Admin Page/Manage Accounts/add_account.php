<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $year_level = $_POST['year_level'];
    $department = $_POST['department'];
    $course = $_POST['course'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Find the lowest available account_id
        $query = $db->query("SELECT MIN(t1.account_id + 1) as next_id
                            FROM accounts t1
                            LEFT JOIN accounts t2 ON t1.account_id + 1 = t2.account_id
                            WHERE t2.account_id IS NULL");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $next_id = $result['next_id'] ?? 1; // If no gaps, start from 1

        // Insert the new account with the found ID
        $query = $db->prepare("INSERT INTO accounts (account_id, student_id, lastname, firstname, middlename, year_level, department, course, role, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->execute([$next_id, $student_id, $lastname, $firstname, $middlename, $year_level, $department, $course, $role, $email, $password]);

        $_SESSION['success_message'] = "Account added successfully!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding account: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();