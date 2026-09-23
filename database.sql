CREATE DATABASE IF NOT EXISTS `ayo_piket`;
USE `ayo_piket`;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `divisions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_divisi` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nim` VARCHAR(20) NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `division_id` INT NOT NULL,
  `no_wa` VARCHAR(20) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`division_id`) REFERENCES `divisions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `hari` ENUM('Senin', 'Kamis') NOT NULL,
  `bulan` TINYINT NOT NULL,
  `tahun` SMALLINT NOT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `attendances` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `foto_bukti` VARCHAR(255) NOT NULL,
  `status_verifikasi` ENUM('pending', 'valid', 'invalid') DEFAULT 'valid',
  `catatan` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `admins` (`username`, `password`, `nama`) 
VALUES ('admin', '$2y$10$.oQ/xdzijeyifOEantk1q.rihVYYOKGc1fpZWkYI7fHuqUNV8fQdS', 'Administrator Himpunan')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `divisions` (`nama_divisi`) VALUES 
('Steering Committee'),
('Internal'),
('Relasi'),
('Edukasi'),
('Research and Development'),
('Infokom')
ON DUPLICATE KEY UPDATE `id`=`id`;
