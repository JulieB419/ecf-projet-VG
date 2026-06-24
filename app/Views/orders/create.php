<?php
/** @var array $menu */
/** @var array $menus */

use App\Core\Session;

$csrf = '';
if (method_exists(Session::class, 'csrf')) {
    $csrf = (string) Session::csrf();
} elseif (isset($_SESSION['csrf'])) {
    $csrf = (string) $_SESSION['csrf'];
}
?>

<h1 class="mb-3">Commander</h1>

<div class="mb-4 p-3 border rounded bg-light">
  <div class="fw-semibold mb-1">Conditions particulières</div>
  <div class="small text-muted">
    <?= htmlspecialchars((string)($menu['conditions'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
  </div>
</div>

<form id="orderForm" method="post" action="<?= htmlspecialchars(url('commander/' . (int)$menu['id']), ENT_QUOTES, 'UTF-8') ?>"
      data-api-menu-base="<?= htmlspecialchars(url('api/menus'), ENT_QUOTES, 'UTF-8') ?>"
      data-commander-base="<?= htmlspecialchars(url('commander'), ENT_QUOTES, 'UTF-8') ?>/">

  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" id="distance_km" name="distance_km" value="0">

  <!-- 1) Choix du menu (modifiable) -->
  <div class="mb-3">
    <label class="form-label" for="menu_id">Menu</label>
    <select class="form-select" id="menu_id">
      <?php foreach (($menus ?? []) as $m): ?>
        <option value="<?= (int)$m['id'] ?>" <?= ((int)$m['id'] === (int)$menu['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars((string)$m['title'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">
      Vous pouvez changer de menu ici : les plats proposés et le prix se mettront à jour.
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="diet_filter">Filtrer les plats par régime</label>
    <select class="form-select" id="diet_filter">
      <option value="">Tous les régimes</option>
    </select>
  </div>


  <!-- 2) Composition (entrée / plat / dessert) -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label" for="entree_id">Entrée</label>
      <select class="form-select" id="entree_id" name="entree_id"></select>
      <div id="entree_preview" class="small text-muted mt-2"></div>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="plat_id">Plat</label>
      <select class="form-select" id="plat_id" name="plat_id"></select>
      <div id="plat_preview" class="small text-muted mt-2"></div>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="dessert_id">Dessert</label>
      <select class="form-select" id="dessert_id" name="dessert_id"></select>
      <div id="dessert_preview" class="small text-muted mt-2"></div>
    </div>
  </div>

  <!-- 3) Infos prestation -->
  <h2 class="h5 mt-4">Informations de livraison / prestation</h2>

  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label" for="prestation_address">Adresse de prestation</label>
      <input class="form-control" type="text" id="prestation_address" name="prestation_address" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="prestation_city">Ville</label>
      <input class="form-control" type="text" id="prestation_city" name="prestation_city" placeholder="ex: Bordeaux" required>
      <div class="form-text">
        Distance estimée depuis Bordeaux (calcul basé sur la ville).
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="prestation_date">Date</label>
      <input class="form-control" type="date" id="prestation_date" name="prestation_date" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="prestation_time">Heure souhaitée</label>
      <input class="form-control" type="time" id="prestation_time" name="prestation_time" required>
    </div>
  </div>

  <!-- 4) Nombre de personnes + prix dynamique -->
  <h2 class="h5 mt-4">Nombre de personnes</h2>

  <div class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label" for="people_count">Nombre de personnes</label>
      <input class="form-control" type="number" id="people_count" name="people_count"
             min="<?= (int)$menu['min_people'] ?>" value="<?= (int)$menu['min_people'] ?>" required>
      <div class="form-text">
        Minimum requis : <strong id="min_people_txt"><?= (int)$menu['min_people'] ?></strong> personne(s).
        <br><em>Réduction -10% à partir de <span id="discount_threshold_txt"><?= (int)$menu['min_people'] + 5 ?></span> personnes.</em>
      </div>
    </div>

    <div class="col-md-8">
      <div class="p-3 border rounded bg-light">
        <div class="d-flex justify-content-between">
          <span>Prix menu</span>
          <strong id="price_menu">—</strong>
        </div>
        <div class="d-flex justify-content-between small text-muted">
          <span>Frais de déplacement</span>
          <span id="price_delivery">—</span>
        </div>
        <div class="d-flex justify-content-between small text-muted" id="discount_row" style="display:none;">
          <span>Réduction</span>
          <span id="price_discount">—</span>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between">
          <span>Total estimé</span>
          <strong id="price_total">—</strong>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Confirmer ces informations</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('menus/' . (int)$menu['id']), ENT_QUOTES, 'UTF-8') ?>">Retour au menu</a>
  </div>

  <input type="hidden" name="confirm" value="0" id="confirmFlag">
</form>
