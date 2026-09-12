# 🚀 Guide de Déploiement - Sirius-Solar

Ce guide couvre le déploiement complet de l'application Sirius-Solar sur un serveur de production en Suisse.

## 📋 Requirements

- **Serveur:** Linux (Ubuntu 22.04 LTS recommandé)
- **PHP:** 8.2+ avec extensions: ctype, iconv, pdo, openssl
- **Database:** PostgreSQL 16, MariaDB 10.11+, ou MySQL 8.0+
- **Docker:** (Optionnel mais recommandé) Docker & Docker Compose
- **Reverse Proxy:** Nginx ou Apache
- **SSL:** Let's Encrypt (Certbot)

---

## 🏗️ Option 1: Déploiement avec Docker (Recommandé)

### Étape 1: Préparation du serveur

```bash
# Mettre à jour le système
sudo apt update && sudo apt upgrade -y

# Installer Docker
sudo apt install -y docker.io docker-compose

# Ajouter l'utilisateur au groupe docker
sudo usermod -aG docker $USER
newgrp docker
```

### Étape 2: Cloner et configurer l'application

```bash
# Créer un dossier pour l'application
sudo mkdir -p /var/www/sirius-solar
cd /var/www/sirius-solar

# Cloner le repository (remplacer par votre URL)
sudo git clone https://your-repo-url.git .

# Créer le fichier .env.production
sudo cp .env.example .env.production
```

### Étape 3: Configurer les variables d'environnement

```bash
sudo nano .env.production
```

**Configuration pour production:**

```env
APP_ENV=prod
APP_SECRET=GenerateAStrongRandomSecretHere!
APP_SHARE_DIR=var/share

DEFAULT_URI=https://sirius-solar.ch

# Database (choisir une option)
# PostgreSQL
DATABASE_URL="postgresql://app:VeryStrongPassword!@db:5432/sirius_solar?serverVersion=16&charset=utf8"

# Email Configuration
MAILER_DSN="smtp://your-mail-server:587?encryption=tls&username=your-email&password=your-password"
```

### Étape 4: Créer docker-compose.prod.yaml

```yaml
version: '3.9'

services:
  web:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: sirius-solar-web
    restart: always
    ports:
      - "127.0.0.1:8000:8000"
    environment:
      - APP_ENV=prod
      - DATABASE_URL=${DATABASE_URL}
    volumes:
      - ./:/app
      - ./var/log:/app/var/log
    depends_on:
      - db
    networks:
      - sirius-network

  db:
    image: postgres:16-alpine
    container_name: sirius-solar-db
    restart: always
    environment:
      POSTGRES_USER: app
      POSTGRES_PASSWORD: VeryStrongPassword!
      POSTGRES_DB: sirius_solar
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - sirius-network

  reverse-proxy:
    image: nginx:alpine
    container_name: sirius-solar-proxy
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - /etc/letsencrypt:/etc/letsencrypt:ro
    depends_on:
      - web
    networks:
      - sirius-network

volumes:
  postgres_data:

networks:
  sirius-network:
    driver: bridge
```

### Étape 5: Lancer l'application

```bash
# Construire et lancer les containers
docker-compose -f docker-compose.prod.yaml up -d

# Exécuter les migrations
docker-compose -f docker-compose.prod.yaml exec web php bin/console doctrine:migrations:migrate --no-interaction

# Compiler les assets
docker-compose -f docker-compose.prod.yaml exec web php bin/console asset-map:compile

# Vérifier l'état
docker-compose -f docker-compose.prod.yaml ps
```

---

## 🏗️ Option 2: Déploiement Manuel (Sans Docker)

### Étape 1: Configuration du serveur

```bash
# Installer les dépendances
sudo apt install -y php8.2 php8.2-fpm php8.2-pgsql php8.2-mysql \
  php8.2-intl php8.2-curl php8.2-mbstring php8.2-xml \
  postgresql postgresql-contrib nginx git composer

# Cloner l'application
sudo mkdir -p /var/www/sirius-solar
cd /var/www/sirius-solar
sudo git clone https://your-repo-url.git .
```

### Étape 2: Installer les dépendances PHP

```bash
cd /var/www/sirius-solar
composer install --no-dev --optimize-autoloader

# Générer les fichiers pour la production
composer dump-env prod
```

### Étape 3: Configurer la base de données

