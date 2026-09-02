CREATE TABLE `password_reset_tokens`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expiration` DATETIME NOT NULL,
    `used` TINYINT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
ALTER TABLE
    `password_reset_tokens` ADD INDEX `password_reset_tokens_user_id_index`(`user_id`);
ALTER TABLE
    `password_reset_tokens` ADD UNIQUE `password_reset_tokens_token_unique`(`token`);
ALTER TABLE
    `password_reset_tokens` ADD INDEX `password_reset_tokens_expiration_index`(`expiration`);
CREATE TABLE `referentiel`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `module` ENUM(
        'Bases',
        'Conjugaison',
        'Grammaire',
        'Prononciation',
        'Methodologie',
        'Vocabulaire',
        'Au Quotidien'
    ) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `contenu` TEXT NOT NULL,
    `niveaux` SET
        ('A1', 'A2', 'B1', 'B2', 'C1', 'C2') NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(), `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
ALTER TABLE
    `referentiel` ADD INDEX `referentiel_module_index`(`module`);
CREATE TABLE `ressources`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `type_fichier` ENUM(
        'audio',
        'video',
        'pdf',
        'image',
        'autre'
    ) NOT NULL,
    `chemin_fichier` TEXT NOT NULL,
    `nom_fichier_original` VARCHAR(255) NOT NULL,
    `nb_telechargement` INT NOT NULL DEFAULT 0,
    `uploader_id` INT NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(), `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
CREATE TABLE `seances`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL,
    `description` TEXT NULL,
    `outils` TEXT NOT NULL,
    `analyse_seance` BIGINT NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(), `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
CREATE TABLE `seances_ressources`(
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `seance_id` INT NOT NULL,
    `ressource_id` INT NOT NULL
);
CREATE TABLE `session_formation_user`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_formation_id` INT NOT NULL,
    `user_id` INT NOT NULL
);
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    `email` VARCHAR(255) NOT NULL,
    `login` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,

    `role` ENUM(
        'admin',
        'formateur',
        'stagiaire_op',
        'stagiaire_fpc'
    ) NOT NULL DEFAULT 'stagiaire_op',

    `nom` VARCHAR(255) NOT NULL,
    `prenom` VARCHAR(255) NOT NULL,

    `email_verified_at` TIMESTAMP NULL,

    `remember_token` VARCHAR(100) NULL,

    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,

    PRIMARY KEY (`id`),

    UNIQUE KEY `users_email_unique` (`email`),
    UNIQUE KEY `users_login_unique` (`login`)
);
CREATE TABLE `session_formations`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `num_GESCOF` VARCHAR(255) NOT NULL,
    `nom` VARCHAR(255) NOT NULL,
    `code_produit` ENUM('FPC', 'OP') NOT NULL DEFAULT 'OP',
    `objectifs` BIGINT NOT NULL,
    `distanciel` TINYINT NOT NULL DEFAULT 0,
    `lien_teams` VARCHAR(255) NULL COMMENT 'Possiblement auto-généré ?',
    `client` VARCHAR(255) NOT NULL,
    `dates_planning` TEXT NOT NULL COMMENT 'Export d\'une liste de dates depuis GESCOF',
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL
);
CREATE TABLE `seances_session_formation`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `seance_id` INT NOT NULL,
    `session_formation_id` INT NOT NULL
);
CREATE TABLE `seances_referentiel`(
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `seance_id` INT NOT NULL,
    `referentiel_id` INT NOT NULL
);
CREATE TABLE `documents`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `chemin_fichier` TEXT NOT NULL,
    `nom_fichier_original` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(), `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
CREATE TABLE `referentiel_ressources`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `referentiel_id` INT NOT NULL,
    `ressources_id` INT NOT NULL
);
CREATE TABLE `user_ressources`(
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `referentiel_id` INT NOT NULL
);
CREATE TABLE `user_documents`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `document_id` INT NOT NULL
);
ALTER TABLE
    `user_ressources` ADD CONSTRAINT `user_ressources_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `session_formation_user` ADD CONSTRAINT `session_formation_user_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `referentiel_ressources` ADD CONSTRAINT `referentiel_ressources_ressources_id_foreign` FOREIGN KEY(`ressources_id`) REFERENCES `ressources`(`id`);
ALTER TABLE
    `seances_ressources` ADD CONSTRAINT `seances_ressources_seance_id_foreign` FOREIGN KEY(`seance_id`) REFERENCES `seances`(`id`);
ALTER TABLE
    `session_formation_user` ADD CONSTRAINT `session_formation_user_session_formation_id_foreign` FOREIGN KEY(`session_formation_id`) REFERENCES `session_formations`(`id`);
ALTER TABLE
    `user_ressources` ADD CONSTRAINT `user_ressources_referentiel_id_foreign` FOREIGN KEY(`referentiel_id`) REFERENCES `ressources`(`uploader_id`);
ALTER TABLE
    `seances_session_formation` ADD CONSTRAINT `seances_session_formation_seance_id_foreign` FOREIGN KEY(`seance_id`) REFERENCES `seances`(`id`);
ALTER TABLE
    `seances_referentiel` ADD CONSTRAINT `seances_referentiel_seance_id_foreign` FOREIGN KEY(`seance_id`) REFERENCES `seances`(`id`);
ALTER TABLE
    `seances_session_formation` ADD CONSTRAINT `seances_session_formation_session_formation_id_foreign` FOREIGN KEY(`session_formation_id`) REFERENCES `session_formations`(`id`);
ALTER TABLE
    `seances_referentiel` ADD CONSTRAINT `seances_referentiel_referentiel_id_foreign` FOREIGN KEY(`referentiel_id`) REFERENCES `referentiel`(`id`);
ALTER TABLE
    `user_documents` ADD CONSTRAINT `user_documents_document_id_foreign` FOREIGN KEY(`document_id`) REFERENCES `documents`(`id`);
ALTER TABLE
    `user_documents` ADD CONSTRAINT `user_documents_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `referentiel_ressources` ADD CONSTRAINT `referentiel_ressources_referentiel_id_foreign` FOREIGN KEY(`referentiel_id`) REFERENCES `referentiel`(`id`);
ALTER TABLE
    `password_reset_tokens` ADD CONSTRAINT `password_reset_tokens_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `seances_ressources` ADD CONSTRAINT `seances_ressources_ressource_id_foreign` FOREIGN KEY(`ressource_id`) REFERENCES `ressources`(`id`);