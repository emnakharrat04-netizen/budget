<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];

$msg = '';
// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_tx'])) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id,budget_id,category_id,type,amount,description,tx_date,comment) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $uid,
            $_POST['budget_id'] ?: null,
            $_POST['category_id'] ?: null,
            $_POST['type'],
            (float)$_POST['amount'],
            trim($_POST['description']),
            $_POST['tx_date'],
            trim($_POST['comment'] ?? '')
        ]);
        $msg = 'Transaction ajoutée avec succès !';
    } elseif (isset($_POST['del_tx'])) {
        $del = $pdo->prepare("DELETE FROM transactions WHERE id=? AND user_id=?");
        $del->execute([(int)$_POST['tx_id'], $uid]);
        $msg = 'Transaction supprimée.';
    } elseif (isset($_POST['edit_tx'])) {
        $upd = $pdo->prepare("UPDATE transactions SET type=?,amount=?,category_id=?,budget_id=?,description=?,tx_date=?,comment=? WHERE id=? AND user_id=?");
        $upd->execute([
            $_POST['type'], (float)$_POST['amount'],
            $_POST['category_id'] ?: null,
            $_POST['budget_id'] ?: null,
            trim($_POST['description']),
            $_POST['tx_date'], trim($_POST['comment'] ?? ''),
            (int)$_POST['tx_id'], $uid
        ]);
        $msg = 'Transaction modifiée.';
    }
}

// Filters
$fType = $_GET['type'] ?? '';
$fCat  = $_GET['cat']  ?? '';
$fMonth= $_GET['month']?? '';
$fQ    = trim($_GET['q'] ?? '');

$where = ["t.user_id = $uid"];
$params = [];
if ($fType) { $where[] = "t.type = ?"; $params[] = $fType; }
if ($fCat)  { $where[] = "t.category_id = ?"; $params[] = (int)$fCat; }
if ($fMonth){ $where[] = "DATE_FORMAT(t.tx_date,'%Y-%m') = ?"; $params[] = $fMonth; }
if ($fQ)    { $where[] = "t.description LIKE ?"; $params[] = "%$fQ%"; }

$sql = "SELECT t.*, c.name AS cat_name, c.icon AS cat_icon, b.name AS bud_name
        FROM transactions t
        LEFT JOIN categories c ON c.id=t.category_id
        LEFT JOIN budgets b ON b.id=t.budget_id
        WHERE ".implode(' AND ',$where)."
        ORDER BY t.tx_date DESC, t.id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $txs = $stmt->fetchAll();

// Categories & budgets for form
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$buds = $pdo->prepare("SELECT b.* FROM budgets b LEFT JOIN budget_members bm ON bm.budget_id=b.id WHERE b.owner_id=? OR bm.user_id=? ORDER BY b.name");
$buds->execute([$uid,$uid]); $budgets = $buds->fetchAll();
?>

<div class="page-header">
  <div><div class="page-title">Transactions</div><div class="page-sub">Gérer vos revenus et dépenses</div></div>
  <button class="btn btn-primary" onclick="openModal('m-add')"><i class="ti ti-plus"></i> Nouvelle transaction</button>
</div>

