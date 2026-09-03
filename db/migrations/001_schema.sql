CREATE TABLE agca_saison (
  id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(20) NOT NULL UNIQUE,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  statut ENUM('active','gelee') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_serie (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(5) NOT NULL UNIQUE,
  libelle VARCHAR(50) NOT NULL,
  structure VARCHAR(40) NOT NULL COMMENT 'S=simple D=double, ordre des parties',
  pts_gagne TINYINT NOT NULL,
  pts_nul TINYINT NOT NULL,
  pts_perdu TINYINT NOT NULL,
  index_min DECIMAL(4,1) NULL,
  index_max DECIMAL(4,1) NULL,
  joker_h TINYINT NOT NULL DEFAULT 0,
  joker_d TINYINT NOT NULL DEFAULT 0,
  mixte TINYINT(1) NOT NULL DEFAULT 0,
  forfait_score TINYINT NOT NULL DEFAULT 15,
  ordre TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_golf (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(80) NOT NULL UNIQUE,
  ville VARCHAR(80) NULL,
  contact VARCHAR(200) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_equipe (
  id INT AUTO_INCREMENT PRIMARY KEY,
  golf_id INT NOT NULL,
  serie_id INT NOT NULL,
  nom VARCHAR(50) NOT NULL,
  capitaine_nom VARCHAR(60) NULL,
  capitaine_prenom VARCHAR(60) NULL,
  capitaine_email VARCHAR(120) NULL,
  capitaine_tel VARCHAR(40) NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_equipe_serie_nom (serie_id, nom),
  CONSTRAINT fk_equipe_golf FOREIGN KEY (golf_id) REFERENCES agca_golf(id),
  CONSTRAINT fk_equipe_serie FOREIGN KEY (serie_id) REFERENCES agca_serie(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_utilisateur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identifiant VARCHAR(50) NOT NULL,
  serie_id INT NULL,
  equipe_id INT NULL,
  hash_sha1 CHAR(40) NULL,
  hash_bcrypt VARCHAR(255) NULL,
  est_admin TINYINT(1) NOT NULL DEFAULT 0,
  nom_affiche VARCHAR(80) NULL,
  email VARCHAR(120) NULL,
  derniere_connexion DATETIME NULL,
  tentatives TINYINT NOT NULL DEFAULT 0,
  bloque_jusqua DATETIME NULL,
  UNIQUE KEY uq_utilisateur (identifiant, serie_id),
  CONSTRAINT fk_utilisateur_serie FOREIGN KEY (serie_id) REFERENCES agca_serie(id),
  CONSTRAINT fk_utilisateur_equipe FOREIGN KEY (equipe_id) REFERENCES agca_equipe(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_division (
  id INT AUTO_INCREMENT PRIMARY KEY,
  saison_id INT NOT NULL,
  serie_id INT NOT NULL,
  libelle VARCHAR(50) NOT NULL,
  ordre TINYINT NOT NULL DEFAULT 0,
  CONSTRAINT fk_division_saison FOREIGN KEY (saison_id) REFERENCES agca_saison(id),
  CONSTRAINT fk_division_serie FOREIGN KEY (serie_id) REFERENCES agca_serie(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_division_equipe (
  division_id INT NOT NULL,
  equipe_id INT NOT NULL,
  position TINYINT NOT NULL,
  PRIMARY KEY (division_id, equipe_id),
  UNIQUE KEY uq_division_position (division_id, position),
  CONSTRAINT fk_de_division FOREIGN KEY (division_id) REFERENCES agca_division(id),
  CONSTRAINT fk_de_equipe FOREIGN KEY (equipe_id) REFERENCES agca_equipe(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_journee (
  id INT AUTO_INCREMENT PRIMARY KEY,
  saison_id INT NOT NULL,
  serie_id INT NOT NULL,
  numero TINYINT NOT NULL,
  phase ENUM('aller','retour') NOT NULL,
  date_calendrier DATE NOT NULL,
  UNIQUE KEY uq_journee (saison_id, serie_id, phase, numero),
  CONSTRAINT fk_journee_saison FOREIGN KEY (saison_id) REFERENCES agca_saison(id),
  CONSTRAINT fk_journee_serie FOREIGN KEY (serie_id) REFERENCES agca_serie(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_rencontre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  division_id INT NOT NULL,
  journee_id INT NOT NULL,
  recevant_id INT NOT NULL,
  invite_id INT NOT NULL,
  date_reelle DATE NOT NULL,
  reportee TINYINT(1) NOT NULL DEFAULT 0,
  statut ENUM('a_jouer','enregistree','forfait') NOT NULL DEFAULT 'a_jouer',
  forfaitaire_id INT NULL,
  total_pour SMALLINT NOT NULL DEFAULT 0,
  total_contre SMALLINT NOT NULL DEFAULT 0,
  pts_rencontre_pour DECIMAL(3,1) NOT NULL DEFAULT 0,
  pts_rencontre_contre DECIMAL(3,1) NOT NULL DEFAULT 0,
  bonus_invite DECIMAL(2,1) NOT NULL DEFAULT 0,
  alertes JSON NULL,
  alertes_vues TINYINT(1) NOT NULL DEFAULT 0,
  enregistree_le DATETIME NULL,
  enregistree_par INT NULL,
  UNIQUE KEY uq_rencontre (division_id, journee_id, recevant_id),
  KEY idx_rencontre_statut_date (statut, date_reelle),
  CONSTRAINT fk_rencontre_division FOREIGN KEY (division_id) REFERENCES agca_division(id),
  CONSTRAINT fk_rencontre_journee FOREIGN KEY (journee_id) REFERENCES agca_journee(id),
  CONSTRAINT fk_rencontre_recevant FOREIGN KEY (recevant_id) REFERENCES agca_equipe(id),
  CONSTRAINT fk_rencontre_invite FOREIGN KEY (invite_id) REFERENCES agca_equipe(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_joueur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  golf_id INT NOT NULL,
  nom VARCHAR(60) NOT NULL,
  prenom VARCHAR(60) NULL,
  sexe CHAR(1) NULL,
  dernier_index DECIMAL(4,1) NULL,
  cree_par INT NULL,
  cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fusionne_dans INT NULL,
  KEY idx_joueur_golf_nom (golf_id, nom),
  CONSTRAINT fk_joueur_golf FOREIGN KEY (golf_id) REFERENCES agca_golf(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_partie (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rencontre_id INT NOT NULL,
  numero TINYINT NOT NULL,
  type ENUM('simple','double') NOT NULL,
  rec_joueur1_id INT NULL, rec_index1 DECIMAL(4,1) NULL, rec_sexe1 CHAR(1) NULL,
  rec_joueur2_id INT NULL, rec_index2 DECIMAL(4,1) NULL, rec_sexe2 CHAR(1) NULL,
  inv_joueur1_id INT NULL, inv_index1 DECIMAL(4,1) NULL, inv_sexe1 CHAR(1) NULL,
  inv_joueur2_id INT NULL, inv_index2 DECIMAL(4,1) NULL, inv_sexe2 CHAR(1) NULL,
  resultat CHAR(1) NULL COMMENT 'G N P vu du recevant',
  score_trous TINYINT NULL,
  score_restants TINYINT NULL,
  score_as TINYINT(1) NOT NULL DEFAULT 0,
  pts_pour SMALLINT NOT NULL DEFAULT 0,
  pts_contre SMALLINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_partie (rencontre_id, numero),
  CONSTRAINT fk_partie_rencontre FOREIGN KEY (rencontre_id) REFERENCES agca_rencontre(id) ON DELETE CASCADE,
  CONSTRAINT fk_partie_rj1 FOREIGN KEY (rec_joueur1_id) REFERENCES agca_joueur(id),
  CONSTRAINT fk_partie_rj2 FOREIGN KEY (rec_joueur2_id) REFERENCES agca_joueur(id),
  CONSTRAINT fk_partie_ij1 FOREIGN KEY (inv_joueur1_id) REFERENCES agca_joueur(id),
  CONSTRAINT fk_partie_ij2 FOREIGN KEY (inv_joueur2_id) REFERENCES agca_joueur(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_journal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quand DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  utilisateur_id INT NULL,
  action VARCHAR(40) NOT NULL,
  cible_type VARCHAR(30) NULL,
  cible_id INT NULL,
  detail JSON NULL,
  KEY idx_journal_cible (cible_type, cible_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agca_relance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rencontre_id INT NOT NULL,
  envoyee_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_relance_rencontre (rencontre_id),
  CONSTRAINT fk_relance_rencontre FOREIGN KEY (rencontre_id) REFERENCES agca_rencontre(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
