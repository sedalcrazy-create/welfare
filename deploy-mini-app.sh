#!/bin/bash

# Bale Mini App Deployment Script
# Run this on the server

set -e

echo "🚀 Starting Bale Mini App Deployment..."

# 1. Navigate to project directory
cd /var/www/welfare

# 2. Pull latest code
echo "📥 Pulling latest code from GitHub..."
git pull origin main

# 3. Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
cd resources/mini-app
npm install

# 4. Build production bundle
echo "🔨 Building production bundle..."
npm run build

# 5. Set permissions
echo "🔐 Setting permissions..."
cd /var/www/welfare
chown -R www-data:www-data public/mini-app
chmod -R 755 public/mini-app

# 6. Clear Laravel cache
echo "🧹 Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "✅ Deployment completed successfully!"
echo ""
echo "Mini App URL: https://ria.jafamhis.ir/welfare/mini-app/"
echo ""
echo "Next steps:"
echo "1. Check nginx configuration (see nginx-mini-app.conf)"
echo "2. Test the URL in browser"
echo "3. Test in Bale Bot"
