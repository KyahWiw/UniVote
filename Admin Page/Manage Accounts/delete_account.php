<?php
// filepath: c:\xampp\htdocs\UniVote\Admin Page\Manage Accounts\delete_account.php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_no = $_POST['account_no'];

    try {
        $query = $db->prepare("DELETE FROM accounts WHERE account_id = ?");
        $query->execute([$account_no]);

        $_SESSION['success_message'] = "Account deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting account: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();