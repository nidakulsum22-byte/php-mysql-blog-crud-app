<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
requireLogin();

$user = currentUser();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if ($post) {
    // Task 4: only admin or the post's author may delete
    if (isAdmin() || $post['author_id'] == $user['id']) {
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post deleted.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'You do not have permission to delete that post.'];
    }
}

header('Location: index.php');
exit;
