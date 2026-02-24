-- เพิ่มตาราง favorites เข้าไปใน library_db
USE `library_db`;

CREATE TABLE IF NOT EXISTS `favorites` (
  `fav_id`     INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `book_id`    INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_fav` (`user_id`,`book_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
