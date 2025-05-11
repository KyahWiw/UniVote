<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_no = $_POST['account_no'];
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
        if (!empty($password)) {
            // Update with a new password (stored as plain text)
            $query = $db->prepare("UPDATE accounts SET student_id = ?, lastname = ?, firstname = ?, middlename = ?, year_level = ?, department = ?, course = ?, role = ?, email = ?, password = ? WHERE account_id = ?");
            $query->execute([$student_id, $lastname, $firstname, $middlename, $year_level, $department, $course, $role, $email, $password, $account_no]);
        } else {
            // Update without changing the password
            $query = $db->prepare("UPDATE accounts SET student_id = ?, lastname = ?, firstname = ?, middlename = ?, year_level = ?, department = ?, course = ?, role = ?, email = ? WHERE account_id = ?");
            $query->execute([$student_id, $lastname, $firstname, $middlename, $year_level, $department, $course, $role, $email, $account_no]);
        }

        $_SESSION['success_message'] = "Account updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating account: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();