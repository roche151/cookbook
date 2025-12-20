#!/bin/bash
set -e

# Run Laravel scheduler in long-running mode
php artisan schedule:work
