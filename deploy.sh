#!/bin/bash
cd /home/hpo-admin/htdocs/Department_web  # Change to your project directory
git reset --hard origin/main  # Ensure no local changes interfere
git pull origin main  # Pull latest changes
chown -R www-data:www-data /home/hpo-admin/htdocs/Department_web  # Adjust file permissions
chmod -R 775 /home/hpo-admin/htdocs/Department_web  # Ensure correct permissions
