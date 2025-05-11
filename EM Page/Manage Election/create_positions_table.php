<?php
include "../connect.php";

try {
    // Create positions table
    $sql = "CREATE TABLE IF NOT EXISTS positions (
        position_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        position_name VARCHAR(255) NOT NULL,
        position_order INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES election_events(id) ON DELETE CASCADE
    )";
    
    $db->exec($sql);
    echo "Positions table created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 