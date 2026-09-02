-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           8.0.30 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage de la structure de table edl_plus. activity_log
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `attribute_changes` json DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. clients
DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. documents
DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` enum('presentation_structure','mes_documents') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Convention, Livret d''accueil, ...',
  `session_formation_id` bigint unsigned DEFAULT NULL,
  `chemin_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_fichier_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` bigint unsigned NOT NULL DEFAULT '0',
  `uploader_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_session_formation_id_foreign` (`session_formation_id`),
  KEY `documents_uploader_id_foreign` (`uploader_id`),
  CONSTRAINT `documents_session_formation_id_foreign` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_uploader_id_foreign` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. emargements
DROP TABLE IF EXISTS `emargements`;
CREATE TABLE IF NOT EXISTS `emargements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seance_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `present` tinyint(1) NOT NULL DEFAULT '0',
  `signe_at` timestamp NULL DEFAULT NULL,
  `signature_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commentaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emargements_seance_id_user_id_unique` (`seance_id`,`user_id`),
  KEY `emargements_user_id_foreign` (`user_id`),
  CONSTRAINT `emargements_seance_id_foreign` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `emargements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` datetime NOT NULL,
  `used` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `password_reset_tokens_token_unique` (`token`),
  KEY `password_reset_tokens_user_id_foreign` (`user_id`),
  KEY `password_reset_tokens_expiration_index` (`expiration`),
  CONSTRAINT `password_reset_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. questionnaires
DROP TABLE IF EXISTS `questionnaires`;
CREATE TABLE IF NOT EXISTS `questionnaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('satisfaction_chaud','satisfaction_froid','evaluation_acquis') COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_formation_id` bigint unsigned DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questionnaires_session_formation_id_foreign` (`session_formation_id`),
  CONSTRAINT `questionnaires_session_formation_id_foreign` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. questionnaire_questions
DROP TABLE IF EXISTS `questionnaire_questions`;
CREATE TABLE IF NOT EXISTS `questionnaire_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `questionnaire_id` bigint unsigned NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('texte','choix_unique','choix_multiple','echelle') COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `obligatoire` tinyint(1) NOT NULL DEFAULT '1',
  `ordre` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questionnaire_questions_questionnaire_id_foreign` (`questionnaire_id`),
  CONSTRAINT `questionnaire_questions_questionnaire_id_foreign` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. questionnaire_reponses
DROP TABLE IF EXISTS `questionnaire_reponses`;
CREATE TABLE IF NOT EXISTS `questionnaire_reponses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `questionnaire_id` bigint unsigned NOT NULL,
  `questionnaire_question_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `valeur` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `questionnaire_reponses_questionnaire_question_id_user_id_unique` (`questionnaire_question_id`,`user_id`),
  KEY `questionnaire_reponses_questionnaire_id_foreign` (`questionnaire_id`),
  KEY `questionnaire_reponses_user_id_foreign` (`user_id`),
  CONSTRAINT `questionnaire_reponses_questionnaire_id_foreign` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questionnaire_reponses_questionnaire_question_id_foreign` FOREIGN KEY (`questionnaire_question_id`) REFERENCES `questionnaire_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questionnaire_reponses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. referentiel
DROP TABLE IF EXISTS `referentiel`;
CREATE TABLE IF NOT EXISTS `referentiel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module` enum('Bases','Conjugaison','Grammaire','Prononciation','Methodologie','Vocabulaire','Au Quotidien') COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveaux` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referentiel_code_unique` (`code`),
  KEY `referentiel_module_index` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. referentiel_ressources
DROP TABLE IF EXISTS `referentiel_ressources`;
CREATE TABLE IF NOT EXISTS `referentiel_ressources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `referentiel_id` bigint unsigned NOT NULL,
  `ressource_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referentiel_ressources_referentiel_id_ressource_id_unique` (`referentiel_id`,`ressource_id`),
  KEY `referentiel_ressources_ressource_id_foreign` (`ressource_id`),
  CONSTRAINT `referentiel_ressources_referentiel_id_foreign` FOREIGN KEY (`referentiel_id`) REFERENCES `referentiel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referentiel_ressources_ressource_id_foreign` FOREIGN KEY (`ressource_id`) REFERENCES `ressources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. ressources
DROP TABLE IF EXISTS `ressources`;
CREATE TABLE IF NOT EXISTS `ressources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_fichier` enum('audio','video','pdf','image','autre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_fichier_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'Taille du fichier en octets',
  `nb_telechargement` int unsigned NOT NULL DEFAULT '0',
  `uploader_id` bigint unsigned NOT NULL,
  `session_formation_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ressources_uploader_id_foreign` (`uploader_id`),
  KEY `ressources_session_formation_id_foreign` (`session_formation_id`),
  CONSTRAINT `ressources_session_formation_id_foreign` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ressources_uploader_id_foreign` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. seances
