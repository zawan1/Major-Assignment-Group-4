<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_role('admin'); // Using admin role for assistant

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
if (!$doctor_id) {
    $stmt = $pdo->query("SELECT id FROM users WHERE role='doctor' LIMIT 1");
    $doctor = $stmt->fetch();
    $doctor_id = $doctor ? $doctor['id'] : null;
}

// Get today's date
$today = date('Y-m-d');

// Fetch today's appointments
$todayAppointments = [];
if ($doctor_id) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE doctor_id = :did AND appointment_date = :dt AND status != 'cancelled' ORDER BY token_number ASC");
    $stmt->execute(['did' => $doctor_id, 'dt' => $today]);
    $todayAppointments = $stmt->fetchAll();
}

// Fetch done appointments (completed)
$doneAppointments = [];
if ($doctor_id) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE doctor_id = :did AND status = 'completed' ORDER BY appointment_date DESC, token_number ASC LIMIT 50");
    $stmt->execute(['did' => $doctor_id]);
    $doneAppointments = $stmt->fetchAll();
}

// Fetch future appointments
$futureAppointments = [];
if ($doctor_id) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE doctor_id = :did AND appointment_date > :dt AND status = 'booked' ORDER BY appointment_date ASC, token_number ASC");
    $stmt->execute(['did' => $doctor_id, 'dt' => $today]);
    $futureAppointments = $stmt->fetchAll();
}

// Fetch unavailable dates
$unavailableDates = [];
if ($doctor_id) {
    $stmt = $pdo->prepare("SELECT * FROM unavailable_dates WHERE doctor_id = :did AND unavailable_date >= CURDATE() ORDER BY unavailable_date ASC");
    $stmt->execute(['did' => $doctor_id]);
    $unavailableDates = $stmt->fetchAll();
}

// Income calculations
$dailyIncome = 0;
$weeklyIncome = 0;
$monthlyIncome = 0;

if ($doctor_id) {
    // Daily income
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM appointments WHERE doctor_id = :did AND appointment_date = :dt AND status = 'completed'");
    $stmt->execute(['did' => $doctor_id, 'dt' => $today]);
    $dailyIncome = $stmt->fetchColumn();

    // Weekly income
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM appointments WHERE doctor_id = :did AND appointment_date >= :weekStart AND status = 'completed'");
    $stmt->execute(['did' => $doctor_id, 'weekStart' => $weekStart]);
    $weeklyIncome = $stmt->fetchColumn();

    // Monthly income
    $monthStart = date('Y-m-01');
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM appointments WHERE doctor_id = :did AND appointment_date >= :monthStart AND status = 'completed'");
    $stmt->execute(['did' => $doctor_id, 'monthStart' => $monthStart]);
    $monthlyIncome = $stmt->fetchColumn();
}

// Get income data for charts (last 7 days, last 12 months)
$dailyIncomeData = [];
$monthlyIncomeData = [];

if ($doctor_id) {
    // Last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM appointments WHERE doctor_id = :did AND appointment_date = :dt AND status = 'completed'");
        $stmt->execute(['did' => $doctor_id, 'dt' => $date]);
        $dailyIncomeData[] = [
            'date' => date('M d', strtotime($date)),
            'income' => floatval($stmt->fetchColumn())
        ];
    }

    // Last 12 months
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM appointments WHERE doctor_id = :did AND DATE_FORMAT(appointment_date, '%Y-%m') = :month AND status = 'completed'");
        $stmt->execute(['did' => $doctor_id, 'month' => $month]);
        $monthlyIncomeData[] = [
            'month' => date('M Y', strtotime("$month-01")),
            'income' => floatval($stmt->fetchColumn())
        ];
    }
}

// Get patient statistics
$totalPatients = 0;
$todayPatients = count($todayAppointments);
$patientAgeGroups = ['0-18' => 0, '19-35' => 0, '36-50' => 0, '51+' => 0];

