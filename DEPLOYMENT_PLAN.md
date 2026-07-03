# Deployment Plan: Inventory Management App → GCP

## Context

Laravel 13 + PostgreSQL inventory management app needs a production deployment pipeline from scratch. Currently there is no Docker setup, no CI/CD, and product images are stored on the local disk. This plan covers:
- Dockerizing the app and PostgreSQL
- Migrating product image storage to GCS
- Pushing images to Artifact Registry
- Running on a Compute Engine VM inside a custom VPC
- Automated CI/CD via GitHub Actions

---

## Phase 1 — Application Code Changes

### 1.1 Add GCS Flysystem Package

Add to `composer.json` under `require`:
```json
"google/cloud-storage": "^1.43",
"league/flysystem-google-cloud-storage": "^3.3"
```

Run `composer require google/cloud-storage league/flysystem-google-cloud-storage`.

### 1.2 Modify `config/filesystems.php`

Add a `gcs` disk entry in the `disks` array:
```php
'gcs' => [
    'driver'        => 'gcs',
    'project_id'    => env('GCS_PROJECT_ID'),
    'key_file_path' => env('GCS_KEY_FILE_PATH', null), // null = use ADC on GCE
    'bucket'        => env('GCS_BUCKET'),
    'path_prefix'   => env('GCS_PATH_PREFIX', ''),
    'visibility'    => 'public',
    'throw'         => false,
],
```

Leave `local` and `public` disks intact. Setting `FILESYSTEM_DISK=gcs` in production `.env` switches the active disk.

### 1.3 Fix Hardcoded Disk in `app/Http/Controllers/ProductController.php`

`store()` and `update()` hardcode `'public'` disk — this silently bypasses GCS even when `FILESYSTEM_DISK=gcs` is set. Fix:

- Replace all `->store('products', 'public')` → `->store('products', config('filesystems.default', 'public'))`
- Replace `Storage::disk('public')->delete(...)` → `Storage::disk(config('filesystems.default', 'public'))->delete(...)`

Blade views already call `Storage::url()` via the default disk — no view changes needed.

### 1.4 Update `.env.example`

Add after the `AWS_*` block:
```env
GCS_PROJECT_ID=your-gcp-project-id
GCS_BUCKET=your-bucket-name
GCS_PATH_PREFIX=
GCS_KEY_FILE_PATH=
```

---

## Phase 2 — Docker Files (all new)

### `Dockerfile` (multi-stage)

**Stage 1 — `node:20-alpine` (frontend)**
- Copy `package.json`, `package-lock.json`, Vite/Tailwind config, `resources/`
- `npm ci --ignore-scripts && npm run build`
- Output: `public/build/`

**Stage 2 — `composer:2.7` (vendor)**
- Copy `composer.json`, `composer.lock`
- `composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction`
- Copy all app source files
- `composer dump-autoload --optimize`

**Stage 3 — `php:8.3-fpm-alpine` (final)**
- `apk add`: `nginx supervisor postgresql-client libpq-dev libpng-dev libjpeg-turbo-dev freetype-dev icu-dev libzip-dev oniguruma-dev`
- `docker-php-ext-install`: `pdo pdo_pgsql pgsql opcache bcmath mbstring tokenizer xml ctype fileinfo gd intl zip`
- `COPY --from=vendor` vendor dir and app source
- `COPY --from=frontend` the `public/build/` assets
- Copy nginx, supervisor, and entrypoint configs
- `EXPOSE 80`
- `ENTRYPOINT ["/entrypoint.sh"]`

### `.dockerignore`

```
.git
.env
.env.*
node_modules/
vendor/
public/build/
public/storage/
storage/app/public/*
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
tests/
.github/
docker/
docker-compose*.yml
!storage/app/public/.gitkeep
```

### `docker-compose.yml` (local dev)

