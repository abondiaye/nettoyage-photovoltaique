# 🎯 Complete Integration Guide - Sirius-Solar

End-to-end guide to integrate everything and deploy.

---

## 📅 Timeline & Checklist

### **Week 1: Preparation**

#### Day 1-2: Generate Credentials
- [ ] Create Docker Hub token
- [ ] Generate SSH key for server
- [ ] Create Slack workspace webhook
- [ ] Note all credentials somewhere safe

**Time:** 30 minutes

#### Day 3: Local Testing
- [ ] Install Act
- [ ] Create `.secrets` file locally
- [ ] Run test workflow: `act -j tests --secret-file .secrets`
- [ ] Verify all tests pass

**Time:** 1-2 hours

#### Day 4-5: GitHub Setup
- [ ] Run `bash github-secrets-setup.sh`
- [ ] Verify secrets in GitHub UI
- [ ] Make a test commit to main
- [ ] Watch CI/CD run

**Time:** 30 minutes

---

### **Week 2: Server Preparation**

#### Day 6-7: Server Setup
- [ ] SSH into your server
- [ ] Install Docker & Docker Compose
- [ ] Clone repository
- [ ] Create `.env.production`
- [ ] Run `sudo ./setup-cron.sh`

**Time:** 2-3 hours

#### Day 8: Database Backup Test
- [ ] Run manual backup: `bash backup.sh`
- [ ] Verify backup created
- [ ] Test backup restore
- [ ] Verify monitoring works

**Time:** 1 hour

#### Day 9-10: Pre-deployment Checks
- [ ] Run deployment guide checklist
- [ ] Test all endpoints locally
- [ ] Verify SSL certificate
- [ ] Test email sending

**Time:** 2 hours

---

### **Week 3: Deployment**

#### Day 11: First Deployment
- [ ] Commit all changes
- [ ] Push to main branch
- [ ] Watch GitHub Actions deploy
- [ ] Verify app is live
- [ ] Run tests in production

**Time:** 1 hour

#### Day 12: Monitoring Setup
- [ ] Configure Prometheus (optional)
- [ ] Setup Grafana dashboards
- [ ] Configure Uptimerobot
- [ ] Test Slack alerts

**Time:** 2 hours

#### Day 13-14: Documentation & Training
- [ ] Document deployment process
- [ ] Train team on monitoring
- [ ] Setup on-call rotation
- [ ] Verify logs are working

**Time:** 3 hours

---

## 🚀 Step-by-Step Integration

### **Step 1: Initial Preparation (Local)**

```bash
# 1.1 Clone and setup
cd ~
git clone https://your-repo-url.git sirius-solar
cd sirius-solar

# 1.2 Copy environment template
cp .env.example .env.local

# 1.3 Start local dev environment
docker-compose up -d

# 1.4 Install dependencies
docker-compose exec web composer install

# 1.5 Setup database
docker-compose exec web php bin/console doctrine:migrations:migrate

# 1.6 Verify it works
open http://localhost:8000
```

**Verify:** App loads on localhost:8000

---

### **Step 2: GitHub Secrets Setup**

```bash
# 2.1 Install GitHub CLI
brew install gh  # macOS

# 2.2 Login to GitHub
gh auth login

# 2.3 Navigate to repo
cd /path/to/sirius-solar

# 2.4 Run automated setup
bash github-secrets-setup.sh

# 2.5 Verify secrets created
gh secret list
```

**Verify:** All 7 secrets appear in `gh secret list`

---

### **Step 3: Local Pipeline Testing**

```bash
# 3.1 Install Act
brew install act

# 3.2 Create local secrets file
cat > .secrets << 'EOF'
DOCKER_USERNAME=testuser
DOCKER_PASSWORD=testpass
DEPLOY_HOST=localhost
DEPLOY_USER=test
DEPLOY_PORT=22
SLACK_WEBHOOK=https://hooks.slack.com/test
EOF

chmod 600 .secrets

# 3.3 Run test workflow
act -j tests --secret-file .secrets

# 3.4 Watch output
# Should see ✓ for each step
```

**Verify:** All test steps pass ✓

---

### **Step 4: Server Preparation**

```bash
# 4.1 Connect to server
ssh deploy@your-server-ip

# 4.2 Create app directory
sudo mkdir -p /var/www/sirius-solar
sudo chown deploy:deploy /var/www/sirius-solar

# 4.3 Clone repo
cd /var/www/sirius-solar
git clone https://your-repo-url.git .

# 4.4 Create production environment
cp .env.example .env.production

# 4.5 Edit configuration
nano .env.production
# Update: APP_SECRET, DATABASE_URL, MAILER_DSN, etc.

# 4.6 Setup automated tasks
sudo ./setup-cron.sh
```

**Verify:** Cron jobs appear in `sudo -u www-data crontab -l`

---

### **Step 5: First Deployment**

```bash
# 5.1 Make a test commit
cd /path/to/sirius-solar
git add .
git commit -m "Deploy: Initial production setup"

# 5.2 Push to main (triggers CI/CD)
git push origin main

# 5.3 Watch deployment
gh run watch

# 5.4 Check server
ssh deploy@your-server-ip
curl http://localhost:8000/health

# 5.5 Test with curl
curl https://sirius-solar.ch/health
```

**Verify:** You see "healthy" response

---

### **Step 6: Verify Application**

```bash
# 6.1 Check logs
ssh deploy@your-server-ip
tail -f /var/www/sirius-solar/var/log/prod.log

# 6.2 Test endpoints
curl https://sirius-solar.ch/
curl https://sirius-solar.ch/login
curl https://sirius-solar.ch/health

# 6.3 Check services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm

# 6.4 Verify backups ran
ls -lh /var/backups/sirius-solar/
```

