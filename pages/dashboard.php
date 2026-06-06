<?php

require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];

// Stats
$rev = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='income' AND MONTH(tx_date)=MONTH(CURDATE()) AND YEAR(tx_date)=YEAR(CURDATE())");
$rev->execute([$uid]); $totalRev = (float)$rev->fetchColumn();

$exp = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='expense' AND MONTH(tx_date)=MONTH(CURDATE()) AND YEAR(tx_date)=YEAR(CURDATE())");
$exp->execute([$uid]); $totalExp = (float)$exp->fetchColumn();

$balance = $totalRev - $totalExp;

// Dépenses par catégorie
$catExp = $pdo->prepare("
    SELECT c.name, c.color, c.icon, COALESCE(SUM(t.amount),0) AS total
    FROM categories c
    LEFT JOIN transactions t ON t.category_id=c.id AND t.user_id=? AND t.type='expense'
      AND MONTH(t.tx_date)=MONTH(CURDATE()) AND YEAR(t.tx_date)=YEAR(CURDATE())
    GROUP BY c.id ORDER BY total DESC LIMIT 6
");
$catExp->execute([$uid]); $catData = $catExp->fetchAll();

// Évolution 6 mois
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $s = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type='expense' AND DATE_FORMAT(tx_date,'%Y-%m')=?");
    $s->execute([$uid, $m]); $months[] = ['label' => date('M', strtotime($m.'-01')), 'val' => (float)$s->fetchColumn()];
}

// Transactions récentes
$recent = $pdo->prepare("
    SELECT t.*, c.name AS cat_name, c.icon AS cat_icon FROM transactions t
    LEFT JOIN categories c ON c.id=t.category_id
    WHERE t.user_id=? ORDER BY t.tx_date DESC, t.id DESC LIMIT 5
");
$recent->execute([$uid]); $recentTx = $recent->fetchAll();

$values = array_column($months, 'val');
$maxBar = !empty($values) ? max($values) : 1;
$pieTotal = array_sum(array_column($catData,'total')) ?: 1;
$pieColors = ['#378ADD','#3B6D11','#E24B4A','#BA7517','#534AB7','#888780'];
$pieDeg = 0; $pieGrad = '';
foreach ($catData as $i => $c) {
    $share = ($c['total']/$pieTotal)*360;
    $pieGrad .= ($pieGrad?',':'') . $pieColors[$i%6].' '.(round($pieDeg)).'deg '.round($pieDeg+$share).'deg';
    $pieDeg += $share;
}
?>

<div class="page-header">
  <div>
    <div class="page-title">Tableau de bord</div>
    <div class="page-sub">Bonjour, <?= sanitize($user['name']) ?> ! — <?= date('l d F Y') ?></div>
  </div>
</div>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label"><i class="ti ti-trending-up" style="color:var(--green-m)"></i> Revenus du mois</div>
    <div class="stat-value green"><?= formatMoney($totalRev) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="ti ti-trending-down" style="color:var(--red)"></i> Dépenses du mois</div>
    <div class="stat-value red"><?= formatMoney($totalExp) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="ti ti-piggy-bank" style="color:var(--blue-d)"></i> Solde disponible</div>
    <div class="stat-value <?= $balance>=0?'green':'red' ?>"><?= formatMoney($balance) ?></div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:16px">
  <div class="card">
    <div class="card-title">Répartition des dépenses</div>
    <div style="display:flex;align-items:center;gap:16px">
      <div class="pie-chart" style="background:conic-gradient(<?= $pieGrad ?: '#ddd 0deg 360deg' ?>);flex-shrink:0"></div>
      <ul class="chart-legend" style="flex:1">
        <?php foreach ($catData as $i => $c): if($c['total']<=0) continue; ?>
        <li><span class="legend-dot" style="background:<?= $pieColors[$i%6] ?>"></span><?= sanitize($c['icon'].' '.$c['name']) ?> — <?= formatMoney($c['total']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="card">
    <div class="card-title">Évolution mensuelle des dépenses</div>

    <div style="
        display:flex;
        align-items:flex-end;
        justify-content:space-around;
        height:280px;
        padding:20px;
        border-top:1px solid #eee;
    ">

        <?php foreach ($months as $m): ?>

            <?php $height = ($m['val'] / $maxBar) * 220; ?>

            <div style="
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:flex-end;
                height:100%;
                flex:1;
            ">

                <div style="
                    width:40px;
                    height:<?= $height ?>px;
                    background:#378ADD;
                    border-radius:8px 8px 0 0;
                "></div>

                <div style="margin-top:8px;font-size:12px;">
                    <?= $m['label'] ?>
                </div>

                <div style="font-size:11px;color:gray;">
                    <?= round($m['val']) ?> €
                </div>

            </div>

        <?php endforeach; ?>

    </div>
</div>
        </div>
<div class="card">
  <div class="flex-between mb-0" style="margin-bottom:12px">
    <div class="card-title" style="margin:0">Transactions récentes</div>
    <a href="<?= BASE_URL ?>/pages/transactions.php" class="btn btn-sm">Voir tout <i class="ti ti-arrow-right"></i></a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Catégorie</th><th>Description</th><th>Date</th><th>Type</th><th>Montant</th></tr></thead>
      <tbody>
        <?php foreach ($recentTx as $tx): ?>
        <tr>
          <td><?= sanitize($tx['cat_icon'].' '.$tx['cat_name']) ?></td>
          <td><?= sanitize($tx['description']) ?></td>
          <td><?= date('d/m/Y', strtotime($tx['tx_date'])) ?></td>
          <td><span class="badge badge-<?= $tx['type'] === 'income' ? 'income' : 'expense' ?>"><?= $tx['type']==='income'?'Revenu':'Dépense' ?></span></td>
          <td class="<?= $tx['type']==='income'?'amount-pos':'amount-neg' ?>"><?= ($tx['type']==='income'?'+':'-').formatMoney($tx['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentTx)): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:20px">Aucune transaction ce mois-ci.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
