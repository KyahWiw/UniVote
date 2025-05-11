<?php
include "connect.php";

try {
    // Modify the candidates table to make candidate_no auto-increment
    $query = $db->prepare("ALTER TABLE candidates MODIFY candidate_no INT AUTO_INCREMENT PRIMARY KEY");
    $query->execute();
    echo "Table structure updated successfully!";
} catch (PDOException $e) {
    echo "Error updating table structure: " . $e->getMessage();
}
?> 