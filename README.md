# Sirius Solar - Plateforme de Gestion de Réservations

Système complet de réservation et gestion pour service de nettoyage de panneaux solaires en Suisse.

## 🚀 Démarrage Rapide

```bash
# 1. Cloner/accéder au projet
cd nettoyage-photovoltaique

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données (.env.local)
cp .env .env.local
# Éditer DATABASE_URL

# 4. Créer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Créer l'utilisateur admin
php bin/console app:create-admin

# 6. Démarrer le serveur
symfony server:start
# ou: php -S localhost:8000 -t public

# 7. Accéder à l'application
# Admin: http://localhost:8000/login
# Public: http://localhost:8000/
```

## 📋 Table des Matières

- [Architecture](#architecture)
- [Fonctionnalités](#fonctionnalités)
- [Routes Principales](#routes-principales)
- [Installation Complète](#installation-complète)
- [Documentation](#documentation)
- [Technologies](#technologies)

## 🏗️ Architecture

### Stack Technologique

```
Frontend:
  - Twig (templates)
  - HTML5 / CSS3
  - Vanilla JavaScript

Backend:
  - Symfony 6.4 Framework
  - Doctrine ORM
  - PHP 8.1+

Database:
  - MySQL 8.0+ / MariaDB

Infrastructure:
  - Composer (dépendances)
  - Symfony Console (CLI)
  - Doctrine Migrations
```

### Structure des Dossiers

```
nettoyage-photovoltaique/
├── src/
│   ├── Controller/        # Contrôleurs (Admin + Public)
│   ├── Entity/            # Models Doctrine (8 entités)
│   ├── Repository/        # Queries personnalisées
│   ├── Service/           # Logic métier (3 services)
│   └── Command/           # Commandes CLI
├── templates/             # Templates Twig (11 fichiers)
├── config/                # Configuration Symfony
├── migrations/            # Migrations Doctrine
├── public/                # Assets publics
├── var/                   # Cache, logs, uploads
└── composer.json          # Dépendances PHP
```

## ✨ Fonctionnalités

### Pour les Clients

✅ **Formulaire de Réservation**
- Design moderne et responsive
- Calcul du prix en temps réel
- Validation côté client
- 5 sections organisées
- Confirmation immédiate avec numéro

✅ **Suivi de Statut**
- Timeline visuelle (5 étapes)
- Détails complets de la réservation
- Messages contextuels
- Informations de contact

✅ **Notifications par Email**
- Confirmation de réservation
- Confirmation de visite
- Report d'intervention
- Annulation
- Completion d'intervention

### Pour les Administrateurs

✅ **Dashboard**
- Statistiques en temps réel
- Demandes en attente
- Notifications non lues
- Vue d'ensemble globale

✅ **Gestion des Réservations**
- Liste avec filtrage par statut
- Détails complets avec historique
- Actions contextuelles:
  - ✅ Confirmer une demande
  - ❌ Refuser une demande
  - 📅 Reporter une intervention
  - ✨ Marquer comme réalisée
  - 🚫 Annuler une intervention

✅ **Gestion des Clients**
- Recherche et filtrage
- Fiche client complète
- Historique des réservations
- Édition des données
- Suppression sécurisée

✅ **Système de Notifications**
- Notifications internes
- Marquer comme lu
- Filtrage par type
- Suppression

✅ **Audit Complet**
- Historique de toute modification
- Utilisateur qui a modifié
- Dates et heures
- Anciennes vs nouvelles valeurs

## 🌐 Routes Principales

### Public
- `GET /` - Accueil
- `GET /reserver` - Formulaire de réservation
- `POST /reserver` - Soumission de réservation
- `GET /reservation/{numero}` - Suivi statut
- `GET /login` - Page de connexion

### Admin (protégé par ROLE_ADMIN)
- `GET /admin/` - Dashboard
- `GET /admin/reservations/` - Liste réservations
- `GET /admin/reservations/{id}` - Détails réservation
- `POST /admin/reservations/{id}/confirm` - Confirmer
- `POST /admin/reservations/{id}/refuse` - Refuser
- `POST /admin/reservations/{id}/reschedule` - Reporter
- `POST /admin/reservations/{id}/cancel` - Annuler
- `POST /admin/reservations/{id}/complete` - Réaliser
- `GET /admin/customers/` - Liste clients
- `GET /admin/customers/{id}` - Détails client
- `GET /admin/customers/{id}/edit` - Éditer client
- `POST /admin/customers/{id}/delete` - Supprimer client
- `GET /admin/notifications` - Notifications
- `POST /admin/notification/{id}/read` - Marquer lue
- `GET /admin/logout` - Déconnexion

## 📦 Installation Complète

### Prérequis
- PHP 8.1+
- Composer
- MySQL 8.0+ ou MariaDB
- Apache/Nginx ou Symfony CLI

### Étapes Détaillées

Voir le guide complet: **[SETUP_DATABASE.md](SETUP_DATABASE.md)**

Résumé:
1. Configuration `.env.local`
2. `composer install`
3. `doctrine:database:create`
4. `doctrine:migrations:migrate`
5. `app:create-admin`
6. `symfony server:start`

### Données de Test

Générer 5 clients + 15 réservations:
```bash
php bin/console app:generate-test-data
```

## 📚 Documentation

### Guides Disponibles

| Document | Contenu |
|----------|---------|
| [SETUP_DATABASE.md](SETUP_DATABASE.md) | Installation complète et troubleshooting |
| [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) | Architecture détaillée et patterns |
| [NEXT_STEPS.md](NEXT_STEPS.md) | Configuration Symfony et sécurité |
| [PHASE1_CHANGELOG.md](PHASE1_CHANGELOG.md) | Frontend redesign + multilingual |
| [PHASE2_CHANGELOG.md](PHASE2_CHANGELOG.md) | Backend services + controllers |
| [PHASE3_CHANGELOG.md](PHASE3_CHANGELOG.md) | Frontend public + configuration |

### Commandes Utiles

```bash
# Développement
symfony server:start              # Démarrer le serveur
symfony console cache:clear       # Vider le cache
symfony console debug:router      # Voir les routes

# Base de données
symfony console make:migration    # Générer migration
symfony console doctrine:migrations:migrate  # Exécuter migrations
symfony console app:create-admin  # Créer admin
symfony console app:generate-test-data  # Données test

# Tests
php bin/phpunit                   # Lancer tests
symfony console lint:yaml config/ # Valider config
```

## 🔒 Sécurité

### Authentification
- Symfony Security avec form login
- Hashage bcrypt
- CSRF tokens
- Remember me cookies

### Autorisation
- ROLE_ADMIN pour routes admin
- PUBLIC_ACCESS pour routes publiques
- Vérifications côté serveur

### Données
- Validation client (UX)
- Validation serveur (sécurité)
- Prepared statements Doctrine
- Sanitization

## 📊 Model de Données

### Entités Principales

```
User (Administrateurs)
  ├─ email (unique)
  ├─ password (hashé)
  ├─ roles (ROLE_ADMIN)
  └─ metadata (nom, prenom, createdAt)

Customer (Clients)
  ├─ nom, prenom
  ├─ email, telephone
  ├─ adresse, ville, codePostal
  └─ reservations (1→N)

Reservation (Demandes/Interventions)
  ├─ numero (unique: RES-YYYYMMDD-XXX)
  ├─ statut (EN_ATTENTE, CONFIRMEE, REALISEE, REFUSEE, ANNULEE_ADMIN)
  ├─ dateSouhaitee, heureSouhaitee
  ├─ nombrePanneaux, typeToiture
  ├─ prixEstime
  ├─ customer (N→1)
  ├─ interventions (1→N)
  ├─ history (1→N)
  └─ notifications (1→N)

Intervention (Travaux réalisés)
  ├─ dateIntervention
  ├─ technicien
  ├─ prixRealise
  ├─ reservation (N→1)
  └─ photos

ReservationHistory (Audit log)
  ├─ action (VALIDATION, REFUS, REPORT, etc)
  ├─ ancienneValeur → nouvelleValeur
  ├─ administrateur
  ├─ dateAction
  └─ reservation (N→1)

Notification
  ├─ titre, message
  ├─ type (confirmation, refusal, reschedule, etc)
  ├─ lue (boolean)
  ├─ dateCreation
  └─ reservation (N→1, nullable)
```

## 💰 Calcul du Prix

```
Base: 50 CHF par panneau

Multiplicateurs selon type de toiture:
- Plat:                1.0
- Inclinaison légère:  1.1
- Inclinaison moyen:   1.2
- Inclinaison fort:    1.3
- Avec obstacles:      1.5

Formule: prix = panneaux × 50 × multiplicateur
```

Exemples:
- 10 panneaux, plat → 500 CHF
- 10 panneaux, incline fort → 650 CHF
- 20 panneaux, avec obstacles → 1500 CHF

## 🎯 Transitions d'État

```
EN_ATTENTE (nouveau)
  ├─→ CONFIRMEE (admin confirme)
  ├─→ REFUSEE (admin refuse)
  └─→ ANNULEE_ADMIN (admin annule)

CONFIRMEE
  ├─→ CONFIRMEE (reschedule = change date/heure)
  ├─→ REALISEE (intervention faite)
  └─→ ANNULEA_ADMIN (admin annule)

REALISEE (état final)
REFUSEE (état final)
ANNULEA_ADMIN (état final)
```

## 📧 Emails Envoyés

1. **Confirmation** - Quand client soumet
2. **Confirmation Admin** - Quand admin confirme
3. **Report** - Quand date change
4. **Refus** - Quand admin refuse
5. **Annulation** - Quand admin annule
6. **Completion** - Quand intervention réalisée

## 📱 Responsive Design

- ✅ Mobile (< 600px)
- ✅ Tablette (600px - 900px)
- ✅ Desktop (> 900px)

Templates optimisés avec CSS Grid et Flexbox

## 🚀 Déploiement

### Pré-production
```bash
# Compiler assets
symfony asset-map:compile

# Tests
php bin/phpunit

# Security check
symfony security:check
```

### Production
```bash
# Paramètres
APP_ENV=prod
APP_DEBUG=false

# Database
php bin/console doctrine:migrations:migrate --env=prod

# Cache
php bin/console cache:clear --env=prod
symfony cache:clear
```

## 🤝 Contribution

Pour contribuer:
1. Fork le projet
2. Créer une branche feature
3. Commit avec messages clairs
4. Push et créer une Pull Request

## 📞 Support

Pour l'assistance:
- Email: ndiayeharouna1991@gmail.com
- Téléphone: 077 909 64 13

## 📄 Licence

Propriétaire - Tous droits réservés

## ✅ Checklist de Production

- [ ] Configuration .env.prod complétée
- [ ] Database migrée
- [ ] SSL/HTTPS configuré
- [ ] Mailer en production
- [ ] Logs configurés
- [ ] Backups automatiques
- [ ] Monitoring actif
- [ ] Tests passent tous
- [ ] Documentation à jour

---

**Version:** 3.0 (Phase 3 Complétée)  
**Dernière mise à jour:** Août 2025  
**Status:** Production Ready ✅
