# 🧪 Local Testing Guide - Sirius-Solar

Complete guide for testing the application locally before deployment.

## 📋 Prerequisites

- Docker & Docker Compose
- Git
- At least 2GB free RAM
- 5GB free disk space

## 🚀 Quick Start (5 minutes)

### Option 1: Using Docker Compose (Easiest)

```bash
# 1. Clone the repository
git clone https://your-repo-url.git sirius-solar
cd sirius-solar

# 2. Create .env.local for development
cp .env.example .env.local

# 3. Start containers
docker-compose up -d

# 4. Install dependencies
docker-compose exec web composer install

# 5. Setup database
docker-compose exec web php bin/console doctrine:migrations:migrate

# 6. Compile assets
docker-compose exec web php bin/console asset-map:compile

# 7. Visit the app
open http://localhost:8000
```

### Option 2: Manual Setup (Development)

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file
cp .env.example .env.local

# 3. Start PHP built-in server
symfony server:start

# 4. In another terminal, start the database
docker run -d \
  --name sirius-postgres \
  -e POSTGRES_DB=sirius_solar \
  -e POSTGRES_USER=app \
  -e POSTGRES_PASSWORD=password \
  -p 5432:5432 \
  postgres:16-alpine

# 5. Setup database
php bin/console doctrine:migrations:migrate

# 6. Visit http://127.0.0.1:8000
```

---

## 🧪 Running Tests

### Unit & Functional Tests

```bash
# Run all tests
php bin/phpunit

# Run specific test file
php bin/phpunit tests/Controller/HomeControllerTest.php

# Run with coverage report
php bin/phpunit --coverage-html=coverage/

# Watch mode (auto-run on file changes)
php bin/phpunit --watch
```

### Code Quality Checks

```bash
# PHP-CS-Fixer (Code style)
php vendor/bin/php-cs-fixer fix src/ --dry-run --diff

# PHPStan (Static analysis)
php vendor/bin/phpstan analyse src/

# Psalm (Type checking)
php vendor/bin/psalm

# Security check
symfony security:check
```

### Performance Testing

```bash
# Lint configuration
php bin/console lint:container

# Lint templates
php bin/console lint:twig templates/

# Lint YAML
php bin/console lint:yaml config/ translations/

# Compile assets for production
php bin/console asset-map:compile --env=prod

# Check Symfony best practices
php bin/console debug:config
```

---

## 🔄 Docker Compose Commands

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f web

# View specific service logs
docker-compose logs -f postgres

# Execute command in container
docker-compose exec web php bin/console cache:clear

# Access web container shell
docker-compose exec web /bin/sh

# Rebuild containers
docker-compose up -d --build
```

### Database Management

```bash
# Connect to PostgreSQL
docker-compose exec postgres psql -U app -d sirius_solar

# Create a backup
docker-compose exec postgres pg_dump -U app sirius_solar > backup.sql

# Restore from backup
cat backup.sql | docker-compose exec -T postgres psql -U app -d sirius_solar

# Reset database (WARNING: deletes data!)
docker-compose exec postgres psql -U app -d sirius_solar -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
```

---

## 📝 Common Workflows

### 1. Adding a New Migration

```bash
# Create migration
php bin/console make:migration

# Review the migration
cat migrations/Version*.php

# Run migration
php bin/console doctrine:migrations:migrate

# Revert last migration
php bin/console doctrine:migrations:migrate prev
```

### 2. Creating an Entity

```bash
# Generate entity with maker bundle
php bin/console make:entity User

# Generate migration for new entity
php bin/console make:migration

# Run migration
php bin/console doctrine:migrations:migrate

# Verify database schema
php bin/console doctrine:schema:validate
```

### 3. Debugging Issues

```bash
# Enable debug mode
export APP_ENV=dev
export APP_DEBUG=1

# Check logs
tail -f var/log/dev.log

# Use Symfony profiler/debugger
open http://localhost:8000/_profiler

# Database query analysis
php bin/console doctrine:query:sql "SELECT * FROM users LIMIT 5"

# Clear cache if stuck
php bin/console cache:clear
```

### 4. Working with Assets

```bash
# Watch for asset changes (hot reload)
npm run dev

# Build assets for production
npm run build

# Compile asset map
php bin/console asset-map:compile

# Verify assets are loaded correctly
curl -I http://localhost:8000/assets/app.css
```

---

## 🌐 Testing in Different Browsers

### Local Tunnel (Test on Mobile/Other Devices)

```bash
# Using ngrok (free)
ngrok http 8000

# Using Symfony tunneling
symfony open:local -q

# Then visit the provided URL from any device
```

### Responsive Design Testing