DROP TABLE IF EXISTS `seances`;
CREATE TABLE IF NOT EXISTS `seances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_formation_id` bigint unsigned NOT NULL,
  `formateur_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `langue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objectifs` json DEFAULT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci,
  `outils` json DEFAULT NULL,
  `sources` text COLLATE utf8mb4_unicode_ci,
  `analyse_seance` text COLLATE utf8mb4_unicode_ci,
  `fiche_pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seances_formateur_id_foreign` (`formateur_id`),
  KEY `seances_user_id_foreign` (`user_id`),
  KEY `seances_session_formation_id_date_index` (`session_formation_id`,`date`),
  CONSTRAINT `seances_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seances_session_formation_id_foreign` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. seances_referentiel
DROP TABLE IF EXISTS `seances_referentiel`;
CREATE TABLE IF NOT EXISTS `seances_referentiel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seance_id` bigint unsigned NOT NULL,
  `referentiel_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seances_referentiel_seance_id_referentiel_id_unique` (`seance_id`,`referentiel_id`),
  KEY `seances_referentiel_referentiel_id_foreign` (`referentiel_id`),
  CONSTRAINT `seances_referentiel_referentiel_id_foreign` FOREIGN KEY (`referentiel_id`) REFERENCES `referentiel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seances_referentiel_seance_id_foreign` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. seances_ressources
DROP TABLE IF EXISTS `seances_ressources`;
CREATE TABLE IF NOT EXISTS `seances_ressources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seance_id` bigint unsigned NOT NULL,
  `ressource_id` bigint unsigned NOT NULL,
  `transmis` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `seances_ressources_seance_id_ressource_id_unique` (`seance_id`,`ressource_id`),
  KEY `seances_ressources_ressource_id_foreign` (`ressource_id`),
  CONSTRAINT `seances_ressources_ressource_id_foreign` FOREIGN KEY (`ressource_id`) REFERENCES `ressources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seances_ressources_seance_id_foreign` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. session_formations
DROP TABLE IF EXISTS `session_formations`;
CREATE TABLE IF NOT EXISTS `session_formations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `num_GESCOF` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Libellé du stage',
  `code_produit` enum('FPC','OP') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OP',
  `langue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Anglais',
  `client_id` bigint unsigned DEFAULT NULL,
  `formateur_id` bigint unsigned DEFAULT NULL,
  `objectifs` text COLLATE utf8mb4_unicode_ci,
  `distanciel` tinyint(1) NOT NULL DEFAULT '0',
  `lien_teams` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rythme_op` enum('trimestre','annee') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dates_planning` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_formations_client_id_foreign` (`client_id`),
  KEY `session_formations_formateur_id_foreign` (`formateur_id`),
  KEY `session_formations_num_gescof_index` (`num_GESCOF`),
  CONSTRAINT `session_formations_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `session_formations_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. session_formation_user
DROP TABLE IF EXISTS `session_formation_user`;
CREATE TABLE IF NOT EXISTS `session_formation_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_formation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `disparu_import_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_formation_user_session_formation_id_user_id_unique` (`session_formation_id`,`user_id`),
  KEY `session_formation_user_user_id_foreign` (`user_id`),
  CONSTRAINT `session_formation_user_session_formation_id_foreign` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_formation_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. session_jours
DROP TABLE IF EXISTS `session_jours`;
CREATE TABLE IF NOT EXISTS `session_jours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_formation_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `commentaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_jours_session_formation_id_date_unique` (`session_formation_id`,`date`),
  CONSTRAINT `session_jours_session_formation_id_foreign` FOREIGN KEY (`session_formation_id`) REFERENCES `session_formations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','formateur','stagiaire_op','stagiaire_fpc') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stagiaire_op',
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentation` text COLLATE utf8mb4_unicode_ci,
  `formateur_fpc` tinyint(1) NOT NULL DEFAULT '0',
  `formateur_op` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_login_unique` (`login`),
  KEY `users_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. user_documents
DROP TABLE IF EXISTS `user_documents`;
CREATE TABLE IF NOT EXISTS `user_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `document_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_documents_user_id_document_id_unique` (`user_id`,`document_id`),
  KEY `user_documents_document_id_foreign` (`document_id`),
  CONSTRAINT `user_documents_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de table edl_plus. user_referentiel
DROP TABLE IF EXISTS `user_referentiel`;
CREATE TABLE IF NOT EXISTS `user_referentiel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `referentiel_id` bigint unsigned NOT NULL,
  `consulte_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_referentiel_user_id_referentiel_id_unique` (`user_id`,`referentiel_id`),
  KEY `user_referentiel_referentiel_id_foreign` (`referentiel_id`),
  CONSTRAINT `user_referentiel_referentiel_id_foreign` FOREIGN KEY (`referentiel_id`) REFERENCES `referentiel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_referentiel_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les données exportées n'étaient pas sélectionnées.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
