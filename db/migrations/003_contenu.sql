CREATE TABLE agca_page (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  titre VARCHAR(150) NOT NULL,
  corps_md MEDIUMTEXT NOT NULL,
  corps_html MEDIUMTEXT NOT NULL,
  systeme TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'page requise par le site : ni supprimable ni renommable',
  dans_menu TINYINT(1) NOT NULL DEFAULT 0,
  ordre SMALLINT NOT NULL DEFAULT 0,
  modifie_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  modifie_par INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_actualite (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  date_publication DATE NOT NULL,
  resume VARCHAR(400) NULL,
  corps_md MEDIUMTEXT NOT NULL,
  corps_html MEDIUMTEXT NOT NULL,
  publie TINYINT(1) NOT NULL DEFAULT 0,
  cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  modifie_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  modifie_par INT NULL,
  KEY idx_actualite_pub (publie, date_publication)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_competition (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  nom VARCHAR(100) NOT NULL,
  accroche VARCHAR(120) NULL,
  formule VARCHAR(300) NULL,
  corps_md MEDIUMTEXT NOT NULL,
  corps_html MEDIUMTEXT NOT NULL,
  ordre SMALLINT NOT NULL DEFAULT 0,
  actif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_document (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  fichier VARCHAR(160) NOT NULL UNIQUE,
  type_mime VARCHAR(80) NOT NULL,
  taille INT NOT NULL,
  categorie ENUM('statuts','ag','voyage','palmares','autre') NOT NULL DEFAULT 'autre',
  televerse_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  televerse_par INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_palmares (
  id INT AUTO_INCREMENT PRIMARY KEY,
  competition_id INT NOT NULL,
  saison VARCHAR(20) NOT NULL,
  lieu VARCHAR(100) NULL,
  vainqueur VARCHAR(200) NULL,
  detail_md MEDIUMTEXT NULL,
  detail_html MEDIUMTEXT NULL,
  document_id INT NULL,
  ordre SMALLINT NOT NULL DEFAULT 0,
  CONSTRAINT fk_palmares_competition FOREIGN KEY (competition_id) REFERENCES agca_competition(id) ON DELETE CASCADE,
  CONSTRAINT fk_palmares_document FOREIGN KEY (document_id) REFERENCES agca_document(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_organigramme (
  id INT AUTO_INCREMENT PRIMARY KEY,
  groupe ENUM('bureau','ca') NOT NULL,
  fonction VARCHAR(100) NOT NULL,
  prenom VARCHAR(60) NULL,
  nom VARCHAR(60) NOT NULL,
  golf VARCHAR(80) NULL,
  ordre SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_album (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(120) NOT NULL,
  annee SMALLINT NULL,
  url VARCHAR(300) NOT NULL,
  ordre SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_page_document (
  page_id INT NOT NULL,
  document_id INT NOT NULL,
  ordre SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (page_id, document_id),
  CONSTRAINT fk_pd_page FOREIGN KEY (page_id) REFERENCES agca_page(id) ON DELETE CASCADE,
  CONSTRAINT fk_pd_document FOREIGN KEY (document_id) REFERENCES agca_document(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE agca_golf
  ADD COLUMN site_web VARCHAR(200) NULL,
  ADD COLUMN membre TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN ordre SMALLINT NOT NULL DEFAULT 0;