<?php if ($msg): ?>
<div class="alert-box alert-success"><i class="ti ti-check"></i><?= sanitize($msg) ?></div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" class="card" style="margin-bottom:16px">
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
    <div style="flex:1;min-width:160px">
      <label class="text-muted">Rechercher</label>
      <input class="form-control" name="q" value="<?= sanitize($fQ) ?>" placeholder="Description..."/>
    </div>
    <div>
      <label class="text-muted">Type</label>
      <select class="form-control" name="type">
        <option value="">Tous</option>
        <option value="income"  <?= $fType==='income' ?'selected':'' ?>>Revenus</option>
        <option value="expense" <?= $fType==='expense'?'selected':'' ?>>Dépenses</option>
      </select>
    </div>
    <div>
      <label class="text-muted">Catégorie</label>
      <select class="form-control" name="cat">
        <option value="">Toutes</option>
        <?php foreach ($cats as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $fCat==$c['id']?'selected':'' ?>><?= sanitize($c['icon'].' '.$c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-muted">Mois</label>
      <input class="form-control" type="month" name="month" value="<?= sanitize($fMonth) ?>"/>
    </div>
    <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> Filtrer</button>
    <a class="btn" href="transactions.php">Réinitialiser</a>
  </div>
</form>

<div class="card">
  <div class="flex-between" style="margin-bottom:12px">
    <span class="text-muted"><?= count($txs) ?> transaction(s) trouvée(s)</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Catégorie</th><th>Description</th><th>Budget</th><th>Date</th><th>Type</th><th class="text-right">Montant</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($txs as $tx): ?>
        <tr>
          <td><?= sanitize($tx['cat_icon'].' '.$tx['cat_name']) ?></td>
          <td><?= sanitize($tx['description']) ?></td>
          <td><?= sanitize($tx['bud_name'] ?? '—') ?></td>
          <td><?= date('d/m/Y', strtotime($tx['tx_date'])) ?></td>
          <td><span class="badge badge-<?= $tx['type']==='income'?'income':'expense' ?>"><?= $tx['type']==='income'?'Revenu':'Dépense' ?></span></td>
          <td class="text-right <?= $tx['type']==='income'?'amount-pos':'amount-neg' ?>"><?= ($tx['type']==='income'?'+':'-').formatMoney($tx['amount']) ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <button class="btn btn-sm" onclick='openEditModal(<?= json_encode($tx) ?>)'><i class="ti ti-edit"></i></button>
              <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette transaction ?')">
                <input type="hidden" name="tx_id" value="<?= $tx['id'] ?>"/>
                <button class="btn btn-sm btn-danger" name="del_tx" type="submit"><i class="ti ti-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($txs)): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Aucune transaction trouvée.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="m-add">
  <div class="modal-box">
    <div class="modal-title"><i class="ti ti-plus"></i> Nouvelle transaction</div>
    <form method="POST">
      <div class="form-group">
        <label>Type</label>
        <select class="form-control" name="type" required>
          <option value="expense">Dépense</option>
          <option value="income">Revenu</option>
        </select>
      </div>
      <div class="form-group">
        <label>Montant (€)</label>
        <input class="form-control" type="number" name="amount" min="0.01" step="0.01" required placeholder="0.00"/>
      </div>
      <div class="form-group">
        <label>Catégorie</label>
        <select class="form-control" name="category_id">
          <option value="">— Sans catégorie —</option>
          <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>"><?= sanitize($c['icon'].' '.$c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Budget</label>
        <select class="form-control" name="budget_id">
          <option value="">— Personnel —</option>
          <?php foreach ($budgets as $b): ?>
          <option value="<?= $b['id'] ?>"><?= sanitize($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Date</label>
        <input class="form-control" type="date" name="tx_date" value="<?= date('Y-m-d') ?>" required/>
      </div>
      <div class="form-group">
        <label>Description</label>
        <input class="form-control" type="text" name="description" placeholder="Ex : Courses hebdomadaires" required/>
      </div>
      <div class="form-group">
        <label>Commentaire (optionnel)</label>
        <textarea class="form-control" name="comment" rows="2" placeholder="Notes supplémentaires..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="closeModal('m-add')">Annuler</button>
        <button type="submit" name="add_tx" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="m-edit">
  <div class="modal-box">
    <div class="modal-title"><i class="ti ti-edit"></i> Modifier la transaction</div>
    <form method="POST">
      <input type="hidden" name="tx_id" id="edit-id"/>
      <div class="form-group">
        <label>Type</label>
        <select class="form-control" name="type" id="edit-type">
          <option value="expense">Dépense</option>
          <option value="income">Revenu</option>
        </select>
      </div>
      <div class="form-group">
        <label>Montant (€)</label>
        <input class="form-control" type="number" name="amount" id="edit-amount" min="0.01" step="0.01" required/>
      </div>
      <div class="form-group">
        <label>Catégorie</label>
        <select class="form-control" name="category_id" id="edit-cat">
          <option value="">— Sans catégorie —</option>
          <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>"><?= sanitize($c['icon'].' '.$c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
    <label>Budget</label>
    <select class="form-control" name="budget_id" id="edit-budget">
        <option value="">— Personnel —</option>
        <?php foreach ($budgets as $b): ?>
        <option value="<?= $b['id'] ?>">
            <?= sanitize($b['name']) ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>     
      <div class="form-group">
        <label>Date</label>
        <input class="form-control" type="date" name="tx_date" id="edit-date" required/>
      </div>
      <div class="form-group">
        <label>Description</label>
        <input class="form-control" type="text" name="description" id="edit-desc" required/>
      </div>
      <div class="form-group">
        <label>Commentaire</label>
        <textarea class="form-control" name="comment" id="edit-comment" rows="2"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="closeModal('m-edit')">Annuler</button>
        <button type="submit" name="edit_tx" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(tx) {
  document.getElementById('edit-id').value     = tx.id;
  document.getElementById('edit-type').value   = tx.type;
  document.getElementById('edit-amount').value = tx.amount;
  document.getElementById('edit-cat').value    = tx.category_id || '';
  document.getElementById('edit-budget').value  = tx.budget_id || '';
  document.getElementById('edit-date').value   = tx.tx_date;
  document.getElementById('edit-desc').value   = tx.description;
  document.getElementById('edit-comment').value= tx.comment || '';
  openModal('m-edit');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
