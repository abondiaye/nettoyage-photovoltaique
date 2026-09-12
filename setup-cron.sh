#!/bin/bash

# Setup Automated Backups and Monitoring
# Run this script as root: sudo ./setup-cron.sh

set -e

APP_DIR="/var/www/sirius-solar"
CRON_USER="www-data"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}✓${NC} $1"; }
log_warn() { echo -e "${YELLOW}⚠${NC} $1"; }
log_error() { echo -e "${RED}✗${NC} $1"; }

log_info "Setting up automated backups and monitoring..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    log_error "This script must be run as root (use: sudo ./setup-cron.sh)"
    exit 1
fi

# 1. Create backup directory
log_info "Creating backup directory..."
mkdir -p /var/backups/sirius-solar
chown $CRON_USER:$CRON_USER /var/backups/sirius-solar
chmod 755 /var/backups/sirius-solar

# 2. Create log directory
log_info "Creating log directory..."
mkdir -p /var/log/sirius-solar
chown $CRON_USER:$CRON_USER /var/log/sirius-solar
chmod 755 /var/log/sirius-solar

# 3. Make scripts executable
log_info "Making scripts executable..."
chmod +x "$APP_DIR/backup.sh"
chmod +x "$APP_DIR/monitoring.sh"
chmod +x "$APP_DIR/deploy.sh"

# 4. Setup PostgreSQL password file for automated backups
log_info "Setting up PostgreSQL credentials..."
cat > /home/$CRON_USER/.pgpass << EOF
localhost:5432:sirius_solar:app:password_here
EOF
chmod 600 /home/$CRON_USER/.pgpass
chown $CRON_USER:$CRON_USER /home/$CRON_USER/.pgpass

log_warn "⚠️  UPDATE THE PASSWORD IN /home/$CRON_USER/.pgpass!"

# 5. Create crontab for backups
log_info "Setting up cron jobs..."

# Remove existing cron entries if they exist
sudo -u $CRON_USER crontab -r 2>/dev/null || true

# Create new crontab
sudo -u $CRON_USER crontab - << 'EOF'
# Sirius-Solar Automated Tasks

# Daily backup at 2 AM
0 2 * * * cd /var/www/sirius-solar && bash backup.sh >> /var/log/sirius-solar/backup.log 2>&1

# Hourly monitoring check
0 * * * * cd /var/www/sirius-solar && bash monitoring.sh >> /var/log/sirius-solar/monitoring.log 2>&1

# Weekly maintenance at Sunday 3 AM
0 3 * * 0 cd /var/www/sirius-solar && php bin/console cache:clear --env=prod >> /var/log/sirius-solar/maintenance.log 2>&1

# Database optimization (weekly on Saturday)
0 4 * * 6 cd /var/www/sirius-solar && php bin/console doctrine:schema:validate >> /var/log/sirius-solar/maintenance.log 2>&1

# SSL certificate renewal check (weekly)
0 12 * * 1 sudo /usr/bin/certbot renew --quiet

# Log rotation check (daily)
0 1 * * * logrotate /etc/logrotate.d/sirius-solar
EOF

log_info "Cron jobs installed successfully!"

# 6. Create logrotate config
log_info "Setting up log rotation..."
cat > /etc/logrotate.d/sirius-solar << 'EOF'
/var/log/sirius-solar/*.log
{
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.2-fpm > /dev/null 2>&1 || true
    endscript
}
EOF

log_info "Log rotation configured"

# 7. Create environment file for cron jobs
log_info "Creating environment file for cron..."
cat > "$APP_DIR/.env.cron" << 'EOF'
# Cron environment variables
SLACK_WEBHOOK=''
AWS_S3_BUCKET=''
B2_BUCKET=''

# Add your cloud storage credentials here
EOF

log_warn "⚠️  Configure .env.cron with your cloud storage and Slack webhook details"

# 8. Display cron schedule
log_info "Current cron schedule:"
echo "---"
sudo -u $CRON_USER crontab -l
echo "---"

# 9. Test backup script
log_info "Testing backup script..."
if cd "$APP_DIR" && bash backup.sh; then
    log_info "✅ Backup script test successful!"
else
    log_warn "⚠️  Backup script test failed - check PostgreSQL credentials"
fi

# 10. Display next steps
echo ""
echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}Setup Complete! 🎉${NC}"
echo -e "${GREEN}=====================================${NC}"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Configure credentials in .env.cron:"
echo "   sudo nano $APP_DIR/.env.cron"
echo ""
echo "2. Test PostgreSQL backup:"
echo "   pg_dump -U app -h localhost sirius_solar | gzip > /tmp/test.sql.gz"
echo ""
echo "3. Verify cron jobs:"
echo "   sudo -u www-data crontab -l"
echo ""
echo "4. Monitor logs:"
echo "   tail -f /var/log/sirius-solar/*.log"
echo ""
echo "📊 Backup location: /var/backups/sirius-solar"
echo "📝 Log location: /var/log/sirius-solar"
echo ""
echo "💡 Tips:"
echo "  - Backups run daily at 2 AM"
echo "  - Monitoring checks run hourly"
echo "  - Logs are rotated daily (14-day retention)"
echo ""
