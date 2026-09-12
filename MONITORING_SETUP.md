# 📊 Monitoring & Alerting Setup - Sirius-Solar

Complete guide to monitor your Sirius-Solar application in production.

## 🎯 Monitoring Stack

We'll set up a complete monitoring solution with:
- **Application Monitoring** - Health checks & logs
- **Infrastructure Monitoring** - CPU, RAM, Disk, Network
- **Database Monitoring** - Query performance, connections
- **Uptime Monitoring** - External monitoring & alerting
- **Log Aggregation** - Centralized logging

---

## 1️⃣ Application Monitoring

### Health Check Endpoint

The app already has a health check endpoint. Test it:

```bash
curl -I https://sirius-solar.ch/health

# Expected response:
# HTTP/2 200
# Content-Type: text/plain
```

### Application Logs

```bash
# View real-time logs
tail -f /var/www/sirius-solar/var/log/prod.log

# Search for errors
grep ERROR /var/www/sirius-solar/var/log/prod.log

# Count errors by type
grep ERROR /var/www/sirius-solar/var/log/prod.log | \
  sed 's/.*ERROR: //' | sort | uniq -c | sort -rn
```

---

## 2️⃣ Infrastructure Monitoring with Prometheus + Grafana

### Installation

```bash
# Create monitoring directory
mkdir -p /opt/monitoring
cd /opt/monitoring

# Download docker-compose for monitoring stack
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    container_name: prometheus
    restart: unless-stopped
    ports:
      - "127.0.0.1:9090:9090"
    volumes:
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
    networks:
      - monitoring

  grafana:
    image: grafana/grafana:latest
    container_name: grafana
    restart: unless-stopped
    ports:
      - "127.0.0.1:3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin123
      - GF_USERS_ALLOW_SIGN_UP=false
    volumes:
      - grafana_data:/var/lib/grafana
    networks:
      - monitoring

  node-exporter:
    image: prom/node-exporter:latest
    container_name: node-exporter
    restart: unless-stopped
    ports:
      - "127.0.0.1:9100:9100"
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'
      - '--collector.filesystem.mount-points-exclude=^/(sys|proc|dev|host|etc)($$|/)'
    networks:
      - monitoring

  postgres-exporter:
    image: prometheuscommunity/postgres-exporter:latest
    container_name: postgres-exporter
    restart: unless-stopped
    ports:
      - "127.0.0.1:9187:9187"
    environment:
      DATA_SOURCE_NAME: "postgresql://app:password@db:5432/sirius_solar?sslmode=disable"
    networks:
      - monitoring

volumes:
  prometheus_data:
  grafana_data:

networks:
  monitoring:
    driver: bridge
EOF

# Create Prometheus configuration
cat > prometheus.yml << 'EOF'
global:
  scrape_interval: 15s
  evaluation_interval: 15s
  external_labels:
    monitor: 'sirius-solar'

scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  - job_name: 'node'
    static_configs:
      - targets: ['localhost:9100']

  - job_name: 'postgres'
    static_configs:
      - targets: ['localhost:9187']

  - job_name: 'sirius-app'
    metrics_path: '/metrics'
    static_configs:
      - targets: ['web:8000']
EOF

# Start the monitoring stack
docker-compose up -d

# Verify containers are running
docker-compose ps
```

### Access Dashboards

```bash
# Prometheus (metrics storage)
open http://localhost:9090

# Grafana (visualization)
open http://localhost:3000
# Default login: admin / admin123
```

### Import Grafana Dashboards

1. Go to Grafana: http://localhost:3000
2. Click "+" → "Import"
3. Use these dashboard IDs:
   - **1860** - Node Exporter Full
   - **6417** - PostgreSQL Database
   - **12226** - Prometheus

---

## 3️⃣ Log Aggregation with ELK Stack (Elasticsearch + Logstash + Kibana)

### Installation

