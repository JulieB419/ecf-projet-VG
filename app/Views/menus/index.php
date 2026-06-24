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

<form class="row g-3 mb-4" id="menuFilters">
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

<script>
(function () {
  const apiUrl   = <?= json_encode($apiUrl) ?>;
  const menusUrl = <?= json_encode($menusUrl) ?>;

  const form = document.getElementById('menuFilters');
  const list = document.getElementById('menuList');
  const info = document.getElementById('menuInfo');

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function renderItems(items) {
    list.innerHTML = '';

    if (!items || items.length === 0) {
      info.innerHTML = '<div class="alert alert-warning mb-0">Aucun menu ne correspond aux filtres.</div>';
      return;
    }

    info.innerHTML = '<div class="alert alert-light border mb-0">Menus trouvés : <strong>' + items.length + '</strong></div>';

    for (const m of items) {
      const detailUrl = menusUrl + '/' + encodeURIComponent(m.id);

      list.insertAdjacentHTML('beforeend', `
        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h5 card-title mb-2">${escapeHtml(m.title)}</h2>
              <p class="card-text text-muted mb-2">${escapeHtml(m.short_description ?? '')}</p>
              <ul class="list-unstyled small mb-3">
                <li><strong>Thème :</strong> ${escapeHtml(m.theme ?? '')}</li>
                <li><strong>Régime :</strong> ${escapeHtml(m.diet ?? '')}</li>
                <li><strong>Min. personnes :</strong> ${escapeHtml(m.min_people ?? '')}</li>
                <li><strong>À partir de :</strong> ${escapeHtml(m.base_price ?? '')} €</li>
              </ul>
              <a class="btn btn-outline-primary" href="${detailUrl}">En savoir plus</a>
            </div>
          </div>
        </div>
      `);
    }
  }

  async function load(params) {
    const qs = params.toString();
    const url = qs ? (apiUrl + '?' + qs) : apiUrl;

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      renderItems(data.items || []);
    } catch (e) {
      console.error(e);
      info.innerHTML = '<div class="alert alert-danger mb-0">Erreur lors du chargement des menus.</div>';
      list.innerHTML = '';
    }
  }

  // Au submit : on charge sans recharger la page
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(form);
    const params = new URLSearchParams();
    for (const [k, v] of fd.entries()) {
      if (String(v).trim() !== '') params.set(k, String(v).trim());
    }
    load(params);
  });

  // Chargement initial (sans filtre => tous les menus)
  load(new URLSearchParams());
})();
</script>
