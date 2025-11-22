#!/bin/bash
set -e

echo "🚀 Starting build process..."

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm ci

# Build assets
echo "🔨 Building assets..."
npm run build

# Install Chrome dependencies for Puppeteer (if needed)
echo "🌐 Installing Chrome dependencies for Puppeteer..."
if ! command -v chromium &> /dev/null; then
    apt-get update
    apt-get install -y \
        chromium \
        chromium-chromedriver \
        libnss3-dev \
        libatk-bridge2.0-dev \
        libdrm2 \
        libxcomposite1 \
        libxdamage1 \
        libxrandr2 \
        libgbm1 \
        libxss1 \
        libasound2
fi

# Generate app key if not set
echo "🔑 Generating application key..."
php artisan key:generate --force

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "✅ Build complete!"

