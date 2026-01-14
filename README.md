# Itinairair - Planificateur de Voyage️

ItinairAir est une app de planification de voyage conçue pour aider à organiser des séjours, gérer des itinéraires et collaborer avec des amis.

## ✨ Fonctionnalités Clés

- **Itinéraire Interactif** : Planification par glisser-déposer pour les activités quotidiennes, vols et hébergements.
- **Planification Collaborative** : Invitez des amis à rejoindre votre voyage (Système de Membres de Voyage).
- **Connexion Sociale** : Authentification transparente via Google et Discord.
- **Destinations Intelligentes** : Logique de gestion des chevauchements de destinations et continuité du voyage.

## 🛠️ Stack Technique

### Backend
- **Framework** : Symfony 7.3 (PHP 8.3+)
- **Base de données** : PostgreSQL
- **Authentification** : `knpuniversity/oauth2-client-bundle` (Discord/Google)
- **Uploads** : `vich/uploader-bundle` / Flysystem
- **Mailing** : Symfony Mailer (intégration Brevo)

### Frontend
- **Style** : TailwindCSS 4
- **Interactivité** :
    - **Symfony UX Turbo** : Pour la navigation type SPA et les mises à jour de flux.
    - **Stimulus** : Pour un comportement JavaScript modeste et maintenable.
    - **Live Components** : Mises à jour de composants en temps réel (Symfony UX).
    - **Vanilla Calendar Pro** : Pour des interfaces de sélection de dates robustes.
- **Gestion des Assets** : Symfony AssetMapper.

### DevOps & Infrastructure
- **Hébergement** : Hébergé chez OVH sur un VPS (Ubuntu).
- **CI/CD** : GitHub Actions pour les **workflows** de build et déploiement.
- **Déploiement** : **Deployer PHP** configuré pour des déploiements atomiques.
