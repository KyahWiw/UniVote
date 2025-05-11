<?php
session_start();
include "connect.php";
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        try {
            $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();

            $success_count = 0;
            $error_count = 0;
            $errors = [];

            // Start from row 2 (skip headers)
            $row = 2;
            while ($worksheet->getCellByColumnAndRow(1, $row)->getValue() !== null) {
                try {
                    $account_id = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                    $student_id = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                    $lastname = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                    $firstname = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                    $middlename = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                    $year_level = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                    $department = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                    $course = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                    $role = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                    $email = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                    $password = $worksheet->getCellByColumnAndRow(11, $row)->getValue();

                    // Validate required fields
                    if (empty($account_id) || empty($student_id) || empty($lastname) || empty($firstname)) {
                        $error_count++;
                        $errors[] = "Row $row: Missing required fields (Account ID, Student ID, Last Name, or First Name)";
                        $row++;
                        continue;
                    }

                    // Check if account_id exists
                    $check_query = $db->prepare("SELECT account_id FROM accounts WHERE account_id = ?");
                    $check_query->execute([$account_id]);

                    if ($check_query->rowCount() > 0) {
                        // Update existing account
                        $query = $db->prepare("UPDATE accounts SET 
                            student_id = ?, 
                            lastname = ?, 
                            firstname = ?, 
                            middlename = ?, 
                            year_level = ?, 
                            department = ?, 
                            course = ?, 
                            role = ?, 
                            email = ?, 
                            password = ? 
                            WHERE account_id = ?");
                        $query->execute([
                            $student_id, $lastname, $firstname, $middlename, $year_level,
                            $department, $course, $role, $email, $password, $account_id
                        ]);
                    } else {
                        // Insert new account
                        $query = $db->prepare("INSERT INTO accounts 
                            (account_id, student_id, lastname, firstname, middlename, 
                            year_level, department, course, role, email, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $query->execute([
                            $account_id, $student_id, $lastname, $firstname, $middlename,
                            $year_level, $department, $course, $role, $email, $password
                        ]);
                    }

                    $success_count++;
                } catch (PDOException $e) {
                    $error_count++;
                    $errors[] = "Row $row: " . $e->getMessage();
                }
                $row++;
            }

            if ($error_count > 0) {
                $_SESSION['error_message'] = "Import completed with errors. Successfully imported: $success_count, Errors: $error_count<br>Error details: " . implode("<br>", $errors);
            } else {
                $_SESSION['success_message'] = "Import completed successfully. Imported $success_count accounts.";
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error processing file: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Please select a valid Excel file.";
    }
}

header("Location: index.php");
exit();