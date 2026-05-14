-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 14 mai 2026 à 16:50
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `helpdesk`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id_article` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `mots_cles` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `audit_log`
--

CREATE TABLE `audit_log` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `table_concernee` varchar(100) DEFAULT NULL,
  `date_action` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `audit_log`
--

INSERT INTO `audit_log` (`id_log`, `id_user`, `action`, `table_concernee`, `date_action`) VALUES
(1, 2, 'Modification utilsateur ID 3', 'utilisateur', '2026-05-07 01:16:53');

-- --------------------------------------------------------

--
-- Structure de la table `categorie_ticket`
--

CREATE TABLE `categorie_ticket` (
  `id_categorie` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie_ticket`
--

INSERT INTO `categorie_ticket` (`id_categorie`, `libelle`) VALUES
(1, 'Fonctionnel'),
(2, 'Technique'),
(3, 'Demande d’évolution'),
(4, 'SIG');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `id_ticket` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date_commentaire` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id_commentaire`, `id_ticket`, `id_user`, `message`, `date_commentaire`) VALUES
(1, 1, 3, 'le support va s\'en occuper bientot', '2026-05-06 13:40:05');

-- --------------------------------------------------------

--
-- Structure de la table `commune`
--

CREATE TABLE `commune` (
  `id_commune` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commune`
--

INSERT INTO `commune` (`id_commune`, `nom`, `region`) VALUES
(1, 'Thiadiaye', 'Thies'),
(2, 'Guekhokh', 'Thies'),
(3, 'Saly', 'Thies'),
(4, 'Dakar', 'Dakar'),
(5, 'Pikine', 'Dakar'),
(6, 'Guédiawaye', 'Dakar'),
(7, 'Rufisque', 'Dakar'),
(8, 'Thiès', 'Thiès'),
(9, 'Mbour', 'Thiès'),
(10, 'Saint-Louis', 'Saint-Louis'),
(11, 'Ziguinchor', 'Ziguinchor'),
(12, 'Kaolack', 'Kaolack'),
(13, 'Tambacounda', 'Tambacounda');

-- --------------------------------------------------------

--
-- Structure de la table `historique_ticket`
--

CREATE TABLE `historique_ticket` (
  `id_historique` int(11) NOT NULL,
  `id_ticket` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `date_action` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `module_sifcom`
--

CREATE TABLE `module_sifcom` (
  `id_module` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `module_sifcom`
--

INSERT INTO `module_sifcom` (`id_module`, `nom`) VALUES
(1, 'Parcellaire'),
(2, 'Dossiers fonciers'),
(3, 'SIG (Système d\'Information Géographique)'),
(4, 'Gestion des utilisateurs'),
(5, 'Reporting et statistiques'),
(6, 'Gestion des documents'),
(7, 'Procédures foncières'),
(8, 'Attribution NICAD'),
(9, 'Archivage');

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `priorite`
--

CREATE TABLE `priorite` (
  `id_priorite` int(11) NOT NULL,
  `libelle` varchar(50) DEFAULT NULL,
  `delai_resolution` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `priorite`
--

INSERT INTO `priorite` (`id_priorite`, `libelle`, `delai_resolution`) VALUES
(1, 'Critique', 4),
(2, 'Haute', 24),
(3, 'Moyenne', 48),
(4, 'Basse', 72);

-- --------------------------------------------------------

--
-- Structure de la table `statut_ticket`
--

CREATE TABLE `statut_ticket` (
  `id_statut` int(11) NOT NULL,
  `libelle` varchar(50) DEFAULT NULL,
  `couleur` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_ticket`
--

INSERT INTO `statut_ticket` (`id_statut`, `libelle`, `couleur`) VALUES
(1, 'Nouveau', 'blue'),
(2, 'En cours', 'orange'),
(3, 'En attente', 'gray'),
(4, 'Resolu', 'green'),
(5, 'Cloture', 'black'),
(6, 'Rejete', 'red'),
(7, 'En pause', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `ticket`
--

CREATE TABLE `ticket` (
  `id_ticket` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `id_module` int(11) DEFAULT NULL,
  `id_priorite` int(11) DEFAULT NULL,
  `id_statut` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_cloture` datetime DEFAULT NULL,
  `date_limite` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ticket`
--

INSERT INTO `ticket` (`id_ticket`, `titre`, `description`, `id_user`, `id_categorie`, `id_module`, `id_priorite`, `id_statut`, `date_creation`, `date_cloture`, `date_limite`) VALUES
(1, 'Erreur enregistrement parcelle', 'Impossible de sauvegarder une parcelle dans le système', 6, 2, 1, 3, 3, '2026-05-06 13:09:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('ADMIN','SUPPORT_N1','SUPPORT_N2','UTILISATEUR','SUPERVISEUR') NOT NULL,
  `id_commune` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `id_commune`, `date_creation`) VALUES
(2, 'Ba', 'Malick', 'malickba3267@gmail.com', '$2y$10$rtUPKwcRiIyZMqVLoBN3CuSyUBs5Mo2exDqVzjElpAoj.IXtI3P3W', 'ADMIN', 2, '2026-04-30 12:29:41'),
(3, 'Ba', 'Ibrahima', 'iba3267@gmail.com', '$2y$10$3Tqz0zPjyMyoGWq7gpqC8ufSUjM4D2Nqud72P/q3/YmC7UiLG6lQu', 'SUPERVISEUR', 1, '2026-04-30 13:29:31'),
(4, 'Fall', 'Matar', 'mfall32@gmail.com', '$2y$10$97U/MlQOpkmO.wK3mq8vXOLCNDTpJ1WCp9pqjg2QTRwVXd9nObERC', 'SUPPORT_N1', 3, '2026-04-30 13:31:32'),
(5, 'Faye', 'Aliou', 'afaye32@gmail.com', '$2y$10$5EnNAjegvYUvlnutsUew5.iTxXTY5uvLEgah.f52h/6PbxRzvWqce', 'SUPPORT_N2', 3, '2026-04-30 13:33:06'),
(6, 'Bah', 'Sadou', 'sbah32@gmail.com', '$2y$10$4FK3i2lDOopTFgtjOfbeU.3AD.RfWVFnJPUKaP4cnKqqC662Hr5Cy', 'UTILISATEUR', 2, '2026-04-30 13:34:32');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id_article`);

--
-- Index pour la table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `categorie_ticket`
--
ALTER TABLE `categorie_ticket`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `commune`
--
ALTER TABLE `commune`
  ADD PRIMARY KEY (`id_commune`);

--
-- Index pour la table `historique_ticket`
--
ALTER TABLE `historique_ticket`
  ADD PRIMARY KEY (`id_historique`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `module_sifcom`
--
ALTER TABLE `module_sifcom`
  ADD PRIMARY KEY (`id_module`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `priorite`
--
ALTER TABLE `priorite`
  ADD PRIMARY KEY (`id_priorite`);

--
-- Index pour la table `statut_ticket`
--
ALTER TABLE `statut_ticket`
  ADD PRIMARY KEY (`id_statut`);

--
-- Index pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id_ticket`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_categorie` (`id_categorie`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_priorite` (`id_priorite`),
  ADD KEY `id_statut` (`id_statut`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_commune` (`id_commune`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id_article` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `categorie_ticket`
--
ALTER TABLE `categorie_ticket`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `commune`
--
ALTER TABLE `commune`
  MODIFY `id_commune` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `historique_ticket`
--
ALTER TABLE `historique_ticket`
  MODIFY `id_historique` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_sifcom`
--
ALTER TABLE `module_sifcom`
  MODIFY `id_module` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `priorite`
--
ALTER TABLE `priorite`
  MODIFY `id_priorite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `statut_ticket`
--
ALTER TABLE `statut_ticket`
  MODIFY `id_statut` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`),
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `historique_ticket`
--
ALTER TABLE `historique_ticket`
  ADD CONSTRAINT `historique_ticket_ibfk_1` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`),
  ADD CONSTRAINT `historique_ticket_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`),
  ADD CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`id_categorie`) REFERENCES `categorie_ticket` (`id_categorie`),
  ADD CONSTRAINT `ticket_ibfk_3` FOREIGN KEY (`id_module`) REFERENCES `module_sifcom` (`id_module`),
  ADD CONSTRAINT `ticket_ibfk_4` FOREIGN KEY (`id_priorite`) REFERENCES `priorite` (`id_priorite`),
  ADD CONSTRAINT `ticket_ibfk_5` FOREIGN KEY (`id_statut`) REFERENCES `statut_ticket` (`id_statut`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`id_commune`) REFERENCES `commune` (`id_commune`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
