# Guide d'Implémentation - Sirius Solar Admin System

## Vue d'ensemble
Implémentation complète d'un système d'administration pour la gestion des réservations de nettoyage de panneaux solaires.

## Architecture

### 1. **Entités de Base de Données** (`src/Entity/`)

#### Reservation.php
- **Propriétés clés**: `numero`, `statut`, `dateSouhaitee`, `heureSouhaitee`, `nombrePanneaux`, `prixEstime`
- **Statuts possibles**: 
  - `EN_ATTENTE` (nouvelle demande)
  - `CONFIRMEE` (validée par admin)
  - `REALISEE` (intervention effectuée)
  - `REFUSEE` (rejetée)
  - `ANNULEE_ADMIN` (annulée par admin)
- **Relations**: 
  - Many-to-One vers `Customer`
  - One-to-Many vers `Intervention`
  - One-to-Many vers `ReservationHistory` (audit log)

#### Customer.php
- Contient les informations client: nom, prénom, email, téléphone, adresse
- Relations: One-to-Many vers `Reservation`

#### Intervention.php
- Enregistrement d'une intervention réalisée
- Propriétés: `dateIntervention`, `technicien`, `prixRealise`, `photos`

#### ReservationHistory.php
- Audit log de toutes les modifications
- Propriétés: `action`, `ancienneValeur`, `nouvelleValeur`, `administrateur`, `dateAction`

#### Notification.php
- Notifications pour l'admin
- Propriétés: `titre`, `message`, `type`, `lue`, `dateCreation`

#### User.php
- Utilisateurs administrateurs
- Implémente `UserInterface` pour l'authentification Symfony
- Propriétés: `email`, `password`, `roles`, `nom`, `prenom`

### 2. **Repositories** (`src/Repository/`)

Chaque entité possède un repository avec des méthodes personnalisées:

- **ReservationRepository**: `findByStatut()`, `findPending()`, `findByDateRange()`, `findByCustomer()`, `getStats()`
- **CustomerRepository**: `findBySearchTerm()`, `findByEmail()`, `findByVille()`
- **InterventionRepository**: `findByDateRange()`, `findUpcoming()`, `findCompleted()`
- **NotificationRepository**: `findUnread()`, `findByType()`, `countUnread()`
- **ReservationHistoryRepository**: `findByReservation()`, `findByAction()`, `findByAdministrateur()`
- **UserRepository**: `findByEmail()`, `findAllAdmins()`, `findActive()`

### 3. **Services** (`src/Service/`)

#### ReservationService.php
Orchestre les transitions d'état des réservations:
- `validateReservation()`: EN_ATTENTE → CONFIRMEE
- `refuseReservation()`: EN_ATTENTE → REFUSEE
- `rescheduleReservation()`: Change la date/heure
- `cancelReservation()`: Annulation admin
- `completeReservation()`: CONFIRMEE → REALISEE
- `generateReservationNumber()`: Format RES-YYYYMMDD-XXX
- Chaque transition crée un historique et une notification

#### NotificationService.php
Gestion des notifications:
- `createNotification()`: Crée une nouvelle notification
- `markAsRead()`: Marquer comme lue
- `markAllAsRead()`: Marquer tout comme lu
- `deleteNotification()`: Suppression
- `createUpcomingInterventionNotifications()`: Rappels automatiques
- `getUnreadCount()`: Compteur non-lus

#### EmailService.php
Envoi d'emails au format HTML:
- `sendConfirmationEmail()`: Confirmation de réservation
- `sendRefusalEmail()`: Rejet de demande
- `sendRescheduleEmail()`: Report d'intervention
- `sendCancellationEmail()`: Annulation
- `sendCompletionEmail()`: Intervention réalisée

### 4. **Contrôleurs** (`src/Controller/`)

#### AdminDashboardController.php
- Routes: `/admin/` (dashboard), `/admin/notifications`, `/admin/notification/{id}/read`
- Affiche statistiques, demandes en attente, notifications non lues
- Protégé par `#[IsGranted('ROLE_ADMIN')]`

#### AdminReservationController.php
- Routes: `/admin/reservations/` (liste), `/{id}` (détails)
- Actions: confirm, refuse, reschedule, cancel, complete
- Gère les transitions d'état avec validation

#### AdminCustomerController.php
- Routes: `/admin/customers/` (liste), `/{id}` (détails), `/{id}/edit`, `/{id}/delete`
- CRUD complet pour les clients
- Empêche suppression si réservations existantes

#### ReservationController.php
- Routes: `/reserver` (formulaire), `/reserver` (POST), `/reservation/{numero}` (statut)
- Formulaire public de réservation
- Crée client + réservation en une seule transaction
- Calcul automatique du prix estimé

