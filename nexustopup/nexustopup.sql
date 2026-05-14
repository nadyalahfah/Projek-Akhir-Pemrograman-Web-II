-- =============================================
-- NEXUSTOPUP - Database Schema
-- Import ke phpMyAdmin → database nexustopup
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- ── ADMINS ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `name`       VARCHAR(100) NOT NULL DEFAULT 'Administrator',
  `email`      VARCHAR(150) NOT NULL DEFAULT '',
  `last_login` DATETIME     NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`username`, `password`, `name`, `email`) VALUES
('admin', 'admin123', 'Administrator', 'admin@nexustopup.com');

-- ── GAMES ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `games` (
  `id`              VARCHAR(60)  NOT NULL,
  `name`            VARCHAR(100) NOT NULL,
  `image`           VARCHAR(255) NOT NULL DEFAULT 'assets/images/default-game.svg',
  `category`        VARCHAR(50)  NOT NULL DEFAULT 'Other',
  `currency`        VARCHAR(50)  NOT NULL DEFAULT 'Diamond',
  `needs_server_id` TINYINT(1)   NOT NULL DEFAULT 0,
  `popular`         TINYINT(1)   NOT NULL DEFAULT 0,
  `active`          TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PRODUCTS ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id`          VARCHAR(60)      NOT NULL,
  `game_id`     VARCHAR(60)      NOT NULL,
  `amount`      VARCHAR(50)      NOT NULL,
  `bonus`       VARCHAR(50)      NOT NULL DEFAULT '',
  `price`       INT UNSIGNED     NOT NULL DEFAULT 0,
  `discount`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `final_price` INT UNSIGNED     NOT NULL DEFAULT 0,
  `popular`     TINYINT(1)       NOT NULL DEFAULT 0,
  `active`      TINYINT(1)       NOT NULL DEFAULT 1,
  `sort_order`  INT              NOT NULL DEFAULT 0,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DEVICES ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `devices` (
  `id`         VARCHAR(20)  NOT NULL,
  `first_seen` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `txn_count`  INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TRANSACTIONS ─────────────────────────────
CREATE TABLE IF NOT EXISTS `transactions` (
  `id`             VARCHAR(20)  NOT NULL,
  `device_id`      VARCHAR(20)  NOT NULL,
  `game_id`        VARCHAR(60)  NOT NULL,
  `game_name`      VARCHAR(100) NOT NULL,
  `product_id`     VARCHAR(60)  NOT NULL,
  `item`           VARCHAR(150) NOT NULL,
  `user_id`        VARCHAR(100) NOT NULL,
  `server_id`      VARCHAR(100) NOT NULL DEFAULT '',
  `whatsapp`       VARCHAR(20)  NOT NULL,
  `email`          VARCHAR(150) NOT NULL DEFAULT '',
  `payment_method` VARCHAR(50)  NOT NULL,
  `payment_id`     VARCHAR(50)  NOT NULL,
  `base_price`     INT UNSIGNED NOT NULL DEFAULT 0,
  `fee`            INT UNSIGNED NOT NULL DEFAULT 0,
  `total`          INT UNSIGNED NOT NULL DEFAULT 0,
  `status`         ENUM('pending','processing','success','failed') NOT NULL DEFAULT 'pending',
  `notes`          TEXT         NOT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_device`  (`device_id`),
  INDEX `idx_status`  (`status`),
  INDEX `idx_game`    (`game_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PAYMENT METHODS ──────────────────────────
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id`         VARCHAR(30)  NOT NULL,
  `name`       VARCHAR(50)  NOT NULL,
  `icon`       VARCHAR(60)  NOT NULL DEFAULT 'fa-credit-card',
  `fee`        INT UNSIGNED NOT NULL DEFAULT 0,
  `active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SEED: GAMES ──────────────────────────────
INSERT INTO `games` (`id`,`name`,`image`,`category`,`currency`,`needs_server_id`,`popular`,`active`,`sort_order`) VALUES
('mobile-legends', 'Mobile Legends',     'assets/images/moba.webp',   'MOBA',          'Diamond',         1, 1, 1, 1),
('free-fire',      'Free Fire',          'assets/images/epep.jpg',    'Battle Royale',  'Diamond',         0, 1, 1, 2),
('pubg-mobile',    'PUBG Mobile',        'assets/images/pubg.jpg',    'Battle Royale',  'UC',              0, 1, 1, 3),
('genshin-impact', 'Genshin Impact',     'assets/images/genshin.jpg', 'RPG',            'Genesis Crystal', 0, 1, 1, 4),
('valorant',       'Valorant',           'assets/images/valo.png',    'FPS',            'VP',              0, 0, 1, 5),
('call-of-duty',   'Call of Duty Mobile','assets/images/cod.jpg',     'FPS',            'CP',              0, 0, 1, 6),
('arena-of-valor', 'Arena of Valor',     'assets/images/hok.jpg',     'MOBA',           'Voucher',         1, 0, 1, 7),
('clash-of-clans', 'Clash of Clans',     'assets/images/coc.jpg',     'Strategy',       'Gems',            0, 0, 1, 8);

-- ── SEED: PRODUCTS ───────────────────────────
INSERT INTO `products` (`id`,`game_id`,`amount`,`bonus`,`price`,`discount`,`final_price`,`popular`,`active`,`sort_order`) VALUES
('ml-1','mobile-legends','86',   '',    20000,  0,  20000, 1,1,1),
('ml-2','mobile-legends','172',  '',    40000,  0,  40000, 0,1,2),
('ml-3','mobile-legends','257',  '',    60000,  0,  60000, 0,1,3),
('ml-4','mobile-legends','344',  '',    80000,  0,  80000, 0,1,4),
('ml-5','mobile-legends','429',  '',   100000, 10,  90000, 0,1,5),
('ml-6','mobile-legends','514',  '',   120000,  0, 120000, 0,1,6),
('ml-7','mobile-legends','706',  '',   160000,  0, 160000, 1,1,7),
('ml-8','mobile-legends','878',  '+92',200000,  5, 190000, 0,1,8),
('ml-9','mobile-legends','2195', '+230',500000,15, 425000, 0,1,9),
('ff-1','free-fire','50',  '',  7000,  0,  7000, 0,1,1),
('ff-2','free-fire','100', '', 14000,  0, 14000, 1,1,2),
('ff-3','free-fire','210', '', 28000,  0, 28000, 0,1,3),
('ff-4','free-fire','355', '', 47000,  0, 47000, 0,1,4),
('ff-5','free-fire','720', '', 95000, 10, 85500, 0,1,5),
('ff-6','free-fire','1450','',190000, 15,161500, 0,1,6),
('pubg-1','pubg-mobile','60',  '', 15000,  0,  15000, 0,1,1),
('pubg-2','pubg-mobile','325', '', 75000,  0,  75000, 1,1,2),
('pubg-3','pubg-mobile','660', '',150000,  0, 150000, 0,1,3),
('pubg-4','pubg-mobile','1800','',400000, 10, 360000, 0,1,4),
('gs-1','genshin-impact','60',  '', 16000,  0,  16000, 0,1,1),
('gs-2','genshin-impact','330', '', 79000,  0,  79000, 1,1,2),
('gs-3','genshin-impact','1090','',249000,  0, 249000, 0,1,3),
('gs-4','genshin-impact','2240','',479000,  5, 455050, 0,1,4),
('valo-1','valorant','475', '', 50000,  0,  50000, 0,1,1),
('valo-2','valorant','1000','',100000,  0, 100000, 1,1,2),
('valo-3','valorant','2050','',200000,  0, 200000, 0,1,3),
('valo-4','valorant','3650','',350000,  8, 322000, 0,1,4),
('cod-1','call-of-duty','80',  '', 15000,  0,  15000, 0,1,1),
('cod-2','call-of-duty','400', '', 70000,  0,  70000, 1,1,2),
('cod-3','call-of-duty','800', '',140000,  0, 140000, 0,1,3),
('cod-4','call-of-duty','2000','',350000, 10, 315000, 0,1,4),
('aov-1','arena-of-valor','60',  '', 15000,  0,  15000, 0,1,1),
('aov-2','arena-of-valor','300', '', 70000,  0,  70000, 1,1,2),
('aov-3','arena-of-valor','600', '',140000,  0, 140000, 0,1,3),
('aov-4','arena-of-valor','1500','',350000, 10, 315000, 0,1,4),
('coc-1','clash-of-clans','80',  '', 15000,  0,  15000, 0,1,1),
('coc-2','clash-of-clans','500', '', 75000,  0,  75000, 1,1,2),
('coc-3','clash-of-clans','1200','',150000,  0, 150000, 0,1,3),
('coc-4','clash-of-clans','2500','',300000, 10, 270000, 0,1,4);

-- ── SEED: PAYMENT METHODS ────────────────────
INSERT INTO `payment_methods` (`id`,`name`,`icon`,`fee`,`active`,`sort_order`) VALUES
('gopay',        'GoPay',        'fa-mobile-screen-button', 0,    1, 1),
('ovo',          'OVO',          'fa-mobile-screen-button', 0,    1, 2),
('dana',         'DANA',         'fa-mobile-screen-button', 0,    1, 3),
('bank-transfer','Transfer Bank','fa-building-columns',     0,    1, 4),
('credit-card',  'Kartu Kredit', 'fa-credit-card',          2500, 1, 5);
