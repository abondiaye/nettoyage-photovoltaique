# Quick Start - Configuration et Lancement

Guide pas à pas pour configurer et tester l'application.

## ✅ Étape 1: Vérifier les Prérequis

Ouvrir un terminal à la racine du projet et vérifier:

```bash
# Vérifier PHP
php --version
# Doit être 8.1+

# Vérifier Composer
composer --version

# Vérifier MySQL
mysql --version
# Ou pour MariaDB: mariadb --version
```

Si une commande est manquante, l'installer d'abord.

## ✅ Étape 2: Configurer l'Environnement

### Sur macOS/Linux:

```bash
# Créer le fichier .env.local s'il n'existe pas
cp .env .env.local

# Éditer .env.local avec votre éditeur préféré
nano .env.local
# Ou:
code .env.local
```

### Configuration pour MySQL local:

```env
# Dans .env.local, remplacer/ajouter:
DATABASE_URL="mysql://root:@127.0.0.1:3306/sirius_solar"

# Si vous avez un password MySQL:
DATABASE_URL="mysql://root:votre_password@127.0.0.1:3306/sirius_solar"

# Pour tests:
MAILER_DSN="sendmail://default"

APP_ENV=dev
APP_DEBUG=true
```

### Vérifier la configuration:

```bash
# Consulter la BD URL configurée
grep DATABASE_URL .env.local
```

## ✅ Étape 3: Installer les Dépendances

```bash
# À la racine du projet:
composer install

# Attendre la fin (peut prendre 1-2 minutes)
# Vous devriez voir "completed successfully"
```

## ✅ Étape 4: Créer la Base de Données

### S'assurer que MySQL est en cours d'exécution:

```bash
# macOS (avec Homebrew)
brew services start mysql

# Linux
sudo systemctl start mysql

# Windows (si non-démarré)
# Lancer MySQL via le Panneau de Contrôle ou Docker
```

### Créer la base de données:

```bash
php bin/console doctrine:database:create
```

**Résultat attendu:**
```
Created database `sirius_solar` for connection named default
```

Si erreur `Access denied`:
```bash
# Vérifier les credentials dans .env.local
# Ou créer manuellement:
mysql -u root -p -e "CREATE DATABASE sirius_solar;"
```

## ✅ Étape 5: Exécuter les Migrations

```bash
# Générer les migrations (créé si absent)
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

**Répondre `yes` quand demandé.**

**Résultat attendu:**
```
[OK] Successfully migrated to version: Version20250812120000
```

Vérifier les tables créées:
```bash
mysql -u root sirius_solar -e "SHOW TABLES;"
```

Vous devriez voir 6 tables:
- user
- customer
- reservation
- intervention
- reservation_history
- notification

## ✅ Étape 6: Créer l'Utilisateur Administrateur

```bash
php bin/console app:create-admin
```

Répondre aux questions interactivement:
```
Email de l'administrateur: admin@sirius-solar.ch
Mot de passe: [Entrer un password fort, min 8 caractères]
Nom: Sirius
Prénom: Admin
```

**Résultat attendu:**
```
✓ Administrateur créé avec succès!

| Email                     | Nom    | Prénom |
|---------------------------|--------|--------|
| admin@sirius-solar.ch     | Sirius | Admin  |
```

## ✅ Étape 7: (Optionnel) Générer des Données de Test

```bash
php bin/console app:generate-test-data
```

**Cela crée:**
- 5 clients
- 10-15 réservations
- 10 notifications

**Résultat:**
```
✓ Données de test générées avec succès!

| Ressource    | Quantité |
|--------------|----------|
| Clients      | 5        |
| Réservations | 12       |
| Notifications| 10       |
```

## ✅ Étape 8: Vider le Cache

```bash
php bin/console cache:clear
```

## ✅ Étape 9: Démarrer le Serveur

### Option A: Avec Symfony CLI (recommandé)

```bash
symfony server:start

# Le serveur démarre sur:
# http://localhost:8000

# Pour arrêter:
# Ctrl+C
```

### Option B: Avec PHP intégré

```bash
php -S localhost:8000 -t public

# Le serveur démarre sur:
# http://localhost:8000

