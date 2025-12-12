-- Database Name: appointment_system

-- 1. Users Table (Doctors, Admins/Assistants, Patients)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL DEFAULT 'patient',
    password VARCHAR(255), -- Storing plain text as per current implementation (NOT RECOMMENDED for production)
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Appointments Table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    patient_age INT,
    patient_contact VARCHAR(20),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    token_number INT NOT NULL,
    status ENUM('booked', 'called', 'completed', 'cancelled') DEFAULT 'booked',
    fee DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT, -- Nullable, linking to users table if patient is registered
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 3. Unavailable Dates Table (for Doctors)
CREATE TABLE IF NOT EXISTS unavailable_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    unavailable_date DATE NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Slots Table (for predefined time slots)
CREATE TABLE IF NOT EXISTS slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    capacity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- DEFAULT DATA INSERTION
-- Insert Admin (Assistant)
INSERT INTO users (name, role, password) VALUES ('Assistant', 'admin', 'admin123');

-- Insert Doctor
INSERT INTO users (name, role, password) VALUES ('Dr. Smith', 'doctor', 'doctor123');
