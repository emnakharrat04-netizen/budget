<?php
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$pdo = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['validate_user'])) {
        $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([(int)$_POST['uid']]);
        $msg = 'Compte validé. L\'utilisateur peut maintenant se connecter.';
    } elseif (isset($_POST['disable_user'])) {
        $pdo->prepare("UPDATE users SET status='disabled' WHERE id!=? AND id=?")->execute([$user['id'],(int)$_POST['uid']]);
        $msg = 'Compte désactivé.';
    } elseif (isset($_POST['delete_user'])) {
        $pdo->prepare("DELETE FROM users WHERE id!=? AND id=?")->execute([$user['id'],(int)$_POST['uid']]);
        $msg = 'Compte supprimé.';
    } elseif (isset($_POST['set_role'])) {
        $pdo->prepare("UPDATE users SET role=? WHERE id!=? AND id=?")->execute([$_POST['role'],$user['id'],(int)$_POST['uid']]);
        $msg = 'Rôle mis à jour.';
    }
}

// Global stats
$totUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$actUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$pendUsers= $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
$totTx    = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totBudgets=$pdo->query("SELECT COUNT(*) FROM budgets")->fetchColumn();

$activeTab = $_GET['tab'] ?? 'users';
?>

<div class="page-header">
  <div><div class="page-title">Administration</div><div class="page-sub">Supervision du système</div></div>
</div>

<?php if ($msg): ?><div class="alert-box alert-success"><i class="ti ti-check"></i><?= sanitize($msg) ?></div><?php endif; ?>

<div class="stats-row">
  <div class="stat-card"><div class="stat-label">Utilisateurs</div><div class="stat-value blue"><?= $totUsers ?></div></div>
  <div class="stat-card"><div class="stat-label">Actifs</div><div class="stat-value green"><?= $actUsers ?></div></div>
  <div class="stat-card"><div class="stat-label">En attente</div><div class="stat-value amber"><?= $pendUsers ?></div></div>
  <div class="stat-card"><div class="stat-label">Transactions</div><div class="stat-value"><?= $totTx ?></div></div>
  <div class="stat-card"><div class="stat-label">Budgets</div><div class="stat-value"><?= $totBudgets ?></div></div>
</div>

<div class="tab-bar">
  <a href="?tab=users" class="tab-btn <?= $activeTab==='users'?'active':'' ?>">Utilisateurs</a>
  <a href="?tab=budgets" class="tab-btn <?= $activeTab==='budgets'?'active':'' ?>">Budgets globaux</a>
  <a href="?tab=transactions" class="tab-btn <?= $activeTab==='transactions'?'active':'' ?>">Toutes les transactions</a>
</div>

<?php if ($activeTab === 'users'):
    $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM transactions WHERE user_id=u.id) AS tx_count FROM users u ORDER BY u.created_at DESC")->fetchAll();
?>
<div class="card table-wrap">
  <div class="flex-between" style="margin-bottom:12px">
    <div class="card-title" style="margin:0">Gestion des utilisateurs</div>
    <span class="text-muted"><?= count($users) ?> utilisateur(s)</span>
  </div>
  <table>
    <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Transactions</th><th>Inscrit le</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td style="font-weight:600"><?= sanitize($u['name']) ?></td>
      <td><?= sanitize($u['email']) ?></td>
      <td>
        <form method="POST" style="display:inline">
          <input type="hidden" name="uid" value="<?= $u['id'] ?>"/>
          <select class="form-control" name="role" onchange="this.form.submit()" style="padding:3px 6px;font-size:12px;width:auto" <?= $u['id']==$user['id']?'disabled':'' ?>>
            <option value="user"  <?= $u['role']==='user' ?'selected':'' ?>>Utilisateur</option>
            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
          </select>
          <input type="hidden" name="set_role"/>
        </select>
        </form>
      </td>
      <td><span class="badge badge-<?= $u['status']==='active'?'active':($u['status']==='pending'?'pending':'disabled') ?>"><?= ucfirst($u['status']) ?></span></td>
      <td><?= $u['tx_count'] ?></td>
      <td><?= date('d/m/Y',strtotime($u['created_at'])) ?></td>
      <td>
  <div style="display:flex;gap:6px;flex-wrap:wrap">

    <!-- VALIDATE -->
    <?php if ($u['status']==='pending'): ?>
      <form method="POST">
        <input type="hidden" name="uid" value="<?= $u['id'] ?>"/>
        <button class="btn btn-sm btn-success" name="validate_user">
          <i class="ti ti-check"></i> Valider
        </button>
      </form>
    <?php endif; ?>

    <!-- DISABLE -->
    <?php if ($u['id'] != $user['id'] && $u['status']==='active'): ?>
      <form method="POST">
        <input type="hidden" name="uid" value="<?= $u['id'] ?>"/>
        <button class="btn btn-sm" name="disable_user">
          <i class="ti ti-ban"></i> Désactiver
        </button>
      </form>
    <?php endif; ?>

    <!-- DELETE -->
    <?php if ($u['id'] != $user['id']): ?>
      <form method="POST" onsubmit="return confirm('Supprimer ce compte ?')">
        <input type="hidden" name="uid" value="<?= $u['id'] ?>"/>
        <button class="btn btn-sm btn-danger" name="delete_user">
          <i class="ti ti-trash"></i> Supprimer
        </button>
      </form>
    <?php endif; ?>

  </div>
