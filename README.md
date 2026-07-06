# Inventory Management

A web-based inventory and asset-borrowing management system built with Laravel 13. It tracks products (assets), their stock and condition, and the borrowing/return lifecycle — with role-based access control and a token-authenticated REST API alongside the web UI.

Built during a Telkom internship.

## Features

- **Product & category management** — track code, name, stock, available stock, storage location, condition (`good` / `lightly_damaged` / `heavily_damaged`), and product image. Products use soft deletes.
- **Borrowing lifecycle** — record borrowings with borrower details, borrow/due dates, and per-item condition. Items are checked out and returned with status tracking (`borrowed` / `returned` / `overdue`).
- **Role-based access control** — three roles (`admin`, `staff`, `manager`) enforced through policies and a `role` middleware.
- **Dashboard** — summary metrics and charts.
- **REST API** — versioned (`/api/v1`) and authenticated with Laravel Sanctum tokens, documented with Scribe (interactive docs + OpenAPI spec).
- **Image storage** — local disk by default, Google Cloud Storage in production.

## Tech Stack

- **PHP** 8.4, **Laravel** 13
- **PostgreSQL** (default), SQLite (tests / CI)
- **Laravel Sanctum** — API token authentication
- **Laravel Breeze** — web auth scaffolding (Blade + Alpine.js)
- **Tailwind CSS** 3 + Vite
- **Scribe** — API documentation (interactive docs + OpenAPI spec)
- **Google Cloud Storage** — production image storage
- **Docker** + **Nginx** + **PHP-FPM** + **Supervisor** for deployment

## Roles & Permissions

| Capability                   | admin | staff | manager |
| ---------------------------- | :---: | :---: | :-----: |
| View products / borrowings   |   ✅   |   ✅   |    ✅    |
| Create / update / delete products |   ✅   |   ✅   |    —    |
| Create / update borrowings, process returns |   ✅   |   ✅   |    —    |
| Restore / force-delete records |   ✅   |   —   |    —    |
| Manage users                 |   ✅   |   —   |    —    |

Managers have read-only access; write operations are restricted to admin and staff.

## Requirements

- PHP 8.4+
- Composer 2
- Node.js 22+
- PostgreSQL (or SQLite for local/testing)

## Getting Started

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure the database in .env (defaults to PostgreSQL)
#    DB_DATABASE=inventory_management
#    DB_USERNAME / DB_PASSWORD=...

# 4. Migrate and seed
php artisan migrate --seed

# 5. Build frontend assets
npm run build
```

Run everything (server, queue, logs, Vite) with a single command:

```bash
composer run dev
```

Or start the server on its own:

```bash
php artisan serve
```

The app is available at `http://localhost:8000`.

### Seeded accounts

All seeded users share the password `password123`:

| Role    | Email               |
| ------- | ------------------- |
| Admin   | `admin@telkom.com`  |
| Staff   | `staff@telkom.com`  |
| Manager | `manager@telkom.com`|

## API

The API is versioned under `/api/v1` and authenticated with Sanctum bearer tokens.

```bash
# Obtain a token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -d "email=admin@telkom.com&password=password123"

# Use the token
curl http://localhost:8000/api/v1/products \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

### Endpoints

| Method | Endpoint                          | Description                    |
| ------ | --------------------------------- | ----------------------------- |
| POST   | `/api/v1/auth/login`              | Log in, returns a token       |
| POST   | `/api/v1/auth/logout`             | Revoke the current token      |
| —      | `/api/v1/products`                | Product resource (full CRUD)  |
| GET/POST | `/api/v1/categories`            | List / create categories      |
| GET/POST | `/api/v1/borrowings`            | List / create borrowings      |
| GET    | `/api/v1/borrowings/{id}`         | Show a borrowing              |
| PATCH  | `/api/v1/borrowings/{id}/return`  | Process a return              |
| GET    | `/api/v1/dashboard/summary`       | Dashboard summary metrics     |
| GET    | `/api/v1/dashboard/chart`         | Dashboard chart data          |

### API documentation

Interactive API documentation is generated with [Scribe](https://scribe.knuckles.wtf/laravel)
from PHP attributes on the API controllers, and is available at `/docs`. An OpenAPI spec is
served at `/docs.openapi`. Regenerate the docs after changing the attributes:

```bash
php artisan scribe:generate
```

## Testing

```bash
php artisan test --compact
```

## Docker

A local stack (app + PostgreSQL) is provided:

```bash
docker compose up --build
```

The app is served at `http://localhost:8000`. The production image (`Dockerfile`) bundles Nginx, PHP-FPM, and Supervisor, and is deployed via `docker-compose.prod.yml`.

## Deployment / CI

- **CI** (`.github/workflows/ci.yml`) — runs the test suite and builds frontend assets on pushes and PRs to `main`.
- **Deploy** (`.github/workflows/deploy.yml`) — builds and pushes the Docker image to Google Artifact Registry, then deploys to a VM over SSH on pushes to `main`.

In production, `FILESYSTEM_DISK=gcs` stores product images in Google Cloud Storage. Configure the `GCS_*` variables in `.env`.

## License

MIT
