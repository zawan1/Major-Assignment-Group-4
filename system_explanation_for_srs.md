# Comprehensive System Explanation
> **Note**: This document provides a detailed technical and functional explanation of the Online Appointment Token System to assist in drafting Software Requirements Specifications (SRS) and Thesis documentation.

## 1. System Overview

The **Online Appointment Token System** is a web-based application designed to streamline the consultation process in medical clinics. It replaces traditional manual queuing with a digital token-based workflow. The system facilitates three key user types: **Patients** (Guest/Public), **Doctors**, and **Assistants (Admins)**.

**Core Philosophy**: "Book online, arrive on time." The system allows patients to book appointments without account registration, reducing barriers to entry, while providing clinic staff with powerful tools to manage the daily flow and track financial performance.

## 2. System Architecture

The system follows a typical **Model-View-Controller (MVC)** inspired architecture, though implemented in a lightweight procedural PHP manner for performance and simplicity.

### 2.1 Technology Stack
*   **Frontend**: HTML5, CSS3 (Custom Theme + Bootstrap 5), JavaScript (Vanilla + Chart.js).
*   **Backend**: PHP (7.4+).
*   **Database**: MariaDB / MySQL.
*   **Server**: Apache (via XAMPP).

### 2.2 Directory Structure
*   **Public Interface**: `patient_booking.php`, `token_display.php`.
*   **Secure Dashboards**: `doctor_dashboard.php`, `assistant_dashboard.php`.
*   **API/Logic Layer**: `api/actions.php`.
*   **Data Access Layer**: `includes/db.php`.

## 3. Modules & Functional Requirements

### 3.1 Patient Module (Public Booking)
This module acts as the public face of the clinic, accessible without login.

*   **Guest Booking**: Patients can book slots by providing Name, Age, Contact, and Preferred Time. No account creation is required to minimize friction.
*   **Real-time Availability Calendar**:
    *   **Available**: Dates with open slots.
    *   **Unavailable**: Specific dates marked off by the doctor (e.g., holidays).
    *   **Booked**: Dates that have existing appointments (though multiple bookings per date are allowed until capacity is reached).
*   **Token Generation**: Upon successful booking, the system automatically assigns the next available sequential token number for that specific date.
*   **Validation**: The system prevents duplicate bookings for the same patient (identified by contact number) at the exact same time and date.

### 3.2 Doctor Module (Clinical Dashboard)
A focused, distraction-free interface for the medical practitioner.

*   **Today's Queue**: Displays ONLY the patients scheduled for the current day.
*   **Patient Status Management**:
    *   **Call**: Changes patient status from "Booked" to "Called".
    *   **Complete**: Changes status from "Called" to "Completed".
*   **Real-time Queue Status**: Visual indicators show who is waiting, who is currently being seen, and who has finished.

### 3.3 Assistant/Admin Module (Management & Stats)
 The central command center for the clinic's front desk.

*   **Comprehensive Appointment Management**: View schedules for any date (Past, Present, Future).
*   **Financial Tracking**:
    *   **Fee Entry**: When marking an appointment as "Completed", the assistant enters the consultation fee.
    *   **Income Analytics**: Calculates Daily, Weekly, and Monthly income.
    *   **Visualizations**: Line charts for daily income trends and bar charts for monthly revenue.
*   **Slot & Availability Management**:
    *   **Manage Slots**: Define working hours and capacity for specific days.
    *   **Block Dates**: Mark specific dates as unavailable (e.g., "Doctor on Leave"), which immediately updates the patient booking calendar.
*   **Patient Demographics**: Statistical breakdown of patient age groups (0-18, 19-35, etc.) to help the clinic understand their patient base.

## 4. Process Logic & Algorithms

### 4.1 Token Generation Algorithm
The system uses a sequential counter reset daily for each doctor.
*   **Logic**: `SELECT MAX(token_number) + 1 FROM appointments WHERE appointment_date = [TARGET_DATE]`
*   **Concurrency Handling**: The application uses database constraints and transactions to ensure two patients booking simultaneously do not get the same token.

### 4.2 Queue State Machine
An appointment moves through strict states:
1.  **Booked**: Default state upon creation.
2.  **Called**: Doctor/Assistant initiates the consultation.
3.  **Completed**: Consultation finished; fee recorded.
4.  **Cancelled**: Appointment removed from active queue; slot freed.

### 4.3 Slot Validation Logic
Before confirming a booking, the system performs a multi-step check:
1.  **Sanatization**: Cleans inputs to prevent XSS/Injection.
2.  **Availability Check**: Queries `unavailable_dates` table.
3.  **Duplicate Check**: Queries `appointments` for matching `(doctor_id, date, time, contact)`.
4.  **Date Validity**: Ensures the requested date is not in the past.

## 5. Database Schema Design

The operational data is stored in a relational database with the following structure:

### `users`
*   Stores authentication details for staff.
*   **Columns**: `id`, `name`, `role` (admin|doctor|patient), `email`, `password`.

### `appointments`
*   The core transaction table.
*   **Columns**:
    *   `id`: PK.
    *   `token_number`: Daily sequential ID.
    *   `status`: Enum('booked', 'called', 'completed', 'cancelled').
    *   `fee`: Decimal (recorded at completion).
    *   Foreign Keys: `doctor_id` -> `users(id)`.

### `available_slots` (Table: `slots`)
*   Defines the basic schedule structure.
*   **Columns**: `id`, `doctor_id`, `slot_date`, `start_time`, `end_time`, `capacity`.

### `unavailable_dates`
*   Overrides standard availability.
*   **Columns**: `id`, `doctor_id`, `unavailable_date`, `reason`.

## 6. User Interface Design (UI)

*   **Glassmorphism Theme**: The application uses a modern, translucent "frosted glass" aesthetic with vibrant gradients (Blue/Indigo/Purple palettes) to convey professionalism and cleanliness.
*   **Responsive Layout**: Built on Bootstrap 5 grid, ensuring the dashboards work seamlessly on tablets (for assistants walking around) and mobile phones (for patients booking on the go).
*   **Interactive Elements**:
    *   **Hover Effects**: Cards lift up when hovered.
    *   **Dynamic Calendar**: The booking calendar is generated via JavaScript, allowing immediate visual feedback on date status without page reloads.

## 7. Security & Non-Functional Requirements

*   **Security**:
    *   **Role-Based Access Control (RBAC)**: Middleware checks `$_SESSION['role']` on every sensitive page load. A doctor cannot access the admin config; an unauthenticated user cannot access dashboards.
    *   **Prepared Statements**: All database queries use PDO prepared statements to prevent SQL Injection.
    *   **XSS Protection**: All user outputs are escaped using `htmlspecialchars()`.
*   **Performance**:
    *   Uses lightweight native PHP without heavy framework overhead.
    *   Database indexing on high-frequency columns (`appointment_date`, `doctor_id`, `status`) to ensure dashboard queries remain fast as data grows.
