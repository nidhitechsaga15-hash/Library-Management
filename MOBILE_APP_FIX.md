# Mobile App Connection Fix

## Issues Fixed:

1. ✅ **API Base URL Updated**: Changed from `192.168.1.21` to `192.168.1.12`
2. ✅ **Server Restarted**: Now listening on `0.0.0.0:8000` (accessible from mobile)

## Current Configuration:

- **Server IP**: `192.168.1.12`
- **Server Port**: `8000`
- **API Base URL**: `http://192.168.1.12:8000`
- **Server Status**: Running on `0.0.0.0:8000` ✅

## Next Steps:

1. **Restart Mobile App**:
   ```bash
   cd mobile-app
   npm start
   # Press 'r' to reload
   ```

2. **Test Connection**:
   - Open mobile app
   - Try to login
   - Check console logs for any errors

3. **If Still Having Issues**:

   **Check Firewall**:
   ```bash
   sudo ufw allow 8000/tcp
   # or
   sudo iptables -A INPUT -p tcp --dport 8000 -j ACCEPT
   ```

   **Check Network**:
   - Ensure mobile device and server are on same WiFi network
   - Ping test: `ping 192.168.1.12` from mobile device

   **Check Server Logs**:
   ```bash
   tail -f /tmp/laravel-server.log
   ```

4. **Alternative: Use Tunnel Mode** (if same network doesn't work):
   ```bash
   cd mobile-app
   npx expo start --tunnel
   ```

## Troubleshooting:

- **Connection Refused**: Server not running or firewall blocking
- **Timeout**: Network issue or server overloaded
- **CORS Error**: Check Laravel CORS configuration
- **401 Unauthorized**: Check API token/authentication

## Server Management:

**Start Server**:
```bash
cd /var/www/html/Library-management
./start-server-proper.sh
```

**Stop Server**:
```bash
pkill -f "php artisan serve"
```

**Check Server Status**:
```bash
ss -tuln | grep 8000
# Should show: 0.0.0.0:8000
```

