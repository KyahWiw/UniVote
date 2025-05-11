<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_name = $_POST['event_name'];
    $description = $_POST['description'];
    $event_type = $_POST['event_type'];
    $organized_by = $_POST['organized_by'];
    $voting_mode = $_POST['voting_mode'];
    $positions = $_POST['positions'];
    $event_datetime_start = $_POST['event_datetime_start_date'] . ' ' . $_POST['event_datetime_start_time'];
    $event_datetime_end = $_POST['event_datetime_end_date'] . ' ' . $_POST['event_datetime_end_time'];

    // Handle banner upload
    $banner_path = null;
    if (isset($_FILES['event_banner']) && $_FILES['event_banner']['error'] === UPLOAD_ERR_OK) {
        $banner_name = basename($_FILES['event_banner']['name']);
        $banner_tmp = $_FILES['event_banner']['tmp_name'];
        $upload_dir = __DIR__ . '../Assets/image/uploads/'; // Updated path
        $banner_path = '../Assets/image/uploads/' . $banner_name; // Relative path for storing in the database

        // Ensure the directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }

        if (!move_uploaded_file($banner_tmp, $upload_dir . $banner_name)) {
            $_SESSION['error_message'] = "Failed to upload the event banner.";
            header("Location: index.php");
            exit();
        }
    }

    try {
        $query = $db->prepare("INSERT INTO election_events (event_name, description, event_type, organized_by, voting_mode, positions, event_datetime_start, event_datetime_end, event_banner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->execute([$event_name, $description, $event_type, $organized_by, $voting_mode, $positions, $event_datetime_start, $event_datetime_end, $banner_path]);

        $_SESSION['success_message'] = "Election event added successfully.";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding event: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}

$department = $_POST['department']; // Get the selected department

$query = $db->prepare("UPDATE election_events SET event_name = ?, description = ?, event_type = ?, organized_by = ?, voting_mode = ?, positions = ?, department = ?, event_datetime_start = ?, event_datetime_end = ?, event_banner = ? WHERE id = ?");
$query->execute([$event_name, $description, $event_type, $organized_by, $voting_mode, $positions, $department, $event_datetime_start, $event_datetime_end, $banner_path, $event_id]);
?>