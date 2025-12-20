#!/bin/bash
set -e

# Start Laravel queue worker
php artisan queue:work --sleep=1 --tries=3 --max-time=3600
