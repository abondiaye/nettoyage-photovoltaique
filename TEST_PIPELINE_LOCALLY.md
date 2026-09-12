# 🧪 Test GitHub Actions Locally with Act

Run your CI/CD pipeline locally before pushing to GitHub.

---

## 1️⃣ Install Act

### macOS
```bash
brew install act
```

### Ubuntu/Debian
```bash
bash <(curl https://raw.githubusercontent.com/nektos/act/master/install.sh)
```

### Or download directly
```bash
# Go to https://github.com/nektos/act/releases
# Download the appropriate version for your OS
```

### Verify installation
```bash
act --version
```

---

## 2️⃣ Create Local Secrets File

Create `.secrets` file in your repo root:

```bash
cat > .secrets << 'EOF'
DOCKER_USERNAME=your-docker-username
DOCKER_PASSWORD=your-docker-token
DEPLOY_HOST=1.2.3.4
DEPLOY_USER=deploy
DEPLOY_PORT=22
SLACK_WEBHOOK=https://hooks.slack.com/services/...
EOF

chmod 600 .secrets
echo ".secrets" >> .gitignore
```

**Never commit this file!**

---

## 3️⃣ List Available Workflows

```bash
# See all workflow files
act -l

# Example output:
# ID          Workflow            Event
# build_test  Tests & Code Quality push
# deploy      Deploy to Production push
# tests       Tests & Code Quality pull_request
```

---

## 4️⃣ Run Workflows Locally

### Run all workflows
```bash
act --secret-file .secrets
```

### Run specific workflow
```bash
act -j build_test --secret-file .secrets
```

### Run on specific branch
```bash
act -b main --secret-file .secrets
```

### Run with verbose output
```bash
act -v --secret-file .secrets
```

### Run with Docker debugging
```bash
# Keep containers running for inspection
act --secret-file .secrets --container-daemon-socket unix:///var/run/docker.sock
```

---

## 5️⃣ Common Act Commands

### List all jobs
```bash
act --list
```

### Run tests workflow only
```bash
act -W .github/workflows/tests.yml --secret-file .secrets
```

### Run on push event (default)
```bash
act push --secret-file .secrets
```

### Run on pull_request event
```bash
act pull_request --secret-file .secrets
```

### See Docker images used
```bash
act --list-images
```

### Reuse containers (faster)
```bash
act --reuse --secret-file .secrets
```

---

## 6️⃣ Testing Workflow

### Step 1: Prepare environment
```bash
# Make sure Docker is running
docker ps

# Go to repo directory
cd /path/to/sirius-solar

# Create secrets file
cat > .secrets << 'EOF'
DOCKER_USERNAME=testuser
DOCKER_PASSWORD=testpass
DEPLOY_HOST=localhost
DEPLOY_USER=deploy
DEPLOY_PORT=22
SLACK_WEBHOOK=https://hooks.slack.com/...
EOF
```

### Step 2: Run tests workflow
```bash
act -j tests --secret-file .secrets
```

**What it does:**
- ✓ Validates composer.json
- ✓ Installs PHP dependencies
- ✓ Creates test database
- ✓ Runs migrations
- ✓ Lints configuration
- ✓ Runs unit tests
- ✓ Code quality checks

### Step 3: Monitor output
```
Stage  [Tests & Code Quality/tests]  
  ✓ Step [Setup PHP]
  ✓ Step [Install dependencies]
  ✓ Step [Create database]
  ✓ Step [Run migrations]
  ✓ Step [Lint Symfony config]
  ✓ Step [Lint Twig templates]
  ✓ Step [Lint YAML]
  ✓ Step [Run tests]
  ✓ Step [Upload coverage]
```

---

## 7️⃣ Troubleshooting Act

### Issue: "Docker daemon not running"
```bash
# Start Docker
open -a Docker  # macOS
# or
docker ps  # Test if running
```

### Issue: "Cannot find image"
```bash
# Pull images manually
docker pull php:8.2-fpm-alpine
docker pull postgres:16-alpine
docker pull ubuntu:22.04
```

### Issue: "Port already in use"
```bash
# Check what's using the port
lsof -i :8000

# Kill the process
kill -9 <PID>
```

### Issue: "Permission denied"
```bash
# Run with sudo
sudo act --secret-file .secrets

# Or add user to docker group
sudo usermod -aG docker $USER
newgrp docker
```

---

## 8️⃣ Full Testing Workflow

