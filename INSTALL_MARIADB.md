# MariaDB Installation et Configuration

## ✅ Étape 1: MariaDB est Installé et Démarré

**Statut:** ✅ En cours d'exécution

```bash
brew services start mariadb
# Résultat: ✔︎ Successfully started `mariadb`
```

## 📝 Étape 2: Configurer .env.local

Ouvrir le fichier `.env.local` à la racine du projet:

```bash
nano .env.local
```

Remplacer la ligne DATABASE_URL par:

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/sirius_solar"
```

**Ou si vous avez un password root:**

```env
DATABASE_URL="mysql://root:votre_password@127.0.0.1:3306/sirius_solar"
```

Sauvegarder et fermer (Ctrl+X, puis Y, puis Entrée).

## ✅ Étape 3: Créer la Base de Données

```bash
# À la racine du projet nettoyage-photovoltaique/
cd nettoyage-photovoltaique

# Créer la base de données
php bin/console doctrine:database:create
```

**Résultat attendu:**
```
Created database `sirius_solar` for connection named default
```

**Si erreur "Access denied":**
```bash
# Vérifier la connexion MariaDB
mysql -u root

# Vous devriez voir le prompt MariaDB
MariaDB [(none)]>

# Créer manuellement la base de données
CREATE DATABASE sirius_solar;
EXIT;
```

## ✅ Étape 4: Générer les Migrations

```bash
php bin/console make:migration
```

**Résultat attendu:**
```
created: migrations/Version20250812120000.php
```

## ✅ Étape 5: Exécuter les Migrations

```bash
php bin/console doctrine:migrations:migrate
```

**Répondre `yes` quand demandé**

**Résultat attendu:**
```
[OK] Successfully migrated to version: Version20250812120000
```

## ✅ Étape 6: Créer l'Utilisateur Admin

```bash
php bin/console app:create-admin
```

Répondre aux questions:
```
Email de l'administrateur: admin@sirius-solar.ch
Mot de passe: [Entrer un password]
Nom: Sirius
Prénom: Admin
```

**Résultat attendu:**
```
✓ Administrateur créé avec succès!
```

## ✅ Étape 7: Vider le Cache

```bash
php bin/console cache:clear
```

## ✅ Étape 8: Démarrer le Serveur

```bash
symfony server:start
```

Ou:

```bash
php -S localhost:8000 -t public
```

## ✅ Étape 9: Tester

Ouvrir un navigateur:

1. **Accueil:** http://localhost:8000/
2. **Formulaire:** http://localhost:8000/reserver
3. **Login Admin:** http://localhost:8000/login
4. **Dashboard:** http://localhost:8000/admin/

---

## 🚨 Troubleshooting

### "Connection refused"
```bash
# Vérifier que MariaDB est en cours d'exécution
brew services list

# Relancer si nécessaire
brew services restart mariadb
```

### "Access denied for user 'root'"
```bash
# Tester la connexion
mysql -u root

# Si pas d'accès, définir un password
mysqladmin -u root password "votre_password"

# Puis mettre à jour .env.local
DATABASE_URL="mysql://root:votre_password@127.0.0.1:3306/sirius_solar"
```

### "Database already exists"
```bash
# Normal si créée avant. Les migrations créeront les tables.
```

### "No command 'php' found"
```bash
# Vous devez avoir PHP 8.1+ installé
php --version

# Si absent, installer via Homebrew
brew install php@8.2
```

---

## ✅ Checklist Finale

- [ ] MariaDB installé et démarré (`brew services start mariadb`)
- [ ] `.env.local` configuré avec DATABASE_URL
- [ ] `php bin/console doctrine:database:create` réussi
- [ ] `php bin/console doctrine:migrations:migrate` réussi
- [ ] `php bin/console app:create-admin` réussi
- [ ] `php bin/console cache:clear` exécuté
- [ ] Serveur démarre: `symfony server:start`
- [ ] Page d'accueil charge: http://localhost:8000/
- [ ] Login fonctionne: http://localhost:8000/login
- [ ] Dashboard admin charge: http://localhost:8000/admin/

---

## 📞 Si Vous Avez Besoin d'Aide

1. Consultez QUICK_START.md pour plus de détails
2. Vérifiez les logs: `tail -f var/log/dev.log`
3. Consultez VERIFY_FIX.md pour les vérifications

---

**Vous êtes maintenant prêt à utiliser Sirius Solar! 🚀**