```bash
# Créer une base de données
sudo -u postgres psql <<EOF
CREATE DATABASE sirius_solar;
CREATE USER app_user WITH PASSWORD 'VeryStrongPassword!';
ALTER ROLE app_user SET client_encoding TO 'utf8';
GRANT ALL PRIVILEGES ON DATABASE sirius_solar TO app_user;
\c sirius_solar
GRANT ALL PRIVILEGES ON SCHEMA public TO app_user;
EOF

# Mettre à jour .env avec les credentials
nano .env
```

### Étape 4: Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction  # Si nécessaire
```

### Étape 5: Compiler les assets

```bash
php bin/console asset-map:compile
chmod -R 755 public/assets
```

### Étape 6: Configurer Nginx

```nginx
server {
    listen 80;
    server_name sirius-solar.ch www.sirius-solar.ch;

    root /var/www/sirius-solar/public;
    index index.php;

    # Redirige HTTP vers HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sirius-solar.ch www.sirius-solar.ch;

    ssl_certificate /etc/letsencrypt/live/sirius-solar.ch/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sirius-solar.ch/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    root /var/www/sirius-solar/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    location ~* \.(?:css|gif|html|ico|jpe?g|js|png|svg|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss;
}
```

Sauvegardez dans `/etc/nginx/sites-available/sirius-solar` puis activez:

```bash
sudo ln -s /etc/nginx/sites-available/sirius-solar /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Étape 7: Configurer SSL avec Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d sirius-solar.ch -d www.sirius-solar.ch
```

---

## 🔒 Sécurité - Configuration Importante

### 1. Permissions des fichiers

```bash
cd /var/www/sirius-solar

# Propriétaire
sudo chown -R www-data:www-data .

# Permissions
sudo chmod -R 755 .
sudo chmod -R 775 var/
sudo chmod -R 775 public/uploads/  # Si vous avez des uploads
```

### 2. Variables sensibles

**JAMAIS** commit de `.env.local` ou `APP_SECRET` en production!

```bash
# Générer un APP_SECRET sécurisé
php bin/console secrets:generate-keys --env=prod
php bin/console secrets:set APP_SECRET --env=prod
```

### 3. Firewall

```bash
sudo ufw allow 22/tcp  # SSH
sudo ufw allow 80/tcp  # HTTP
sudo ufw allow 443/tcp # HTTPS
sudo ufw enable
```

---

## 📊 Monitoring et Logs

```bash
# Vérifier les erreurs PHP
tail -f var/log/prod.log

# Vérifier les erreurs Nginx
tail -f /var/log/nginx/error.log

# Vérifier les accès
tail -f /var/log/nginx/access.log
```

---

## 🔄 Mise à jour en Production

```bash
cd /var/www/sirius-solar

# Récupérer les changements
git pull origin main

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Compiler les assets
php bin/console asset-map:compile

# Vider le cache
php bin/console cache:clear --env=prod

# Recharger Nginx
sudo systemctl reload nginx
```

---

## 🆘 Dépannage

### Erreur: "Cannot write to logs directory"
```bash
sudo chown -R www-data:www-data var/log/
sudo chmod -R 775 var/log/
```

### Erreur: "Database connection failed"
```bash
# Vérifier les credentials
php bin/console dbal:run-sql "SELECT 1"

# Vérifier la connectivité
nc -v database-host 5432  # ou 3306 pour MySQL
```

### Performance lente
```bash
# Profiler l'application
php bin/console debug:profile

# Optimizer Composer
composer dumpautoload --optimize
```

---

## 📞 Support Hébergeurs Suisses

Hébergeurs recommandés pour la Suisse:
- **Exoscale** (https://exoscale.com/) - Infrastructure cloud
- **VSHN** (https://vshn.ch/) - Managed Kubernetes
- **Nine.ch** (https://nine.ch/) - Hosting traditionnel
- **Infomaniak** (https://www.infomaniak.com/) - Données en Suisse
- **cyon.ch** (https://www.cyon.ch/) - Web hosting Suisse

---

## ✅ Checklist de Déploiement

- [ ] Base de données créée et accessible
- [ ] APP_SECRET généré et sécurisé
- [ ] Migrations exécutées avec succès
- [ ] Assets compilés et optimisés
- [ ] SSL/TLS configuré (HTTPS)
- [ ] Logs configurés et surveillés
- [ ] Backup automatique en place
- [ ] Monitoring configuré
- [ ] Emails configurés et testés
- [ ] Variables d'environnement vérifiées

---

Pour toute question ou problème, consultez la [documentation Symfony](https://symfony.com/doc/current/deployment.html).
