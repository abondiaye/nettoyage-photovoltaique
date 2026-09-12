#!/bin/bash

# Sirius-Solar Backup Script
# Backups database and files to local storage and cloud

set -e

# Configuration
BACKUP_DIR="/var/backups/sirius-solar"
DB_NAME="sirius_solar"
DB_USER="app"
DB_HOST="localhost"
RETENTION_DAYS=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.sql.gz"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Functions
log_info() { echo -e "${GREEN}✓${NC} $1"; }
log_warn() { echo -e "${YELLOW}⚠${NC} $1"; }
log_error() { echo -e "${RED}✗${NC} $1"; exit 1; }

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

log_info "Starting backup..."

# 1. Database Backup
log_info "Backing up PostgreSQL database..."
pg_dump -U "$DB_USER" -h "$DB_HOST" "$DB_NAME" | gzip > "$BACKUP_FILE"
chmod 600 "$BACKUP_FILE"
log_info "Database backup created: $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"

# 2. Files Backup (public uploads)
FILES_BACKUP="$BACKUP_DIR/files_$TIMESTAMP.tar.gz"
if [ -d "/var/www/sirius-solar/public/uploads" ]; then
    log_info "Backing up uploaded files..."
    tar czf "$FILES_BACKUP" -C /var/www/sirius-solar/public uploads/ 2>/dev/null || true
    log_info "Files backup created: $FILES_BACKUP ($(du -h "$FILES_BACKUP" 2>/dev/null | cut -f1))"
fi

# 3. Configuration Backup
CONFIG_BACKUP="$BACKUP_DIR/config_$TIMESTAMP.tar.gz"
log_info "Backing up configuration..."
tar czf "$CONFIG_BACKUP" -C /var/www/sirius-solar config/ .env .env.local 2>/dev/null || true
log_info "Config backup created"

# 4. Upload to S3/Cloud Storage (Optional)
if command -v aws &> /dev/null && [ ! -z "$AWS_S3_BUCKET" ]; then
    log_info "Uploading to AWS S3..."
    aws s3 cp "$BACKUP_FILE" "s3://$AWS_S3_BUCKET/backups/database/"
    aws s3 cp "$FILES_BACKUP" "s3://$AWS_S3_BUCKET/backups/files/" 2>/dev/null || true
    log_info "Uploaded to S3 successfully"
fi

# 5. Upload to Backblaze B2 (Alternative)
if command -v b2 &> /dev/null && [ ! -z "$B2_BUCKET" ]; then
    log_info "Uploading to Backblaze B2..."
    b2 upload-file "$B2_BUCKET" "$BACKUP_FILE" "backups/database/"
    log_info "Uploaded to B2 successfully"
fi

# 6. Cleanup old backups (keep last 30 days)
log_info "Cleaning up old backups..."
find "$BACKUP_DIR" -type f -mtime +$RETENTION_DAYS -delete
log_info "Cleanup complete"

# 7. Statistics
log_info "Backup Statistics:"
echo "  Total backups: $(ls -1 "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | wc -l)"
echo "  Total size: $(du -sh "$BACKUP_DIR" | cut -f1)"
echo "  Oldest backup: $(ls -t "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | tail -1 | xargs -I {} basename {})"

# 8. Verification
log_info "Verifying backup integrity..."
if gzip -t "$BACKUP_FILE" 2>/dev/null; then
    log_info "✅ Backup verification passed!"
else
    log_error "Backup verification failed!"
fi

# 9. Send notification
if [ ! -z "$SLACK_WEBHOOK" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    curl -X POST "$SLACK_WEBHOOK" \
        -H 'Content-Type: application/json' \
        -d "{
            \"text\": \"✅ Sirius-Solar Backup Complete\",
            \"blocks\": [
                {
                    \"type\": \"section\",
                    \"text\": {
                        \"type\": \"mrkdwn\",
                        \"text\": \"*Database Backup*\nSize: $BACKUP_SIZE\nTime: $(date)\nStatus: Success\"
                    }
                }
            ]
        }" 2>/dev/null || true
fi

log_info "Backup completed successfully!"
