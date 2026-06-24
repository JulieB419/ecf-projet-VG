<?php
use App\Core\Session;
$csrf = Session::csrf();
?>
<h1 class="mb-3">Ajouter un plat</h1>

<form method="post" action="<?= htmlspecialchars(url('administration/plats/nouveau'), ENT_QUOTES, 'UTF-8') ?>" class="vstack gap-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

  <div>
    <label class="form-label" for="name">Nom</label>
    <input class="form-control" type="text" id="name" name="name" required>
  </div>

  <div>
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
    <div class="form-text">Tu peux aussi taguer le plat via les champs ci-dessous (type / régimes / allergènes).</div>
  </div>

  <div>
    <label class="form-label" for="type">Type</label>
    <select class="form-select" id="type" name="type">
      <option value="entree">Entrée</option>
      <option value="plat" selected>Plat</option>
      <option value="dessert">Dessert</option>
    </select>
  </div>

  <div>
    <label class="form-label" for="regimes">Régimes (Ctrl/Cmd + clic pour multi)</label>
    <select class="form-select" id="regimes" name="regimes[]" multiple size="5">
      <option value="classique" selected>Classique</option>
      <option value="vegetarien">Végétarien</option>
      <option value="vegan">Vegan</option>
      <option value="halal">Halal</option>
      <option value="sans_gluten">Sans gluten</option>
      <option value="sans_lactose">Sans lactose</option>
    </select>
    <div class="form-text">Sélection actuelle : <span id="regimesPreview" style="display:inline-block;margin-left:6px;"></span></div>
  </div>

  <div>
    <label class="form-label" for="allergens">Allergènes (Ctrl/Cmd + clic pour multi)</label>
    <select class="form-select" id="allergens" name="allergens[]" multiple size="8">
      <option value="gluten">Gluten</option>
      <option value="crustaces">Crustacés</option>
      <option value="oeufs">Œufs</option>
      <option value="poissons">Poissons</option>
      <option value="arachides">Arachides</option>
      <option value="soja">Soja</option>
      <option value="lait">Lait</option>
      <option value="fruits_a_coque">Fruits à coque</option>
      <option value="celeri">Céleri</option>
      <option value="moutarde">Moutarde</option>
      <option value="sesame">Sésame</option>
      <option value="sulfites">Sulfites</option>
      <option value="lupin">Lupin</option>
      <option value="mollusques">Mollusques</option>
    </select>
    <div class="form-text">Sélection actuelle : <span id="allergensPreview" style="display:inline-block;margin-left:6px;"></span></div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Enregistrer</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration/plats'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
  </div>
</form>

<script>
  function asBadges(selectEl) {
    const sel = Array.from(selectEl.selectedOptions).map(o => o.textContent.trim()).filter(Boolean);
    return sel.length
      ? sel.map(t => `<span style="display:inline-block;padding:3px 8px;margin:2px;border:1px solid #ddd;border-radius:999px;font-size:12px;">${t}</span>`).join('')
      : '<span style="opacity:.7">(aucun)</span>';
  }
  const regimes = document.getElementById('regimes');
  const allergens = document.getElementById('allergens');
  const regimesPreview = document.getElementById('regimesPreview');
  const allergensPreview = document.getElementById('allergensPreview');
  function render() {
    if (regimes && regimesPreview) regimesPreview.innerHTML = asBadges(regimes);
    if (allergens && allergensPreview) allergensPreview.innerHTML = asBadges(allergens);
  }
  if (regimes) regimes.addEventListener('change', render);
  if (allergens) allergens.addEventListener('change', render);
  render();
</script>
