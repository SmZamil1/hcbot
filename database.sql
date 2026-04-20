-- ============================================================
-- HopeCoin Bot Database Schema
-- Run this in phpMyAdmin to create all tables
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED PRIMARY KEY,
  `username` VARCHAR(128) DEFAULT '',
  `full_name` VARCHAR(255) DEFAULT '',
  `coins` BIGINT DEFAULT 100,
  `total_coins` BIGINT DEFAULT 0,
  `level` INT DEFAULT 1,
  `taps` BIGINT DEFAULT 0,
  `games_played` INT DEFAULT 0,
  `games_won` INT DEFAULT 0,
  `tap_lvl` INT DEFAULT 1,
  `en_lvl` INT DEFAULT 1,
  `regen_lvl` INT DEFAULT 1,
  `refs` INT DEFAULT 0,
  `tasks_done` INT DEFAULT 0,
  `streak` INT DEFAULT 0,
  `daily_claimed_at` DATETIME NULL,
  `week_coins` BIGINT DEFAULT 0,
  `day_coins` BIGINT DEFAULT 0,
  `banned` TINYINT DEFAULT 0,
  `last_seen` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_coins` (`coins` DESC),
  KEY `idx_week_coins` (`week_coins` DESC),
  KEY `idx_day_coins` (`day_coins` DESC),
  KEY `idx_refs` (`refs` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `key_name` VARCHAR(100) PRIMARY KEY,
  `value` TEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `game_plays` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `game` VARCHAR(50),
  `bet` INT DEFAULT 0,
  `result` VARCHAR(20),
  `coins_change` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` VARCHAR(50) PRIMARY KEY,
  `title` VARCHAR(255),
  `description` TEXT,
  `reward` INT,
  `active` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `done_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `task_id` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_task` (`user_id`, `task_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referrer_id` BIGINT UNSIGNED NOT NULL,
  `new_user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_referral` (`referrer_id`, `new_user_id`),
  FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`new_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `merge_boards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `board_data` JSON,
  `score` INT DEFAULT 0,
  `coins_earned` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `brain_game_queue` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `game_type` VARCHAR(50) DEFAULT 'trivia',
  `entry_fee` INT DEFAULT 0,
  `status` VARCHAR(20) DEFAULT 'waiting',
  `room_id` VARCHAR(50),
  `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `brain_game_rooms` (
  `id` VARCHAR(50) PRIMARY KEY,
  `game_type` VARCHAR(50),
  `player1_id` BIGINT UNSIGNED NOT NULL,
  `player2_id` BIGINT UNSIGNED NOT NULL,
  `player1_score` INT DEFAULT 0,
  `player2_score` INT DEFAULT 0,
  `current_question` INT DEFAULT 0,
  `entry_fee` INT DEFAULT 0,
  `questions` JSON,
  `winner_id` BIGINT UNSIGNED,
  `status` VARCHAR(20) DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_status` (`status`),
  KEY `idx_player1` (`player1_id`),
  KEY `idx_player2` (`player2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leaderboard_config` (
  `board_type` VARCHAR(50) PRIMARY KEY,
  `next_reset` DATETIME,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leaderboard_prizes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `board_type` VARCHAR(50),
  `rank` INT,
  `amount` INT,
  `claimed` TINYINT DEFAULT 0,
  `claimed_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user` (`user_id`),
  KEY `idx_claimed` (`claimed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `group_settings` (
  `group_id` BIGINT PRIMARY KEY,
  `spam_filter` TINYINT DEFAULT 1,
  `anti_flood` TINYINT DEFAULT 1,
  `link_block` TINYINT DEFAULT 0,
  `forward_block` TINYINT DEFAULT 0,
  `auto_welcome` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `group_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` BIGINT,
  `user_id` BIGINT UNSIGNED,
  `event` VARCHAR(50),
  `message` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_group` (`group_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEFAULT DATA
-- ============================================================

INSERT IGNORE INTO `tasks` (`id`, `title`, `description`, `reward`, `active`) VALUES
('task_tap_100', 'Tap 100 Times', 'Tap the coin 100 times in the app', 500, 1),
('task_invite_1', 'Invite 1 Friend', 'Invite a friend using your referral link', 300, 1),
('task_play_1', 'Play 1 Game', 'Play any game at least once', 250, 1),
('task_win_3', 'Win 3 Games', 'Win at least 3 games', 400, 1),
('task_join_channel', 'Join Channel', 'Join our telegram channel @hopenity', 200, 1),
('task_join_group', 'Join Group', 'Join our telegram group @hopenitychat', 200, 1);

INSERT IGNORE INTO `settings` (`key_name`, `value`) VALUES
('referral_reward', '500'),
('bet_multiplier', '1.8'),
('daily_play_limit', '10');

INSERT IGNORE INTO `leaderboard_config` (`board_type`, `next_reset`) VALUES
('daily', DATE_ADD(NOW(), INTERVAL 1 DAY)),
('weekly', DATE_ADD(NOW(), INTERVAL 7 DAY)),
('refs', DATE_ADD(NOW(), INTERVAL 30 DAY));

-- ============================================================
-- Sample User (REMOVE AFTER TESTING)
-- ============================================================
INSERT IGNORE INTO `users` (`id`, `username`, `full_name`, `coins`, `total_coins`, `level`) VALUES
(123456789, 'testuser', 'Test User', 5000, 10000, 5);

