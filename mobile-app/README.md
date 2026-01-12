# Library Management Mobile App

React Native mobile application for Library Management System.

## Features

- ✅ Login with role selection (Admin/Staff/Student)
- ✅ Registration with role selection and captcha
- ✅ Separate dashboards for Admin, Staff, and Student
- ✅ Mobile-optimized UI
- ✅ API integration with Laravel backend

## Setup Instructions

1. Install dependencies:
```bash
npm install
```

2. Update API URL in `src/services/apiService.js`:
```javascript
const API_BASE_URL = 'http://YOUR_SERVER_IP:8000';
```

3. Start the app:
```bash
npm start
```

4. For Android:
```bash
npm run android
```

5. For iOS:
```bash
npm run ios
```

## Backend API Setup

You need to create API routes in Laravel for mobile app. Create these routes in `routes/api.php`:

- POST `/api/mobile/login`
- POST `/api/mobile/register`
- GET `/api/mobile/profile`
- GET `/api/mobile/admin/dashboard`
- GET `/api/mobile/staff/dashboard`
- GET `/api/mobile/student/dashboard`

## Project Structure

```
mobile-app/
├── src/
│   ├── screens/
│   │   ├── auth/
│   │   │   ├── LoginScreen.js
│   │   │   └── RegisterScreen.js
│   │   └── dashboards/
│   │       ├── AdminDashboard.js
│   │       ├── StaffDashboard.js
│   │       └── StudentDashboard.js
│   ├── components/
│   ├── services/
│   │   └── apiService.js
│   ├── context/
│   │   └── AuthContext.js
│   └── utils/
├── App.js
├── package.json
└── app.json
```

## Notes

- Update the ReCaptcha site key in RegisterScreen.js
- Configure your Laravel backend to accept mobile API requests
- Add CORS configuration for mobile app domain

