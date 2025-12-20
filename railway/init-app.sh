#!/bin/bash
# Exit on first error
set -e

# Install Node dependencies and build frontend assets
echo "Building frontend assets..."
npm ci
npm run build

# Ensure storage link exists (ignore if already exists)
php artisan storage:link || true

# Run database migrations
php artisan migrate --force

# Clear caches and rebuild them for performance
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# Warm up application cache (optional but helpful)
php artisan optimize
