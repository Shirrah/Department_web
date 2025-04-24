#!/bin/bash

# Navigate to your project directory
cd /home/hpo-admin/htdocs/Department_web || exit

# Pull the latest code, discarding local changes
git reset --hard origin/main
git pull origin main

# Set the correct owner and permissions (replace web1 with actual web user if needed)
chown -R web1:web1 /home/hpo-admin/htdocs/Department_web
chmod -R 775 /home/hpo-admin/htdocs/Department_web

# Restart PHP 8.4 FPM to clear cache
systemctl restart php8.4-fpm

# Reload NGINX to apply changes
systemctl reload nginx
