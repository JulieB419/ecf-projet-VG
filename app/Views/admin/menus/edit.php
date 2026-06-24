<?php
/** @var array $menu */
/** @var array $themes */
/** @var array $diets */
/** @var array $dishes */
/** @var array $selected */
use App\Core\Session;
$csrf = Session::csrf();

$imgText = '';
if (!empty($menu['images']) && is_array($menu['images'])) {
    $imgText = implode("\n", $menu['images']);
}
?>
<h1 class="mb-3">Modifier le menu #<?= (int)$menu['id'] ?></h1>

<form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars(url('administration/menus/'.$menu['id'].'/modifier'), ENT_QUOTES, 'UTF-8') ?>" class="vstack gap-4">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

  <div class="row g-3">
    <div class="col-lg-6">
      <label class="form-label" for="title">Titre</label>
      <input class="form-control" type="text" id="title" name="title" required value="<?= htmlspecialchars((string)$menu['title'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="theme_id">Thème</label>
      <select class="form-select" id="theme_id" name="theme_id" required>
        <?php foreach ($themes as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ((int)$t['id']===(int)$menu['theme_id'])?'selected':'' ?>>
            <?= htmlspecialchars((string)$t['name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="diet_id">Régime</label>
      <select class="form-select" id="diet_id" name="diet_id" required>
        <?php foreach ($diets as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ((int)$d['id']===(int)$menu['diet_id'])?'selected':'' ?>>
            <?= htmlspecialchars((string)$d['name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-3">
      <label class="form-label" for="min_people">Nombre min. personnes</label>
      <input class="form-control" type="number" min="1" id="min_people" name="min_people" value="<?= (int)$menu['min_people'] ?>" required>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="base_price">Prix (pour le minimum)</label>
      <input class="form-control" type="number" step="0.01" min="0" id="base_price" name="base_price" value="<?= htmlspecialchars((string)$menu['base_price'], ENT_QUOTES, 'UTF-8') ?>" required>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="stock_available">Stock</label>
      <input class="form-control" type="number" min="0" id="stock_available" name="stock_available" value="<?= (int)$menu['stock_available'] ?>" required>
    </div>
    <div class="col-lg-3 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= ((int)$menu['is_active']===1)?'checked':'' ?>>
        <label class="form-check-label" for="is_active">Menu actif</label>
      </div>
    </div>
  </div>

  <div>
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars((string)$menu['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>

  <div>
    <label class="form-label" for="conditions">Conditions particulières</label>
    <textarea class="form-control" id="conditions" name="conditions" rows="3" required><?= htmlspecialchars((string)$menu['conditions'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Images</label>

    <?php
      $imagesArr = array_values(array_filter(array_map('trim', explode("
", (string)$imgText))));
    ?>

    <?php if (!empty($imagesArr)): ?>
      <div class="d-flex flex-wrap gap-2 mb-2">
        <?php foreach ($imagesArr as $url): ?>
          <label class="border rounded p-2 d-flex flex-column align-items-center" style="width:140px;">
            <img src="<?= htmlspecialchars($url) ?>" alt="" style="max-width:120px; max-height:90px; object-fit:cover;">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="images_existing[]" value="<?= htmlspecialchars($url) ?>" checked>
              <span class="form-check-label" style="font-size:.85rem;">Garder</span>
            </div>
          </label>
        <?php endforeach; ?>
      </div>
      <small class="text-muted d-block mb-2">Décochez une image pour la supprimer du menu.</small>
    <?php endif; ?>

    <input class="form-control" type="file" name="images_upload[]" accept="image/*" multiple>
    <small class="text-muted">Ajoutez des images depuis votre PC. Elles seront enregistrées et liées au menu.</small>

    <!-- Compatibilité : champ historique (non affiché) -->
    <textarea class="d-none" id="images" name="images" rows="3"><?= htmlspecialchars((string)$imgText, ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>


  <div class="row g-3">
    <div class="col-lg-4">
      <label class="form-label">Entrées (multi)</label>
      <select class="form-select" name="entrees[]" multiple size="8">
        <?php foreach ($dishes as $p): ?>
          <?php $sel = in_array((int)$p['id'], $selected['entree'] ?? [], true); ?>
          <option value="<?= (int)$p['id'] ?>" <?= $sel?'selected':'' ?>><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-4">
      <label class="form-label">Plats (multi)</label>
      <select class="form-select" name="plats[]" multiple size="8">
        <?php foreach ($dishes as $p): ?>
          <?php $sel = in_array((int)$p['id'], $selected['plat'] ?? [], true); ?>
          <option value="<?= (int)$p['id'] ?>" <?= $sel?'selected':'' ?>><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-4">
      <label class="form-label">Desserts (multi)</label>
      <select class="form-select" name="desserts[]" multiple size="8">
        <?php foreach ($dishes as $p): ?>
          <?php $sel = in_array((int)$p['id'], $selected['dessert'] ?? [], true); ?>
          <option value="<?= (int)$p['id'] ?>" <?= $sel?'selected':'' ?>><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Sauvegarder</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration/menus'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
  </div>
</form>