```yaml
services:
  app:
    build: .
    ports: ["8000:80"]
    volumes:
      - .:/var/www/html
      - .env:/var/www/html/.env
    environment:
      DB_HOST: postgres
    depends_on: [postgres]

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: inventory_management
      POSTGRES_USER: inventory_user
      POSTGRES_PASSWORD: secret
    ports: ["5432:5432"]
    volumes:
      - pgdata:/var/lib/postgresql/data

volumes:
  pgdata:
```

### `docker-compose.prod.yml` (production on VM)

```yaml
services:
  app:
    image: ${APP_IMAGE:-asia-southeast2-docker.pkg.dev/PROJECT_ID/inventory-app/app:latest}
    restart: unless-stopped
    env_file: .env
    ports: ["80:80"]
    depends_on: [postgres]

  postgres:
    image: postgres:16-alpine
    restart: unless-stopped
    env_file: .env
    volumes:
      - pgdata:/var/lib/postgresql/data
    # No external ports in production

volumes:
  pgdata:
```

### `docker/nginx/default.conf`

```nginx
server {
    listen 80;
    root /var/www/html/public;
    index index.php;
    client_max_body_size 10M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

### `docker/supervisor/supervisord.conf`

```ini
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:queue-worker]
command=php /var/www/html/artisan queue:work database --queue=default --tries=3 --sleep=3 --max-time=3600
directory=/var/www/html
autostart=true
autorestart=true
stopwaitsecs=60
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

### `docker/entrypoint.sh`

```bash
#!/bin/sh
set -e

echo "[entrypoint] Waiting for PostgreSQL..."
until pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" -d "$DB_DATABASE" -q; do
    sleep 1
done

cd /var/www/html
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

if [ "$FILESYSTEM_DISK" = "local" ] || [ "$FILESYSTEM_DISK" = "public" ] || [ -z "$FILESYSTEM_DISK" ]; then
    php artisan storage:link --force
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
```

---

## Phase 3 — GCP Infrastructure (gcloud CLI)

Set variables first:
```bash
export PROJECT_ID="inventory-mgmt-prod"
export REGION="asia-southeast2"   # Jakarta
export ZONE="asia-southeast2-a"
export REPO_NAME="inventory-app"
```

### Enable APIs
```bash
gcloud services enable compute.googleapis.com artifactregistry.googleapis.com \
    storage.googleapis.com iam.googleapis.com cloudresourcemanager.googleapis.com
```

### VPC + Firewall
```bash
gcloud compute networks create inventory-vpc --subnet-mode=custom
gcloud compute networks subnets create inventory-subnet \
    --network=inventory-vpc --region=$REGION --range=10.0.1.0/24

gcloud compute firewall-rules create allow-http   --network=inventory-vpc --allow=tcp:80  --source-ranges=0.0.0.0/0 --target-tags=inventory-app
gcloud compute firewall-rules create allow-https  --network=inventory-vpc --allow=tcp:443 --source-ranges=0.0.0.0/0 --target-tags=inventory-app
gcloud compute firewall-rules create allow-ssh    --network=inventory-vpc --allow=tcp:22  --source-ranges=0.0.0.0/0 --target-tags=inventory-app
gcloud compute firewall-rules create allow-internal --network=inventory-vpc --allow=tcp,udp,icmp --source-ranges=10.0.1.0/24
```

### Artifact Registry
```bash
gcloud artifacts repositories create $REPO_NAME \
    --repository-format=docker --location=$REGION
```

Image prefix: `asia-southeast2-docker.pkg.dev/$PROJECT_ID/$REPO_NAME/app`

### GCS Bucket
```bash
export BUCKET_NAME="inventory-product-images-$(openssl rand -hex 4)"
echo "Save this bucket name: $BUCKET_NAME"

gcloud storage buckets create gs://$BUCKET_NAME \
    --location=$REGION --uniform-bucket-level-access --no-versioning

# Public read for product images
gcloud storage buckets add-iam-policy-binding gs://$BUCKET_NAME \
    --member="allUsers" --role="roles/storage.objectViewer"
```

### Service Accounts

