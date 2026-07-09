<?php
require_once __DIR__ . '/auth.php';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'ApexPlanet Blog') ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">
        <span class="dot"></span> ApexPlanet <span class="divider">|</span> <span class="subtitle">Blog CMS</span>
    </div>
    <?php if (isLoggedIn()): ?>
    <div class="navbar-actions">
        <div class="navbar-user">
            <div class="avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
            <span><?= htmlspecialchars($user['username']) ?></span>
            <span class="badge badge-<?= $user['role'] ?>"><?= strtoupper($user['role']) ?></span>
        </div>
        <?php if (isAdmin()): ?>
            <a href="users.php" class="btn btn-ghost btn-sm">Users</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-ghost btn-sm">Sign out</a>
    </div>
    <?php endif; ?>
</nav>
<div class="container">
