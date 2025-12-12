<?php
// api/actions.php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function json($arr){ header('Content-Type: application/json'); echo json_encode($arr); exit; }

$action = $_POST['action'] ?? '';

if ($action === 'create_slot') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../login.php');
        exit;
    }
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $slot_date = $_POST['slot_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $capacity = intval($_POST['capacity'] ?? 0) ?: null;

    if (!$doctor_id || !$slot_date || !$start_time || !$end_time) {
        $_SESSION['flash'] = "All fields required for slot.";
        header('Location: ../manage_slots.php');
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO slots (doctor_id,slot_date,start_time,end_time,capacity,created_at) VALUES (:did,:dt,:st,:et,:cap,NOW())");
    $stmt->execute(['did'=>$doctor_id,'dt'=>$slot_date,'st'=>$start_time,'et'=>$end_time,'cap'=>$capacity]);
    header('Location: ../manage_slots.php');
    exit;
}

if ($action === 'delete_slot') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM slots WHERE id = :id");
        $stmt->execute(['id'=>$id]);
    }
    header('Location: ../manage_slots.php');
    exit;
}

if ($action === 'get_slots') {
    header('Content-Type: application/json');
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    if (!$doctor_id || !$date) json([]);
    $stmt = $pdo->prepare("SELECT id,slot_date, start_time, end_time, capacity FROM slots WHERE doctor_id = :did AND slot_date = :dt ORDER BY start_time");
    $stmt->execute(['did'=>$doctor_id,'dt'=>$date]);
    $rows = $stmt->fetchAll();
    echo json_encode($rows); exit;
}

if ($action === 'call_next') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') json(['success'=>false,'message'=>'Unauthorized']);

    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $date = $_POST['date'] ?? date('Y-m-d');

    if (!$doctor_id) json(['success'=>false,'message'=>'Missing doctor id']);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = :did AND appointment_date = :dt AND status = 'booked' ORDER BY token_number ASC LIMIT 1 FOR UPDATE");
        $stmt->execute(['did'=>$doctor_id,'dt'=>$date]);
        $row = $stmt->fetch();
        if (!$row) {
            $pdo->commit();
            $list = $pdo->prepare("SELECT a.*, u.name AS patient_name FROM appointments a JOIN users u ON u.id = a.user_id WHERE a.doctor_id = :did AND a.appointment_date = :dt ORDER BY a.token_number ASC");
            $list->execute(['did'=>$doctor_id,'dt'=>$date]);
            $items = $list->fetchAll();
            json(['success'=>false,'message'=>'No booked tokens to call', 'items'=>$items]);
        } else {
            $aid = $row['id'];
            $upd = $pdo->prepare("UPDATE appointments SET status = 'called' WHERE id = :id");
            $upd->execute(['id'=>$aid]);

            $list = $pdo->prepare("SELECT a.*, u.name AS patient_name FROM appointments a JOIN users u ON u.id = a.user_id WHERE a.doctor_id = :did AND a.appointment_date = :dt ORDER BY a.token_number ASC");
            $list->execute(['did'=>$doctor_id,'dt'=>$date]);
            $items = $list->fetchAll();

            $pdo->commit();
            json(['success'=>true,'items'=>$items]);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        json(['success'=>false,'message'=>$e->getMessage()]);
    }
}

if ($action === 'cancel_appointment') {
    if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT user_id FROM appointments WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    if (!$row) { header('Location: ../appointments.php'); exit; }
    if ($_SESSION['role'] !== 'admin' && $row['user_id'] != $_SESSION['user_id']) { echo "Unauthorized"; exit; }
    $upd = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id");
    $upd->execute(['id'=>$id]);
    header('Location: ../appointments.php');
    exit;
}

if ($action === 'delete_appointment') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
        $stmt->execute(['id'=>$id]);
    }
    header('Location: ../appointments.php');
    exit;
}

if ($action === 'add_unavailable_date') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $unavailable_date = $_POST['unavailable_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    
    if (!$doctor_id || !$unavailable_date) {
        $_SESSION['flash'] = "Doctor and date are required.";
        header('Location: ../assistant_dashboard.php?doctor_id=' . $doctor_id);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO unavailable_dates (doctor_id, unavailable_date, reason) VALUES (:did, :dt, :reason)");
        $stmt->execute(['did' => $doctor_id, 'dt' => $unavailable_date, 'reason' => $reason ?: null]);
        $_SESSION['flash'] = "Unavailable date added successfully.";
    } catch (PDOException $e) {
        $_SESSION['flash'] = "Date already marked as unavailable.";
    }
    header('Location: ../assistant_dashboard.php?doctor_id=' . $doctor_id);
    exit;
}

if ($action === 'remove_unavailable_date') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("SELECT doctor_id FROM unavailable_dates WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt = $pdo->prepare("DELETE FROM unavailable_dates WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash'] = "Unavailable date removed.";
            header('Location: ../assistant_dashboard.php?doctor_id=' . $row['doctor_id']);
        } else {
            header('Location: ../assistant_dashboard.php');
        }
    } else {
        header('Location: ../assistant_dashboard.php');
    }
    exit;
}

if ($action === 'update_appointment_status') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    if (!$id || !in_array($status, ['booked', 'called', 'completed', 'cancelled'])) {
        header('Location: ../assistant_dashboard.php');
        exit;
    }
    
    // If completing, allow fee update
    $fee = null;
    if ($status === 'completed' && isset($_POST['fee'])) {
        $fee = floatval($_POST['fee']);
    }
    
    if ($fee !== null) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = :status, fee = :fee WHERE id = :id");
        $stmt->execute(['status' => $status, 'fee' => $fee, 'id' => $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE appointments SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
    
    // Get doctor_id for redirect
    $stmt = $pdo->prepare("SELECT doctor_id FROM appointments WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        header('Location: ../assistant_dashboard.php?doctor_id=' . $row['doctor_id']);
    } else {
        header('Location: ../assistant_dashboard.php');
    }
    exit;
}

if (php_sapi_name() !== 'cli') {
    header('Location: ../index.php');
    exit;
}
