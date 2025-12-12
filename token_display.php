<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$date = $_GET['date'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Tokens</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        :root {
            --primary: #10b981;
            --primary-dark: #059669;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
        }

        .nav-link {
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            transform: translateY(-1px);
        }

        .container {
            max-width: 1100px;
            padding: 2rem 1rem;
        }

        .container h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .container > p {
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        .card h3 {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            color: #1e293b;
            font-weight: 700;
            margin: 0;
            border-bottom: 2px solid #cbd5e1;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #1e6e3e;
            font-weight: 700;
            padding: 1.2rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-weight: 500;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: #f0fdf4;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .status {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status:contains("booked") {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #0c4a6e;
        }

        .status:contains("called") {
            background: linear-gradient(135deg, #fef08a 0%, #fde047 100%);
            color: #713f12;
        }

        .status:contains("completed") {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn.small {
            padding: 0.4rem 0.9rem;
            font-size: 0.85rem;
        }

        .call-next {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .call-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        .call-next:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container h2 {
                font-size: 1.8rem;
            }

            .card h3 {
                font-size: 1.1rem;
            }

            .table {
                font-size: 0.9rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.8rem;
            }

            .btn.small {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-ticket-perforated"></i> Today's Tokens</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="nav-link text-white me-3"><i class="bi bi-house"></i> Home</a>
                <a href="patient_booking.php" class="nav-link text-white"><i class="bi bi-calendar-plus"></i> Book Appointment</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
<?php

$stmt = $pdo->prepare("SELECT a.*, u.name as patient_name, d.name as doctor_name
    FROM appointments a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN users d ON d.id = a.doctor_id
    WHERE a.appointment_date = :dt
    ORDER BY d.id, a.token_number ASC");
$stmt->execute(['dt'=>$date]);
$tokens = $stmt->fetchAll();

$grouped = [];
foreach ($tokens as $t) {
    $grouped[$t['doctor_id']]['doctor_name'] = $t['doctor_name'];
    $grouped[$t['doctor_id']]['items'][] = $t;
}
?>

<h2>Token Display — <?php echo e($date); ?></h2>
<p>Use "Call Next" to call the next waiting token for a doctor. (Admin only)</p>

<?php if (empty($grouped)): ?>
  <p>No tokens for this date.</p>
<?php else: ?>
  <?php foreach ($grouped as $did => $g): ?>
    <section class="card">
      <h3>Doctor: <?php echo e($g['doctor_name']); ?></h3>
      <table class="table">
        <thead><tr><th>Token</th><th>Patient</th><th>Status</th><th>Action</th></tr></thead>
        <tbody id="doctor-<?php echo e($did); ?>">
          <?php foreach ($g['items'] as $it): ?>
            <tr data-id="<?php echo e($it['id']); ?>">
              <td>#<?php echo e($it['token_number']); ?></td>
              <td><?php echo e($it['patient_name']); ?></td>
              <td class="status"><?php echo e($it['status']); ?></td>
              <td>
                <?php if ($it['status'] === 'booked' && $_SESSION['role'] === 'admin'): ?>
                  <button class="btn small call-next" data-doctor="<?php echo e($did); ?>">Call Next</button>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('click', function(e){
  if (e.target.matches('.call-next')) {
    const doctorId = e.target.dataset.doctor;
    e.target.disabled = true;
    fetch('api/actions.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({action:'call_next', doctor_id: doctorId, date: '<?php echo e($date); ?>'})
    }).then(r=>r.json()).then(res=>{
      if (res.success) {
        const tbody = document.getElementById('doctor-' + doctorId);
        tbody.innerHTML = '';
        res.items.forEach(it=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>#${it.token_number}</td><td>${it.patient_name}</td><td class="status">${it.status}</td><td>${it.status==='booked' ? '<button class="btn small call-next" data-doctor="'+doctorId+'">Call Next</button>' : '-'}</td>`;
          tbody.appendChild(tr);
        });
      } else {
        alert(res.message || 'No booked tokens to call');
      }
    }).catch(err=>{ console.error(err); alert('AJAX error'); }).finally(()=> e.target.disabled = false);
  }
});
</script>

    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
