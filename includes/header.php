<?php
// includes/header.php  — appelé en haut de chaque page connectée
require_once __DIR__ . '/auth.php';
requireLogin();
$user = getCurrentUser();

// Compter les alertes non lues
$pdo = getDB();
$alertStmt = $pdo->prepare("SELECT COUNT(*) FROM alerts WHERE user_id=? AND is_read=0");
$alertStmt->execute([$user['id']]);
$alertCount = (int)$alertStmt->fetchColumn();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/tabler-icons.min.css"/>
</head>
<body>
<div class="layout">

  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar">
    <div class="sb-logo">
      <span class="logo-name"><?= APP_NAME ?></span>
      <span class="logo-user"><?= sanitize($user['name']) ?></span>
    </div>
    <nav class="sb-nav">
      <a href="<?= BASE_URL ?>/pages/dashboard.php"    class="nav-item <?= $currentPage==='dashboard.php'   ?'active':'' ?>"><i class="ti ti-layout-dashboard"></i> Tableau de bord</a>
      <a href="<?= BASE_URL ?>/pages/transactions.php" class="nav-item <?= $currentPage==='transactions.php'?'active':'' ?>"><i class="ti ti-arrows-exchange"></i> Transactions</a>
      <a href="<?= BASE_URL ?>/pages/budgets.php"      class="nav-item <?= $currentPage==='budgets.php'     ?'active':'' ?>"><i class="ti ti-wallet"></i> Budgets</a>
      <a href="<?= BASE_URL ?>/pages/categories.php"   class="nav-item <?= $currentPage==='categories.php'  ?'active':'' ?>"><i class="ti ti-tag"></i> Catégories</a>
      <a href="<?= BASE_URL ?>/pages/collab.php"       class="nav-item <?= $currentPage==='collab.php'      ?'active':'' ?>"><i class="ti ti-users"></i> Collaboratif</a>
      <a href="<?= BASE_URL ?>/pages/alerts.php"       class="nav-item <?= $currentPage==='alerts.php'      ?'active':'' ?>">
        <i class="ti ti-bell"></i> Alertes
        <?php if ($alertCount > 0): ?>
          <span class="badge-count"><?= $alertCount ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= BASE_URL ?>/pages/stats.php"        class="nav-item <?= $currentPage==='stats.php'       ?'active':'' ?>"><i class="ti ti-chart-bar"></i> Statistiques</a>
      <a href="<?= BASE_URL ?>/pages/profile.php"      class="nav-item <?= $currentPage==='profile.php'     ?'active':'' ?>"><i class="ti ti-user"></i> Profil</a>
      <?php if ($user['role'] === 'admin'): ?>
      <a href="<?= BASE_URL ?>/pages/admin.php"        class="nav-item <?= $currentPage==='admin.php'       ?'active':'' ?>"><i class="ti ti-shield"></i> Administration</a>
      <?php endif; ?>
    </nav>
    <div class="sb-footer">
      <a href="<?= BASE_URL ?>/api/logout.php" class="nav-item nav-logout"><i class="ti ti-logout"></i> Déconnexion</a>
    </div>
  </aside>

  <!-- ===== MAIN ===== -->
  <main class="main-content">
