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

INSERT INTO `admins` (`id`, `username`, `password`, `nama`) 
VALUES (1, 'admin', '$2y$10$.oQ/xdzijeyifOEantk1q.rihVYYOKGc1fpZWkYI7fHuqUNV8fQdS', 'Administrator Himpunan')
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

INSERT INTO `divisions` (`id`, `nama_divisi`) VALUES 
(1, 'Steering Committee'),
(2, 'Internal'),
(3, 'Relasi'),
(4, 'Edukasi'),
(5, 'Research and Development'),
(6, 'Infokom')
ON DUPLICATE KEY UPDATE `nama_divisi`=VALUES(`nama_divisi`);

-- Data 24 Pengurus Himpunan (4 orang per divisi)
INSERT INTO `members` (`id`, `nim`, `nama`, `division_id`, `no_wa`, `is_active`) VALUES
-- Steering Committee (Divisi 1)
(1, '2510631170005', 'Ridho Mughni Nursila', 1, '081234567801', 1),
(2, '2510631170072', 'Rizky Fitri Putri Awaliyah', 1, '081234567802', 1),
(3, '2510631170032', 'Dimas Indrawijaya', 1, '081234567803', 1),
(4, '2510631170060', 'Abdul Kholiq Safaraz', 1, '081234567804', 1),

-- Internal (Divisi 2)
(5, '2510631170014', 'SRI DAYANTI', 2, '081234567805', 1),
(6, '2510631170056', 'Aqilah filzah hidayat', 2, '081234567806', 1),
(7, '2510631170047', 'Nasya Putri Anjani', 2, '081234567807', 1),
(8, '2510631170069', 'Muhammad Wildan Hilmi', 2, '081234567808', 1),

-- Relasi (Divisi 3)
(9, '2510631170041', 'Marssello Hotasi', 3, '081234567809', 1),
(10, '2510631170008', 'Vika Nur Azizah', 3, '081234567810', 1),
(11, '2510631170002', 'Fahmy Ramadhan', 3, '081234567811', 1),
(12, '2510631170023', 'Fitria', 3, '081234567812', 1),

-- Edukasi (Divisi 4)
(13, '2510631170029', 'Arsyad Afkar Al Luthfi', 4, '081234567813', 1),
(14, '2510631170044', 'Muhammad Danish Ghaisan', 4, '081234567814', 1),
(15, '2510631170026', 'Anandya Giri Ramadhan', 4, '081234567815', 1),
(16, '2510631170050', 'Roihan Fakhri', 4, '081234567816', 1),

-- Research and Development (Divisi 5)
(17, '2510631170038', 'Hamnah Luthfiyyah Azzahra', 5, '081828113423', 1),
(18, '2510631170035', 'Fajar Abdilah', 5, '0823427423', 1),
(19, '2510631170017', 'Arsya Awfazahran', 5, '081234567819', 1),
(20, '2510631170011', 'Nanang Saepudin', 5, '08232424', 1),

-- Infokom (Divisi 6)
(21, '2510631170066', 'Muhammad Fajar Ramadhan', 6, '082934823423', 1),
(22, '2510631170063', 'Farid Rahman Arrasy', 6, '081234567822', 1),
(23, '2510631170020', 'Fauziyah Dinda Laudy', 6, '081234567823', 1),
(24, '2510631170053', 'Wafiq Azizah', 6, '081234567824', 1)
ON DUPLICATE KEY UPDATE 
  `nim`=VALUES(`nim`), 
  `nama`=VALUES(`nama`), 
  `division_id`=VALUES(`division_id`), 
  `no_wa`=VALUES(`no_wa`), 
  `is_active`=VALUES(`is_active`);
