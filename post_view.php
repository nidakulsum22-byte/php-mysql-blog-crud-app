<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT p.*, u.username AS author FROM posts p JOIN users u ON p.author_id = u.id WHERE p.id = ?'
);
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($post['title']) . ' — ApexPlanet';
require_once __DIR__ . '/includes/header.php';
?>

<a href="index.php" class="back-link">← Back to posts</a>

<article class="post-detail">
  <h1><?= htmlspecialchars($post['title']) ?></h1>
  <div class="post-meta">
    <div class="author">
      <div class="avatar avatar-sm"><?= strtoupper(substr($post['author'], 0, 1)) ?></div>
      <span><?= htmlspecialchars($post['author']) ?></span>
    </div>
    <span class="muted">Published <?= htmlspecialchars($post['created_at']) ?> · Updated <?= htmlspecialchars($post['updated_at']) ?></span>
  </div>
  <div class="post-body">
    <?= nl2br(htmlspecialchars($post['content'])) ?>
  </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
