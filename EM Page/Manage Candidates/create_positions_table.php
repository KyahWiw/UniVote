<?php
include "connect.php";

try {
    // Create positions table
    $query = $db->prepare("
        CREATE TABLE IF NOT EXISTS positions (
            position_id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            position_name VARCHAR(200) NOT NULL,
            position_order INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES election_events(id) ON DELETE CASCADE
        )
    ");
    $query->execute();
    echo "Positions table created successfully!";
} catch (PDOException $e) {
    echo "Error creating positions table: " . $e->getMessage();
}
?> 