**VM identity SA** (Artifact Registry pull + GCS write):
```bash
gcloud iam service-accounts create inventory-vm-sa \
    --display-name="Inventory VM Service Account"

gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:inventory-vm-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/artifactregistry.reader"

gcloud storage buckets add-iam-policy-binding gs://$BUCKET_NAME \
    --member="serviceAccount:inventory-vm-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/storage.objectAdmin"
```

**CI/CD SA** (Artifact Registry push only):
```bash
gcloud iam service-accounts create inventory-cicd-sa \
    --display-name="Inventory CI/CD Service Account"

gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:inventory-cicd-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/artifactregistry.writer"

# Export JSON key → base64 → store as GCP_SA_KEY GitHub Secret
gcloud iam service-accounts keys create /tmp/cicd-sa-key.json \
    --iam-account="inventory-cicd-sa@$PROJECT_ID.iam.gserviceaccount.com"
base64 -i /tmp/cicd-sa-key.json
rm /tmp/cicd-sa-key.json
```

### Compute Engine VM
```bash
gcloud compute addresses create inventory-vm-ip --region=$REGION
export STATIC_IP=$(gcloud compute addresses describe inventory-vm-ip --region=$REGION --format='get(address)')
echo "Static IP: $STATIC_IP"

gcloud compute instances create inventory-vm \
    --zone=$ZONE \
    --machine-type=e2-medium \
    --image-family=ubuntu-2204-lts --image-project=ubuntu-os-cloud \
    --boot-disk-size=30GB --boot-disk-type=pd-standard \
    --network=inventory-vpc --subnet=inventory-subnet \
    --address=$STATIC_IP \
    --tags=inventory-app \
    --service-account="inventory-vm-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --scopes=cloud-platform \
    --metadata=startup-script='#!/bin/bash
      apt-get update -y
      curl -fsSL https://get.docker.com | sh
      usermod -aG docker ubuntu
      systemctl enable docker && systemctl start docker'
```

---

## Phase 4 — VM Bootstrap (one-time manual)

```bash
# SSH in (wait ~2 min after VM creation for startup script)
gcloud compute ssh ubuntu@inventory-vm --zone=$ZONE

# Auth Docker to Artifact Registry
gcloud auth configure-docker asia-southeast2-docker.pkg.dev

# Create app directory
mkdir -p /home/ubuntu/inventory-app
nano /home/ubuntu/inventory-app/.env
```

Production `.env` key values:
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:<run: php artisan key:generate --show>
APP_URL=http://<STATIC_IP>
LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=inventory_management
DB_USERNAME=inventory_user
DB_PASSWORD=<strong-password>

SESSION_DRIVER=database
FILESYSTEM_DISK=gcs
QUEUE_CONNECTION=database
CACHE_STORE=database

GCS_PROJECT_ID=<project-id>
GCS_BUCKET=<bucket-name>
GCS_PATH_PREFIX=
GCS_KEY_FILE_PATH=

POSTGRES_DB=inventory_management
POSTGRES_USER=inventory_user
POSTGRES_PASSWORD=<strong-password>
```

Copy compose file and do first deploy:
```bash
# From local machine:
gcloud compute scp docker-compose.prod.yml ubuntu@inventory-vm:/home/ubuntu/inventory-app/ --zone=$ZONE