```bash
# Create ELK directory
mkdir -p /opt/elk
cd /opt/elk

cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.0.0
    container_name: elasticsearch
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
    ports:
      - "127.0.0.1:9200:9200"
    volumes:
      - elastic_data:/usr/share/elasticsearch/data
    networks:
      - elk

  kibana:
    image: docker.elastic.co/kibana/kibana:8.0.0
    container_name: kibana
    ports:
      - "127.0.0.1:5601:5601"
    environment:
      ELASTICSEARCH_HOSTS: http://elasticsearch:9200
    networks:
      - elk

  filebeat:
    image: docker.elastic.co/beats/filebeat:8.0.0
    container_name: filebeat
    user: root
    volumes:
      - ./filebeat.yml:/usr/share/filebeat/filebeat.yml:ro
      - /var/www/sirius-solar/var/log:/var/log/sirius-solar:ro
      - /var/log:/var/log:ro
    command: filebeat -e -strict.perms=false
    networks:
      - elk

volumes:
  elastic_data:

networks:
  elk:
    driver: bridge
EOF

# Create Filebeat configuration
cat > filebeat.yml << 'EOF'
filebeat.inputs:
  - type: log
    enabled: true
    paths:
      - /var/log/sirius-solar/*.log
    multiline.pattern: '^\['
    multiline.negate: true
    multiline.match: after
    fields:
      app: sirius-solar
      env: production

output.elasticsearch:
  hosts: ["elasticsearch:9200"]

logging.level: info
EOF

docker-compose up -d
```

### Access Kibana

```bash
open http://localhost:5601
```

---

## 4️⃣ Uptime Monitoring - External Services

### Option 1: Uptimerobot (Free)

```bash
# 1. Sign up at https://uptimerobot.com
# 2. Add monitor for your health endpoint
#    - URL: https://sirius-solar.ch/health
#    - Check interval: 5 minutes
#    - Alert email: your-email@example.com
# 3. Enable SMS/Slack alerts (paid plans)
```

### Option 2: Statping (Self-hosted, Free)

```bash
docker run -d \
  --name statping \
  -e DB_CONN=postgres \
  -e DB_HOST=localhost \
  -e DB_USER=app \
  -e DB_PASS=password \
  -e DB_DATABASE=statping \
  -p 8080:8080 \
  statping/statping:latest

# Access at http://localhost:8080
```

---

## 5️⃣ Alerts & Notifications

### Slack Integration

```bash
# 1. Create a Slack workspace
# 2. Create an incoming webhook: https://api.slack.com/messaging/webhooks

# 3. Test the webhook
curl -X POST 'YOUR_WEBHOOK_URL' \
  -H 'Content-Type: application/json' \
  -d '{
    "text": "🚀 Sirius-Solar Deployment Notification",
    "blocks": [{
      "type": "section",
      "text": {
        "type": "mrkdwn",
        "text": "*Environment:* Production\n*Status:* ✅ Online"
      }
    }]
  }'

# 4. Add webhook to .env or .env.production
SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### Email Alerts

```bash
# Configure SMTP in .env.production
MAILER_DSN=smtp://user:password@smtp.gmail.com:587?encryption=tls

# Test email
php bin/console make:command test:alert-email
php bin/console test:alert-email admin@sirius-solar.ch
```

---

## 6️⃣ Performance Monitoring

### Database Query Monitoring

```bash
# Enable slow query logging in PostgreSQL
sudo -u postgres psql -d sirius_solar << EOF
ALTER SYSTEM SET log_min_duration_statement = 1000;
SELECT pg_reload_conf();
EOF

# View slow queries
sudo tail -f /var/log/postgresql/postgresql-*.log | grep -i slow
```

### Application Performance Monitoring (APM)

```bash
# Using Blackfire (free tier)
# 1. Sign up at https://blackfire.io
# 2. Install Blackfire CLI
# 3. Profile your app

blackfire run php bin/console cache:clear

# View profiling results at https://blackfire.io/dashboard
```

---

## 7️⃣ Automated Health Checks Script

The provided `monitoring.sh` script checks:

```bash
# Run it manually
cd /var/www/sirius-solar
bash monitoring.sh

