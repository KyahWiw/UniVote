<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'];
    $position = $_POST['position'];
    $partylist_id = $_POST['partylist_id'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $achievements = $_POST['achievements'];
    $event_id = $_POST['event_id'];

    // Get partylist name from partylist_id
    $query = $db->prepare("SELECT name FROM partylists WHERE partylist_id = ?");
    $query->execute([$partylist_id]);
    $partylist_name = $query->fetchColumn();

    // Handle file upload
    $candidate_picture = null;
    if (isset($_FILES['candidate_picture']) && $_FILES['candidate_picture']['error'] === UPLOAD_ERR_OK) {
        $picture_name = basename($_FILES['candidate_picture']['name']);
        $picture_tmp = $_FILES['candidate_picture']['tmp_name'];
        $upload_dir = __DIR__ . '/uploads/';
        $candidate_picture = 'uploads/' . $picture_name;

        // Create the uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Move the uploaded file to the uploads directory
        if (!move_uploaded_file($picture_tmp, $upload_dir . $picture_name)) {
            $_SESSION['error_message'] = "Failed to upload the candidate picture.";
            header("Location: index.php?event_id=" . urlencode($event_id));
            exit();
        }
    }

    try {
        // Insert candidate data into the database
        $query = $db->prepare("INSERT INTO candidates (student_id, position, partylist, lastname, firstname, middlename, age, gender, course, year_level, achievements, candidate_picture, event_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->execute([$student_id, $position, $partylist_name, $lastname, $firstname, $middlename, $age, $gender, $course, $year_level, $achievements, $candidate_picture, $event_id]);

        // Get the auto-incremented candidate_no
        $candidate_no = $db->lastInsertId();

        $_SESSION['success_message'] = "Candidate added successfully with candidate number: " . $candidate_no;
        header("Location: index.php?event_id=" . urlencode($event_id));
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding candidate: " . $e->getMessage();
        header("Location: index.php?event_id=" . urlencode($event_id));
        exit();
    }
}
?>