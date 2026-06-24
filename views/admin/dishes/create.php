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
    <div class="form-text">Sélection actuelle : <span id="regimesPreview" class="selection-preview"></span></div>
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
    <div class="form-text">Sélection actuelle : <span id="allergensPreview" class="selection-preview"></span></div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Enregistrer</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration/plats'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
  </div>
</form>

