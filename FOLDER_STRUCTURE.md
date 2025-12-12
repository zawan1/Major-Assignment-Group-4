# Project Folder Structure

## ✅ Essential Files (Keep)

### Core Application Files
- `index.php` - Homepage
- `login.php` - Staff login (doctors/assistants only)
- `logout.php` - Logout functionality

### Main Features
- `patient_booking.php` - **Public booking page** (no login required)
- `assistant_dashboard.php` - **Assistant dashboard** (appointments, income, analytics)
- `doctor_dashboard.php` - **Doctor dashboard** (today's patients only)
- `appointments.php` - Appointments list/view (staff only)
- `token_display.php` - Today's tokens display (public)

### Database & Setup
- `database.sql` - Database schema
- `setup_database.php` - Auto database setup script

### Core Includes
- `includes/db.php` - Database connection
- `includes/header.php` - Header/navigation
- `includes/footer.php` - Footer

### API
- `api/actions.php` - API endpoints (appointments, slots, unavailable dates)

### Assets
- `assets/css/style.css` - Base styles
- `assets/js/app.js` - JavaScript utilities

### Documentation
- `README.md` - Main documentation

### Utility Files
- `manage_slots.php` - Slot management (used by assistant)

## ❌ Removed Files

- `admin/dashboard_admin.php` - Redirected to assistant_dashboard.php
- `admin/view_appointments.php` - Redundant (assistant dashboard has this)
- `admin/manage_doctors.php` - Old admin interface
- `admin/manage_users.php` - Old admin interface
- `book_appointment.php` - Redundant (patient_booking.php handles this)
- `migrate_database.php` - Replaced by setup_database.php
- `README_ENHANCED.md` - Merged into README.md
- `UPGRADE_NOTES.md` - Temporary documentation
- `TEST_PLAN.md` - Testing documentation
- `register.php` - Removed (patient registration not needed)
- `dashboard_user.php` - Removed (patient login not available)

## 📊 Current Structure

```
online-appointment-token-system/
├── api/
│   └── actions.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── includes/
│   ├── db.php
│   ├── footer.php
│   └── header.php
├── assistant_dashboard.php
├── appointments.php
├── database.sql
├── doctor_dashboard.php
├── index.php
├── login.php
├── logout.php
├── manage_slots.php
├── patient_booking.php
├── setup_database.php
├── token_display.php
└── README.md
```

## 🎯 System Flow

1. **Public Users/Patients** → `patient_booking.php` → Book appointment → Get token (No login required)
2. **Assistants** → `login.php` → `assistant_dashboard.php` → Manage everything
3. **Doctors** → `login.php` → `doctor_dashboard.php` → View today's patients

