<?php
/** @var array $dish */
use App\Core\Session;
$csrf = Session::csrf();
?>
<h1 class="mb-3">Modifier un plat #<?= (int)$dish['id'] ?></h1>

<form method="post" action="<?= htmlspecialchars(url('administration/plats/'.$dish['id'].'/modifier'), ENT_QUOTES, 'UTF-8') ?>" class="vstack gap-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

  <div>
    <label class="form-label" for="name">Nom</label>
    <input class="form-control" type="text" id="name" name="name" required value="<?= htmlspecialchars((string)$dish['name'], ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div>
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="6"><?= htmlspecialchars((string)$dish['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>

  <?php
    $t = (string)($dish['type'] ?? 'plat');
    $selectedReg = array_filter(explode(',', (string)($dish['regimes'] ?? '')));
    $selectedAll = array_filter(explode(',', (string)($dish['allergens'] ?? '')));
  ?>

  <div>
    <label class="form-label" for="type">Type</label>
    <select class="form-select" id="type" name="type">
      <option value="entree" <?= $t==='entree'?'selected':'' ?>>Entrée</option>
      <option value="plat" <?= $t==='plat'?'selected':'' ?>>Plat</option>
      <option value="dessert" <?= $t==='dessert'?'selected':'' ?>>Dessert</option>
    </select>
  </div>

  <div>
    <label class="form-label" for="regimes">Régimes (Ctrl/Cmd + clic pour multi)</label>
    <select class="form-select" id="regimes" name="regimes[]" multiple size="5">
      <?php foreach (['classique'=>'Classique','vegetarien'=>'Végétarien','vegan'=>'Vegan','halal'=>'Halal','sans_gluten'=>'Sans gluten','sans_lactose'=>'Sans lactose'] as $val=>$lab): ?>
        <option value="<?= $val ?>" <?= in_array($val, $selectedReg, true)?'selected':'' ?>><?= $lab ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">Sélection actuelle : <span id="regimesPreview" style="display:inline-block;margin-left:6px;"></span></div>
  </div>

  <div>
    <label class="form-label" for="allergens">Allergènes (Ctrl/Cmd + clic pour multi)</label>
    <select class="form-select" id="allergens" name="allergens[]" multiple size="8">
      <?php foreach (['gluten'=>'Gluten','crustaces'=>'Crustacés','oeufs'=>'Œufs','poissons'=>'Poissons','arachides'=>'Arachides','soja'=>'Soja','lait'=>'Lait','fruits_a_coque'=>'Fruits à coque','celeri'=>'Céleri','moutarde'=>'Moutarde','sesame'=>'Sésame','sulfites'=>'Sulfites','lupin'=>'Lupin','mollusques'=>'Mollusques'] as $val=>$lab): ?>
        <option value="<?= $val ?>" <?= in_array($val, $selectedAll, true)?'selected':'' ?>><?= $lab ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">Sélection actuelle : <span id="allergensPreview" style="display:inline-block;margin-left:6px;"></span></div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Sauvegarder</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration/plats'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
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
