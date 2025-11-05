CREATE DATABASE IF NOT EXISTS cid10 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cid10;

CREATE TABLE chapters (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  numeral       VARCHAR(8) NOT NULL,         -- I, II, ... XXII
  code_start    CHAR(3) NOT NULL,            -- A00, B00, ...
  code_end      CHAR(3) NOT NULL,            -- B99, C99, ...
  title         VARCHAR(255) NOT NULL,
  UNIQUE KEY uk_chapter_range (code_start, code_end)
);

CREATE TABLE blocks (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id    INT NOT NULL,
  code_start    CHAR(3) NOT NULL,            -- ex.: I10
  code_end      CHAR(3) NOT NULL,            -- ex.: I15
  title         VARCHAR(255) NOT NULL,
  FOREIGN KEY (chapter_id) REFERENCES chapters(id),
  INDEX idx_block_codes (code_start, code_end)
);

CREATE TABLE categories (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  block_id      INT NOT NULL,
  code          CHAR(3) NOT NULL,            -- ex.: I10, E11, J45
  title         VARCHAR(255) NOT NULL,
  FOREIGN KEY (block_id) REFERENCES blocks(id),
  UNIQUE KEY uk_category_code (code),
  INDEX idx_category_title (title)
);

CREATE TABLE subcategories (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  category_id   INT NOT NULL,
  code_full     CHAR(6) NOT NULL,            -- ex.: I21.0, E11.2  (formato 'Xnn.n' ou 'Xnn.nn')
  title         VARCHAR(255) NOT NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  UNIQUE KEY uk_subcategory_code (code_full),
  INDEX idx_subcategory_title (title)
);
