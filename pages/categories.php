<?php
require_once __DIR__ . '/../includes/header.php';
$pdo = getDB();
$uid = $user['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_cat'])) {
        $pdo->prepare("INSERT INTO categories (user_id,name,icon,color) VALUES (?,?,?,?)")
            ->execute([$uid, trim($_POST['name']), trim($_POST['icon']) ?: '💰', $_POST['color'] ?: '#DBEAFE']);
        $msg = 'Catégorie créée !';
    } elseif (isset($_POST['del_cat'])) {
        $pdo->prepare("DELETE FROM categories WHERE id=? AND user_id=?")->execute([(int)$_POST['cid'], $uid]);
        $msg = 'Catégorie supprimée.';
    } elseif (isset($_POST['edit_cat'])) {
        $pdo->prepare("UPDATE categories SET name=?,icon=?,color=? WHERE id=? AND user_id=?")
            ->execute([trim($_POST['name']), trim($_POST['icon']), $_POST['color'], (int)$_POST['cid'], $uid]);
        $msg = 'Catégorie modifiée.';
    }
}

$cats = $pdo->query("SELECT c.*, COALESCE((SELECT SUM(amount) FROM transactions WHERE category_id=c.id AND user_id=".($uid)." AND type='expense'),0) AS total FROM categories c WHERE c.user_id IS NULL OR c.user_id=$uid ORDER BY c.name")->fetchAll();
$colors = ['#FEF9C3','#DBEAFE','#D1FAE5','#EDE9FE','#FEE2E2','#E0F2FE','#FFEDD5','#FCE7F3'];
?>

<div class="page-header">
  <div><div class="page-title">Catégories</div><div class="page-sub">Organiser vos dépenses par catégorie</div></div>
  <button class="btn btn-primary" onclick="openModal('m-add-cat')"><i class="ti ti-plus"></i> Nouvelle catégorie</button>
</div>

<?php if ($msg): ?>
<div class="alert-box alert-success"><i class="ti ti-check"></i><?= sanitize($msg) ?></div>
<?php endif; ?>

<div class="cat-grid">
  <?php foreach ($cats as $c): ?>
  <div class="cat-item">
    <div class="cat-icon" style="background:<?= sanitize($c['color']) ?>"><?= sanitize($c['icon']) ?></div>
    <div style="flex:1;min-width:0">
      <div class="cat-name"><?= sanitize($c['name']) ?></div>
      <div class="cat-total"><?= formatMoney($c['total']) ?></div>
    </div>
    <div class="cat-actions">
      <?php if ($c['user_id'] == $uid): ?>
      <button class="btn btn-sm" onclick='openEditCat(<?= json_encode($c) ?>)'><i class="ti ti-edit"></i>✎</button>
      <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
        <input type="hidden" name="cid" value="<?= $c['id'] ?>"/>
        <button class="btn btn-sm btn-danger" name="del_cat" onclick="return confirm('Supprimer cette catégorie ?')">
    <i class="ti ti-trash"></i> 🗑️
</button>
      </form>
      <?php else: ?>
      <span class="text-muted" style="font-size:11px">Défaut</span>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="m-add-cat">
  <div class="modal-box">
    <div class="modal-title"><i class="ti ti-tag"></i> Nouvelle catégorie</div>
    <form method="POST">
      <div class="form-group"><label>Nom</label>
        <input class="form-control" type="text" name="name" required placeholder="Ex : Abonnements"/>
      </div>
      <div class="form-group"><label>Icône (emoji)</label>
        <input class="form-control" type="text" name="icon" value="💰" maxlength="4" style="width:80px"/>
      </div>
      <div class="form-group"><label>Couleur</label>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px">
          <?php foreach ($colors as $col): ?>
          <label style="cursor:pointer"><input type="radio" name="color" value="<?= $col ?>" style="display:none" <?= $col==='#DBEAFE'?'checked':'' ?>/><div style="width:26px;height:26px;border-radius:6px;background:<?= $col ?>;border:2px solid transparent" class="col-swatch"></div></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="closeModal('m-add-cat')">Annuler</button>
        <button type="submit" name="add_cat" class="btn btn-primary">Créer</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="m-edit-cat">
  <div class="modal-box">
    <div class="modal-title"><i class="ti ti-edit"></i> Modifier la catégorie</div>
    <form method="POST">
      <input type="hidden" name="cid" id="edit-cid"/>
      <div class="form-group"><label>Nom</label>
        <input class="form-control" type="text" name="name" id="edit-cname" required/>
      </div>
      <div class="form-group"><label>Icône</label>
        <input class="form-control" type="text" name="icon" id="edit-cicon" maxlength="4" style="width:80px"/>
      </div>
      <input type="hidden" name="color" id="edit-ccolor" value="#DBEAFE"/>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="closeModal('m-edit-cat')">Annuler</button>
        <button type="submit" name="edit_cat" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
// Highlight selected color swatch
document.querySelectorAll('.col-swatch').forEach(s => {
  const inp = s.previousElementSibling;
  if (inp && inp.checked) s.style.borderColor = '#378ADD';
  s.parentElement.addEventListener('change', () => {
    document.querySelectorAll('.col-swatch').forEach(x => x.style.borderColor='transparent');
    s.style.borderColor = '#378ADD';
  });
});
function openEditCat(c) {
  document.getElementById('edit-cid').value   = c.id;
  document.getElementById('edit-cname').value = c.name;
  document.getElementById('edit-cicon').value = c.icon;
  document.getElementById('edit-ccolor').value= c.color;
  openModal('m-edit-cat');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
