#!/bin/bash
set -e

# Ensure caches are primed
php artisan optimize || true

# Start the Laravel app using PHP built-in server (suitable for testing)
# Railway exposes PORT env var; default to 8080 if missing
PORT="${PORT:-8080}"
echo "Starting Laravel app on port ${PORT}"
php -d variables_order=EGPCS -S 0.0.0.0:"${PORT}" -t public public/index.php
