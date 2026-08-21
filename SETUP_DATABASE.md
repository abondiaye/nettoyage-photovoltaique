# Configuration de la Base de Données - Sirius Solar

## Prérequis

- PHP 8.1+
- Composer
- MySQL/MariaDB ou PostgreSQL
- Symfony CLI (optionnel mais recommandé)

## Étapes d'Installation

### 1. Configuration de l'environnement

Créer ou modifier le fichier `.env.local` à la racine du projet:

```bash
# Database Configuration
DATABASE_URL="mysql://root:password@127.0.0.1:3306/sirius_solar"

# Ou pour PostgreSQL:
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/sirius_solar"

# Mailer Configuration (optionnel pour dev)
MAILER_DSN="sendmail://default"
# Ou utiliser Mailtrap/MailHog en dev:
# MAILER_DSN="smtp://localhost:1025"

# Application
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=votre_cle_secrete_aleatoire_ici
```

### 2. Installer les dépendances

```bash
cd nettoyage-photovoltaique
composer install
```

### 3. Créer la base de données

```bash
php bin/console doctrine:database:create
```

Vous devriez voir:
```
Created database `sirius_solar` for connection named default
```

### 4. Générer les migrations

```bash
php bin/console make:migration
```

Cela crée un fichier dans `migrations/` avec le timestamp actuel. Par exemple:
```
migrations/Version20250812120000.php
```

Vérifier le fichier généré pour vous assurer que toutes les entités sont incluses:
- Reservation
- Customer
- Intervention
- ReservationHistory
- Notification
- User

### 5. Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

Taper `yes` quand demandé. Vous devriez voir:

```
[OK] Successfully migrated to version: Version20250812120000
```

### 6. Créer l'utilisateur administrateur

```bash
php bin/console app:create-admin
```

Répondre aux questions:
```
Email de l'administrateur: admin@sirius-solar.ch
Mot de passe: [entrer un mot de passe sécurisé]
Nom: Sirius
Prénom: Admin
```

Vous devriez voir:
```
✓ Administrateur créé avec succès!
```

### 7. (Optionnel) Générer des données de test

```bash
php bin/console app:generate-test-data
```

Cela crée:
- 5 clients de test
- 10-15 réservations de test
- 10 notifications de test

### 8. Démarrer le serveur de développement

#### Avec Symfony CLI
```bash
symfony server:start
```

#### Avec PHP builtin
```bash
php -S localhost:8000 -t public
```

Le serveur démarre sur http://localhost:8000

### 9. Tester l'installation

1. **Accédez à la page de login:**
   - URL: http://localhost:8000/login
   - Email: admin@sirius-solar.ch
   - Password: [le mot de passe que vous avez défini]

2. **Après connexion, vous devriez voir:**
   - Dashboard admin sur http://localhost:8000/admin/
   - Statistiques des réservations
   - Liste des notifications

3. **Tester le formulaire de réservation:**
   - URL: http://localhost:8000/reserver
   - Remplir le formulaire
   - Soumettre une réservation

4. **Vérifier dans l'admin:**
   - La nouvelle réservation appear dans la liste des demandes EN_ATTENTE

## Troubleshooting

### "SQLSTATE[HY000]: General error: 1030 Got error"
→ Le serveur MySQL n'est pas en cours d'exécution
→ Redémarrer MySQL: `brew services restart mysql` (macOS)

### "Access denied for user 'root'@'localhost'"
→ Vérifier les identifiants dans DATABASE_URL
→ Vérifier que le user MySQL a les droits de création de base de données

### "Column not found: 1054 Unknown column"
→ Les migrations n'ont pas été exécutées
→ Exécuter: `php bin/console doctrine:migrations:migrate`

### "CSRF token is invalid"
→ C'est normal après un php:S réinitialisation
→ Vider le cache: `php bin/console cache:clear`

### Les modifications du code ne s'appliquent pas
→ Vider le cache: `php bin/console cache:clear`
→ Vérifier que APP_ENV=dev dans .env

## Vérifications

Vérifier que tout est correctement installé:

```bash
# Vérifier les routes
php bin/console debug:router | grep -E "(admin|reservation|login)"

# Vérifier les entités
php bin/console doctrine:mapping:info

# Vérifier la configuration de sécurité
php bin/console debug:config security

# Vérifier l'utilisateur admin créé
php bin/console doctrine:query:sql "SELECT * FROM user"
```

## Architecture de Base de Données

### Tables créées

1. **user** - Administrateurs
   - id, email, password, roles, nom, prenom, created_at, last_login, is_active

2. **customer** - Clients
   - id, nom, prenom, email, telephone, adresse, ville, code_postal

3. **reservation** - Réservations
   - id, numero, statut, date_souhaitee, heure_souhaitee, nombre_panneaux, type_toiture, prix_estime, notes, date_creation, customer_id

4. **intervention** - Interventions réalisées
   - id, date_intervention, technicien, prix_realise, photos, date_realisation, reservation_id

5. **reservation_history** - Audit log
   - id, reservation_id, action, ancienne_valeur, nouvelle_valeur, administrateur, date_action, heure_action

6. **notification** - Notifications admin
   - id, reservation_id (nullable), titre, message, type, lue, date_creation

## Commandes Utiles

```bash
# Réinitialiser complètement la DB
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Voir les migrations exécutées
php bin/console doctrine:migrations:list

# Annuler une migration
php bin/console doctrine:migrations:migrate prev

# Vider le cache
php bin/console cache:clear

# Lancer les tests
php bin/phpunit

# Voir les logs
tail -f var/log/dev.log
```

## Variables d'Environnement Importantes

| Variable | Exemple | Description |
|----------|---------|-------------|
| DATABASE_URL | mysql://root@localhost/sirius_solar | URL de connexion à la base de données |
| APP_ENV | dev / prod | Environnement application |
| APP_DEBUG | true / false | Mode debug |
| APP_SECRET | une_cle_longue_aleatoire | Clé secrète pour chiffrement |
| MAILER_DSN | smtp://localhost:1025 | Configuration du mailer |

## Prochaines Étapes

1. ✅ Configuration complète
2. ✅ Base de données créée
3. ✅ Utilisateur admin créé
4. ✅ Données de test (optionnel)
5. ➡️ Tester l'application
6. ➡️ Personnaliser les templates
7. ➡️ Configurer les emails en production
8. ➡️ Déployer en production

## Support

Pour plus d'informations:
- Consulter IMPLEMENTATION_GUIDE.md
- Consulter NEXT_STEPS.md
- Vérifier les logs: tail -f var/log/dev.log
