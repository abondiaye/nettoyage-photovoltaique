#!/bin/bash

# Sirius-Solar Deployment Script
# Usage: ./deploy.sh [production|staging]

set -e

ENVIRONMENT=${1:-production}
BRANCH=${2:-main}

echo "🚀 Deploying Sirius-Solar ($ENVIRONMENT)..."

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Functions
log_info() { echo -e "${GREEN}✓${NC} $1"; }
log_warn() { echo -e "${YELLOW}⚠${NC} $1"; }
log_error() { echo -e "${RED}✗${NC} $1"; exit 1; }

# Verify environment file
if [ ! -f ".env.$ENVIRONMENT" ]; then
    log_error "Configuration file .env.$ENVIRONMENT not found!"
fi

log_info "Pulling latest code from $BRANCH..."
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

log_info "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

log_info "Dumping environment variables..."
composer dump-env $ENVIRONMENT

log_info "Running database migrations..."
php bin/console doctrine:migrations:migrate --env=$ENVIRONMENT --no-interaction

log_info "Clearing application cache..."
php bin/console cache:clear --env=$ENVIRONMENT

log_info "Warming up cache..."
php bin/console cache:warmup --env=$ENVIRONMENT

log_info "Compiling assets..."
php bin/console asset-map:compile --env=$ENVIRONMENT

if [ "$ENVIRONMENT" = "production" ]; then
    log_warn "Setting proper permissions..."
    chmod -R 755 .
    chmod -R 775 var/

    log_info "Restarting PHP-FPM..."
    sudo systemctl restart php8.2-fpm

    log_info "Reloading Nginx..."
    sudo systemctl reload nginx
fi

log_info "Verifying deployment..."
if curl -f http://localhost:8000/health > /dev/null 2>&1; then
    log_info "✅ Deployment successful!"
else
    log_warn "Could not verify health check"
fi

echo ""
echo -e "${GREEN}=============================${NC}"
echo -e "${GREEN}Deployment Complete! 🎉${NC}"
echo -e "${GREEN}=============================${NC}"
