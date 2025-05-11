<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_name = $_POST['event_name'];
    $description = $_POST['description'];
    $event_type = $_POST['event_type'];
    $organized_by = $_POST['organized_by'];
    $department = isset($_POST['department']) ? $_POST['department'] : null;
    $event_datetime_start = $_POST['event_datetime_start_date'] . ' ' . $_POST['event_datetime_start_time'];
    $event_datetime_end = $_POST['event_datetime_end_date'] . ' ' . $_POST['event_datetime_end_time'];
    $voting_mode = $_POST['voting_mode'];
    $positions = $_POST['positions'];
    $number_of_candidates = $_POST['number_of_candidates'];
    $number_of_partylists = $_POST['number_of_partylists'];
    $event_banner = null;

    // Only save department if event_type is Department
    if ($event_type === "Department") {
        $department = isset($_POST['department']) ? $_POST['department'] : null;
    } else if ($event_type === "SSC") {
        $department = "SSC"; // Save "SSC" as department for Supreme Student Council
    } else {
        $department = null;
    }

    // Handle banner upload
    if (isset($_FILES['event_banner']) && $_FILES['event_banner']['error'] === UPLOAD_ERR_OK) {
        $banner_name = basename($_FILES['event_banner']['name']);
        $banner_tmp = $_FILES['event_banner']['tmp_name'];
        $upload_dir = __DIR__ . '/uploads/';
        $event_banner = 'uploads/' . $banner_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        move_uploaded_file($banner_tmp, $upload_dir . $banner_name);
    }

    try {
        $query = $db->prepare("INSERT INTO election_events (
            event_name, description, event_type, organized_by, voting_mode, positions, department, event_datetime_start, event_datetime_end, event_banner, number_of_candidates, number_of_partylists, is_archived
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->execute([
            $event_name,
            $description,
            $event_type,
            $organized_by,
            $voting_mode,
            $positions,
            $department,
            $event_datetime_start,
            $event_datetime_end,
            $event_banner,
            $number_of_candidates,
            $number_of_partylists,
            0 // is_archived default
        ]);

        $_SESSION['success_message'] = "Election event added successfully.";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding event: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}
?>