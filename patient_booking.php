<?php
require_once __DIR__ . '/includes/db.php';

$stmt = $pdo->query("SELECT id, name FROM users WHERE role='doctor' LIMIT 1");
$doctor = $stmt->fetch();

if (!$doctor) {
    die("No doctor found in the system. Please contact administrator.");
}

$doctor_id = $doctor['id'];
$doctor_name = $doctor['name'];

$errors = [];
$success = '';
$token_number = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic sanitization
    $patient_name = trim($_POST['patient_name'] ?? '');
    $patient_name = strip_tags($patient_name);
    $patient_age = isset($_POST['patient_age']) ? intval($_POST['patient_age']) : 0;
    $patient_contact = trim($_POST['patient_contact'] ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');

    // Server-side validation (non-breaking)
    if ($patient_name === '' || $patient_age <= 0 || $patient_contact === '' || $appointment_date === '' || $appointment_time === '') {
        $errors[] = "All fields are required.";
    }

    // Validate name length
    if (empty($errors) && mb_strlen($patient_name) > 100) {
        $errors[] = "Name is too long (max 100 characters).";
    }

    // Validate age range
    if (empty($errors) && ($patient_age < 1 || $patient_age > 120)) {
        $errors[] = "Please enter a valid age between 1 and 120.";
    }

    // Validate contact number (allow digits, spaces, +, - and parentheses)
    if (empty($errors)) {
        $contact_clean = preg_replace('/[^0-9]/', '', $patient_contact);
        if (strlen($contact_clean) < 7) {
            $errors[] = "Please enter a valid contact number.";
        }
    }

    // Validate appointment time format HH:MM
    if (empty($errors) && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $appointment_time)) {
        $errors[] = "Please provide a valid time in HH:MM format.";
    }

    // Validate appointment date format and not in the past
    if (empty($errors)) {
        $d = DateTime::createFromFormat('Y-m-d', $appointment_date);
        if (!$d || $d->format('Y-m-d') !== $appointment_date) {
            $errors[] = "Invalid appointment date format.";
        } elseif (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
            $errors[] = "Appointment date must not be in the past.";
        }
    }

    if (empty($errors)) {
        // Check unavailable dates
        $check = $pdo->prepare("SELECT id FROM unavailable_dates WHERE doctor_id = :did AND unavailable_date = :dt");
        $check->execute(['did' => $doctor_id, 'dt' => $appointment_date]);
        if ($check->fetch()) {
            $errors[] = "This date is not available for appointments.";
        }
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Prevent duplicate bookings for same contact/time
            $dup = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = :did AND appointment_date = :dt AND appointment_time = :tm AND patient_contact = :contact LIMIT 1");
            $dup->execute(['did' => $doctor_id, 'dt' => $appointment_date, 'tm' => $appointment_time, 'contact' => $patient_contact]);
            if ($dup->fetch()) {
                throw new Exception("You already have an appointment at this time.");
            }

            $stmt = $pdo->prepare("SELECT COALESCE(MAX(token_number),0) + 1 AS next_token FROM appointments WHERE doctor_id = :did AND appointment_date = :dt");
            $stmt->execute(['did' => $doctor_id, 'dt' => $appointment_date]);
            $next = $stmt->fetchColumn();

            $ins = $pdo->prepare("INSERT INTO appointments (doctor_id, patient_name, patient_age, patient_contact, appointment_date, appointment_time, token_number, status, created_at) VALUES (:did, :name, :age, :contact, :dt, :tm, :token, 'booked', NOW())");
            $ins->execute([
                'did' => $doctor_id,
                'name' => $patient_name,
                'age' => $patient_age,
                'contact' => $patient_contact,
                'dt' => $appointment_date,
                'tm' => $appointment_time,
                'token' => $next
            ]);

            $pdo->commit();
            $success = "Appointment booked successfully!";
            $token_number = $next;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

$unavailable_dates = [];
$stmt = $pdo->prepare("SELECT unavailable_date FROM unavailable_dates WHERE doctor_id = :did AND unavailable_date >= CURDATE()");
$stmt->execute(['did' => $doctor_id]);
$unavailable_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

$booked_dates = [];
$stmt = $pdo->prepare("SELECT DISTINCT appointment_date FROM appointments WHERE doctor_id = :did AND appointment_date >= CURDATE() AND status != 'cancelled'");
$stmt->execute(['did' => $doctor_id]);
$booked_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo htmlspecialchars($doctor_name); ?></title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #2563eb; --primary-dark: #1e40af; --accent: #7c3aed; --control-height: 36px; --control-radius: 10px; }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; padding: 0.5rem; color: #e2e8f0; }

        /* Navbar */
        .navbar-custom { background: linear-gradient(135deg, rgba(37,99,235,0.95) 0%, rgba(30,64,175,0.95) 100%); box-shadow: 0 8px 30px rgba(16,24,40,0.08); }
        .navbar-brand, .nav-link { color: white !important; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.2px; }

        .booking-wrapper { max-width: 940px; margin: 0 auto; }

        /* Header hero */
        .header-hero { text-align: center; margin-bottom: 0.6rem; }
        .header-hero h1 { font-size: 1.25rem; font-weight: 800; color: #fff; margin-bottom: 0.1rem; background: linear-gradient(90deg,#fff,#e0e7ff); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .header-hero .doctor-info { font-size: 0.78rem; color: #cbd5e1; margin-bottom: 0.15rem; }
        .header-hero p { color: #cbd5e1; font-size: 0.78rem; }

        /* Card (glassmorphism) */
        .booking-card { background: rgba(15, 23, 42, 0.5); border-radius: 14px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; border: 1px solid rgba(148, 163, 184, 0.1); backdrop-filter: blur(6px); }
        .card-header { background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%); color: white; padding: 0.6rem 0.9rem; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.6rem; }
        .card-body { padding: 0.9rem; }

        /* Calendar */
        .calendar-section { margin-bottom: 0.45rem; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem; }
        .calendar-title { font-size: 0.72rem; font-weight: 700; color: #e2e8f0; }
        .calendar-nav { display: flex; gap: 0.4rem; align-items:center; }
        .calendar-nav-btn { background: white; color: var(--primary); border: none; width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.12s; font-size: 0.7rem; box-shadow: 0 4px 12px rgba(2,6,23,0.06); }
        .calendar-nav-btn:hover { background: var(--primary); color: white; transform: translateY(-2px); }
        .month-year { font-weight: 700; color: #0f172a; min-width: 100px; text-align: center; font-size: 0.78rem; }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; margin-bottom: 0.2rem; background: transparent; }
        .day-header { text-align: center; font-weight: 700; font-size: 0.55rem; color: #94a3b8; padding: 0.12rem 0; text-transform: uppercase; }

        .calendar-day { aspect-ratio: 1/1; display:flex; align-items:center; justify-content:center; border-radius:3px; cursor:pointer; font-weight:700; font-size:0.58rem; transition: all 0.06s ease; border: 0.5px solid rgba(148, 163, 184, 0.2); min-height: 16px; padding:0; margin:0; background: rgba(30, 41, 59, 0.5); color: #e2e8f0; }
        .calendar-day.empty { background: transparent; cursor: default; }
        .calendar-day.past { background: transparent; color: #cbd5e1; cursor: not-allowed; }
        .calendar-day.available { background: linear-gradient(180deg, rgba(30, 41, 59, 0.7), rgba(30, 41, 59, 0.5)); color: #e2e8f0; border: 1px solid rgba(37, 99, 235, 0.3); }
        .calendar-day.available:hover { border-color: var(--primary); transform: scale(1.05); box-shadow: 0 6px 18px rgba(37,99,235,0.08); }
        .calendar-day.has-bookings::after { content:'•'; position:absolute; bottom:2px; font-size:0.6rem; color:#f59e0b; }
        .calendar-day.unavailable { background: linear-gradient(180deg,#fff1f2,#fee2e2); color:#991b1b; border:1px solid #fca5a5; cursor:not-allowed; opacity:0.9; }
        .calendar-day.selected { background: linear-gradient(90deg,var(--primary),var(--accent)); color:white; box-shadow:0 10px 24px rgba(37,99,235,0.14); transform: scale(1.03); }
        .calendar-day.today { border: 1px dashed var(--primary); background: #e6f0ff; color: var(--primary); }

        .legend { display:flex; gap:0.5rem; flex-wrap:wrap; padding:0.45rem; background: transparent; border-radius:6px; }
        .legend-item { display:flex; align-items:center; gap:0.35rem; font-size:0.62rem; color:#475569; }
        .legend-dot { width:8px; height:8px; border-radius:2px; }

        /* Form controls */
        .form-row { display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.6rem; margin-bottom: 0.6rem; }
        .form-group { display:flex; flex-direction:column; gap:0.15rem; }
        .form-label { font-weight:600; color:#cbd5e1; margin-bottom:0.12rem; font-size:0.78rem; display:flex; align-items:center; gap:0.35rem; }

        .form-control, .form-select, input[type="text"], input[type="tel"], input[type="time"], input[type="number"] { border:1.4px solid rgba(148, 163, 184, 0.2); border-radius: var(--control-radius); padding:0.45rem 0.6rem; font-size:0.9rem; background: rgba(30, 41, 59, 0.5); height: var(--control-height); transition: all 0.18s ease; color:#e2e8f0; }
        .form-control::placeholder, .form-select::placeholder, input::placeholder { color: #94a3b8; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 6px 18px rgba(37,99,235,0.08); outline:none; background: rgba(30, 41, 59, 0.7); }

        .submit-btn { background: linear-gradient(90deg, var(--primary), var(--accent)); color: white; border:none; height: var(--control-height); border-radius: 12px; font-weight:700; font-size:0.95rem; width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; cursor:pointer; box-shadow:0 12px 30px rgba(37,99,235,0.12); }
        .submit-btn:hover { transform: translateY(-2px); box-shadow:0 18px 40px rgba(37,99,235,0.18); }

        /* Alerts */
        .alert-error { background: linear-gradient(90deg,#fff1f2,#fee2e2); border:1px solid #fca5a5; border-radius:10px; padding:0.9rem; margin-bottom:1rem; color:#991b1b; }

        .success-box { background: linear-gradient(90deg,#10b981,#059669); color:white; border-radius:12px; padding:1.6rem; text-align:center; box-shadow:0 12px 36px rgba(6,95,70,0.12); }
        .success-box h3 { font-size:1rem; margin-bottom:0.6rem; font-weight:700; }
        .token-display { font-size:2.4rem; font-weight:800; margin:0.6rem 0; }

        @media (max-width: 768px) {
            .header-hero h1 { font-size:1.1rem; }
            .card-body { padding:1rem; }
            .form-row { grid-template-columns: 1fr; }
            .calendar-grid { gap: 4px; }
            .calendar-day { min-height: 38px; font-size: 0.9rem; }
            .day-header { font-size: 0.7rem; padding: 0.35rem 0; }
            .booking-card { border-radius: 12px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-calendar-check"></i> Book Appointment</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="nav-link me-3"><i class="bi bi-house"></i> Home</a>
            </div>
        </div>
    </nav>

    <div class="booking-wrapper mt-4 mb-5">
        <div class="header-hero">
            <h1><i class="bi bi-calendar-check"></i> Book Your Appointment</h1>
            <div class="doctor-info"><i class="bi bi-person-badge"></i> Dr. <?php echo htmlspecialchars($doctor_name); ?></div>
            <p>Select a date, enter your details, and get your token</p>
        </div>

        <div class="booking-card">
            <div class="card-header">
                <i class="bi bi-clipboard-plus"></i> Appointment Form
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php foreach ($errors as $err): ?>
                            <div><?php echo htmlspecialchars($err); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success && $token_number): ?>
                    <div class="success-box">
                        <h3><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?></h3>
                        <div class="token-display">#<?php echo $token_number; ?></div>
                        <p>Save this token number for your appointment reference</p>
                        <a href="patient_booking.php" class="btn-light"><i class="bi bi-plus-circle"></i> Book Another</a>
                    </div>
                <?php else: ?>
                    <form method="POST" id="bookingForm">
                        <!-- Calendar Selection -->
                        <div class="calendar-section">
                            <div class="calendar-header">
                                <h6 class="calendar-title"><i class="bi bi-calendar3"></i> Select Date</h6>
                                <div class="calendar-nav">
                                    <button type="button" class="calendar-nav-btn" onclick="changeMonth(-1)"><i class="bi bi-chevron-left"></i></button>
                                    <span class="month-year" id="monthYear"></span>
                                    <button type="button" class="calendar-nav-btn" onclick="changeMonth(1)"><i class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>
                            <div class="calendar-grid" id="calendar"></div>
                            <div class="legend">
                                <div class="legend-item"><div class="legend-dot" style="background: white; border: 2px solid #e2e8f0;"></div> Available</div>
                                <div class="legend-item"><div class="legend-dot" style="background: #fef3c7; border: 2px solid #fcd34d;"></div> Booked</div>
                                <div class="legend-item"><div class="legend-dot" style="background: #fee2e2; border: 2px solid #fca5a5;"></div> Unavailable</div>
                            </div>
                            <input type="hidden" name="appointment_date" id="selected_date" required>
                        </div>

                        <!-- Patient Details -->
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-person"></i> Full Name</label>
                                <input type="text" name="patient_name" class="form-control" required placeholder="Your full name">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-calendar-event"></i> Age</label>
                                <input type="number" name="patient_age" class="form-control" required min="1" max="120" placeholder="Age">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-telephone"></i> Contact Number</label>
                                <input type="tel" name="patient_contact" class="form-control" required placeholder="+92 300 1234567">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-clock"></i> Preferred Time</label>
                                <input type="time" name="appointment_time" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn"><i class="bi bi-calendar-plus"></i> Book Appointment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const unavailableDates = <?php echo json_encode($unavailable_dates); ?>;
        const bookedDates = <?php echo json_encode($booked_dates); ?>;
        const today = new Date();
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();
        let selectedDate = null;

        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        function updateMonthYear() {
            document.getElementById('monthYear').textContent = monthNames[currentMonth] + ' ' + currentYear;
        }

        function changeMonth(direction) {
            currentMonth += direction;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            else if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            updateMonthYear();
            renderCalendar();
        }

        function renderCalendar() {
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const calendar = document.getElementById('calendar');
            calendar.innerHTML = '';

            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                const header = document.createElement('div');
                header.className = 'day-header';
                header.textContent = day;
                calendar.appendChild(header);
            });

            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'calendar-day empty';
                calendar.appendChild(empty);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(currentYear, currentMonth, day);
                const dayEl = document.createElement('div');
                dayEl.className = 'calendar-day';
                dayEl.textContent = day;

                if (dateObj < today && dateObj.toDateString() !== today.toDateString()) {
                    dayEl.classList.add('past');
                } else {
                if (unavailableDates.includes(dateStr)) {
                        dayEl.classList.add('unavailable');
                    } else {
                        // Even if booked by others, it is available for new tokens
                        dayEl.classList.add('available');
                        if (bookedDates.includes(dateStr)) {
                             // Optional: Visual cue that it has existing appointments, but still clickable
                             dayEl.classList.add('has-bookings');
                        }
                        dayEl.addEventListener('click', () => selectDate(dateStr, dayEl));
                    }
                }

                if (dateObj.toDateString() === today.toDateString()) {
                    dayEl.classList.add('today');
                }

                calendar.appendChild(dayEl);
            }

            const totalCells = calendar.children.length;
            const cellsAfterHeader = totalCells - 7;
            const remainingCells = 7 - (cellsAfterHeader % 7);
            if (remainingCells < 7 && remainingCells > 0) {
                for (let i = 0; i < remainingCells; i++) {
                    const empty = document.createElement('div');
                    empty.className = 'calendar-day empty';
                    calendar.appendChild(empty);
                }
            }
        }

        function selectDate(dateStr, element) {
            document.querySelectorAll('.calendar-day.selected').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            selectedDate = dateStr;
            document.getElementById('selected_date').value = dateStr;
        }

        updateMonthYear();
        renderCalendar();

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!selectedDate) {
                e.preventDefault();
                alert('Please select a date from the calendar');
                return false;
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
