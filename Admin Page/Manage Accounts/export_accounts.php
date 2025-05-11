<?php
// export_accounts.php
session_start();
include "connect.php";
require __DIR__ . '/../../vendor/autoload.php';  // Fixed path to vendor/autoload.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$headers = [
    'A1' => 'Account ID',
    'B1' => 'Student ID',
    'C1' => 'Last Name',
    'D1' => 'First Name',
    'E1' => 'Middle Name',
    'F1' => 'Year Level',
    'G1' => 'Department',
    'H1' => 'Course',
    'I1' => 'Role',
    'J1' => 'Email',
    'K1' => 'Password'
];

// Set header values and style
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

// Get all accounts from the database
$query = $db->query("SELECT * FROM accounts ORDER BY account_id");
$accounts = $query->fetchAll(PDO::FETCH_ASSOC);

// Add data rows
$row = 2;
foreach ($accounts as $account) {
    $sheet->setCellValue('A' . $row, $account['account_id']);
    $sheet->setCellValue('B' . $row, $account['student_id']);
    $sheet->setCellValue('C' . $row, $account['lastname']);
    $sheet->setCellValue('D' . $row, $account['firstname']);
    $sheet->setCellValue('E' . $row, $account['middlename']);
    $sheet->setCellValue('F' . $row, $account['year_level']);
    $sheet->setCellValue('G' . $row, $account['department']);
    $sheet->setCellValue('H' . $row, $account['course']);
    $sheet->setCellValue('I' . $row, $account['role']);
    $sheet->setCellValue('J' . $row, $account['email']);
    $sheet->setCellValue('K' . $row, $account['password']);
    $row++;
}

// Auto-size columns
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="accounts_export_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();