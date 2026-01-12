# API Configuration for Mobile App

## Laravel Server Setup

1. **Start Laravel server on all interfaces:**
```bash
cd /var/www/html/Library-management
php artisan serve --host=0.0.0.0 --port=8000
```

2. **Check your local IP:**
```bash
hostname -I | awk '{print $1}'
```

3. **Update API URL in mobile app:**
Edit `mobile-app/src/services/apiService.js`:
```javascript
const API_BASE_URL = 'http://YOUR_LOCAL_IP:8000';
```

## Testing API Connection

Test from mobile device/emulator:
```bash
curl http://YOUR_LOCAL_IP:8000/api/mobile/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password","role":"admin"}'
```

## Troubleshooting

- Make sure Laravel server is running on `0.0.0.0:8000` (not `127.0.0.1`)
- Check firewall allows port 8000
- Ensure mobile device and computer are on same network
- For Expo Go, use tunnel mode: `npx expo start --tunnel`

