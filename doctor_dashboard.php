<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_role('doctor');

$doctor_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Handle Call and Complete button actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');
    
    if ($appointment_id > 0 && in_array($action, ['call', 'complete'])) {
        $new_status = ($action === 'call') ? 'called' : 'completed';
        
        $stmt = $pdo->prepare("UPDATE appointments SET status = :status WHERE id = :id AND doctor_id = :did");
        $result = $stmt->execute(['status' => $new_status, 'id' => $appointment_id, 'did' => $doctor_id]);
        
        if ($result) {
            $_SESSION['success_message'] = ucfirst($action) . ' successful!';
        }
    }
    
    header('Location: doctor_dashboard.php');
    exit;
}

// Fetch today's appointments
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE doctor_id = :did AND appointment_date = :dt AND status != 'cancelled' ORDER BY token_number ASC");
$stmt->execute(['did' => $doctor_id, 'dt' => $today]);
$todayAppointments = $stmt->fetchAll();

// Get doctor name
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = :id");
$stmt->execute(['id' => $doctor_id]);
$doctor = $stmt->fetch();
$doctorName = $doctor ? $doctor['name'] : 'Doctor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Today's Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --success: #10b981;
            --warning: #f59e0b;
            --bg-light: #f8fafc;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-custom .navbar-brand {
            color: var(--primary) !important;
            font-weight: 700;
        }

        /* Logout button custom attractive color */
        .logout-btn {
            background: linear-gradient(90deg, #ff7a18 0%, #ffb347 100%);
            color: white !important;
            border: none !important;
            box-shadow: 0 6px 18px rgba(255,122,24,0.18);
        }
        .logout-btn:hover, .logout-btn:focus {
            background: linear-gradient(90deg, #ff8b2a 0%, #ffc46a 100%);
            color: white !important;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .welcome-card h1 {
            color: var(--primary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .patients-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem 2rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .patient-item {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }

        .patient-item:hover {
            background: var(--bg-light);
        }

        .patient-item:last-child {
            border-bottom: none;
        }

        .patient-info {
            flex: 1;
        }

        .patient-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .patient-details {
            display: flex;
            gap: 2rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .token-badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            font-size: 1.5rem;
            font-weight: 700;
            min-width: 100px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .status-booked {
            background: #dbeafe;
            color: var(--primary);
        }

        .status-called {
            background: #fef3c7;
            color: var(--warning);
        }

        .status-completed {
            background: #d1fae5;
            color: var(--success);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stat-box .label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-box .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-call, .btn-complete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-call {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(245, 158, 11, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-complete {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-call:disabled, .btn-complete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .success-message {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #10b981;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Inline Status Update Icons */
        .status-icon {
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 0.5rem;
        }

        .status-icon.call-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 2px solid #f59e0b;
        }

        .status-icon.call-icon:hover {
            background: #f59e0b;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        .status-icon.complete-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 2px solid #10b981;
        }

        .status-icon.complete-icon:hover {
            background: #10b981;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .status-icon:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .inline-status-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f59e0b;
            border-radius: 50%;
            border-top: 2px solid transparent;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        function updateStatus(appointmentId, action, element) {
            const btn = element;
            btn.disabled = true;
            btn.innerHTML = '<span class="status-loading"></span>';

            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'appointment_id=' + appointmentId + '&action=' + action
            })
            .then(response => {
                if (response.ok) {
                    // Reload the page to refresh data
                    location.reload();
                } else {
                    btn.disabled = false;
                    alert('Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                alert('Network error');
            });
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="doctor_dashboard.php"><i class="bi bi-heart-pulse"></i> Doctor Portal</a>
            <div class="d-flex align-items-center">
                <a href="appointments.php" class="nav-link me-3"><i class="bi bi-calendar-check"></i> Appointments</a>
                <a href="token_display.php" class="nav-link me-3"><i class="bi bi-ticket-perforated"></i> Today Tokens</a>
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($doctorName); ?></span>
                <a href="logout.php" class="btn btn-sm logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="welcome-card">
            <h1><i class="bi bi-calendar-check"></i> Today's Schedule</h1>
            <p><?php echo date('l, F d, Y'); ?></p>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <div class="label">Total Patients</div>
                <div class="number"><?php echo count($todayAppointments); ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Pending</div>
                <div class="number"><?php echo count(array_filter($todayAppointments, fn($a) => $a['status'] == 'booked')); ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Called</div>
                <div class="number"><?php echo count(array_filter($todayAppointments, fn($a) => $a['status'] == 'called')); ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Completed</div>
                <div class="number"><?php echo count(array_filter($todayAppointments, fn($a) => $a['status'] == 'completed')); ?></div>
            </div>
        </div>

        <div class="patients-card">
            <div class="card-header-custom">
                <i class="bi bi-people"></i> Patient List
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayAppointments)): ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h3>No appointments scheduled for today</h3>
                        <p>All clear! Enjoy your day.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todayAppointments as $apt): ?>
                        <div class="patient-item">
                            <div class="patient-info">
                                <div class="patient-name">
                                    <?php echo htmlspecialchars($apt['patient_name']); ?>
                                    <span class="status-badge status-<?php echo $apt['status']; ?> ms-2">
                                        <?php echo ucfirst($apt['status']); ?>
                                    </span>
                                </div>
                                <div class="patient-details">
                                    <span><i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></span>
                                    <?php if ($apt['patient_age']): ?>
                                        <span><i class="bi bi-person"></i> Age: <?php echo $apt['patient_age']; ?></span>
                                    <?php endif; ?>
                                    <span><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($apt['patient_contact']); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <div class="token-badge">
                                    #<?php echo $apt['token_number']; ?>
                                </div>
                                <div class="inline-status-container">
                                    <?php if ($apt['status'] === 'booked'): ?>
                                        <button class="status-icon call-icon" onclick="updateStatus(<?php echo $apt['id']; ?>, 'call', this)" title="Click to call patient">
                                            <i class="bi bi-telephone-fill"></i>
                                        </button>
                                    <?php elseif ($apt['status'] === 'called'): ?>
                                        <button class="status-icon complete-icon" onclick="updateStatus(<?php echo $apt['id']; ?>, 'complete', this)" title="Click to mark complete">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </button>
                                    <?php elseif ($apt['status'] === 'completed'): ?>
                                        <span class="status-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 2px solid #10b981; cursor: default;">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
