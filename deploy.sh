#!/bin/bash

cd /home/hpo-admin/htdocs/Department_web || exit

# Reset & pull from main
git reset --hard origin/main
git pull origin main

# Fix ownership and permissions
chown -R hpo-admin:hpo-admin /home/hpo-admin/htdocs/Department_web
chmod -R 775 /home/hpo-admin/htdocs/Department_web

# Restart services with sudo
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
