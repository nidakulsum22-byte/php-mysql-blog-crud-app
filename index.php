<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
requireLogin();

$user = currentUser();

// ── Task 3: Search ──
$search = trim($_GET['q'] ?? '');

// ── Task 3: Pagination ──
$perPage = 4;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Build query with prepared statements (Task 4: prevents SQL injection)
$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE p.title LIKE ? OR p.content LIKE ?';
    $params = ["%$search%", "%$search%"];
}

// Total count for pagination
$countSql = "SELECT COUNT(*) FROM posts p $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalPosts = (int) $stmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalPosts / $perPage));

// Fetch page of posts, joined with author username
$sql = "SELECT p.*, u.username AS author
        FROM posts p
        JOIN users u ON p.author_id = u.id
        $where
        ORDER BY p.created_at DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Blog Posts — ApexPlanet';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h2>Blog Posts</h2>
    <p class="muted"><?= $totalPosts ?> post<?= $totalPosts !== 1 ? 's' : '' ?> <?= $search ? 'matching "' . htmlspecialchars($search) . '"' : '' ?></p>
  </div>
  <a href="post_form.php" class="btn btn-primary">+ New Post</a>
</div>

<?php if ($flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<form method="GET" class="search-bar">
  <input type="text" name="q" placeholder="Search posts by title or content…" value="<?= htmlspecialchars($search) ?>">
  <button type="submit" class="btn btn-secondary">Search</button>
  <?php if ($search): ?><a href="index.php" class="btn btn-ghost">Clear</a><?php endif; ?>
</form>

<?php if (empty($posts)): ?>
  <div class="empty-state">
    <div class="empty-icon">📭</div>
    <p><?= $search ? 'No posts match your search' : 'No posts yet' ?></p>
  </div>
<?php else: ?>
  <div class="posts-grid">
    <?php foreach ($posts as $post): ?>
      <?php $canEdit = isAdmin() || $post['author_id'] == $user['id']; ?>
      <div class="post-card">
        <div class="post-card-head">
          <h3><a href="post_view.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
          <?php if ($canEdit): ?>
          <div class="post-actions">
            <a href="post_form.php?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
            <a href="post_delete.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this post?');">Delete</a>
          </div>
          <?php endif; ?>
        </div>
        <p class="post-preview"><?= htmlspecialchars(mb_strimwidth($post['content'], 0, 140, '…')) ?></p>
        <div class="post-meta">
          <div class="author">
            <div class="avatar avatar-sm"><?= strtoupper(substr($post['author'], 0, 1)) ?></div>
            <span><?= htmlspecialchars($post['author']) ?></span>
          </div>
          <span class="muted"><?= htmlspecialchars($post['created_at']) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php $qs = $search ? '&q=' . urlencode($search) : ''; ?>
      <a class="btn btn-secondary btn-sm <?= $page <= 1 ? 'disabled' : '' ?>"
         href="?page=<?= max(1, $page - 1) . $qs ?>">← Prev</a>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"
           href="?page=<?= $i . $qs ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="btn btn-secondary btn-sm <?= $page >= $totalPages ? 'disabled' : '' ?>"
         href="?page=<?= min($totalPages, $page + 1) . $qs ?>">Next →</a>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