```bash
# Chrome DevTools - F12
# Toggle device toolbar - Ctrl+Shift+M (Windows/Linux) or Cmd+Shift+M (Mac)

# Or use viewport emulation
curl -H "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)" \
  http://localhost:8000
```

---

## 🔒 Security Testing

```bash
# Check for security vulnerabilities in dependencies
symfony security:check

# Validate PHP security
php vendor/bin/psalm --show-info=false

# Check for SQL injection vulnerabilities
# (Manual code review - search for raw SQL queries)

# Test CSRF protection
# Check forms include {{ csrf_token('token_id') }}

# Test authentication
# Attempt login with invalid credentials
curl -X POST http://localhost:8000/login \
  -d "email=invalid@example.com&password=wrong"
```

---

## 📊 Performance Testing

### Load Testing (using Apache Bench)

```bash
# Install apache2-utils
sudo apt-get install apache2-utils

# Test home page (100 requests, 10 concurrent)
ab -n 100 -c 10 http://localhost:8000/

# Test with different concurrency levels
ab -n 1000 -c 50 http://localhost:8000/

# Output to file
ab -n 100 -c 10 -g results.tsv http://localhost:8000/
```

### Memory Profiling

```bash
# Enable Xdebug
export XDEBUG_MODE=profile

# Run your command
php bin/console cache:clear

# View profile results
ls -la /tmp/cachegrind.* 

# Analyze with kcachegrind (GUI)
kcachegrind /tmp/cachegrind.* 

# Or use qcachegrind (cross-platform)
```

---

## 🚀 Pre-Deployment Checklist

Before deploying to production, verify:

### Code Quality
- [ ] All tests pass: `php bin/phpunit`
- [ ] No PHP-CS-Fixer issues: `php vendor/bin/php-cs-fixer fix --dry-run`
- [ ] PHPStan passes: `php vendor/bin/phpstan analyse src/`
- [ ] Security check passes: `symfony security:check`

### Functionality
- [ ] Home page loads
- [ ] Login/Registration works
- [ ] Contact form sends emails
- [ ] All links are working
- [ ] Database queries are optimized (no N+1)

### Performance
- [ ] Page load time < 2 seconds
- [ ] Assets are minified and compressed
- [ ] Cache headers are proper

### Security
- [ ] No sensitive data in logs
- [ ] HTTPS is enforced
- [ ] CSRF tokens are in all forms
- [ ] SQL queries are parameterized
- [ ] User input is properly escaped

### Browser Compatibility
- [ ] Works on Chrome/Edge
- [ ] Works on Firefox
- [ ] Works on Safari
- [ ] Mobile responsive (iOS/Android)

---

## 📱 Testing Email Locally

```bash
# Using Mailtrap (free service)
# 1. Sign up at https://mailtrap.io
# 2. Copy SMTP credentials
# 3. Update .env.local

MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525

# 4. Test email sending
php bin/console make:command test:email
# Then run: php bin/console test:email test@example.com
```

---

## 🐛 Debugging with Symfony Debugger

```bash
# Install symfony var-dumper
composer require --dev symfony/var-dumper

# In your code:
dump($variable);
dd($variable); // dump and die

# View in browser
open http://localhost:8000/_profiler

# Check Database queries tab
# Check Timeline tab
# Check Memory usage tab
```

---

## 📋 GitLab CI/GitHub Actions Local Testing

```bash
# Test locally before pushing
# Install act (GitHub Actions locally): https://github.com/nektos/act

act -l  # List available workflows
act push  # Run on push event

# For GitLab CI, use:
gitlab-runner exec docker build_test
```

---

## 🆘 Troubleshooting

### Containers won't start
```bash
# Check Docker daemon
docker ps

# Check logs
docker-compose logs

# Rebuild containers
docker-compose down -v
docker-compose up -d --build
```

### Database connection error
```bash
# Test connection
docker-compose exec postgres psql -U app -d sirius_solar -c "SELECT 1"

# Check DATABASE_URL in .env.local
cat .env.local | grep DATABASE_URL

# Verify PostgreSQL is running
docker-compose ps
```

### Permission denied errors
```bash
# Fix file permissions
sudo chown -R $(whoami):$(whoami) .
chmod -R 755 .
chmod -R 775 var/
```

### Port already in use
```bash
# Change port in docker-compose.override.yaml
# Or kill process using port 8000
lsof -i :8000
kill -9 <PID>
```

---

## 📚 Additional Resources

- [Symfony Documentation](https://symfony.com/doc/)
- [Docker Documentation](https://docs.docker.com/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Doctrine ORM Guide](https://www.doctrine-project.org/projects/doctrine-orm/)

---

**Happy Testing! 🚀**
