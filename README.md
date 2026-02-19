# Itinairair - Projet Fullstack Symfony
[![Symfony](https://img.shields.io/badge/Symfony-7.3-blue?logo=symfony)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://php.net)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-brightgreen)](https://phpstan.org)
[![CI/CD](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?logo=githubactions&logoColor=white)](https://github.com/features/actions)

ItinairAir est une application SaaS collaborative de gestion de voyage, elle offre une interface fluide pour construire des itinéraires jour par jour et gérer des réservations.

> [https://itinairair.com](https://itinairair.com) - Démo avec compte temporaire d'une heure **sans inscription** nécessaire.

---

### 🗓️ Planification jour par jour
![Planning view](https://github.com/user-attachments/assets/34f68add-3bf0-4f7b-adf7-524ed4dcd4fb)

### 🗺️ Vue itinéraire
![Itinerary view](https://github.com/user-attachments/assets/40f04d48-8c02-4084-b0af-dfb3318a4fd3)


## Stack Technique

### Backend
- **Framework** : Symfony 7.3 (PHP 8.2+)
- **Base de données** : PostgreSQL
- **Authentification** : OAuth2 (Discord / Google)
- **Uploads** : `vich/uploader-bundle` & Flysystem
- **Mailing** : Symfony Mailer (intégration API Brevo)
- **Symfony Scheduler**: Nettoyage automatique des comptes démo

### Frontend
- **Interactivité** : Symfony UX Turbo, Stimulus, Live Components. Google Place API
- **Styling** : Tailwind CSS 4
- **Gestion des Assets** : Symfony AssetMapper.
- **Responsive** : Interface responsive et optimisée pour mobile.

### Tests & Qualité
- **PHPStan niveau 6**
- **PHPUnit**
- **PHP-CS-Fixer**

### DevOps & Infrastructure
* **Hébergement** : VPS hébergé chez OVH sous Ubuntu.
* **Provisioning :** Configuration manuelle (Nginx, ufw, SSH, PHP-FPM, Supervisor, SSL).
* **CI/CD** : GitHub Actions pour les **workflows** de build et déploiement.
* **Déploiement** : **Deployer PHP** configuré pour des déploiements atomiques.

