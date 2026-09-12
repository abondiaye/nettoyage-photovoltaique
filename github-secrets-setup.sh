#!/bin/bash

# GitHub Secrets Automated Setup
# Usage: bash github-secrets-setup.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${GREEN}✓${NC} $1"; }
log_warn() { echo -e "${YELLOW}⚠${NC} $1"; }
log_error() { echo -e "${RED}✗${NC} $1"; exit 1; }
log_header() { echo -e "${BLUE}══════════════════════════════════${NC}\n${BLUE}$1${NC}\n${BLUE}══════════════════════════════════${NC}"; }

# Welcome message
clear
log_header "🔑 GitHub Secrets Setup - Sirius-Solar"

# Check dependencies
log_info "Checking dependencies..."

if ! command -v gh &> /dev/null; then
    log_error "GitHub CLI not installed. Install it first:"
    echo "  macOS: brew install gh"
    echo "  Linux: sudo apt install gh"
    echo "  Or: https://cli.github.com/"
fi

if ! gh auth status &> /dev/null; then
    log_error "Not logged in to GitHub. Run: gh auth login"
fi

# Get repository info
REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner' 2>/dev/null || true)
if [ -z "$REPO" ]; then
    log_error "Not in a GitHub repository. Run this script from your repo root."
fi

log_info "Repository: $REPO"
echo ""

# Create temporary files for sensitive data
SECRETS_FILE="/tmp/sirius-secrets-$$.env"
trap "rm -f $SECRETS_FILE" EXIT

log_header "📋 Secrets to Configure"

echo "The following secrets will be configured:"
echo ""
echo "1. DOCKER_USERNAME      - Docker Hub username"
echo "2. DOCKER_PASSWORD      - Docker Hub access token"
echo "3. DEPLOY_HOST          - Your server IP"
echo "4. DEPLOY_USER          - Server SSH user"
echo "5. DEPLOY_SSH_KEY       - Server SSH private key"
echo "6. DEPLOY_PORT          - SSH port (default 22)"
echo "7. SLACK_WEBHOOK        - Slack notification webhook"
echo ""

read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    log_warn "Setup cancelled"
    exit 0
fi

echo ""
log_header "🔧 Step 1: Docker Hub Credentials"

echo "Get your Docker Hub token:"
echo "1. Go to https://hub.docker.com/settings/security"
echo "2. Click 'New Access Token'"
echo "3. Name it 'sirius-solar-ci'"
echo "4. Copy the token"
echo ""

read -p "Docker Hub Username: " docker_user
if [ -z "$docker_user" ]; then
    log_error "Docker username cannot be empty"
fi

read -sp "Docker Hub Token (won't be shown): " docker_token
echo ""
if [ -z "$docker_token" ]; then
    log_error "Docker token cannot be empty"
fi

# Test Docker credentials
log_info "Testing Docker Hub credentials..."
if echo "$docker_token" | docker login -u "$docker_user" --password-stdin > /dev/null 2>&1; then
    log_info "✅ Docker Hub credentials valid"
else
    log_warn "⚠️  Docker credentials might be invalid (check later)"
fi

echo ""
log_header "🔧 Step 2: Server SSH Credentials"

echo "You need SSH access to your server."
echo ""

read -p "Server IP Address (e.g., 1.2.3.4): " server_ip
if [ -z "$server_ip" ]; then
    log_error "Server IP cannot be empty"
fi

read -p "SSH Username (e.g., deploy, ubuntu, root): " ssh_user
if [ -z "$ssh_user" ]; then
    log_error "SSH user cannot be empty"
fi

read -p "SSH Port (default 22): " ssh_port
ssh_port=${ssh_port:-22}

echo ""
echo "Paste your SSH private key (the entire content, including BEGIN/END lines)"
echo "You can get it from: cat ~/.ssh/id_rsa  or  cat ~/.ssh/id_ed25519"
echo "Press Ctrl+D (Mac) or Ctrl+Z then Enter (Windows) when done:"
echo ""

ssh_key=$(cat)
if [ -z "$ssh_key" ]; then
    log_error "SSH key cannot be empty"
fi

# Test SSH connection
log_info "Testing SSH connection..."
if echo "$ssh_key" > /tmp/test_key && chmod 600 /tmp/test_key && \
   ssh -i /tmp/test_key -o StrictHostKeyChecking=no -o ConnectTimeout=5 \
   "$ssh_user@$server_ip" "echo 'SSH test successful'" > /dev/null 2>&1; then
    log_info "✅ SSH connection successful"
    rm -f /tmp/test_key
else
    log_warn "⚠️  Could not verify SSH connection (ensure firewall allows access)"
    rm -f /tmp/test_key
fi

echo ""
log_header "🔧 Step 3: Slack Notification Webhook"

echo "Get your Slack webhook:"
echo "1. Go to https://api.slack.com/apps"
echo "2. Create New App → From scratch"
echo "3. Name: 'Sirius-Solar'"
echo "4. Go to 'Incoming Webhooks'"
echo "5. 'Add New Webhook to Workspace'"
echo "6. Select channel and authorize"
echo "7. Copy the webhook URL"
echo ""

read -p "Slack Webhook URL (or press Enter to skip): " slack_webhook

if [ ! -z "$slack_webhook" ]; then
    # Test Slack webhook
    log_info "Testing Slack webhook..."
    if curl -s -X POST "$slack_webhook" \
        -H 'Content-Type: application/json' \
        -d '{"text":"✅ Sirius-Solar webhook test successful"}' > /dev/null; then
        log_info "✅ Slack webhook working"
    else
        log_warn "⚠️  Could not verify Slack webhook"
    fi
fi

echo ""
log_header "📤 Uploading Secrets to GitHub"

# Array of secrets to upload
declare -a secrets=(
    "DOCKER_USERNAME:$docker_user"
    "DOCKER_PASSWORD:$docker_token"
    "DEPLOY_HOST:$server_ip"
    "DEPLOY_USER:$ssh_user"
    "DEPLOY_SSH_KEY:$ssh_key"
    "DEPLOY_PORT:$ssh_port"
)

if [ ! -z "$slack_webhook" ]; then
    secrets+=("SLACK_WEBHOOK:$slack_webhook")
fi

# Upload secrets
echo "Uploading secrets to GitHub..."
echo ""

for secret in "${secrets[@]}"; do
    IFS=':' read -r name value <<< "$secret"

    # Skip empty values
    if [ -z "$value" ]; then
        log_warn "Skipping $name (empty)"
        continue
    fi

    # Upload to GitHub
    if gh secret set "$name" --body "$value" --repo "$REPO" 2>/dev/null; then
        echo "  ✓ $name"
    else
        log_warn "Failed to set $name"
    fi
done

echo ""
log_header "✅ Setup Complete!"

echo "Your secrets have been configured in GitHub."
echo ""
echo "Verify secrets:"
echo "  gh secret list --repo $REPO"
echo ""
echo "Next steps:"
echo "  1. Update server with: ssh $ssh_user@$server_ip"
echo "  2. cd /var/www/sirius-solar"
echo "  3. git pull origin main"
echo "  4. Check GitHub Actions: https://github.com/$REPO/actions"
echo ""
echo "Deployment will be triggered automatically on next git push to main!"
echo ""

log_info "Ready for CI/CD! 🚀"
