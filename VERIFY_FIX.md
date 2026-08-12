# Vérification du Fix - Configuration Sécurité

Guide rapide pour vérifier que la correction a été appliquée.

## ✅ Étape 1: Vérifier le Fichier

Ouvrir `config/packages/security.yaml` et vérifier que le firewall "admin" contient:

```yaml
admin:
  pattern: ^/admin
  provider: users        ← Cette ligne doit être présente
  form_login:
    login_path: app_login
    check_path: app_login
    username_parameter: email
    password_parameter: password
  logout:
    path: app_logout
    target: home
  remember_me:
    secret: '%kernel.secret%'
    lifetime: 604800
```

## ✅ Étape 2: Vider le Cache

```bash
php bin/console cache:clear
```

Résultat attendu:
```
Clearing the cache for the dev environment with debug enabled
           ✓ Clearing cache for "cache.global_clearer"
```

## ✅ Étape 3: Vérifier la Configuration

```bash
php bin/console debug:config security
```

Vous devriez voir:
```
security:
  firewalls:
    admin:
      provider: users    ← Vérifier que c'est présent
      pattern: ^/admin
      form_login:
        login_path: app_login
        check_path: app_login
        username_parameter: email
        password_parameter: password
      logout:
        path: app_logout
        target: home
      remember_me:
        secret: ...
        lifetime: 604800
```

## ✅ Étape 4: Démarrer le Serveur

```bash
# Arrêter le serveur actuel (Ctrl+C si en cours)

# Redémarrer
symfony server:start
```

Vous ne devriez PAS voir d'erreur de configuration.

## ✅ Étape 5: Tester la Login

1. Ouvrir http://localhost:8000/login
2. Entrer vos identifiants:
   - Email: `admin@sirius-solar.ch` (ou l'email que vous avez créé)
   - Password: (le password que vous avez défini)
3. Cliquer "Connexion"

**Résultats attendus:**
- ✅ Redirection vers `/admin/`
- ✅ Dashboard chargé
- ✅ Pas d'erreur d'authentification

## ✅ Étape 6: Vérifier les Routes

```bash
php bin/console debug:router | grep -E "(admin|login)"
```

Vous devriez voir les routes admin listées.

## 🚨 Si Ça Ne Fonctionne Pas

### Erreur: "Invalid credentials"
- Vérifier que l'utilisateur admin existe:
  ```bash
  php bin/console doctrine:query:sql "SELECT email FROM user"
  ```
- Si absent, créer un nouvel admin:
  ```bash
  php bin/console app:create-admin
  ```

### Erreur: "CSRF token is invalid"
- Vider le cache:
  ```bash
  php bin/console cache:clear
  ```
- Redémarrer le serveur

### Erreur: "Not configuring explicitly the provider..."
- Vérifier que `provider: users` est dans le firewall "admin"
- Vérifier l'indentation YAML
- Vider le cache et redémarrer

### Erreur: "The doctrine dbal connection driver pdo_mysql is not installed"
- MySQL n'est pas en cours d'exécution
- Redémarrer MySQL:
  ```bash
  brew services restart mysql
  ```

## ✅ Checklist Finale

- [ ] Fichier `security.yaml` contient `provider: users` dans firewall "admin"
- [ ] `php bin/console cache:clear` a réussi
- [ ] `php bin/console debug:config security` montre la bonne config
- [ ] Serveur démarre sans erreur
- [ ] Page de login charge sans erreur
- [ ] Connexion avec admin fonctionne
- [ ] Dashboard admin charge correctement

## 🎉 C'est Bon!

Si tous les points de la checklist sont cochés, la correction a été appliquée avec succès.

### Commandes de Vérification Rapide

```bash
# Tout en un (à exécuter à la racine du projet):
echo "1. Vérifier config..." && \
grep -A 5 "^  admin:" config/packages/security.yaml && \
echo "\n2. Vider cache..." && \
php bin/console cache:clear && \
echo "\n3. Vérifier routes..." && \
php bin/console debug:router | grep "admin_dashboard" && \
echo "\n✅ Tout semble correct!"
```

---

**Fix appliqué:** Août 2025
**Status:** ✅ Complété
