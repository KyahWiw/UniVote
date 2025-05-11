<?php
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $student_id = $_POST['student_id'];
    $position = $_POST['position'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $achievements = $_POST['achievements'];

    // Handle file upload
    if (isset($_FILES['candidate_picture']) && $_FILES['candidate_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($_FILES['candidate_picture']['name']);
        $target_file = $upload_dir . $file_name;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['candidate_picture']['tmp_name'], $target_file)) {
            // Save candidate data to the database
            $query = $db->prepare("INSERT INTO candidates (student_id, position, lastname, firstname, middlename, age, gender, course, year_level, achievements, candidate_picture) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->execute([$student_id, $position, $lastname, $firstname, $middlename, $age, $gender, $course, $year_level, $achievements, $file_name]);

            echo "Candidate added successfully!";
        } else {
            echo "Failed to upload the picture.";
        }
    } else {
        echo "No picture uploaded or an error occurred.";
    }
}
?>