# On the VM:
cd /home/ubuntu/inventory-app
docker pull asia-southeast2-docker.pkg.dev/$PROJECT_ID/inventory-app/app:latest
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan db:seed
```

---

## Phase 5 — GitHub Actions

### `.github/workflows/ci.yml` (all pushes + PRs)

Steps:
1. Checkout
2. `shivammathur/setup-php@v2` → PHP 8.3 + extensions: `pdo pdo_sqlite sqlite3 mbstring xml bcmath gd zip intl`
3. `composer install` (with dev deps for PHPUnit)
4. `cp .env.example .env && php artisan key:generate`
5. `php artisan test --compact --parallel`
6. `actions/setup-node@v4` → `npm ci` → `npm run build`

### `.github/workflows/deploy.yml` (push to `main` only)

**Job 1 — `build-and-push`:**
1. `google-github-actions/auth@v2` with `credentials_json: ${{ secrets.GCP_SA_KEY }}`
2. `gcloud auth configure-docker asia-southeast2-docker.pkg.dev`
3. Extract short SHA (`${GITHUB_SHA::8}`) → output as `image_tag`
4. `docker build --platform linux/amd64` tagged as both `:<sha>` and `:latest`
5. Push both tags to Artifact Registry

**Job 2 — `deploy`** (needs job 1):
1. `appleboy/ssh-action@v1` using `VM_SSH_HOST`, `VM_SSH_USER`, `VM_SSH_KEY`
2. On VM:
   ```bash
   cd /home/ubuntu/inventory-app
   docker pull .../app:<sha>
   APP_IMAGE=".../app:<sha>" docker compose -f docker-compose.prod.yml up -d --no-build
   docker image prune -f --filter "until=24h"
   ```

### GitHub Actions Secrets

| Secret | Description |
|---|---|
| `GCP_PROJECT_ID` | GCP project ID |
| `GCP_SA_KEY` | Base64-encoded JSON key for `inventory-cicd-sa` |
| `VM_SSH_HOST` | Static external IP of the VM |
| `VM_SSH_USER` | `ubuntu` |
| `VM_SSH_KEY` | Ed25519 private key for GitHub Actions SSH |

Generate deploy SSH key:
```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f /tmp/gh-deploy-key -N ""

# Add public key to VM
gcloud compute ssh ubuntu@inventory-vm --zone=$ZONE \
    --command="echo '$(cat /tmp/gh-deploy-key.pub)' >> ~/.ssh/authorized_keys"

# Copy private key → GitHub Secret VM_SSH_KEY
cat /tmp/gh-deploy-key

rm /tmp/gh-deploy-key /tmp/gh-deploy-key.pub
```

---

## Verification

```bash
# 1. Containers running
gcloud compute ssh ubuntu@inventory-vm --zone=asia-southeast2-a \
    --command="docker compose -f /home/ubuntu/inventory-app/docker-compose.prod.yml ps"

# 2. App responds
curl -I http://<STATIC_IP>/        # Expected: 302 redirect to login
curl http://<STATIC_IP>/up         # Expected: 200 OK (Laravel health route)

# 3. GCS integration
docker compose exec app php artisan tinker --execute \
    "Storage::disk('gcs')->put('test.txt','ok'); echo Storage::disk('gcs')->url('test.txt');"
curl https://storage.googleapis.com/<BUCKET_NAME>/test.txt  # Expected: ok

# 4. Supervisor status
docker compose exec app supervisorctl status
# Expected: nginx RUNNING, php-fpm RUNNING, queue-worker RUNNING

# 5. End-to-end image upload
# Login → Products → Create → upload image
# Product page should serve image from storage.googleapis.com/<BUCKET_NAME>/products/...

# 6. CI/CD
# Push a commit to main → GitHub Actions should show both jobs green
# Run `docker ps` on VM to confirm new container SHA matches deployed image tag
```

---

## Key Trade-offs

| Decision | Rationale |
|---|---|
| Postgres in Docker (not Cloud SQL) | Simpler per requirements; add `pg_dump` to GCS for backups. Upgrade to Cloud SQL for production HA. |
| ADC for GCS auth (no JSON key in container) | VM's attached SA grants GCS access via metadata server — no secret management in app. |
| Supervisor (nginx + php-fpm + queue in one container) | Avoids inter-container networking on a single VM. Split if scaling out. |
| SHA-pinned deploys | Both `:latest` and `:<sha>` pushed; deploy pins to SHA to prevent race conditions. |
| `league/flysystem-google-cloud-storage` over S3-compat | Native adapter avoids GCS XML API ACL incompatibilities with Laravel's S3 driver. |
