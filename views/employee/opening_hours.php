<?php use App\Core\Session; $days=['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche']; ?>
<h1 class="h3 mb-3">Horaires</h1>

<form method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <?php foreach ($hours as $h): ?>
    <div class="col-12 border rounded p-3">
      <div class="fw-semibold mb-2"><?= $days[(int)$h['day_of_week']] ?></div>
      <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Ouverture</label><input class="form-control" type="time" name="day[<?= (int)$h['day_of_week'] ?>][open]" value="<?= htmlspecialchars($h['open_time']) ?>"></div>
        <div class="col-md-3"><label class="form-label">Fermeture</label><input class="form-control" type="time" name="day[<?= (int)$h['day_of_week'] ?>][close]" value="<?= htmlspecialchars($h['close_time']) ?>"></div>
        <div class="col-md-3">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="day[<?= (int)$h['day_of_week'] ?>][closed]" <?= ((int)$h['is_closed']===1?'checked':'') ?>>
            <label class="form-check-label">Fermé</label>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary">Enregistrer</button>
    <a class="btn btn-outline-secondary" href="<?= $baseUrl ?>/espace-employe">Retour</a>
  </div>
</form>
