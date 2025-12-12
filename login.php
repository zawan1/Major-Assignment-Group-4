<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = "Username and password are required.";
    } else {
        // Check username and find corresponding user
        $user = null;
        
        if (strtolower($username) === 'doctor') {
            // Find doctor user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'doctor' LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch();
        } elseif (strtolower($username) === 'assistant') {
            // Find assistant (admin) user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch();
        } else {
            $errors[] = "Invalid username. Use 'doctor' or 'assistant'.";
        }

        if ($user) {
            // Plain text password comparison (no hashing)
            if (empty($user['password'])) {
                $errors[] = "Password not set in database. Please run reset_passwords.php";
            } elseif ($password === $user['password']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: assistant_dashboard.php');
                } elseif ($user['role'] === 'doctor') {
                    header('Location: doctor_dashboard.php');
                }
                exit;
            } else {
                $errors[] = "Invalid password. Please use: password123";
            }
        } else {
            $errors[] = "User not found. Please check your username.";
        }
    }
}
?>

<!-- Navbar Section -->
<nav class="navbar">
  <div class="navbar-container">
    <a href="index.php" class="navbar-logo">Effortless Appointments</a>
    <ul class="navbar-links">
    </ul>
  </div>
</nav>

<!-- Login Section -->
<div class="login-container">
  <div class="login-form">
    <h2>Staff Login</h2>
    <p class="subtitle">For doctors and assistants only</p>

    <?php if ($errors): ?>
      <div class="error-messages">
        <?php foreach ($errors as $err) echo "<div>" . htmlspecialchars($err) . "</div>"; ?>
      </div>
    <?php endif; ?>

    <form id="loginForm" method="post" action="login.php" novalidate>
      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required placeholder="doctor or assistant" />
        <small style="color: #666; font-size: 0.85rem; margin-top: 5px; display: block;">Enter "doctor" or "assistant"</small>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required />
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <p class="register-link">For patients: <a href="patient_booking.php">Book appointment here</a></p>
  </div>
</div>

<script>
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = this.username.value.trim().toLowerCase();
    const password = this.password.value.trim();
    
    if (!username || !password) {
      alert('Please enter both username and password.');
      e.preventDefault();
      return false;
    }
    
    if (username !== 'doctor' && username !== 'assistant') {
      alert('Username must be "doctor" or "assistant"');
      e.preventDefault();
      return false;
    }
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<style>
  /* Body and page container */
  body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    overflow: hidden;
    animation: fadeInPage 1s ease-out;
  }

  body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.1) 0%, transparent 50%),
      radial-gradient(circle at 80% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
  }

  /* Navbar */
  .navbar {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    padding: 15px 0;
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
  }

  .navbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
  }

  .navbar-logo {
    color: #fff;
    font-size: 1.8rem;
    font-weight: 800;
    text-decoration: none;
    background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .navbar-links {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
  }

  .navbar-links li {
    margin-left: 20px;
  }

  .navbar-links a {
    color: #cbd5e1;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    transition: color 0.3s ease;
  }

  .navbar-links a:hover {
    color: #3b82f6;
  }

  /* Login Form */
  .login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    min-height: 100vh;
    margin-top: 0;
    animation: slideUpPage 1s ease-out;
    position: relative;
    z-index: 1;
    padding: 20px;
  }

  .login-form {
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(10px);
    padding: 50px;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    width: 100%;
    max-width: 420px;
    text-align: center;
    animation: scaleIn 0.6s ease-out;
    border: 1px solid rgba(148, 163, 184, 0.1);
  }

  .login-form h2 {
    color: #fff;
    font-size: 2rem;
    margin-bottom: 10px;
    font-weight: 700;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, #fff 0%, #e0e7ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .login-form .subtitle {
    color: #cbd5e1;
    font-size: 0.95rem;
    margin-bottom: 35px;
    font-weight: 400;
  }

  .error-messages {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
    color: #fca5a5;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 0.9rem;
    animation: fadeInError 0.5s ease-out;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  .error-messages div {
    margin: 5px 0;
  }

  .input-group {
    margin-bottom: 25px;
    position: relative;
    text-align: left;
  }

  .input-group label {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 10px;
    transition: 0.3s;
  }

  .input-group input {
    width: 100%;
    padding: 14px 16px;
    font-size: 1rem;
    background: rgba(30, 41, 59, 0.5);
    border: 2px solid rgba(148, 163, 184, 0.2);
    border-radius: 10px;
    color: #e2e8f0;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
  }

  .input-group input::placeholder {
    color: #94a3b8;
  }

  .input-group input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background: rgba(30, 41, 59, 0.7);
  }

  .input-group small {
    color: #94a3b8 !important;
    font-size: 0.8rem !important;
    margin-top: 8px !important;
  }

  .login-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #fff;
    font-size: 1.05rem;
    font-weight: 700;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
  }

  .login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(37, 99, 235, 0.35);
  }

  .login-btn:active {
    transform: translateY(0);
  }

  .register-link {
    margin-top: 25px;
    color: #cbd5e1;
    font-size: 0.9rem;
  }

  .register-link a {
    color: #60a5fa;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  .register-link a:hover {
    color: #3b82f6;
  }

  /* Keyframe animations */
  @keyframes fadeInPage {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }

  @keyframes slideUpPage {
    0% {
      transform: translateY(30px);
      opacity: 0;
    }
    100% {
      transform: translateY(0);
      opacity: 1;
    }
  }

  @keyframes scaleIn {
    0% {
      transform: scale(0.95);
      opacity: 0;
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  @keyframes fadeInError {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }
</style>