```bash
#!/bin/bash
# Test full CI/CD pipeline locally

set -e

echo "🧪 Testing Sirius-Solar CI/CD Pipeline"
echo "======================================"
echo ""

# 1. Check prerequisites
echo "✓ Checking prerequisites..."
command -v act || { echo "Act not installed"; exit 1; }
docker ps > /dev/null || { echo "Docker not running"; exit 1; }

# 2. Create secrets file
echo "✓ Creating secrets file..."
cat > .secrets << 'EOF'
DOCKER_USERNAME=test-user
DOCKER_PASSWORD=test-pass
DEPLOY_HOST=localhost
DEPLOY_USER=deploy
DEPLOY_PORT=22
SLACK_WEBHOOK=https://hooks.slack.com/test
EOF

# 3. Run tests workflow
echo "✓ Running tests workflow..."
if act -j tests --secret-file .secrets; then
    echo "✅ Tests passed!"
else
    echo "❌ Tests failed!"
    exit 1
fi

# 4. Run code quality
echo "✓ Running code quality checks..."
if act -j code-quality --secret-file .secrets; then
    echo "✅ Code quality passed!"
else
    echo "⚠️  Code quality issues found"
fi

# 5. Run performance checks
echo "✓ Running performance checks..."
if act -j performance --secret-file .secrets; then
    echo "✅ Performance checks passed!"
else
    echo "⚠️  Performance issues found"
fi

# 6. Summary
echo ""
echo "✅ Local pipeline test complete!"
echo ""
echo "Next steps:"
echo "  1. git add ."
echo "  2. git commit -m 'Ready for deployment'"
echo "  3. git push origin main"
echo "  4. Watch GitHub Actions"
```

---

## 9️⃣ Simulating Real Deployment

### Create `.secrets` with real values
```bash
cat > .secrets << 'EOF'
DOCKER_USERNAME=yourusername
DOCKER_PASSWORD=your-docker-token
DEPLOY_HOST=your-server-ip
DEPLOY_USER=deploy
DEPLOY_SSH_KEY=-----BEGIN OPENSSH PRIVATE KEY-----
[... your SSH key content ...]
-----END OPENSSH PRIVATE KEY-----
DEPLOY_PORT=22
SLACK_WEBHOOK=https://hooks.slack.com/...
EOF

chmod 600 .secrets
```

### Run full deployment workflow
```bash
act push --secret-file .secrets -j deploy
```

**Note:** The actual deployment won't happen locally (SSH to real server), but you'll see what the workflow would do.

---

## 🔟 GitHub Actions Insights

### View logs after push
```bash
# View workflow runs
gh run list --repo your-username/sirius-solar

# View specific run details
gh run view <RUN_ID> --log

# Watch workflow in real-time
gh run watch <RUN_ID>
```

### Cancel a deployment
```bash
gh run cancel <RUN_ID> --repo your-username/sirius-solar
```

---

## 📊 Expected Results

### ✅ Successful Test Run
```
✓ Validate composer.json
✓ Cache Composer packages
✓ Install dependencies
✓ Copy .env file
✓ Generate APP_SECRET
✓ Create database
✓ Run migrations
✓ Lint Symfony config
✓ Lint Twig templates
✓ Lint YAML
✓ Run tests
✓ Upload coverage to Codecov

Workflow execution time: ~2 minutes
```

### ❌ Failed Test Run
```
✗ Run migrations
Error: SQLSTATE[08006] Could not connect to database

Workflow failed at: Run migrations step
```

---

## 💡 Tips & Best Practices

1. **Run locally before pushing**
   ```bash
   act --secret-file .secrets
   ```

2. **Keep secrets secure**
   - Add `.secrets` to `.gitignore`
   - Never commit real credentials
   - Rotate tokens periodically

3. **Use different tokens for CI/CD**
   - Create a separate Docker token for CI
   - Create a dedicated deploy user on server
   - Don't use your personal credentials

4. **Monitor GitHub Actions**
   - Check Actions tab after each push
   - Review deployment logs
   - Set up Slack notifications

5. **Test in stages**
   ```bash
   # Test individual workflows
   act -j tests
   act -j code-quality
   act -j build
   ```

---

## 🚀 Ready for Production

Once local testing passes:

```bash
# 1. Push to main
git push origin main

# 2. Monitor GitHub Actions
open https://github.com/YOUR_USERNAME/sirius-solar/actions

# 3. Watch deployment
gh run watch --repo your-username/sirius-solar

# 4. Verify in Slack
# You should see deployment notifications

# 5. Check production
curl https://sirius-solar.ch/health
```

---

**Happy Testing! 🧪**
