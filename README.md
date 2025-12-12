# Professional Online Doctor Appointment Token System

A comprehensive, modern, and professional web-based appointment management system for doctors and clinics.

## 🚀 Quick Start

1. **Setup Database**
   - Run `setup_database.php` in your browser: `http://localhost/online-appointment-token-system/setup_database.php`
   - Or import `database.sql` manually in phpMyAdmin

2. **Access the System**
   - Public Booking: `patient_booking.php` (No login required)
   - Staff Login: `login.php` (For doctors and assistants only)
   - Today's Tokens: `token_display.php` (Public view)

## 📋 Default Login Credentials

- **Admin/Assistant**: `admin@example.com` / `password123`
- **Doctor**: `doctor@example.com` / `password123`
- **Note**: Patient login is not available. Patients can book directly without registration.

## 🎯 Key Features

### Patient Features
- ✅ Public booking without registration
- ✅ Visual calendar with available/booked dates
- ✅ Automatic token assignment
- ✅ Simple booking form

### Assistant Dashboard
- ✅ Today's, future, and completed appointments
- ✅ Unavailable dates management
- ✅ Income analytics (daily, weekly, monthly)
- ✅ Visual charts and graphs
- ✅ Patient statistics

### Doctor Dashboard
- ✅ Today's patient list only
- ✅ Clean, focused interface
- ✅ Token-based queue view

## 📁 Project Structure

```
online-appointment-token-system/
├── patient_booking.php          # Public booking page (no login)
├── assistant_dashboard.php       # Assistant dashboard
├── doctor_dashboard.php         # Doctor dashboard
├── appointments.php              # Appointments list (staff only)
├── token_display.php             # Today's tokens (public)
├── login.php                     # Staff login (doctors/assistants)
├── index.php                     # Homepage
├── database.sql                  # Database schema
├── setup_database.php            # Auto database setup
├── api/
│   └── actions.php              # API endpoints
├── includes/
│   ├── db.php                   # Database connection
│   ├── header.php               # Header/navigation
│   └── footer.php               # Footer
└── assets/
    ├── css/style.css            # Base styles
    └── js/app.js                # JavaScript utilities
```

## 🔧 Configuration

Edit `includes/db.php` to configure database connection:
```php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'appointment_system';
$DB_USER = 'root';
$DB_PASS = '';
```

## 📱 System Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Modern web browser

## 🎨 Features

- Modern, responsive UI
- Chart.js visualizations
- Real-time token system
- Income tracking
- Patient analytics
- Mobile-friendly design

## 📝 License

Open-source for educational and commercial use.

---

**Version**: 2.0  
**Last Updated**: 2024

