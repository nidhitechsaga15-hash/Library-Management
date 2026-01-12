#!/bin/bash
cd /var/www/html/Library-management
echo "Stopping existing server..."
pkill -9 -f "php artisan serve"
sleep 2
echo "Starting Laravel server on 0.0.0.0:8000..."
php artisan serve --host=0.0.0.0 --port=8000

