#!/bin/bash

# Sirius-Solar Monitoring Script
# Monitors system health and application status

# Configuration
APP_URL="https://sirius-solar.ch"
LOG_FILE="/var/log/sirius-monitoring.log"
SLACK_WEBHOOK="${SLACK_WEBHOOK:-}"
ALERT_THRESHOLD_CPU=80
ALERT_THRESHOLD_MEMORY=85
ALERT_THRESHOLD_DISK=90

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Functions
log_info() { echo "[$(date +'%Y-%m-%d %H:%M:%S')] ✓ $1" | tee -a "$LOG_FILE"; }
log_warn() { echo "[$(date +'%Y-%m-%d %H:%M:%S')] ⚠ $1" | tee -a "$LOG_FILE"; }
log_error() { echo "[$(date +'%Y-%m-%d %H:%M:%S')] ✗ $1" | tee -a "$LOG_FILE"; }

send_slack_alert() {
    if [ ! -z "$SLACK_WEBHOOK" ]; then
        curl -X POST "$SLACK_WEBHOOK" \
            -H 'Content-Type: application/json' \
            -d "{
                \"text\": \"🚨 Sirius-Solar Alert\",
                \"blocks\": [
                    {
                        \"type\": \"section\",
                        \"text\": {
                            \"type\": \"mrkdwn\",
                            \"text\": \"*Alert:* $1\n*Time:* $(date)\n*Host:* $(hostname)\"
                        }
                    }
                ]
            }" 2>/dev/null
    fi
}

# 1. Application Health Check
log_info "Checking application health..."
if curl -sf "$APP_URL/health" > /dev/null 2>&1; then
    log_info "Application health: ✅ OK"
else
    log_error "Application health: ❌ DOWN"
    send_slack_alert "Application is down! URL: $APP_URL"
fi

# 2. CPU Usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}' | cut -d. -f1)
log_info "CPU Usage: ${CPU_USAGE}%"
if [ "$CPU_USAGE" -gt "$ALERT_THRESHOLD_CPU" ]; then
    log_warn "⚠️ High CPU usage detected: ${CPU_USAGE}%"
    send_slack_alert "High CPU usage: ${CPU_USAGE}%"
fi

# 3. Memory Usage
MEM_USAGE=$(free | grep Mem | awk '{print int($3/$2 * 100)}')
log_info "Memory Usage: ${MEM_USAGE}%"
if [ "$MEM_USAGE" -gt "$ALERT_THRESHOLD_MEMORY" ]; then
    log_warn "⚠️ High memory usage detected: ${MEM_USAGE}%"
    send_slack_alert "High memory usage: ${MEM_USAGE}%"
fi

# 4. Disk Usage
DISK_USAGE=$(df /var/www/sirius-solar | tail -1 | awk '{print $5}' | sed 's/%//')
log_info "Disk Usage: ${DISK_USAGE}%"
if [ "$DISK_USAGE" -gt "$ALERT_THRESHOLD_DISK" ]; then
    log_error "⚠️ High disk usage detected: ${DISK_USAGE}%"
    send_slack_alert "High disk usage: ${DISK_USAGE}%"
fi

# 5. Database Connection
log_info "Checking database connectivity..."
if psql -U app -d sirius_solar -h localhost -c "SELECT 1" > /dev/null 2>&1; then
    log_info "Database: ✅ Connected"
else
    log_error "Database: ❌ Connection failed"
    send_slack_alert "Database connection failed"
fi

# 6. Recent Errors
ERROR_COUNT=$(grep -c "ERROR" /var/www/sirius-solar/var/log/prod.log 2>/dev/null || echo 0)
if [ "$ERROR_COUNT" -gt 0 ]; then
    log_warn "Found $ERROR_COUNT errors in application logs"
fi

# 7. Disk Space in /var/backups
BACKUP_SIZE=$(du -sh /var/backups/sirius-solar 2>/dev/null | cut -f1)
log_info "Backup size: $BACKUP_SIZE"

# 8. SSL Certificate Expiry
CERT_EXPIRY=$(openssl x509 -in /etc/letsencrypt/live/sirius-solar.ch/fullchain.pem -noout -enddate 2>/dev/null | cut -d= -f2)
DAYS_UNTIL_EXPIRY=$(( ($(date -d "$CERT_EXPIRY" +%s) - $(date +%s)) / 86400 ))
log_info "SSL Certificate expires in $DAYS_UNTIL_EXPIRY days"

if [ "$DAYS_UNTIL_EXPIRY" -lt 30 ]; then
    log_warn "⚠️ SSL certificate expiring soon: $DAYS_UNTIL_EXPIRY days"
    send_slack_alert "SSL certificate expiring in $DAYS_UNTIL_EXPIRY days"
fi

# 9. Services Status
log_info "Checking services..."
for service in php8.2-fpm nginx postgresql docker; do
    if systemctl is-active --quiet "$service" 2>/dev/null; then
        log_info "  $service: ✅ Running"
    else
        log_warn "  $service: ⚠️ Not running or not available"
    fi
done

# 10. Uptime
UPTIME=$(uptime -p)
log_info "System uptime: $UPTIME"

log_info "Monitoring check completed"
echo ""
