<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];

// Mark all read when page opened
$pdo->prepare("UPDATE alerts SET is_read=1 WHERE user_id=?")->execute([$uid]);

// Generate fresh alerts from budget analysis
$budgets = $pdo->prepare("
    SELECT b.* FROM budgets b
    LEFT JOIN budget_members bm ON bm.budget_id=b.id
    WHERE b.owner_id=? OR bm.user_id=?
");
$budgets->execute([$uid,$uid]); $buds = $budgets->fetchAll();

$alerts = [];
foreach ($buds as $b) {
    $spentQ = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE budget_id=? AND type='expense'");
    $spentQ->execute([$b['id']]); $spent = (float)$spentQ->fetchColumn();
    if ($b['global_limit'] > 0) {
        $pct = ($spent / $b['global_limit']) * 100;
        if ($pct >= 100) $alerts[] = ['type'=>'over','budget'=>$b['name'],'spent'=>$spent,'limit'=>$b['global_limit'],'pct'=>round($pct)];
        elseif ($pct >= 80) $alerts[] = ['type'=>'warning','budget'=>$b['name'],'spent'=>$spent,'limit'=>$b['global_limit'],'pct'=>round($pct)];
        else $alerts[] = ['type'=>'ok','budget'=>$b['name'],'spent'=>$spent,'limit'=>$b['global_limit'],'pct'=>round($pct)];
    }
}

// Also get stored alerts (unread)
$stored = $pdo->prepare("SELECT a.*, b.name AS bud_name FROM alerts a LEFT JOIN budgets b ON b.id=a.budget_id WHERE a.user_id=? ORDER BY a.created_at DESC LIMIT 20");
$stored->execute([$uid]); $storedAlerts = $stored->fetchAll();
?>

<div class="page-header">
  <div><div class="page-title">Alertes</div><div class="page-sub">Suivi des dépassements et avertissements</div></div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-title">État actuel des budgets</div>
  <?php foreach ($alerts as $a):
    $cls = $a['type']==='over' ? 'alert-danger' : ($a['type']==='warning' ? 'alert-warning' : 'alert-success');
    $icon = $a['type']==='over' ? 'ti-alert-triangle' : ($a['type']==='warning' ? 'ti-bell' : 'ti-check');
    $label = $a['type']==='over' ? 'Dépassement !' : ($a['type']==='warning' ? 'Attention' : 'Maîtrisé'); ?>
  <div class="alert-box <?= $cls ?>">
    <i class="ti <?= $icon ?>"></i>
    <div style="flex:1">
      <strong><?= $label ?> — <?= sanitize($a['budget']) ?></strong><br/>
      <?= formatMoney($a['spent']) ?> dépensés sur <?= formatMoney($a['limit']) ?> prévu (<?= $a['pct'] ?>%)
      <?php if ($a['type']==='over'): ?> — Dépassement de <?= formatMoney($a['spent']-$a['limit']) ?><?php endif; ?>
    </div>
    <span class="badge <?= $a['type']==='over'?'badge-over':($a['type']==='warning'?'badge-warning':'badge-active') ?>"><?= $a['pct'] ?>%</span>
  </div>
  <?php endforeach; ?>
  <?php if(empty($alerts)): ?>
  <div style="text-align:center;color:var(--muted);padding:20px">Aucun budget actif à surveiller.</div>
  <?php endif; ?>
</div>

<?php if (!empty($storedAlerts)): ?>
<div class="card">
  <div class="card-title">Historique des alertes</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Type</th><th>Budget</th><th>Message</th></tr></thead>
      <tbody>
        <?php foreach ($storedAlerts as $a): ?>
        <tr>
          <td><?= date('d/m/Y H:i',strtotime($a['created_at'])) ?></td>
          <td><span class="badge badge-<?= $a['type']==='over'?'over':($a['type']==='warning'?'warning':'ok') ?>"><?= ucfirst($a['type']) ?></span></td>
          <td><?= sanitize($a['bud_name'] ?? '—') ?></td>
          <td><?= sanitize($a['message'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
