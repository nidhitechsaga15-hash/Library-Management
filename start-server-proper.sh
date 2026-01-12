#!/bin/bash
cd /var/www/html/Library-management

# Kill existing server
echo "Stopping existing server..."
pkill -9 -f "php artisan serve"
sleep 2

# Start server on all interfaces
echo "Starting Laravel server on 0.0.0.0:8000..."
nohup php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &

sleep 2

# Check if server started
if ss -tuln 2>/dev/null | grep -q ":8000" || netstat -tuln 2>/dev/null | grep -q ":8000"; then
    echo "✓ Server started successfully!"
    echo "Server is running on: http://0.0.0.0:8000"
    LOCAL_IP=$(hostname -I | awk '{print $1}')
    echo "Access from mobile: http://${LOCAL_IP}:8000"
    echo "Update mobile app API URL to: http://${LOCAL_IP}:8000"
    echo "Logs: tail -f /tmp/laravel-server.log"
else
    echo "✗ Server failed to start. Check logs: cat /tmp/laravel-server.log"
fi

