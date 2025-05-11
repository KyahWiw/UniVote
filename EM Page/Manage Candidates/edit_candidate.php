<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $candidate_no = $_POST['candidate_no'];
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
        // Get current picture path from database
        $query = $db->prepare("SELECT candidate_picture FROM candidates WHERE candidate_no = ?");
        $query->execute([$candidate_no]);
        $current_picture = $query->fetchColumn();

        // Delete old picture if it exists
        if ($current_picture && file_exists($current_picture)) {
            unlink($current_picture);
        }

        // Generate unique filename
        $file_ext = pathinfo($_FILES['candidate_picture']['name'], PATHINFO_EXTENSION);
        $picture_name = uniqid('candidate_', true) . '.' . $file_ext;
        $picture_tmp = $_FILES['candidate_picture']['tmp_name'];
        $upload_dir = __DIR__ . '/uploads/';
        $candidate_picture = 'uploads/' . $picture_name;

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['candidate_picture']['type'], $allowed_types)) {
            $_SESSION['error_message'] = "Only JPG, PNG, and GIF files are allowed.";
            header("Location: index.php?event_id=" . urlencode($event_id));
            exit();
        }

        // Validate file size (max 2MB)
        if ($_FILES['candidate_picture']['size'] > 2000000) {
            $_SESSION['error_message'] = "File size must be less than 2MB.";
            header("Location: index.php?event_id=" . urlencode($event_id));
            exit();
        }

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

        // Crop the image if needed
        $image = imagecreatefromstring(file_get_contents($upload_dir . $picture_name));
        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);
            $size = min($width, $height);
            $x = ($width - $size) / 2;
            $y = ($height - $size) / 2;
            $cropped = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $size, 'height' => $size]);
            if ($cropped) {
                imagejpeg($cropped, $upload_dir . $picture_name, 90);
                imagedestroy($cropped);
            }
            imagedestroy($image);
        }
    }

    // Update candidate data in the database
    if ($candidate_picture) {
        $query = $db->prepare("UPDATE candidates SET student_id = ?, position = ?, partylist = ?, lastname = ?, firstname = ?, middlename = ?, age = ?, gender = ?, course = ?, year_level = ?, achievements = ?, candidate_picture = ? WHERE candidate_no = ?");
        $query->execute([$student_id, $position, $partylist_name, $lastname, $firstname, $middlename, $age, $gender, $course, $year_level, $achievements, $candidate_picture, $candidate_no]);
    } else {
        $query = $db->prepare("UPDATE candidates SET student_id = ?, position = ?, partylist = ?, lastname = ?, firstname = ?, middlename = ?, age = ?, gender = ?, course = ?, year_level = ?, achievements = ? WHERE candidate_no = ?");
        $query->execute([$student_id, $position, $partylist_name, $lastname, $firstname, $middlename, $age, $gender, $course, $year_level, $achievements, $candidate_no]);
    }

    $_SESSION['success_message'] = "Candidate updated successfully.";
    header("Location: index.php?event_id=" . urlencode($event_id));
    exit();
}
?>
