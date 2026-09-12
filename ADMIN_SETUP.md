# 👨‍💼 Guide d'Administration - Sirius-Solar

Complete guide pour configurer et gérer l'administration de Sirius-Solar.

---

## 🔑 Informations de l'Entreprise

### Email Administrateur
```
Email: Info@Sirius-solar-services.ch
```

### Adresse Physique
```
Route des dragons 7
1027 Cheseaux-sur-Lausanne
Suisse
```

---

## 🔐 Premier Accès Administrateur

### Mot de Passe Temporaire
```
Email: admin@sirius-solar.ch
Mot de passe: Sirius2024!Temp
```

⚠️ **IMPORTANT:** Le mot de passe par défaut DOIT être changé immédiatement!

### Procédure de Premier Accès

1. **Aller à la page admin**
   ```
   https://sirius-solar.ch/admin
   ```

2. **Se connecter avec:**
   - Email: `admin@sirius-solar.ch`
   - Mot de passe: `Sirius2024!Temp`

3. **Changer le mot de passe**
   - Aller dans "Profil" → "Changer le mot de passe"
   - Entrer un nouveau mot de passe sécurisé
   - Sauvegarder

---

## 🛠️ Configuration Initiale

### 1. Exécuter le Script d'Initialisation

Sur le serveur, exécutez:

```bash
cd /var/www/sirius-solar
chmod +x init-company-settings.sh
./init-company-settings.sh
```

**Le script va:**
- Créer le compte administrateur
- Demander un nouveau mot de passe
- Sauvegarder les infos de l'entreprise
- Vérifier la configuration

### 2. Informations à Fournir

Le script demandera:
- **Email administrateur:** (défaut: admin@sirius-solar.ch)
- **Nouveau mot de passe:** (sécurisé, 8+ caractères)
- **Confirmation du mot de passe**

### 3. Vérification

Le script vérifiera:
- ✓ Configuration Symfony
- ✓ Templates Twig
- ✓ Fichiers YAML

---

## 📝 Modifier les Informations de l'Entreprise

### Fichier de Configuration
```
config/services/company_info.yaml
```

### Informations à mettre à jour:

```yaml
parameters:
  company:
    name: "Sirius-Solar"
    email: "Info@Sirius-solar-services.ch"
    phone: "+41 XX XXX XX XX"  # ← À METTRE À JOUR
    
    address:
      street: "Route des dragons 7"
      city: "Cheseaux-sur-Lausanne"
      postal_code: "1027"
      country: "Switzerland"
```

### Apres modification:
```bash
php bin/console cache:clear --env=prod
```

---

## 📧 Configurer l'Email de l'Entreprise

### Dans `.env.production`

```bash
# Email de l'entreprise
COMPANY_EMAIL=Info@Sirius-solar-services.ch

# Configuration SMTP pour envoyer des emails
MAILER_DSN=smtp://username:password@smtp.gmail.com:587?encryption=tls
```

### Tester l'envoi d'email

```bash
php bin/console make:command test:email
php bin/console test:email Info@Sirius-solar-services.ch
```

---

## 🔒 Politique de Sécurité

### Mot de Passe Administrateur

**Exigences:**
- ✓ Minimum 12 caractères
- ✓ Majuscules et minuscules
- ✓ Nombres et caractères spéciaux
- ✓ Pas de mots du dictionnaire

**Exemple sécurisé:**
```
Sirius2024!Solar#Admin
```

### Changement Régulier

- Changer le mot de passe tous les 3 mois
- Jamais le partager par email
- Ne pas l'écrire dans des fichiers publics

---

## 👥 Ajouter des Utilisateurs Administrateurs

### Via la Ligne de Commande

```bash
php bin/console fos:user:create \
  --super-admin \
  nom@email.com \
  nom@email.com \
  password123
```

### Via l'Interface Admin

1. Aller à Admin → Utilisateurs
2. Cliquer "Ajouter Utilisateur"
3. Remplir les informations
4. Sauvegarder

---

## 🔧 Tâches Administratives Courantes

### Voir les Devis

```
Admin → Devis
```

### Voir les Messages de Contact

```
Admin → Messages
```

### Voir les Réservations

```
Admin → Réservations
```

### Voir les Clients

```
Admin → Clients
```

### Voir les Membres

```
Admin → Membres
```

---

## 📊 Tableaux de Bord

### Dashboard Principal
```
Admin → Dashboard
```

Affiche:
- Nombre de devis aujourd'hui
- Nombre de messages non lus
- Dernières réservations
- Statistiques mensuelles

---

## 🆘 Dépannage

### Impossible de se connecter

```bash
# Réinitialiser le mot de passe administrateur
php bin/console fos:user:change-password admin@sirius-solar.ch

# Vous serez invité à entrer un nouveau mot de passe
```

### Cache corrompu

```bash
# Vider le cache
php bin/console cache:clear --env=prod

# Réchauffer le cache
php bin/console cache:warmup --env=prod
```

### Problème d'email

```bash
# Vérifier la configuration SMTP
php bin/console config:dump-reference swiftmailer

# Tester la connexion SMTP
php bin/console test:email admin@sirius-solar.ch
```

---

## 📋 Checklist Admin Quotidienne

- [ ] Vérifier les nouveaux devis
- [ ] Répondre aux messages de contact
- [ ] Vérifier les réservations
- [ ] Mettre à jour le statut des commandes
- [ ] Vérifier les logs d'erreurs
- [ ] Vérifier les sauvegardes

---

## 🔐 Sécurité - À Faire

- [ ] Changer le mot de passe défaut **IMMÉDIATEMENT**
- [ ] Configurer 2FA (optionnel mais recommandé)
- [ ] Configurer l'email de l'entreprise
- [ ] Configurer les alertes
- [ ] Vérifier les permissions des utilisateurs
- [ ] Mettre en place une politique de mot de passe

---

## 📞 Support

Pour toute question:
- Email: Info@Sirius-solar-services.ch
- Consulter: DEPLOYMENT_GUIDE.md
- Consulter: MONITORING_SETUP.md

---

**Bienvenue en tant qu'administrateur de Sirius-Solar! 👨‍💼**
