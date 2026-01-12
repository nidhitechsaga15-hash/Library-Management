# Mobile App Setup Guide

## Installation Steps

1. **Install Dependencies:**
```bash
cd mobile-app
npm install
```

2. **Configure API URL:**
   - Open `src/services/apiService.js`
   - Update `API_BASE_URL` with your Laravel backend URL
   - Example: `const API_BASE_URL = 'http://192.168.1.100:8000';`

3. **Configure ReCaptcha (Optional):**
   - Get ReCaptcha site key from Google
   - Update in `src/screens/RegisterScreen.js`
   - Replace `YOUR_RECAPTCHA_SITE_KEY` with your actual key

4. **Start the App:**
```bash
npm start
```

5. **Run on Device:**
   - For Android: `npm run android`
   - For iOS: `npm run ios`
   - Scan QR code with Expo Go app

## Backend Configuration

Make sure your Laravel backend:
1. Has CORS enabled for mobile app domain
2. API routes are accessible at `/api/mobile/*`
3. Token authentication is working

## Features

✅ Login with role selection (Admin/Staff/Student)
✅ Registration with role selection and captcha
✅ Separate dashboards for each role
✅ Mobile-optimized UI
✅ API integration with Laravel backend

## Project Structure

```
mobile-app/
├── src/
│   ├── screens/
│   │   ├── LoginScreen.js
│   │   ├── RegisterScreen.js
│   │   └── dashboards/
│   │       ├── AdminDashboard.js
│   │       ├── StaffDashboard.js
│   │       └── StudentDashboard.js
│   ├── services/
│   │   └── apiService.js
│   ├── context/
│   │   └── AuthContext.js
│   └── components/
├── App.js
├── package.json
└── app.json
```

