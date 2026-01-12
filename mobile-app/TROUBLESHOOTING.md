# Mobile App Troubleshooting Guide

## Registration/Login Loading Issue

### 1. Check Laravel Server
```bash
cd /var/www/html/Library-management
./start-server-proper.sh
```

### 2. Verify Server is Running
```bash
# Check if port 8000 is listening
ss -tuln | grep 8000
# or
netstat -tuln | grep 8000
```

### 3. Test API Connection
```bash
curl http://192.168.0.152:8000/api/mobile/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"test","role":"admin"}'
```

### 4. Check Your Local IP
```bash
hostname -I | awk '{print $1}'
```

### 5. Update API URL in Mobile App
Edit `mobile-app/src/services/apiService.js`:
```javascript
const API_BASE_URL = 'http://YOUR_LOCAL_IP:8000';
```

### 6. Common Issues

**Issue: "Cannot connect to server"**
- Solution: Make sure server is running on `0.0.0.0:8000`
- Check: Both devices on same WiFi network
- Check: Firewall allows port 8000

**Issue: "Request timeout"**
- Solution: Increase timeout in `apiService.js`
- Check: Server is not overloaded
- Check: Network connection is stable

**Issue: "Address already in use"**
- Solution: Kill existing process: `pkill -9 -f "php artisan serve"`
- Then restart: `./start-server-proper.sh`

### 7. Using Tunnel Mode (If Same Network Issue)
```bash
cd mobile-app
npx expo start --tunnel
```

### 8. Check Logs
```bash
# Laravel server logs
tail -f /tmp/laravel-server.log

# React Native logs (in Expo terminal)
# Press 'j' to open debugger
```