**Verify:** All endpoints return 200, services running, backups exist

---

### **Step 7: Setup Monitoring**

```bash
# 7.1 Install Prometheus & Grafana
cd /opt/monitoring
docker-compose up -d

# 7.2 Setup dashboards
open http://localhost:3000
# Login: admin / admin123

# 7.3 Import dashboard 1860 (Node Exporter)
# Add data source: Prometheus (localhost:9090)

# 7.4 Setup Slack alerts
# Update your Slack webhook in monitoring.sh
```

**Verify:** Grafana dashboards show data

---

### **Step 8: Testing Full Workflow**

```bash
# 8.1 Make a code change
echo "# Test comment" >> README.md
git add README.md
git commit -m "Test: Verify CI/CD workflow"

# 8.2 Push to main
git push origin main

# 8.3 Watch Actions
gh run watch

# 8.4 Verify deployment
# Check Slack for notifications
# Verify app still works

# 8.5 Revert test change
git revert HEAD
git push origin main
```

**Verify:** CI/CD ran, deployment succeeded, Slack notified

---

## 🎯 Deployment Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Development                          │
│  Local machine → GitHub → GitHub Actions               │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│              Continuous Integration                      │
│  - Run tests (PHP, Database, Linting)                  │
│  - Code quality checks (PHPStan, PHP-CS-Fixer)        │
│  - Build Docker image                                  │
│  - Push to Docker Hub                                  │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│              Continuous Deployment                      │
│  - SSH to production server                            │
│  - Pull latest code                                     │
│  - Backup database                                      │
│  - Run migrations                                       │
│  - Restart services                                     │
│  - Notify Slack                                         │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│              Production Environment                     │
│  - Nginx (reverse proxy + SSL)                         │
│  - PHP-FPM (application)                               │
│  - PostgreSQL (database)                               │
│  - Automated backups (daily)                           │
│  - Automated monitoring (hourly)                       │
│  - Automated log rotation                              │
└─────────────────────────────────────────────────────────┘
```

---

## 📊 Success Criteria

### ✅ All deployments should have:
- [ ] All tests passed (green checkmarks in GitHub Actions)
- [ ] Code quality checks passed
- [ ] Docker image built successfully
- [ ] Application deployed to server
- [ ] Slack notification sent
- [ ] Application health check: 200 OK
- [ ] SSL certificate valid
- [ ] Database migrations completed
- [ ] Backup completed

### ✅ Production should have:
- [ ] App accessible on https://sirius-solar.ch
- [ ] /health endpoint returns 200
- [ ] Login page works
- [ ] All pages load
- [ ] Logs in `/var/log/sirius-solar`
- [ ] Backups in `/var/backups/sirius-solar`
- [ ] Cron jobs running (check with `crontab -l`)

---

## 🆘 Troubleshooting Integration

### CI/CD not triggering
```bash
# Check GitHub Actions
open https://github.com/YOUR_USERNAME/sirius-solar/actions

# Check secrets are set
gh secret list

# Check main branch protection (if set)
# Settings → Branches → Branch protection rules
```

### Deployment fails
```bash
# Check deployment logs
gh run view <RUN_ID> --log

# SSH to server and check logs
ssh deploy@server-ip
tail -f /var/www/sirius-solar/var/log/prod.log

# Check services
sudo systemctl status php8.2-fpm nginx postgresql
```

### App doesn't load after deploy
```bash
# Check permissions
sudo chown -R www-data:www-data /var/www/sirius-solar
sudo chmod -R 755 /var/www/sirius-solar
sudo chmod -R 775 /var/www/sirius-solar/var

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

# Check error logs
sudo tail -f /var/log/nginx/error.log
tail -f /var/www/sirius-solar/var/log/prod.log
```

### Backup not running
```bash
# Check cron
sudo -u www-data crontab -l

# Run backup manually
cd /var/www/sirius-solar && bash backup.sh

# Check logs
tail -f /var/log/sirius-solar/backup.log
```

---

## 📝 Documentation to Maintain

- [ ] Keep DEPLOYMENT_GUIDE.md updated
- [ ] Keep LOCAL_TESTING_GUIDE.md current
- [ ] Update MONITORING_SETUP.md with new alerts
- [ ] Document any custom configurations
- [ ] Keep README.md with setup instructions
- [ ] Document team runbooks for incidents

---

## 🎓 Team Training Checklist

- [ ] Show team how to check GitHub Actions
- [ ] Show how to read Slack deployment notifications
- [ ] Explain monitoring dashboard (Grafana)
- [ ] Show how to check logs
- [ ] Explain backup/restore procedure
- [ ] Document on-call procedures
- [ ] Create incident response playbook

---

## 🏆 You've Done It!

Once all steps are complete:

```bash
echo "🎉 Sirius-Solar is now in production!"
echo "✅ CI/CD is automated"
echo "✅ Backups are running"
echo "✅ Monitoring is active"
echo "✅ Alerts are configured"
echo ""
echo "Next deployments will happen automatically on push to main!"
```

---

## 📞 Support Resources

- GitHub Actions Docs: https://docs.github.com/en/actions
- Docker Docs: https://docs.docker.com/
- Symfony Docs: https://symfony.com/doc/
- PostgreSQL Docs: https://www.postgresql.org/docs/
- Nginx Docs: https://nginx.org/en/docs/

---

**Congratulations on your production deployment! 🚀**
