<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];

$selMonth = $_GET['month'] ?? date('Y-m');

// Totals for selected month
$rev = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='income' AND DATE_FORMAT(tx_date,'%Y-%m')=?");
$rev->execute([$uid,$selMonth]); $totalRev = (float)$rev->fetchColumn();

$exp = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='expense' AND DATE_FORMAT(tx_date,'%Y-%m')=?");
$exp->execute([$uid,$selMonth]); $totalExp = (float)$exp->fetchColumn();

$balance = $totalRev - $totalExp;
$savingsRate = $totalRev > 0 ? round(($balance/$totalRev)*100,1) : 0;
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)substr($selMonth,5,2), (int)substr($selMonth,0,4));
$dailyAvg = $daysInMonth > 0 ? $totalExp/$daysInMonth : 0;

// By category
$catExp = $pdo->prepare("SELECT c.name, c.icon, c.color, COALESCE(SUM(t.amount),0) AS total FROM categories c LEFT JOIN transactions t ON t.category_id=c.id AND t.user_id=? AND t.type='expense' AND DATE_FORMAT(t.tx_date,'%Y-%m')=? GROUP BY c.id HAVING total>0 ORDER BY total DESC");
$catExp->execute([$uid,$selMonth]); $catData = $catExp->fetchAll();

// Last 6 months comparison
$months = [];
for ($i=5;$i>=0;$i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $lbl = date('M y', strtotime($m.'-01'));
    $r = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='income' AND DATE_FORMAT(tx_date,'%Y-%m')=?");
    $r->execute([$uid,$m]); $mr = (float)$r->fetchColumn();
    $e = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='expense' AND DATE_FORMAT(tx_date,'%Y-%m')=?");
    $e->execute([$uid,$m]); $me = (float)$e->fetchColumn();
    $months[] = ['label'=>$lbl,'rev'=>$mr,'exp'=>$me,'bal'=>$mr-$me];
}
$maxCat = max(array_column($catData,'total') ?: [1]);
$pieColors = ['#378ADD','#3B6D11','#E24B4A','#BA7517','#534AB7','#888780','#0891B2','#BE185D'];
?>

<div class="page-header">
  <div><div class="page-title">Statistiques</div><div class="page-sub">Analyse détaillée de vos finances</div></div>
  <form method="GET" style="display:flex;align-items:center;gap:8px">
    <label class="text-muted">Mois :</label>
    <input class="form-control" type="month" name="month" value="<?= $selMonth ?>" style="width:auto"/>
    <button class="btn btn-primary" type="submit"><i class="ti ti-refresh"></i></button>
  </form>
</div>

<div class="stats-row">
  <div class="stat-card"><div class="stat-label"><i class="ti ti-trending-up" style="color:var(--green-m)"></i>Revenus</div><div class="stat-value green"><?= formatMoney($totalRev) ?></div></div>
  <div class="stat-card"><div class="stat-label"><i class="ti ti-trending-down" style="color:var(--red)"></i>Dépenses</div><div class="stat-value red"><?= formatMoney($totalExp) ?></div></div>
  <div class="stat-card"><div class="stat-label"><i class="ti ti-piggy-bank" style="color:var(--blue-d)"></i>Solde</div><div class="stat-value <?= $balance>=0?'green':'red' ?>"><?= formatMoney($balance) ?></div></div>
  <div class="stat-card"><div class="stat-label"><i class="ti ti-percentage" style="color:var(--amber)"></i>Taux d'épargne</div><div class="stat-value <?= $savingsRate>=0?'blue':'red' ?>"><?= $savingsRate ?>%</div></div>
  <div class="stat-card"><div class="stat-label"><i class="ti ti-calendar" style="color:var(--purple)"></i>Dépense moy./jour</div><div class="stat-value"><?= formatMoney($dailyAvg) ?></div></div>
</div>

<div class="grid-2" style="margin-bottom:16px">
  <div class="card">
    <div class="card-title">Dépenses par catégorie</div>
    <?php if(empty($catData)): ?><div class="text-muted" style="padding:20px;text-align:center">Aucune dépense ce mois.</div>
    <?php else: foreach ($catData as $i => $c): $barW = $maxCat>0?round($c['total']/$maxCat*100):0; ?>
    <div class="bar-h">
      <div style="width:120px;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= sanitize($c['icon'].' '.$c['name']) ?></div>
      <div style="flex:1"><div class="bar-h-fill" style="width:<?= $barW ?>%;background:<?= $pieColors[$i%8] ?>"></div></div>
      <div style="min-width:80px;text-align:right;font-size:12px;font-weight:600"><?= formatMoney($c['total']) ?></div>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <div class="card">
    <div class="card-title">Revenus vs Dépenses vs Épargne</div>
    <div style="display:flex;align-items:flex-end;gap:20px;height:160px;padding-top:10px;justify-content:center">
      <?php
      $maxBar = max($totalRev,$totalExp,$balance,1);
      $bars = [['label'=>'Revenus','val'=>$totalRev,'color'=>'var(--green-m)'],['label'=>'Dépenses','val'=>$totalExp,'color'=>'var(--red)'],['label'=>'Épargne','val'=>max($balance,0),'color'=>'var(--blue)']];
      foreach ($bars as $bar): $h = max(8,round(($bar['val']/$maxBar)*140));
      ?>
      <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
        <div style="font-size:11px;font-weight:600;color:<?= $bar['color'] ?>"><?= formatMoney($bar['val']) ?></div>
        <div style="width:50px;height:<?= $h ?>px;background:<?= $bar['color'] ?>;border-radius:4px 4px 0 0;opacity:.85"></div>
        <div style="font-size:11px;color:var(--muted)"><?= $bar['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-title">Comparaison mensuelle (6 derniers mois)</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Mois</th><th>Revenus</th><th>Dépenses</th><th>Solde</th><th>Taux épargne</th></tr></thead>
      <tbody>
        <?php foreach (array_reverse($months) as $m): $sr = $m['rev']>0?round($m['bal']/$m['rev']*100,1):0; ?>
        <tr>
          <td><?= $m['label'] ?></td>
          <td class="amount-pos"><?= formatMoney($m['rev']) ?></td>
          <td class="amount-neg"><?= formatMoney($m['exp']) ?></td>
          <td class="<?= $m['bal']>=0?'amount-pos':'amount-neg' ?>"><?= ($m['bal']>=0?'+':'').formatMoney($m['bal']) ?></td>
          <td><span class="badge <?= $sr>=20?'badge-ok':($sr>=0?'badge-warning':'badge-over') ?>"><?= $sr ?>%</span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