# Or setup cron job (via setup-cron.sh)
sudo ./setup-cron.sh
```

**Checks performed:**
- ✓ Application health endpoint
- ✓ CPU usage
- ✓ Memory usage
- ✓ Disk space
- ✓ Database connectivity
- ✓ SSL certificate expiry
- ✓ Service status (PHP-FPM, Nginx, PostgreSQL)
- ✓ System uptime

---

## 8️⃣ Log Analysis

### Common Commands

```bash
# Recent errors
tail -100 /var/www/sirius-solar/var/log/prod.log | grep ERROR

# Count errors by status code
grep "ERROR" /var/www/sirius-solar/var/log/prod.log | \
  sed 's/.*status code: //' | sort | uniq -c

# Find 404s
grep '404' /var/log/nginx/access.log | wc -l

# Top 10 slowest pages
grep "duration" /var/log/nginx/access.log | \
  awk '{print $(NF-1), $7}' | sort -rn | head -10

# Real-time log monitoring
tail -f /var/log/nginx/access.log | grep -v "health\|assets"
```

---

## 9️⃣ Backup Monitoring

Verify backups are running:

```bash
# Check backup directory
ls -lh /var/backups/sirius-solar/

# Verify last backup
ls -t /var/backups/sirius-solar/backup_*.sql.gz | head -1

# Check backup size trend
ls -lhS /var/backups/sirius-solar/ | head -5

# Restore test (on development only!)
gzip -dc /var/backups/sirius-solar/backup_latest.sql.gz | \
  psql -U app -d sirius_solar_test
```

---

## 🔟 Dashboard Creation in Grafana

### Create Custom Dashboard for Sirius-Solar

1. **Create New Dashboard**
   - Click "+" → "Dashboard"

2. **Add Panels:**
   - Application Health Status
   - Request Rate (requests/sec)
   - Error Rate
   - Database Connection Pool
   - Disk I/O
   - Network Traffic

3. **Example Query (Prometheus):**
   ```
   rate(http_requests_total[5m])
   ```

---

## 🔍 Monitoring Checklist

- [ ] Health check endpoint configured
- [ ] Prometheus + Grafana running
- [ ] ELK stack (or alternative) logging
- [ ] Uptimerobot configured for external monitoring
- [ ] Slack webhook configured
- [ ] Email alerts configured
- [ ] Daily backup verification
- [ ] SSL certificate renewal automated
- [ ] Performance baseline established
- [ ] Alert thresholds configured
- [ ] Team trained on dashboard interpretation
- [ ] On-call rotation documented

---

## 📞 Alert Thresholds (Recommended)

| Metric | Warning | Critical |
|--------|---------|----------|
| CPU Usage | > 70% | > 90% |
| Memory Usage | > 80% | > 95% |
| Disk Space | > 70% | > 90% |
| Response Time | > 2s | > 5s |
| Error Rate | > 1% | > 5% |
| Database Connections | > 80% of pool | > 95% of pool |
| SSL Cert Expiry | < 30 days | < 7 days |

---

## 🆘 Incident Response

### When an alert fires:

1. **Check Dashboard**
   - Go to Grafana http://localhost:3000
   - Identify affected service

2. **Check Logs**
   - Kibana: http://localhost:5601
   - Search for errors around timestamp

3. **Check Application**
   - SSH into server: `ssh user@server`
   - Check `var/log/prod.log`
   - Check `systemctl status` for services

4. **Take Action**
   - If CPU high: Check for runaway processes with `top`
   - If memory high: Restart PHP-FPM
   - If disk full: Check backup size
   - If database slow: Check connections and queries

5. **Document**
   - Note what happened in Slack
   - Post-incident review

---

## 📚 Resources

- [Prometheus Official](https://prometheus.io/)
- [Grafana Dashboards](https://grafana.com/grafana/dashboards)
- [Elastic Stack](https://www.elastic.co/elastic-stack)
- [Uptimerobot](https://uptimerobot.com/)
- [Slack API](https://api.slack.com/)

---

**Keep your application healthy! 🚀**
