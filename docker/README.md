# Docker Setup

This project uses Docker for containerized deployment with the following services:

## Services

- **app** (php-fpm) — Laravel application server
- **nginx** — Web server (port 80)
- **horizon** — Queue worker (Redis)
- **scheduler** — Task scheduler (cron)
- **mysql** — Database
- **redis** — Cache & queue backend

## Files

- `Dockerfile` — PHP-FPM application image with Node.js, Composer, and asset building
- `docker-compose.yml` — Service orchestration
- `docker/nginx/default.conf` — Nginx configuration
- `docker/init.sh` — Database migration & seeding script
- `docker/cache-clear.sh` — Cache clearing script
- `docker/seed.sh` — Database seeding script
- `.dockerignore` — Files to exclude from Docker build

## Quick Start

### Build and Run

```bash
docker compose up -d --build
```

### Initialize Database (first time only)

```bash
docker compose --profile init up init
```

Or manually:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

### Useful Commands

```bash
# View logs
docker compose logs -f nginx

# Run artisan commands
docker compose exec app php artisan [command]

# Clear cache
docker compose exec app bash docker/cache-clear.sh

# Stop services
docker compose down

# Remove all data
docker compose down -v
```

## Environment Variables

Create a `.env` file in the project root with required variables. See `.env.example` or the production env template for reference.

### Key Variables

- `APP_KEY` — Laravel app key (generate with `php artisan key:generate`)
- `APP_URL` — Application URL
- `DB_*` — Database credentials
- `REDIS_*` — Redis connection details

## Production Deployment

For Dockploy or similar platforms:

1. Push repo with all Docker files
2. Set environment variables in deployment platform
3. Map domain to nginx service (port 80)
4. Run init commands for database setup
5. Horizon and Scheduler run automatically as separate services

## Troubleshooting

### Port Already in Use

If port 80 is taken, modify `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8080:80"  # Change 8080 to desired host port
```

### Disk Space Issues

```bash
docker system prune -a --volumes
docker builder prune -a
```

### Container Crashes

```bash
docker compose logs [service-name]
docker compose exec app php artisan
```