#### SecurityController.php
- Routes: `/login`, `/logout`
- Authentification standard Symfony

### 5. **Templates Twig** (`templates/`)

#### Admin Layout (`admin/base.html.twig`)
- Sidebar avec navigation
- Flash messages pour feedback utilisateur
- Responsive design

#### Dashboard (`admin/dashboard/index.html.twig`)
- Cards statistiques (attente, confirmée, réalisée, refusée)
- Liste des demandes en attente
- Notifications non lues

#### Réservations (`admin/reservations/`)
- `list.html.twig`: Filtrage par statut
- `show.html.twig`: Détails + actions contextuelles

#### Clients (`admin/customers/`)
- `list.html.twig`: Recherche/filtrage
- `show.html.twig`: Détails + historique réservations
- `edit.html.twig`: Modification données

#### Login (`security/login.html.twig`)
- Formulaire d'authentification
- Design moderne avec gradient

## Installation & Configuration

### 1. Créer la base de données
```bash
php bin/console doctrine:database:create
```

### 2. Générer les migrations
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 3. Créer un utilisateur admin
```bash
php bin/console make:user

# Ou via une commande custom:
php bin/console app:create-admin
```

### 4. Configuration Symfony (`.env`)
```
DATABASE_URL="mysql://user:password@127.0.0.1:3306/sirius_solar"
MAILER_DSN="sendmail://default"
```

### 5. Configuration de sécurité (`config/packages/security.yaml`)
```yaml
security:
  password_hashers:
    Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
      algorithm: auto
      cost: 4

  providers:
    users_in_memory: { memory: null }
    users:
      entity:
        class: App\Entity\User
        property: email

  firewalls:
    dev:
      pattern: ^/(_(profiler|wdt)|css|images|js)/
      security: false
    login:
      pattern: ^/login$
      security: false
    admin:
      pattern: ^/admin
      form_login:
        login_path: app_login
        check_path: app_login
      logout:
        path: app_logout
        target: home
    main:
      lazy: true
      user_provider: users
      form_login:
        login_path: app_login
        check_path: app_login
      logout:
        path: app_logout

  access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/login, roles: PUBLIC_ACCESS }
```

## Flux de Travail Typique

### Réservation Client
1. Client accède à `/reserver`
2. Remplit le formulaire
3. Système crée Customer + Reservation (EN_ATTENTE)
4. Notification créée pour l'admin
5. Email de confirmation envoyé

### Validation Admin
1. Admin accède `/admin/reservations`
2. Voit les demandes EN_ATTENTE
3. Clique "Détails" → page de réservation
4. Choisit "Confirmer"
5. Réservation passe en CONFIRMEE
6. Client reçoit email de confirmation
7. Notification créée

### Intervention Réalisée
1. Admin accède à réservation CONFIRMEE
2. Clique "Marquer réalisée"
3. Entre prix réalisé + commentaire
4. Réservation passe en REALISEE
5. Client reçoit email de completion
6. Historique enregistré

## Transitions d'État Autorisées

```
EN_ATTENTE → CONFIRMEE (validation)
EN_ATTENTE → REFUSEE (refus)
CONFIRMEE → REALISEE (completion)
CONFIRMEE → ANNULEE_ADMIN (annulation)
EN_ATTENTE → ANNULEE_ADMIN (annulation)
CONFIRMEE → CONFIRMEE (reschedule change date/heure)
```

## Calcul du Prix

```php
prixBase = 50 CHF par panneau
multiplicateurs = {
  'plat': 1.0,
  'incline_leger': 1.1,
  'incline_moyen': 1.2,
  'incline_fort': 1.3,
  'avec_obstacles': 1.5,
}

prixEstime = nombrePanneaux * prixBase * multiplicateur[typeToiture]
```

## Prochaines Étapes

1. **Migrations Doctrine**: Générer et exécuter les migrations
2. **Authentification Complète**: Implémenter l'authentification avec roles
3. **Emails**: Configurer le mailer (SMTP/Sendmail)
4. **Tests**: Créer des tests unitaires pour les services
5. **Frontend Phase 3**: Templates pour le formulaire de réservation public
6. **API REST**: Ajouter des endpoints JSON pour mobile app

## Commandes Utiles

```bash
# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Réinitialiser la DB complètement
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Lancer le serveur de dev
symfony server:start
# ou
php -S localhost:8000 -t public
```

## Notes d'Implémentation

- **Validation**: Effectuée au niveau de la logique métier (ReservationService)
- **Audit**: Toutes les modifications enregistrées dans ReservationHistory
- **Notifications**: Créées automatiquement lors de chaque transition d'état
- **Emails**: Format HTML avec emojis pour meilleure UX
- **Sécurité**: Authentification Symfony + check ROLE_ADMIN sur toutes routes admin
