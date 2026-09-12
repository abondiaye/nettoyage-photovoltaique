# 🔑 GitHub Secrets & CI/CD Configuration

Complete guide to setup GitHub for automated CI/CD.

---

## 1️⃣ Generate Required Credentials

### A. Docker Hub Token

```bash
# 1. Go to https://hub.docker.com/settings/security
# 2. Click "New Access Token"
# 3. Name: "sirius-solar-ci"
# 4. Copy the token (you'll need it below)
```

**Example:**
```
Token: dckr_pat_xyz123abc456def789
Username: yourdockerusername
```

### B. SSH Key for Server Deployment

```bash
# On your local machine, generate SSH key (if you don't have one)
ssh-keygen -t ed25519 -C "sirius-solar-deploy" -f ~/.ssh/sirius-deploy

# Output: 
# - Private key: ~/.ssh/sirius-deploy
# - Public key: ~/.ssh/sirius-deploy.pub

# Copy private key content
cat ~/.ssh/sirius-deploy
# (Copy the entire output, starts with -----BEGIN OPENSSH PRIVATE KEY-----)

# Add public key to server
ssh-copy-id -i ~/.ssh/sirius-deploy.pub deploy@your-server-ip

# Test SSH connection
ssh -i ~/.ssh/sirius-deploy deploy@your-server-ip
```

### C. Slack Webhook URL

```bash
# 1. Go to your Slack workspace settings
# 2. Create an app: https://api.slack.com/apps
# 3. Click "Create New App" → "From scratch"
# 4. Name: "Sirius-Solar"
# 5. Select your workspace
# 6. Go to "Incoming Webhooks"
# 7. Click "Add New Webhook to Workspace"
# 8. Select a channel (e.g., #deployments)
# 9. Copy the webhook URL

# Example webhook:
# https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXX

# Test it:
curl -X POST 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXX' \
  -H 'Content-Type: application/json' \
  -d '{
    "text": "🚀 Sirius-Solar test notification"
  }'
```

---

## 2️⃣ Configure GitHub Secrets

### Quick Setup (Automated)

If you have GitHub CLI installed:

```bash
# Install GitHub CLI (if needed)
brew install gh  # macOS
# or
sudo apt install gh  # Ubuntu/Debian

# Login to GitHub
gh auth login

# Navigate to your repo
cd /path/to/sirius-solar
git config user.email "your-email@example.com"
git config user.name "Your Name"

# Run setup script (we'll create this below)
bash github-secrets-setup.sh
```

### Manual Setup in GitHub UI

1. **Go to GitHub Repository**
   ```
   https://github.com/YOUR_USERNAME/sirius-solar
   Settings → Secrets and variables → Actions
   ```

2. **Add each Secret** (Click "New repository secret")

   | Name | Value | Description |
   |------|-------|-------------|
   | `DOCKER_USERNAME` | yourdockerusername | Your Docker Hub username |
   | `DOCKER_PASSWORD` | dckr_pat_xyz... | Your Docker Hub token |
   | `DEPLOY_HOST` | 1.2.3.4 | Your server IP address |
   | `DEPLOY_USER` | deploy | SSH user on server |
   | `DEPLOY_SSH_KEY` | (full private key) | SSH private key content |
   | `DEPLOY_PORT` | 22 | SSH port (usually 22) |
   | `SLACK_WEBHOOK` | https://hooks.slack.com/... | Slack webhook URL |

---

## 3️⃣ Automated Setup Script

Create `github-secrets-setup.sh`:

```bash
#!/bin/bash

# GitHub Secrets Setup Script
# Usage: bash github-secrets-setup.sh

set -e

echo "🔑 GitHub Secrets Configuration Setup"
echo "===================================="
echo ""

# Check if GitHub CLI is installed
if ! command -v gh &> /dev/null; then
    echo "❌ GitHub CLI not found. Install it:"
    echo "   brew install gh  # macOS"
    echo "   sudo apt install gh  # Ubuntu"
    exit 1
fi

# Check if logged in
if ! gh auth status &> /dev/null; then
    echo "❌ Not logged in to GitHub. Run: gh auth login"
    exit 1
fi

# Get current repo
REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner' 2>/dev/null)
if [ -z "$REPO" ]; then
    echo "❌ Not in a GitHub repository directory"
    exit 1
fi

echo "✓ Repository: $REPO"
echo ""

# Function to prompt and set secret
set_secret() {
    local secret_name=$1
    local secret_desc=$2
    local secret_value=""

    echo "📝 Enter $secret_desc"
    if [ "$secret_name" = "DEPLOY_SSH_KEY" ]; then
        echo "   (Paste your private SSH key content - multi-line)"
        echo "   (End input with Ctrl+D on Mac or Ctrl+Z+Enter on Windows)"
        secret_value=$(cat)
    else
        read -s -p "   Value: " secret_value
    fi
    echo ""

    if [ ! -z "$secret_value" ]; then
        gh secret set "$secret_name" --body "$secret_value" --repo "$REPO"
        echo "✅ $secret_name configured"
    else
        echo "⚠️  $secret_name skipped (empty value)"
    fi
    echo ""
}

# Set all secrets
set_secret "DOCKER_USERNAME" "Docker Hub Username"
set_secret "DOCKER_PASSWORD" "Docker Hub Token (from https://hub.docker.com/settings/security)"
set_secret "DEPLOY_HOST" "Server IP Address (e.g., 1.2.3.4)"
set_secret "DEPLOY_USER" "Deploy User (e.g., deploy)"
set_secret "DEPLOY_SSH_KEY" "SSH Private Key"
set_secret "DEPLOY_PORT" "SSH Port (usually 22)"
set_secret "SLACK_WEBHOOK" "Slack Webhook URL"

echo ""
echo "✅ Secrets Configuration Complete!"
echo ""
echo "Verify secrets:"
echo "  gh secret list --repo $REPO"
echo ""
echo "Next steps:"
echo "  1. Push to main branch"
echo "  2. Check GitHub Actions"
echo "  3. Monitor deployment"
