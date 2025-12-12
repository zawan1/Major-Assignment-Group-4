<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_role('admin');

$stmt = $pdo->prepare("SELECT s.*, u.name AS doctor_name FROM slots s JOIN users u ON u.id = s.doctor_id ORDER BY slot_date DESC, start_time");
$stmt->execute();
$slots = $stmt->fetchAll();

$dstmt = $pdo->prepare("SELECT id,name FROM users WHERE role IN ('doctor','admin')");
$dstmt->execute();
$doctors = $dstmt->fetchAll();
?>

<h2>Manage Slots</h2>

<style>
  @import url('assets/css/theme.css');
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

  :root {
    --primary: #7c3aed;
    --primary-dark: #6d28d9;
  }

  h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
    overflow: hidden;
  }

  .card h3 {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    color: #1e293b;
    font-weight: 700;
    margin: 0;
    border-bottom: 2px solid #cbd5e1;
  }

  .card form {
    padding: 2rem;
    display: grid;
    gap: 1.5rem;
  }

  .card label {
    display: block;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
  }

  .card input,
  .card select {
    width: 100%;
    padding: 0.85rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    color: #0f172a;
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  .card input:focus,
  .card select:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
  }

  .card .btn {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    padding: 0.85rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.2);
  }

  .card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(124, 58, 237, 0.3);
  }

  section {
    margin-bottom: 3rem;
  }

  section h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #1e293b;
  }

  section > p {
    color: #64748b;
    font-size: 1rem;
  }

  .table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
  }

  .table thead th {
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    color: #5b21b6;
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
  }

  .table tbody tr {
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    background-color: #f5f3ff;
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  .btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
  }

  .btn.small {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
  }

  .btn.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
  }

  .btn.danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
  }

  @media (max-width: 768px) {
    h2 {
      font-size: 1.5rem;
    }

    .card form {
      gap: 1rem;
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
  <h3>Create New Slot</h3>
  <form id="slotForm" method="post" action="api/actions.php" novalidate>
    <input type="hidden" name="action" value="create_slot">
    <label>Doctor</label>
    <select name="doctor_id" required>
      <?php foreach ($doctors as $d): ?>
        <option value="<?php echo e($d['id']); ?>"><?php echo e($d['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label>Date</label>
    <input type="date" name="slot_date" required>
    <label>Start Time</label>
    <input type="time" name="start_time" required>
    <label>End Time</label>
    <input type="time" name="end_time" required>
    <label>Capacity (optional)</label>
    <input type="number" name="capacity" min="1" placeholder="e.g., 10">
    <button class="btn">Create Slot</button>
  </form>
</section>

<section>
  <h3>Existing Slots</h3>
  <?php if (count($slots) === 0): ?>
    <p>No slots defined yet.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>ID</th><th>Doctor</th><th>Date</th><th>Start</th><th>End</th><th>Capacity</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($slots as $s): ?>
          <tr>
            <td><?php echo e($s['id']); ?></td>
            <td><?php echo e($s['doctor_name']); ?></td>
            <td><?php echo e($s['slot_date']); ?></td>
            <td><?php echo e($s['start_time']); ?></td>
            <td><?php echo e($s['end_time']); ?></td>
            <td><?php echo e($s['capacity']); ?></td>
            <td>
              <form method="post" action="api/actions.php" style="display:inline;">
                <input type="hidden" name="action" value="delete_slot">
                <input type="hidden" name="id" value="<?php echo e($s['id']); ?>">
                <button class="btn small danger" onclick="return confirm('Delete this slot?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
