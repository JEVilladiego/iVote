-- =============================================================
--  iVOTE CS — Full Database Schema
--  Run this once to set up your database.
-- =============================================================

CREATE DATABASE IF NOT EXISTS ivote_cs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ivote_cs;

-- ---------------------------------------------------------------
-- USERS TABLE  (students + admin share this table, role column differentiates)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    VARCHAR(20)  NOT NULL UNIQUE,        -- e.g. M2023-00000
    first_name    VARCHAR(80)  NOT NULL,
    last_name     VARCHAR(80)  NOT NULL,
    middle_initial VARCHAR(5)  DEFAULT '',
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    course        VARCHAR(80)  NOT NULL,
    year_level    VARCHAR(20)  NOT NULL DEFAULT '1st Year',
    address       VARCHAR(255) DEFAULT '',
    profile_pic   VARCHAR(255) DEFAULT '',
    role          ENUM('student','admin') NOT NULL DEFAULT 'student',
    -- Verification flow:
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    id_document   VARCHAR(255) DEFAULT '',             -- path to uploaded COR/ID
    verified_at   DATETIME     DEFAULT NULL,
    verified_by   INT          DEFAULT NULL,           -- admin user id
    has_voted     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- ELECTIONS TABLE
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS elections (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    start_date  DATETIME     NOT NULL,
    end_date    DATETIME     NOT NULL,
    status      ENUM('upcoming','ongoing','ended') NOT NULL DEFAULT 'upcoming',
    created_by  INT,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- POSITIONS TABLE
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS positions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    title       VARCHAR(100) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- CANDIDATES TABLE
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidates (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    position_id INT NOT NULL,
    student_id  VARCHAR(20) NOT NULL,               -- student's school ID
    name        VARCHAR(160) NOT NULL,
    course      VARCHAR(80)  NOT NULL,
    partylist   VARCHAR(100) DEFAULT '',
    motto       TEXT,
    platforms   TEXT,
    achievements TEXT,
    photo       VARCHAR(255) DEFAULT '',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- VOTES TABLE  (one row per voter per position)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS votes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    election_id  INT NOT NULL,
    position_id  INT NOT NULL,
    candidate_id INT NOT NULL,
    voter_id     INT NOT NULL,                      -- users.id of voter
    voted_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_voter_position (voter_id, position_id),   -- prevents double-voting per position
    FOREIGN KEY (election_id)  REFERENCES elections(id)  ON DELETE CASCADE,
    FOREIGN KEY (position_id)  REFERENCES positions(id)  ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (voter_id)     REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- DEFAULT ADMIN ACCOUNT
-- Password: Admin@1234  (bcrypt hash — change after first login)
-- ---------------------------------------------------------------
INSERT IGNORE INTO users
    (student_id, first_name, last_name, email, password_hash, course, year_level, role, status)
VALUES
    ('ADM-9901', 'System', 'Admin', 'admin@ivote.edu.ph',
     '$2y$12$Iu7wW1mKGBbTNQrC3R5Z8eRm7AGMH/n4SL3OKC28DzaH4K8.wNI3u',
     'Administration', 'N/A', 'admin', 'approved');
