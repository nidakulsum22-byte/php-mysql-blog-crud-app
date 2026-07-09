<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
requireLogin();

$user = currentUser();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$post = ['title' => '', 'content' => ''];
$errors = [];

// Load existing post if editing
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post) {
        header('Location: index.php');
        exit;
    }
    // Task 4: Role-based access control — only admin or the post's author may edit
    if (!isAdmin() && $post['author_id'] != $user['id']) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'You do not have permission to edit that post.'];
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $post = ['title' => $title, 'content' => $content];

        // Server-side validation (Task 4)
        if (strlen($title) < 5)   $errors['title']   = 'Title must be at least 5 characters.';
        if (strlen($content) < 20) $errors['content'] = 'Content must be at least 20 characters.';

        if (empty($errors)) {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ? WHERE id = ?');
                $stmt->execute([$title, $content, $id]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post updated.'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO posts (title, content, author_id) VALUES (?, ?, ?)');
                $stmt->execute([$title, $content, $user['id']]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post published successfully.'];
            }
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = ($id ? 'Edit Post' : 'New Post') . ' — ApexPlanet';
require_once __DIR__ . '/includes/header.php';
?>

<a href="index.php" class="back-link">← Back to posts</a>

<div class="form-card">
  <h2><?= $id ? 'Edit Post' : 'New Post' ?></h2>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
  <?php endif; ?>

  <form method="POST" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <label for="title">Post Title</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>"
           placeholder="Enter a descriptive title..." required minlength="5">
    <?php if (!empty($errors['title'])): ?><div class="field-error"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>

    <label for="content">Content</label>
    <textarea id="content" name="content" rows="8"
              placeholder="Write your post content here..." required minlength="20"><?= htmlspecialchars($post['content']) ?></textarea>
    <?php if (!empty($errors['content'])): ?><div class="field-error"><?= htmlspecialchars($errors['content']) ?></div><?php endif; ?>

    <div class="form-actions">
      <a href="index.php" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><?= $id ? 'Save Changes' : 'Publish Post' ?></button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
