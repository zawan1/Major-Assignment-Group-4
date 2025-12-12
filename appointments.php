<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Only allow admin and doctor access
if (!is_logged_in() || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'doctor')) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$filter = $_GET['filter'] ?? '';
$date = $_GET['date'] ?? '';
$doctor = $_GET['doctor'] ?? '';

$where = [];
$params = [];

if ($date !== '') { $where[] = "a.appointment_date = :date"; $params['date'] = $date; }
if ($doctor !== '') { $where[] = "a.doctor_id = :did"; $params['did'] = $doctor; }

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT a.*, a.patient_name, d.name AS doctor_name
        FROM appointments a
        LEFT JOIN users d ON d.id = a.doctor_id
        $where_sql
        ORDER BY a.appointment_date DESC, a.token_number ASC
        LIMIT 500";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$doctors = $pdo->prepare("SELECT id,name FROM users WHERE role='doctor' OR role='admin'");
$doctors->execute();
$docList = $doctors->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body { background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

      :root { 
        --primary: #2563eb; 
        --primary-dark: #1d4ed8; 
        --control-height: 44px; 
        --control-radius: 10px; 
      }

      /* Navbar Styling */
      .navbar {
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      }

      .navbar-brand {
        font-weight: 700;
        font-size: 1.3rem;
      }

      .nav-link {
        transition: all 0.3s ease;
        font-weight: 500;
      }

      .nav-link:hover {
        transform: translateY(-1px);
      }

      /* Container */
      .container {
        max-width: 1200px;
      }

      .container h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }

      /* Filter Controls */
      .form-inline {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        margin: 1.5rem 0 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: var(--control-radius);
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.08);
      }

      .form-inline label {
        margin: 0;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
      }

      .filter-control {
        padding: 0.6rem 1rem;
        border-radius: var(--control-radius);
        border: 2px solid #e2e8f0;
        background: white;
        height: var(--control-height);
        min-width: 220px;
        font-size: 0.95rem;
        color: #0f172a;
        transition: all 0.3s ease;
      }

      .filter-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
      }

      .filter-btn {
        height: var(--control-height);
        padding: 0 1.5rem;
        border-radius: var(--control-radius);
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.2);
        font-weight: 600;
        transition: all 0.3s ease;
      }

      .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(37, 99, 235, 0.3);
      }

      /* Table Styling */
      .table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
      }

      .table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        color: #1e293b;
        font-weight: 700;
        padding: 1.2rem;
        border-bottom: 2px solid #cbd5e1;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
      }

      .table tbody td {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
      }

      .table tbody tr {
        transition: all 0.2s ease;
      }

      .table tbody tr:hover {
        background-color: #f8fafc;
      }

      .table tbody tr:last-child td {
        border-bottom: none;
      }

      /* Status Badge */
      .badge {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
      }

      .badge-booked {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #0c4a6e;
      }

      .badge-called {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
      }

      .badge-completed {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
      }

      .badge-cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
      }

      /* Action Button */
      .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .btn-danger:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
      }

      /* Responsive */
      @media (max-width: 768px) {
        .form-inline {
          flex-direction: column;
          gap: 0.8rem;
        }

        .filter-control {
          width: 100%;
          min-width: 100%;
        }

        .filter-btn {
          width: 100%;
        }

        .table {
          font-size: 0.9rem;
        }

        .table thead th,
        .table tbody td {
          padding: 0.8rem;
        }
      }
    </style>
</head>
<body>
<?php if ($role === 'doctor'): ?>
    <nav class="navbar navbar-expand-lg navbar-light" style="background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container-fluid">
            <a class="navbar-brand" href="doctor_dashboard.php"><i class="bi bi-heart-pulse"></i> Doctor Portal</a>
            <div class="d-flex align-items-center">
                <a href="doctor_dashboard.php" class="nav-link me-3"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="appointments.php" class="nav-link me-3"><i class="bi bi-calendar-check"></i> Appointments</a>
                <a href="token_display.php" class="nav-link me-3"><i class="bi bi-ticket-perforated"></i> Today Tokens</a>
                <a href="logout.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>
<?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="assistant_dashboard.php"><i class="bi bi-clipboard-pulse"></i> Assistant Dashboard</a>
            <div class="d-flex align-items-center">
                <a href="assistant_dashboard.php" class="nav-link me-3 text-white"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="appointments.php" class="nav-link me-3 text-white"><i class="bi bi-calendar-check"></i> Appointments</a>
                <a href="token_display.php" class="nav-link me-3 text-white"><i class="bi bi-ticket-perforated"></i> Today Tokens</a>
                <a href="manage_slots.php" class="nav-link me-3 text-white"><i class="bi bi-clock-history"></i> Manage Slots</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>
<?php endif; ?>

<div class="container mt-4">
<h2>Appointments</h2>

<form method="get" class="form-inline" aria-label="Filter appointments">
  <label for="filter-date">Date</label>
  <input id="filter-date" class="filter-control" type="date" name="date" value="<?php echo e($date); ?>">
  <label for="filter-doctor">Doctor</label>
  <select id="filter-doctor" class="filter-control" name="doctor">
    <option value="">Any</option>
    <?php foreach ($docList as $d): ?>
      <option value="<?php echo e($d['id']); ?>" <?php if($doctor == $d['id']) echo 'selected'; ?>><?php echo e($d['name']); ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn filter-btn" type="submit"><i class="bi bi-funnel"></i> Filter</button>
</form>

<table class="table">
  <thead>
    <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Token</th><th>Status</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo e($r['id']); ?></td>
        <td><?php echo e($r['patient_name']); ?></td>
        <td><?php echo e($r['doctor_name']); ?></td>
        <td><?php echo e($r['appointment_date']); ?></td>
        <td><?php echo e($r['appointment_time']); ?></td>
        <td><?php echo e($r['token_number']); ?></td>
        <td><?php echo e($r['status']); ?></td>
        <td>
          <?php if ($role === 'admin'): ?>
            <form method="post" action="api/actions.php" style="display:inline;">
              <input type="hidden" name="action" value="delete_appointment">
              <input type="hidden" name="id" value="<?php echo e($r['id']); ?>">
              <button class="btn small danger" onclick="return confirm('Delete appointment?')">Delete</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
