<?php
require_once __DIR__ . '/../config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS `recommended_courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `level_id` INT NOT NULL,
    `topic_keyword` VARCHAR(120) NULL,
    `title` VARCHAR(255) NOT NULL,
    `instructor` VARCHAR(150) NOT NULL,
    `youtube_id` VARCHAR(50) NOT NULL,
    `youtube_url` VARCHAR(255) NOT NULL,
    `playlist_url` VARCHAR(255) NULL,
    `duration` VARCHAR(60) NOT NULL,
    `content_type` ENUM('targeted_video', 'full_playlist', 'hands_on_lab') NOT NULL DEFAULT 'targeted_video',
    `badge_text` VARCHAR(80) NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_courses_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);
echo "Table recommended_courses created successfully!\n";