# Pour arrêter:
# Ctrl+C
```

### Option C: Avec Docker (si installé)

```bash
docker-compose up -d
# L'application sera sur http://localhost:8000
```

## ✅ Étape 10: Tester l'Application

### A. Page d'Accueil

Ouvrir un navigateur:
```
http://localhost:8000/
```

Vous devriez voir la page d'accueil Sirius Solar.

### B. Formulaire de Réservation Public

```
http://localhost:8000/reserver
```

Tester:
1. Remplir le formulaire
2. Observer le calcul du prix en temps réel
3. Modifier le nombre de panneaux → le prix change
4. Modifier le type de toiture → le prix change
5. Soumettre
6. Voir le numéro de réservation (ex: RES-20250812-001)

### C. Page de Suivi de Statut

Utiliser le numéro de réservation obtenu:
```
http://localhost:8000/reservation/RES-20250812-001
```

Vous devriez voir:
- Timeline avec statut EN_ATTENTE
- Détails de la réservation
- Message d'attente d'examen

### D. Login Administrateur

```
http://localhost:8000/login
```

Connectez-vous avec:
- Email: `admin@sirius-solar.ch`
- Password: [le password que vous avez défini]

### E. Dashboard Admin

```
http://localhost:8000/admin/
```

Vous devriez voir:
- 4 statistiques (en attente, confirmées, réalisées, refusées)
- Liste des demandes en attente
- Notifications non lues

### F. Gestion des Réservations

```
http://localhost:8000/admin/reservations/
```

Vous devriez voir la réservation que vous avez créée.

Cliquer "Détails" pour:
- Voir les informations complètes
- Voir les actions disponibles (Confirmer, Refuser, etc.)

**Test d'une action:**
1. Cliquer "Détails"
2. Cliquer "✅ Confirmer"
3. Voir le statut passer à CONFIRMEE
4. Voir la notification créée dans l'historique

### G. Gestion des Clients

```
http://localhost:8000/admin/customers/
```

Vous devriez voir le client créé lors de la réservation.

Cliquer sur un client pour:
- Voir ses informations
- Voir ses réservations
- Éditer son profil
- Voir l'historique

### H. Notifications

```
http://localhost:8000/admin/notifications
```

Vous devriez voir les notifications non lues.

## 🐛 Troubleshooting

### "SQLSTATE[HY000]: General error: 1030"
```bash
# MySQL n'est pas en cours d'exécution
# Redémarrer:
brew services restart mysql
```

### "Access denied for user 'root'@'localhost'"
```bash
# Vérifier DATABASE_URL dans .env.local
# Assurez-vous du format:
# mysql://user:password@host:port/database
```

### "Column not found: 1054 Unknown column"
```bash
# Les migrations n'ont pas été exécutées
php bin/console doctrine:migrations:migrate
```

### "CSRF token is invalid"
```bash
# Vider le cache
php bin/console cache:clear

# Ou redémarrer le serveur
```

### Les modifications du code n'apparaissent pas
```bash
# Vider le cache
php bin/console cache:clear

# Vérifier APP_ENV=dev dans .env.local
```

### "Port 8000 is already in use"
```bash
# Utiliser un autre port
symfony server:start --port=8001

# Ou tuer le processus:
lsof -i :8000
kill -9 <PID>
```

## ✅ Vérifications Rapides

```bash
# Vérifier les routes
php bin/console debug:router | head -20

# Vérifier les entités
php bin/console doctrine:mapping:info

# Vérifier la config sécurité
php bin/console debug:config security

# Vérifier les utilisateurs créés
php bin/console doctrine:query:sql "SELECT email, roles FROM user"

# Voir les logs en temps réel
tail -f var/log/dev.log
```

## 📋 Checklist Complète

- [ ] PHP 8.1+ installé
- [ ] Composer installé
- [ ] MySQL en cours d'exécution
- [ ] `.env.local` configuré
- [ ] `composer install` exécuté
- [ ] `doctrine:database:create` exécuté
- [ ] `doctrine:migrations:migrate` exécuté
- [ ] `app:create-admin` exécuté
- [ ] Données de test générées (optionnel)
- [ ] Cache vidé
- [ ] Serveur démarré
- [ ] Page d'accueil chargée
- [ ] Formulaire public testé
- [ ] Login admin fonctionnel
- [ ] Dashboard chargé
- [ ] Réservation confirmée avec succès

## 🎉 C'est Bon!

Si vous avez coché tous les points, l'application est prête à l'emploi!

### Commandes Utiles pour Continuer

```bash
# Voir les logs
tail -f var/log/dev.log

# Lancer les tests (quand disponibles)
php bin/phpunit

# Réinitialiser complètement la BD
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Modifier un utilisateur
php bin/console doctrine:query:sql "UPDATE user SET roles = '[\"ROLE_ADMIN\"]' WHERE email = 'admin@sirius-solar.ch'"
```

## 📞 Support

Si vous rencontrez un problème:
1. Consulter la section Troubleshooting ci-dessus
2. Vérifier les logs: `tail -f var/log/dev.log`
3. Consulter SETUP_DATABASE.md pour plus de détails
4. Vérifier que MySQL est en cours d'exécution

---

**Vous êtes maintenant prêt à utiliser Sirius Solar! 🚀**
