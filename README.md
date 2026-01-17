# Itinairair - Projet Fullstack Symfony

ItinairAir est une application SaaS collaborative de gestion de voyage, elle offre une interface fluide pour construire des itinéraires jour par jour et gérer des réservations.

> [https://itinairair.com](https://itinairair.com) - Démo avec compte temporaire d'une heure **sans inscription** nécessaire.

## 🎯 Objectifs du projet
J'ai créé ce projet pour maîtriser le cycle de vie d'un SaaS, de la conception et de l'architecture jusqu'au déploiement en production sur un VPS.

L'objectif principal était d'utiliser l'écosystème Symfony en minimisant la dépendance au JavaScript, le tout en gardant une interface fluide et responsive **SPA-like** grâce à l'utilisation de Symfony UX / Turbo / Stimulus.

## 🛠️ Stack Technique

### Backend
- **Framework** : Symfony 7.3 (PHP 8.3+)
- **Base de données** : PostgreSQL
- **Authentification** : OAuth2 (Discord/Google) via `knpuniversity/oauth2-client-bundle`
- **Uploads** : `vich/uploader-bundle` & Flysystem
- **Mailing** : Symfony Mailer (intégration API Brevo)

### Frontend
- **Interactivité** : Symfony UX Turbo, Stimulus, Live Components.
- **Styling** : Tailwind CSS 4
- **Gestion des Assets** : Symfony AssetMapper.
- **Responsive** : Interface responsive et optimisée pour mobile.

### DevOps & Infrastructure
* **Hébergement** : VPS hébergé chez OVH sous Ubuntu.
* **Provisioning :** Configuration manuelle (Nginx, ufw, SSH, PHP-FPM, Supervisor, SSL) dans le but d'apprentissage.
* **CI/CD** : GitHub Actions pour les **workflows** de build et déploiement.
* **Déploiement** : **Deployer PHP** configuré pour des déploiements atomiques.


[//]: # (## ✨ Fonctionnalités Clés)

[//]: # ()
[//]: # (- **Itinéraire Interactif** : Planification par glisser-déposer pour les activités quotidiennes, vols et hébergements.)

[//]: # (- **Planification Collaborative** : Invitez des amis à rejoindre votre voyage &#40;Système de Membres de Voyage&#41;.)

[//]: # (- **Connexion Sociale** : Authentification transparente via Google et Discord.)

[//]: # (- **Destinations Intelligentes** : Logique de gestion des chevauchements de destinations et continuité du voyage.)
