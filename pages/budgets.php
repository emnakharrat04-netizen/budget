<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_budget'])) {
        $ins = $pdo->prepare("INSERT INTO budgets (owner_id,name,period,global_limit,date_from,date_to,is_shared) VALUES (?,?,?,?,?,?,?)");
        $ins->execute([$uid, trim($_POST['name']), $_POST['period'], (float)$_POST['global_limit'],
            $_POST['date_from'] ?: null, $_POST['date_to'] ?: null, isset($_POST['is_shared'])?1:0]);
        $bid = $pdo->lastInsertId();
        // budget_members: owner is admin
        $pdo->prepare("INSERT INTO budget_members (budget_id,user_id,role) VALUES (?,?,'admin')")->execute([$bid,$uid]);
        $msg = 'Budget créé !';
    } elseif (isset($_POST['del_budget'])) {
        $pdo->prepare("DELETE FROM budgets WHERE id=? AND owner_id=?")->execute([(int)$_POST['bid'],$uid]);
        $msg = 'Budget supprimé.';
    }
}

// Fetch budgets accessible to user
$stmt = $pdo->prepare("
    SELECT DISTINCT b.*, u.name AS owner_name
    FROM budgets b
    LEFT JOIN budget_members bm ON bm.budget_id=b.id
    JOIN users u ON u.id=b.owner_id
    WHERE b.owner_id=? OR bm.user_id=?
    ORDER BY b.created_at DESC
");
$stmt->execute([$uid,$uid]); $budgets = $stmt->fetchAll();

// For each budget, compute spent
foreach ($budgets as &$b) {
    $s = $pdo->prepare("SELECT COALESCE(SUM(t.amount),0) FROM transactions t
        JOIN budget_members bm ON bm.budget_id=t.budget_id AND bm.user_id=t.user_id
        WHERE t.budget_id=? AND t.type='expense'");
    $s->execute([$b['id']]); $b['spent'] = (float)$s->fetchColumn();
    $b['pct'] = $b['global_limit']>0 ? min(round($b['spent']/$b['global_limit']*100),999) : 0;
    $b['status'] = getBudgetStatus($b['spent'], $b['global_limit']);
}
unset($b);
?>

<div class="page-header">
  <div><div class="page-title">Budgets</div><div class="page-sub">Créer et suivre vos budgets</div></div>
  <button class="btn btn-primary" onclick="openModal('m-add-bud')"><i class="ti ti-plus"></i> Nouveau budget</button>
</div>

<?php if ($msg): ?>
<div class="alert-box alert-success"><i class="ti ti-check"></i><?= sanitize($msg) ?></div>
<?php endif; ?>

<?php if (empty($budgets)): ?>
<div class="card" style="text-align:center;padding:40px;color:var(--muted)">
  <i class="ti ti-wallet" style="font-size:40px;margin-bottom:10px;display:block"></i>
  Aucun budget. Créez-en un !
</div>
<?php endif; ?>

<?php foreach ($budgets as $b):
  $fillClass = $b['status']==='over'?'fill-red':($b['status']==='warning'?'fill-amber':'fill-green');
  $badgeClass= $b['status']==='over'?'badge-over':($b['status']==='warning'?'badge-warning':'badge-ok');
  $badgeLabel= $b['status']==='over'?'Dépassé':($b['status']==='warning'?'Proche limite':'Actif');
?>
<div class="budget-item">
  <div class="budget-head">
    <div>
      <div class="budget-name"><?= sanitize($b['name']) ?> <?= $b['is_shared']?'<span class="badge badge-admin" style="margin-left:4px"><i class="ti ti-users" style="font-size:10px"></i> Partagé</span>':'' ?></div>
      <div class="budget-meta"><?= ucfirst($b['period']) ?> — <?= sanitize($b['owner_name']) ?></div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
      <span class="budget-meta"><?= formatMoney($b['spent']) ?> / <?= formatMoney($b['global_limit']) ?></span>
      <?php if ($b['owner_id'] == $uid): ?>
      <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce budget ?')">
        <input type="hidden" name="bid" value="<?= $b['id'] ?>"/>
        <button class="btn btn-sm btn-danger" name="del_budget">
  <i class="ti ti-trash"></i> Supprimer
</button>      </form>
      <?php endif; ?>
    </div>
  </div>
  <div class="progress-bar">
    <div class="progress-fill <?= $fillClass ?>" style="width:<?= min($b['pct'],100) ?>%"></div>
  </div>
  <div class="progress-note"><?= $b['pct'] ?>% consommé — Reste : <?= formatMoney(max($b['global_limit']-$b['spent'],0)) ?></div>
</div>
<?php endforeach; ?>

<!-- Add Budget Modal -->
<div class="modal-overlay" id="m-add-bud">
  <div class="modal-box">
    <div class="modal-title"><i class="ti ti-wallet"></i> Nouveau budget</div>
    <form method="POST">
      <div class="form-group"><label>Nom</label>
        <input class="form-control" type="text" name="name" required placeholder="Ex : Budget Juillet"/>
      </div>
      <div class="form-group"><label>Plafond global (€)</label>
        <input class="form-control" type="number" name="global_limit" min="0" step="0.01" required placeholder="3000.00"/>
      </div>
      <div class="form-group"><label>Période</label>
        <select class="form-control" name="period">
          <option value="monthly">Mensuel</option>
          <option value="weekly">Hebdomadaire</option>
          <option value="custom">Personnalisé</option>
        </select>
      </div>
      <div class="row-2">
        <div class="form-group"><label>Du</label>
          <input class="form-control" type="date" name="date_from" value="<?= date('Y-m-01') ?>"/>
        </div>
        <div class="form-group"><label>Au</label>
          <input class="form-control" type="date" name="date_to" value="<?= date('Y-m-t') ?>"/>
        </div>
      </div>
      <div class="form-group" style="display:flex;align-items:center;gap:8px">
        <input type="checkbox" name="is_shared" id="is_shared" style="width:16px;height:16px"/>
        <label for="is_shared" style="margin:0;cursor:pointer">Budget partagé (collaboratif)</label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="closeModal('m-add-bud')">Annuler</button>
        <button type="submit" name="add_budget" class="btn btn-primary">Créer</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
