<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['invite'])) {
        $email = trim($_POST['email']);
        $bid = (int)$_POST['budget_id'];
        $invUser = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $invUser->execute([$email]); $inv = $invUser->fetch();
        if (!$inv) { $msg = 'Aucun utilisateur trouvé avec cet email.'; }
        else {
            $chk = $pdo->prepare("SELECT id FROM budget_members WHERE budget_id=? AND user_id=?");
            $chk->execute([$bid,$inv['id']]);
            if ($chk->fetch()) { $msg = 'Cet utilisateur est déjà membre.'; }
            else {
                $pdo->prepare("INSERT INTO budget_members (budget_id,user_id,role) VALUES (?,?,?)")
                    ->execute([$bid,$inv['id'],$_POST['role']]);
                $msg = 'Membre ajouté avec succès !';
            }
        }
    } elseif (isset($_POST['remove_member'])) {
        $pdo->prepare("DELETE FROM budget_members WHERE budget_id=? AND user_id=? AND user_id!=?")
            ->execute([(int)$_POST['bid'],(int)$_POST['mid'],$uid]);
        $msg = 'Membre retiré.';
    }
}

// Shared budgets user is in
$sharedBuds = $pdo->prepare("
    SELECT b.*, u.name AS owner_name, bm.role AS my_role
    FROM budgets b
    JOIN budget_members bm ON bm.budget_id=b.id AND bm.user_id=?
    JOIN users u ON u.id=b.owner_id
    WHERE b.is_shared=1
    ORDER BY b.created_at DESC
");
$sharedBuds->execute([$uid]); $budgets = $sharedBuds->fetchAll();

$activeBud = isset($_GET['bid']) ? (int)$_GET['bid'] : ($budgets[0]['id'] ?? 0);

// Members of active budget
$members = []; $sharedTx = []; $contributions = [];
if ($activeBud) {
    $mStmt = $pdo->prepare("SELECT bm.*, u.name, u.email FROM budget_members bm JOIN users u ON u.id=bm.user_id WHERE bm.budget_id=? ORDER BY bm.joined_at");
    $mStmt->execute([$activeBud]); $members = $mStmt->fetchAll();

    $txStmt = $pdo->prepare("SELECT t.*, u.name AS author, c.name AS cat_name, c.icon AS cat_icon FROM transactions t JOIN users u ON u.id=t.user_id LEFT JOIN categories c ON c.id=t.category_id WHERE t.budget_id=? ORDER BY t.tx_date DESC LIMIT 20");
    $txStmt->execute([$activeBud]); $sharedTx = $txStmt->fetchAll();

    foreach ($members as $m) {
        $cStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE budget_id=? AND user_id=? AND type='expense'");
        $cStmt->execute([$activeBud,$m['user_id']]); $contributions[$m['user_id']] = (float)$cStmt->fetchColumn();
    }
}
$maxContrib = max(array_values($contributions) ?: [1]);
$myBudsForInvite = $pdo->prepare("SELECT * FROM budgets WHERE owner_id=? AND is_shared=1");
$myBudsForInvite->execute([$uid]); $myBuds = $myBudsForInvite->fetchAll();
$avatarColors = ['#E6F1FB:#0C447C','#EAF3DE:#3B6D11','#FAEEDA:#BA7517','#EEEDFE:#534AB7','#FCEBEB:#A32D2D'];
?>

<div class="page-header">
  <div><div class="page-title">Budget collaboratif</div><div class="page-sub">Gérer vos budgets partagés</div></div>
  <button class="btn btn-primary" onclick="openModal('m-invite')"><i class="ti ti-user-plus"></i> Inviter un membre</button>
</div>

<?php if ($msg): ?>
<div class="alert-box alert-<?= strpos($msg,'succès')!==false?'success':'danger' ?>"><i class="ti ti-<?= strpos($msg,'succès')!==false?'check':'alert-circle' ?>"></i><?= sanitize($msg) ?></div>
<?php endif; ?>

<?php if (empty($budgets)): ?>
<div class="card" style="text-align:center;padding:40px;color:var(--muted)">
  <i class="ti ti-users" style="font-size:40px;margin-bottom:10px;display:block"></i>
  Aucun budget partagé. Créez un budget partagé dans la section Budgets.
</div>
<?php else: ?>

<!-- Budget selector -->
<div class="card" style="margin-bottom:16px">
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <?php foreach ($budgets as $b): ?>
    <a href="?bid=<?= $b['id'] ?>" class="btn <?= $b['id']==$activeBud?'btn-primary':'' ?>"><?= sanitize($b['name']) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($activeBud): ?>
<div class="tab-bar">
  <button class="tab-btn active" onclick="swTab('overview',this)">Vue d'ensemble</button>
  <button class="tab-btn" onclick="swTab('members',this)">Membres</button>
  <button class="tab-btn" onclick="swTab('transactions',this)">Transactions partagées</button>
</div>

<div class="tab-pane active" id="tab-overview">
  <?php
    $totShared = array_sum($contributions);
    $myShare = $contributions[$uid] ?? 0;
    $budRow = $pdo->prepare("SELECT * FROM budgets WHERE id=?"); $budRow->execute([$activeBud]); $bRow = $budRow->fetch();
  ?>
  <div class="stats-row">
    <div class="stat-card"><div class="stat-label">Dépenses communes</div><div class="stat-value red"><?= formatMoney($totShared) ?></div></div>
    <div class="stat-card"><div class="stat-label">Votre contribution</div><div class="stat-value blue"><?= formatMoney($myShare) ?></div></div>
    <div class="stat-card"><div class="stat-label">Plafond partagé</div><div class="stat-value green"><?= formatMoney($bRow['global_limit']) ?></div></div>
  </div>
  <div class="card">
    <div class="card-title">Contribution par membre</div>
    <?php foreach ($members as $i => $m): $c = $contributions[$m['user_id']] ?? 0; $pct = $maxContrib>0?round($c/$maxContrib*100):0; [$bg,$fg] = explode(':',$avatarColors[$i%5]); $ini = implode('',array_map(fn($w)=>$w[0],explode(' ',$m['name']))); ?>
    <div class="member-row">
      <div class="member-avatar" style="background:<?= $bg ?>;color:<?= $fg ?>"><?= strtoupper(substr($ini,0,2)) ?></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600"><?= sanitize($m['name']) ?></div>
        <div class="contrib-bar"><div class="contrib-fill" style="width:<?= $pct ?>%;background:<?= $fg ?>"></div></div>
      </div>
      <div style="font-size:13px;font-weight:600;min-width:70px;text-align:right"><?= formatMoney($c) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="tab-pane" id="tab-members">
  <div class="card">
    <?php foreach ($members as $i => $m): [$bg,$fg] = explode(':',$avatarColors[$i%5]); $ini = implode('',array_map(fn($w)=>$w[0],explode(' ',$m['name']))); ?>
    <div class="member-row">
      <div class="member-avatar" style="background:<?= $bg ?>;color:<?= $fg ?>"><?= strtoupper(substr($ini,0,2)) ?></div>
      <div style="flex:1"><div style="font-size:13px;font-weight:600"><?= sanitize($m['name']) ?></div><div class="text-muted"><?= sanitize($m['email']) ?></div></div>
      <span class="badge <?= $m['role']==='admin'?'badge-admin':'badge-ok' ?>"><?= ucfirst($m['role']) ?></span>
      <?php if ($m['user_id'] != $uid): ?>
      <form method="POST" style="display:inline;margin-left:6px">
        <input type="hidden" name="bid" value="<?= $activeBud ?>"/>
        <input type="hidden" name="mid" value="<?= $m['user_id'] ?>"/>
        <button class="btn btn-sm btn-danger" name="remove_member" onclick="return confirm('Retirer ce membre ?')"><i class="ti ti-x"></i></button>
      </form>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="tab-pane" id="tab-transactions">
  <div class="card table-wrap">
    <table>
      <thead><tr><th>Auteur</th><th>Description</th><th>Catégorie</th><th>Date</th><th>Montant</th></tr></thead>
      <tbody>
        <?php foreach ($sharedTx as $tx): ?>
        <tr>
          <td style="font-weight:600"><?= sanitize($tx['author']) ?></td>
          <td><?= sanitize($tx['description']) ?></td>
          <td><?= sanitize($tx['cat_icon'].' '.$tx['cat_name']) ?></td>
          <td><?= date('d/m/Y',strtotime($tx['tx_date'])) ?></td>
          <td class="<?= $tx['type']==='income'?'amount-pos':'amount-neg' ?>"><?= ($tx['type']==='income'?'+':'-').formatMoney($tx['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($sharedTx)): ?><tr><td colspan="5" style="text-align:center;color:var(--muted);padding:20px">Aucune transaction partagée.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Invite Modal -->
<div class="modal-overlay" id="m-invite">
  <div class="modal-box">
    <div class="modal-title"><i class="ti ti-user-plus"></i> Inviter un membre</div>
    <form method="POST">
      <div class="form-group"><label>Email du membre</label>
        <input class="form-control" type="email" name="email" required placeholder="prenom.nom@example.com"/>
      </div>
      <div class="form-group"><label>Budget partagé</label>
        <select class="form-control" name="budget_id" required>
          <?php foreach ($myBuds as $b): ?>
          <option value="<?= $b['id'] ?>"><?= sanitize($b['name']) ?></option>
          <?php endforeach; ?>
          <?php if(empty($myBuds)): ?><option value="">— Aucun budget partagé —</option><?php endif; ?>
        </select>
      </div>
      <div class="form-group"><label>Rôle</label>
        <select class="form-control" name="role">
          <option value="member">Membre</option>
          <option value="admin">Admin</option>
          <option value="readonly">Lecture seule</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="closeModal('m-invite')">Annuler</button>
        <button type="submit" name="invite" class="btn btn-primary">Inviter</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
