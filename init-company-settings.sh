#!/bin/bash

# Initialize Company Settings and Admin Account
# Run this script after first deployment

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}✓${NC} $1"; }
log_warn() { echo -e "${YELLOW}⚠${NC} $1"; }
log_error() { echo -e "${RED}✗${NC} $1"; exit 1; }

echo ""
echo "════════════════════════════════════════════"
echo "   Sirius-Solar - Initialisation Admin"
echo "════════════════════════════════════════════"
echo ""

# Check if in correct directory
if [ ! -f "bin/console" ]; then
    log_error "Veuillez exécuter ce script depuis la racine du projet Symfony"
fi

log_info "Initialisation des paramètres de l'entreprise"
echo ""

# Create or update admin user
log_info "Création/mise à jour du compte administrateur..."
echo ""

read -p "Email administrateur (défaut: admin@sirius-solar.ch): " admin_email
admin_email=${admin_email:-admin@sirius-solar.ch}

read -sp "Nouveau mot de passe administrateur: " admin_password
echo ""

read -sp "Confirmer le mot de passe: " admin_password_confirm
echo ""

if [ "$admin_password" != "$admin_password_confirm" ]; then
    log_error "Les mots de passe ne correspondent pas!"
fi

# Create admin user via Symfony console
log_info "Création de l'utilisateur administrateur..."
php bin/console app:create-admin-user "$admin_email" "$admin_password" || \
php bin/console fos:user:create --super-admin "$admin_email" "$admin_email" "$admin_password" --no-interaction || true

log_info "Utilisateur administrateur créé/mis à jour!"

echo ""
echo "════════════════════════════════════════════"
echo "   Informations de l'Entreprise"
echo "════════════════════════════════════════════"
echo ""

# Update company information
log_info "Configuration des informations de l'entreprise..."

cat > .env.local << EOF
# Company Information
COMPANY_NAME="Sirius-Solar"
COMPANY_EMAIL="Info@Sirius-solar-services.ch"
COMPANY_PHONE="+41 XX XXX XX XX"
COMPANY_ADDRESS="Route des dragons 7"
COMPANY_CITY="Cheseaux-sur-Lausanne"
COMPANY_POSTAL_CODE="1027"
COMPANY_COUNTRY="Switzerland"

# Admin Information
ADMIN_EMAIL="$admin_email"
EOF

log_info "Informations enregistrées dans .env.local"

echo ""
echo "════════════════════════════════════════════"
echo "   Vérification de la Configuration"
echo "════════════════════════════════════════════"
echo ""

# Verify configuration
log_info "Vérification de la configuration Symfony..."
php bin/console lint:container

log_info "Vérification des templates Twig..."
php bin/console lint:twig templates/

log_info "Vérification des fichiers YAML..."
php bin/console lint:yaml config/

echo ""
echo "════════════════════════════════════════════"
echo "   ✅ Initialisation Complète!"
echo "════════════════════════════════════════════"
echo ""
echo "Informations Enregistrées:"
echo "  📧 Email Admin: $admin_email"
echo "  🏢 Email Entreprise: Info@Sirius-solar-services.ch"
echo "  📍 Adresse: Route des dragons 7, 1027 Cheseaux-sur-Lausanne"
echo ""
echo "⚠️  IMPORTANT:"
echo "  1. L'administrateur DOIT changer son mot de passe à la première connexion"
echo "  2. Mettre à jour le numéro de téléphone dans .env.local"
echo "  3. Ajouter les comptes de médias sociaux si nécessaire"
echo ""
echo "✅ Vous pouvez maintenant accéder à l'administration!"
echo ""
