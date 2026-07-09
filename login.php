<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errors['general'] = 'Username and password are required.';
        } else {
            // Prepared statement — prevents SQL injection
            $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                header('Location: index.php');
                exit;
            } else {
                $errors['general'] = 'Invalid username or password.';
            }
        }
    }
}

$pageTitle = 'Sign In — ApexPlanet';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Sign in</h2>

    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">Account created! You can now log in.</div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit" class="btn btn-primary btn-block">Sign In →</button>
    </form>

    <div class="demo-box">
      <strong>Demo accounts:</strong><br>
      admin / admin123 &nbsp;|&nbsp; editor / editor123
    </div>

    <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
