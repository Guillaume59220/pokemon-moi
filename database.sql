
create table pokedex (
    id INTEGER NOT NULL AUTO_INCREMENT,
    nom_proprietaire VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

create table pokemon (
    id INTEGER NOT NULL AUTO_INCREMENT,
    numero INTEGER NOT NULL,
    nom VARCHAR(50) NOT NULL,
    experience INTEGER NOT NULL DEFAULT 0,
    vie INTEGER NOT NULL,
    defense INTEGER NOT NULL,
    attaque INTEGER NOT NULL,
    id_pokedex INTEGER,
    PRIMARY KEY (id),
    FOREIGN KEY (id_pokedex) REFERENCES pokedex(id)ON DELETE SET NULL
);

ALTER TABLE `pokemon` ADD (img_url VARCHAR(255));

CREATE TABLE dresseur (
  id INTEGER NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  nom VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_naissance DATE,
  genre ENUM('masculin', 'feminin', 'non precisé'),
  PRIMARY KEY (id),
  UNIQUE `u_email` (`email`) USING BTREE
);

CREATE TABLE auth_dresseur (
  id INTEGER NOT NULL AUTO_INCREMENT,
  id_dresseur INTEGER NOT NULL,
  last_connection DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  token VARCHAR(128) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (id_dresseur) REFERENCES dresseur(id) ON DELETE CASCADE
);

ALTER TABLE pokemon ADD (
  id_dresseur INTEGER,
  FOREIGN KEY (id_dresseur) REFERENCES dresseur(id) ON DELETE SET NULL
);