</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($activeTab === 'budgets'):
    $allBuds = $pdo->query("SELECT b.*, u.name AS owner_name,
        (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE budget_id=b.id AND type='expense') AS spent
        FROM budgets b JOIN users u ON u.id=b.owner_id ORDER BY b.created_at DESC")->fetchAll();
?>
<div class="card table-wrap">
  <table>
    <thead><tr><th>Nom</th><th>Propriétaire</th><th>Période</th><th>Plafond</th><th>Dépensé</th><th>Statut</th><th>Partagé</th></tr></thead>
    <tbody>
    <?php foreach ($allBuds as $b): $s = getBudgetStatus($b['spent'],$b['global_limit']); ?>
    <tr>
      <td style="font-weight:600"><?= sanitize($b['name']) ?></td>
      <td><?= sanitize($b['owner_name']) ?></td>
      <td><?= ucfirst($b['period']) ?></td>
      <td><?= formatMoney($b['global_limit']) ?></td>
      <td class="<?= $b['spent']>$b['global_limit']?'amount-neg':'amount-pos' ?>"><?= formatMoney($b['spent']) ?></td>
      <td><span class="badge badge-<?= $s==='over'?'over':($s==='warning'?'warning':'ok') ?>"><?= $s==='over'?'Dépassé':($s==='warning'?'Proche':'Actif') ?></span></td>
      <td><?= $b['is_shared']?'<span class="badge badge-admin">Oui</span>':'Non' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($activeTab === 'transactions'):
    $allTx = $pdo->query("SELECT t.*, u.name AS uname, c.name AS cat_name, c.icon AS cat_icon FROM transactions t JOIN users u ON u.id=t.user_id LEFT JOIN categories c ON c.id=t.category_id ORDER BY t.tx_date DESC, t.id DESC LIMIT 50")->fetchAll();
?>
<div class="card table-wrap">
  <div class="card-title">50 dernières transactions (tous utilisateurs)</div>
  <table>
    <thead><tr><th>Utilisateur</th><th>Catégorie</th><th>Description</th><th>Date</th><th>Type</th><th>Montant</th></tr></thead>
    <tbody>
    <?php foreach ($allTx as $tx): ?>
    <tr>
      <td style="font-weight:600"><?= sanitize($tx['uname']) ?></td>
      <td><?= sanitize($tx['cat_icon'].' '.$tx['cat_name']) ?></td>
      <td><?= sanitize($tx['description']) ?></td>
      <td><?= date('d/m/Y',strtotime($tx['tx_date'])) ?></td>
      <td><span class="badge badge-<?= $tx['type']==='income'?'income':'expense' ?>"><?= $tx['type']==='income'?'Revenu':'Dépense' ?></span></td>
      <td class="<?= $tx['type']==='income'?'amount-pos':'amount-neg' ?>"><?= ($tx['type']==='income'?'+':'-').formatMoney($tx['amount']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
