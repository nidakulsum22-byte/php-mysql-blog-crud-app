<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = ['username' => '', 'role' => 'editor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm']  ?? '';
        $role     = in_array($_POST['role'] ?? '', ['admin', 'editor']) ? $_POST['role'] : 'editor';
        $old = ['username' => $username, 'role' => $role];

        // Server-side validation
        if (strlen($username) < 3) $errors['username'] = 'Username must be at least 3 characters.';
        if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors['confirm'] = 'Passwords do not match.';

        if (empty($errors)) {
            // Check uniqueness using a prepared statement
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors['username'] = 'Username already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                $stmt->execute([$username, $hash, $role]);
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }
}

$pageTitle = 'Register — ApexPlanet';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Create an account</h2>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($old['username']) ?>" required minlength="3">
      <?php if (!empty($errors['username'])): ?><div class="field-error"><?= htmlspecialchars($errors['username']) ?></div><?php endif; ?>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="6">
      <?php if (!empty($errors['password'])): ?><div class="field-error"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>

      <label for="confirm">Confirm Password</label>
      <input type="password" id="confirm" name="confirm" required minlength="6">
      <?php if (!empty($errors['confirm'])): ?><div class="field-error"><?= htmlspecialchars($errors['confirm']) ?></div><?php endif; ?>

      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="editor" <?= $old['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
        <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>

      <button type="submit" class="btn btn-primary btn-block">Create Account →</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