if ($doctor_id) {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT patient_contact) FROM appointments WHERE doctor_id = :did");
    $stmt->execute(['did' => $doctor_id]);
    $totalPatients = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT patient_age FROM appointments WHERE doctor_id = :did AND patient_age IS NOT NULL");
    $stmt->execute(['did' => $doctor_id]);
    $ages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($ages as $age) {
        if ($age <= 18) $patientAgeGroups['0-18']++;
        elseif ($age <= 35) $patientAgeGroups['19-35']++;
        elseif ($age <= 50) $patientAgeGroups['36-50']++;
        else $patientAgeGroups['51+']++;
    }
}

// Fetch doctors list
$doctors = $pdo->query("SELECT id, name FROM users WHERE role='doctor'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Dashboard - Doctor Appointment System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
        }

        body {
            background: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .dashboard-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card.income-daily { border-left-color: var(--success); }
        .stat-card.income-weekly { border-left-color: var(--info); }
        .stat-card.income-monthly { border-left-color: var(--primary); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-custom {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
            font-weight: 600;
        }

        .table-custom {
            margin: 0;
        }

        .table-custom thead {
            background: var(--bg-light);
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }

        .unavailable-date-item {
            background: #fee2e2;
            border-left: 4px solid var(--danger);
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="assistant_dashboard.php"><i class="bi bi-clipboard-pulse"></i> Assistant Dashboard</a>
            <div class="d-flex align-items-center">
                <a href="appointments.php" class="nav-link me-3 text-white"><i class="bi bi-calendar-check"></i> Appointments</a>
                <a href="token_display.php" class="nav-link me-3 text-white"><i class="bi bi-ticket-perforated"></i> Today Tokens</a>
                <a href="manage_slots.php" class="nav-link me-3 text-white"><i class="bi bi-clock-history"></i> Manage Slots</a>
                <span class="navbar-text me-3 text-white">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Assistant'); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
                    <p class="text-muted mb-0">Manage appointments and view analytics</p>
                </div>
                <div class="col-md-6 text-end">
                    <select class="form-select" id="doctorSelect" onchange="window.location.href='?doctor_id='+this.value">
                        <?php foreach ($doctors as $doc): ?>
                            <option value="<?php echo $doc['id']; ?>" <?php echo ($doctor_id == $doc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doc['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Income Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card income-daily">
                    <div class="stat-label">Daily Income</div>
                    <div class="stat-value">Rs. <?php echo number_format($dailyIncome, 2); ?></div>
                    <small class="text-muted"><i class="bi bi-calendar-day"></i> Today</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card income-weekly">
                    <div class="stat-label">Weekly Income</div>
                    <div class="stat-value">Rs. <?php echo number_format($weeklyIncome, 2); ?></div>
                    <small class="text-muted"><i class="bi bi-calendar-week"></i> This Week</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card income-monthly">
                    <div class="stat-label">Monthly Income</div>
                    <div class="stat-value">Rs. <?php echo number_format($monthlyIncome, 2); ?></div>
                    <small class="text-muted"><i class="bi bi-calendar-month"></i> This Month</small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Today's Appointments -->
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-calendar-check"></i> Today's Appointments (<?php echo count($todayAppointments); ?>)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-custom">
                                <thead>
                                    <tr>
                                        <th>Token</th>
                                        <th>Patient</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($todayAppointments)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No appointments today</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($todayAppointments as $apt): ?>
                                            <tr>
                                                <td><strong>#<?php echo $apt['token_number']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                                <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $apt['status'] == 'completed' ? 'success' : ($apt['status'] == 'called' ? 'warning' : 'primary'); ?> badge-custom">
                                                        <?php echo ucfirst($apt['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($apt['status'] == 'booked'): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="updateStatus(<?php echo $apt['id']; ?>, 'called')">Call</button>
                                                    <?php elseif ($apt['status'] == 'called'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="completeAppointment(<?php echo $apt['id']; ?>)">Complete</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future Appointments -->
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-calendar-event"></i> Future Appointments (<?php echo count($futureAppointments); ?>)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-custom">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Token</th>
                                        <th>Patient</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($futureAppointments)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No future appointments</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($futureAppointments as $apt): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                                                <td><strong>#<?php echo $apt['token_number']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                                <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Done Appointments -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-check-circle"></i> Completed Appointments (Last 50)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-custom">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Token</th>
                                        <th>Patient</th>
                                        <th>Age</th>
                                        <th>Contact</th>
                                        <th>Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($doneAppointments)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No completed appointments</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($doneAppointments as $apt): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                                                <td><strong>#<?php echo $apt['token_number']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                                <td><?php echo $apt['patient_age'] ?: '-'; ?></td>
                                                <td><?php echo htmlspecialchars($apt['patient_contact']); ?></td>
                                                <td>Rs. <?php echo number_format($apt['fee'] ?: 0, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-graph-up"></i> Daily Income (Last 7 Days)
                    </div>
                    <div class="chart-container">
                        <canvas id="dailyIncomeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-pie-chart"></i> Patient Age Distribution
                    </div>
                    <div class="chart-container">
                        <canvas id="ageDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-graph-up-arrow"></i> Monthly Income (Last 12 Months)
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyIncomeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-calendar-x"></i> Manage Unavailable Dates
                    </div>
                    <div class="card-body">
                        <form method="POST" action="api/actions.php" class="mb-3">
                            <input type="hidden" name="action" value="add_unavailable_date">
                            <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" name="unavailable_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="reason" class="form-control" placeholder="Reason (optional)">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-danger btn-custom w-100">Add</button>
                                </div>
                            </div>
                        </form>
                        <div>
                            <?php if (empty($unavailableDates)): ?>
                                <p class="text-muted">No unavailable dates set</p>
                            <?php else: ?>
                                <?php foreach ($unavailableDates as $ud): ?>
                                    <div class="unavailable-date-item">
                                        <div>
                                            <strong><?php echo date('M d, Y', strtotime($ud['unavailable_date'])); ?></strong>
                                            <?php if ($ud['reason']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($ud['reason']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <form method="POST" action="api/actions.php" style="display:inline;">
                                            <input type="hidden" name="action" value="remove_unavailable_date">
                                            <input type="hidden" name="id" value="<?php echo $ud['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Daily Income Chart
        const dailyCtx = document.getElementById('dailyIncomeChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dailyIncomeData, 'date')); ?>,
                datasets: [{
                    label: 'Income (Rs.)',
                    data: <?php echo json_encode(array_column($dailyIncomeData, 'income')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Age Distribution Chart
        const ageCtx = document.getElementById('ageDistributionChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'doughnut',
            data: {
                labels: ['0-18', '19-35', '36-50', '51+'],
                datasets: [{
                    data: <?php echo json_encode(array_values($patientAgeGroups)); ?>,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Monthly Income Chart
        const monthlyCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthlyIncomeData, 'month')); ?>,
                datasets: [{
                    label: 'Income (Rs.)',
                    data: <?php echo json_encode(array_column($monthlyIncomeData, 'income')); ?>,
                    backgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        function updateStatus(appointmentId, status) {
            if (confirm('Update appointment status?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'api/actions.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_appointment_status';
                form.appendChild(actionInput);
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = appointmentId;
                form.appendChild(idInput);
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = status;
                form.appendChild(statusInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function completeAppointment(appointmentId) {
            const fee = prompt('Enter appointment fee (Rs.):', '500');
            if (fee !== null && fee !== '') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'api/actions.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_appointment_status';
                form.appendChild(actionInput);
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = appointmentId;
                form.appendChild(idInput);
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'completed';
                form.appendChild(statusInput);
                
                const feeInput = document.createElement('input');
                feeInput.type = 'hidden';
                feeInput.name = 'fee';
                feeInput.value = fee;
                form.appendChild(feeInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
