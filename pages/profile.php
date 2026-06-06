<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $uname = trim($_POST['username']);
        $email = trim($_POST['email']);
        // Check uniqueness excluding self
        $chk = $pdo->prepare("SELECT id FROM users WHERE (email=? OR username=?) AND id!=?");
        $chk->execute([$email,$uname,$uid]);
        if ($chk->fetch()) { $err = 'Cet email ou nom d\'utilisateur est déjà utilisé.'; }
        else {
            $pdo->prepare("UPDATE users SET name=?,username=?,email=? WHERE id=?")
                ->execute([$name,$uname,$email,$uid]);
            $_SESSION['user_name'] = $name;
            $user = getCurrentUser();
            $msg = 'Profil mis à jour !';
        }
    } elseif (isset($_POST['change_pw'])) {
        $curr = $_POST['current_pw'];
        $new  = $_POST['new_pw'];
        $conf = $_POST['confirm_pw'];
        $row  = $pdo->prepare("SELECT password FROM users WHERE id=?");
        $row->execute([$uid]); $pw = $row->fetchColumn();
        if (!password_verify($curr,$pw)) { $err = 'Mot de passe actuel incorrect.'; }
        elseif (strlen($new)<8) { $err = 'Le nouveau mot de passe doit contenir au moins 8 caractères.'; }
        elseif ($new !== $conf) { $err = 'Les mots de passe ne correspondent pas.'; }
        else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new,PASSWORD_BCRYPT,['cost'=>12]),$uid]);
            $msg = 'Mot de passe changé avec succès !';
        }
    } elseif (isset($_POST['request_delete'])) {
        $pdo->prepare("UPDATE users SET status='disabled' WHERE id=?")->execute([$uid]);
        $msg = 'Demande de suppression envoyée. Un administrateur traitera votre demande.';
    }
}

$ini = implode('', array_map(fn($w)=>$w[0], explode(' ', $user['name'])));
?>

<div class="page-header">
  <div><div class="page-title">Profil</div><div class="page-sub">Gérer vos informations personnelles</div></div>
</div>

<?php if ($msg): ?><div class="alert-box alert-success"><i class="ti ti-check"></i><?= sanitize($msg) ?></div><?php endif; ?>
<?php if ($err):  ?><div class="alert-box alert-danger"><i class="ti ti-alert-circle"></i><?= sanitize($err) ?></div><?php endif; ?>

<div class="card" style="max-width:500px">
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid var(--border)">
    <div class="avatar-circle"><?= strtoupper(substr($ini,0,2)) ?></div>
    <div>
      <div style="font-weight:700;font-size:15px"><?= sanitize($user['name']) ?></div>
      <div class="text-muted"><?= sanitize($user['email']) ?></div>
      <span class="badge <?= $user['role']==='admin'?'badge-admin':'badge-ok' ?>" style="margin-top:4px">
        <?= $user['role']==='admin' ? 'Administrateur' : 'Utilisateur' ?>
      </span>
    </div>
  </div>

  <form method="POST">
    <div class="card-title">Informations personnelles</div>
    <div class="form-group"><label>Nom complet</label>
      <input class="form-control" type="text" name="name" value="<?= sanitize($user['name']) ?>" required/>
    </div>
    <div class="form-group"><label>Nom d'utilisateur</label>
      <input class="form-control" type="text" name="username" value="<?= sanitize($user['username']) ?>" required/>
    </div>
    <div class="form-group"><label>Email</label>
      <input class="form-control" type="email" name="email" value="<?= sanitize($user['email']) ?>" required/>
    </div>
    <button type="submit" name="update_profile" class="btn btn-primary"><i class="ti ti-check"></i> Enregistrer</button>
  </form>
</div>

<div class="card" style="max-width:500px;margin-top:0">
  <form method="POST">
    <div class="card-title">Changer le mot de passe</div>
    <div class="form-group"><label>Mot de passe actuel</label>
      <input class="form-control" type="password" name="current_pw" required placeholder="••••••••"/>
    </div>
    <div class="form-group"><label>Nouveau mot de passe</label>
      <input class="form-control" type="password" name="new_pw" required minlength="8" placeholder="••••••••"/>
    </div>
    <div class="form-group"><label>Confirmer</label>
      <input class="form-control" type="password" name="confirm_pw" required placeholder="••••••••"/>
    </div>
    <button type="submit" name="change_pw" class="btn btn-primary"><i class="ti ti-lock"></i> Modifier le mot de passe</button>
  </form>
</div>

<div class="card" style="max-width:500px;margin-top:0;border-color:var(--red)">
  <div class="card-title" style="color:var(--red)"><i class="ti ti-alert-triangle"></i> Zone dangereuse</div>
  <p class="text-muted" style="margin-bottom:12px">La suppression de votre compte est irréversible. Toutes vos données seront perdues.</p>
  <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')">
    <button type="submit" name="request_delete" class="btn btn-danger"><i class="ti ti-trash"></i> Demander la suppression du compte</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
