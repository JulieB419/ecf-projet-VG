<?php
/**
 * Vue : Nos menus
 *
 * Diagnostic :
 * - La liste était vide car elle dépendait d'un JS externe (assets/app.js)
 *   qui, très souvent, fait un fetch("/api/menus") => ça pointe sur http://localhost/api/menus
 *   au lieu de http://localhost/vite-gourmand/public/api/menus.
 *
 * Fix :
 * - On calcule l'URL API avec url('api/menus') pour respecter le base path.
 * - On remplit la liste via fetch() depuis cette vue, donc même si assets/app.js est cassé,
 *   la page affichera les menus.
 */

$apiUrl   = url('api/menus');   // ex: /vite-gourmand/public/api/menus
$menusUrl = url('menus');       // ex: /vite-gourmand/public/menus
?>

<h1 class="h3 mb-3">Nos menus</h1>

<form class="row g-3 mb-4" id="menuFilters" data-api-url="<?= htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8') ?>" data-menus-url="<?= htmlspecialchars($menusUrl, ENT_QUOTES, 'UTF-8') ?>">
  <div class="col-md-3">
    <label class="form-label" for="price_max">Prix max</label>
    <input type="number" step="0.01" name="price_max" id="price_max" class="form-control">
  </div>

  <div class="col-md-3">
    <label class="form-label" for="price_min">Prix min</label>
    <input type="number" step="0.01" name="price_min" id="price_min" class="form-control">
  </div>

  <div class="col-md-3">
    <label class="form-label" for="price_max_range">Prix max (fourchette)</label>
    <input type="number" step="0.01" name="price_max_range" id="price_max_range" class="form-control">
  </div>

  <div class="col-md-3">
    <label class="form-label" for="min_people">Min. personnes</label>
    <input type="number" name="min_people" id="min_people" class="form-control">
  </div>

  <div class="col-md-6">
    <label class="form-label" for="theme_id">Thème</label>
    <select name="theme_id" id="theme_id" class="form-select">
      <option value="">Tous</option>
      <?php foreach (($themes ?? []) as $t): ?>
        <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars((string)$t['name'], ENT_QUOTES, 'UTF-8') ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="diet_id">Régime</label>
    <select name="diet_id" id="diet_id" class="form-select">
      <option value="">Tous</option>
      <?php foreach (($diets ?? []) as $d): ?>
        <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars((string)$d['name'], ENT_QUOTES, 'UTF-8') ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Filtrer</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($menusUrl, ENT_QUOTES, 'UTF-8') ?>">Réinitialiser</a>
  </div>
</form>

<div id="menuInfo" class="mb-3"></div>
<div class="row" id="menuList"></div>

