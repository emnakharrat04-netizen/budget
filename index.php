<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login'; // login | register

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $res = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($res['success']) {
            header('Location: ' . BASE_URL . '/pages/dashboard.php');
            exit;
        }
        $error = $res['message'];
    } elseif (isset($_POST['register'])) {
        $res = registerUser(
            trim($_POST['name'] ?? ''),
            trim($_POST['username'] ?? ''),
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? ''
        );
        if ($res['success']) {
            $success = $res['message'];
            $mode = 'login';
        } else {
            $error = $res['message'];
            $mode = 'register';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>FinanceApp — Connexion</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/tabler-icons.min.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo">FinanceApp</div>
      <div class="sub"><?= $mode === 'login' ? 'Connectez-vous à votre compte' : 'Créer un nouveau compte' ?></div>
    </div>

    <?php if ($error): ?>
      <div class="alert-box alert-danger" style="margin-bottom:12px">
        <i class="ti ti-alert-circle"></i><?= sanitize($error) ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert-box alert-success" style="margin-bottom:12px">
        <i class="ti ti-check"></i><?= sanitize($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($mode === 'login'): ?>
    <h2>Connexion</h2>
    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input class="form-control" type="email" name="email" placeholder="vous@email.com" required autofocus/>
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <input class="form-control" type="password" name="password" placeholder="••••••••" required/>
      </div>
      <button class="auth-btn-full" type="submit" name="login">Se connecter</button>
    </form>
    <div class="auth-link" style="margin-top:14px">
      Pas encore de compte ? <a href="?mode=register">Créer un compte</a>
    </div>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:11px;color:var(--muted);text-align:center">
      <strong>Démo :</strong> admin@financeapp.com / emna@financeapp.com<br/>
      Mot de passe universel : <strong>password</strong>
    </div>

    <?php else: ?>
    <h2>Inscription</h2>
    <form method="POST">
      <div class="form-group">
        <label>Nom complet</label>
        <input class="form-control" type="text" name="name" placeholder="Prénom Nom" required/>
      </div>
      <div class="form-group">
        <label>Nom d'utilisateur</label>
        <input class="form-control" type="text" name="username" placeholder="mon_pseudo" required/>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input class="form-control" type="email" name="email" placeholder="vous@email.com" required/>
      </div>
      <div class="form-group">
        <label>Mot de passe (min. 8 caractères)</label>
        <input class="form-control" type="password" name="password" placeholder="••••••••" required minlength="8"/>
      </div>
      <button class="auth-btn-full" type="submit" name="register">Créer mon compte</button>
    </form>
    <div class="auth-link" style="margin-top:14px">
      Déjà un compte ? <a href="?mode=login">Se connecter</a>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
