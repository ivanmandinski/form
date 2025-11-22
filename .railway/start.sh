#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/env.sh"

# Clear cache
rm -rf bootstrap/cache/*.php

# Ensure storage symlink exists for artifact access when using local storage
PUBLIC_DISK_DRIVER=${PUBLIC_DISK_DRIVER:-local}
if [ "$PUBLIC_DISK_DRIVER" != "s3" ]; then
    if [ ! -L public/storage ]; then
        php artisan storage:link || true
    fi
fi

# Discover packages
php artisan package:discover --ansi

# Run migrations (continue even if they fail)
php artisan migrate --force || echo 'Migrations failed, continuing...'

# Start the server
php artisan serve --host=0.0.0.0 --port=$PORT

