-- Create feedback table
CREATE TABLE IF NOT EXISTS `feedback` (
    `feedback_id` INT AUTO_INCREMENT,
    `id_student` varchar(99) COLLATE utf8mb4_general_ci,
    `feedback_type` ENUM('bug', 'feature', 'improvement', 'other') NOT NULL,
    `feedback_priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    `feedback_title` VARCHAR(255) NOT NULL,
    `feedback_description` TEXT NOT NULL,
    `status` ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`feedback_id`),
    KEY `id_student` (`id_student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create feedback attachments table
CREATE TABLE IF NOT EXISTS `feedback_attachments` (
    `attachment_id` INT AUTO_INCREMENT,
    `feedback_id` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `uploaded_at` DATETIME NOT NULL,
    PRIMARY KEY (`attachment_id`),
    KEY `feedback_id` (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 