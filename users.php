<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
requireLogin();

// Task 4: role-based access control — admin only
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query('SELECT id, username, role, created_at FROM users ORDER BY id');
$users = $stmt->fetchAll();

$pageTitle = 'User Management — ApexPlanet';
require_once __DIR__ . '/includes/header.php';
?>

<h2>User Management</h2>

<div class="table-card">
  <table>
    <thead>
      <tr><th>ID</th><th>Username</th><th>Role</th><th>Joined</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $i => $u): ?>
        <tr class="<?= $i % 2 === 0 ? 'row-alt' : '' ?>">
          <td class="muted">#<?= $u['id'] ?></td>
          <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
          <td><span class="badge badge-<?= $u['role'] ?>"><?= strtoupper($u['role']) ?></span></td>
          <td class="muted"><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
