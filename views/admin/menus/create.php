<?php
/** @var array $themes */
/** @var array $diets */
/** @var array $dishes */
use App\Core\Session;
$csrf = Session::csrf();
$imgText = '';
?>
<h1 class="mb-3">Créer un menu</h1>

<form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars(url('administration/menus/nouveau'), ENT_QUOTES, 'UTF-8') ?>" class="vstack gap-4">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

  <div class="row g-3">
    <div class="col-lg-6">
      <label class="form-label" for="title">Titre</label>
      <input class="form-control" type="text" id="title" name="title" required>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="theme_id">Thème</label>
      <select class="form-select" id="theme_id" name="theme_id" required>
        <option value="">—</option>
        <?php foreach ($themes as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars((string)$t['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="diet_id">Régime</label>
      <select class="form-select" id="diet_id" name="diet_id" required>
        <option value="">—</option>
        <?php foreach ($diets as $d): ?>
          <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars((string)$d['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-3">
      <label class="form-label" for="min_people">Nombre min. personnes</label>
      <input class="form-control" type="number" min="1" id="min_people" name="min_people" value="10" required>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="base_price">Prix (pour le minimum)</label>
      <input class="form-control" type="number" step="0.01" min="0" id="base_price" name="base_price" value="0" required>
    </div>
    <div class="col-lg-3">
      <label class="form-label" for="stock_available">Stock</label>
      <input class="form-control" type="number" min="0" id="stock_available" name="stock_available" value="0" required>
    </div>
    <div class="col-lg-3 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
        <label class="form-check-label" for="is_active">Menu actif</label>
      </div>
    </div>
  </div>

  <div>
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
  </div>

  <div>
    <label class="form-label" for="conditions">Conditions particulières</label>
    <textarea class="form-control" id="conditions" name="conditions" rows="3" required></textarea>
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
          <label class="menu-image-choice border rounded p-2 d-flex flex-column align-items-center">
            <img class="menu-image-thumb" src="<?= htmlspecialchars($url) ?>" alt="">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="images_existing[]" value="<?= htmlspecialchars($url) ?>" checked>
              <span class="form-check-label menu-image-label">Garder</span>
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
          <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-4">
      <label class="form-label">Plats (multi)</label>
      <select class="form-select" name="plats[]" multiple size="8">
        <?php foreach ($dishes as $p): ?>
          <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-4">
      <label class="form-label">Desserts (multi)</label>
      <select class="form-select" name="desserts[]" multiple size="8">
        <?php foreach ($dishes as $p): ?>
          <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Créer</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration/menus'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
  </div>
</form>
