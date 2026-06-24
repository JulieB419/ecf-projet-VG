<?php
/** @var array $settings */
/** @var array $hours */
/** @var bool $saved */
$title = "Informations du traiteur";
?>
<h1 class="mb-3">Informations du traiteur</h1>

<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Enregistré ✅</div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(url('administration/informations'), ENT_QUOTES, 'UTF-8') ?>" class="card p-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\Core\Session::csrf()) ?>">

  <div class="mb-3">
    <label class="form-label">Adresse (affichée dans le footer)</label>
    <textarea class="form-control" name="caterer_address" rows="4" placeholder="Adresse sur plusieurs lignes..."><?= htmlspecialchars($settings['caterer_address'] ?? "12 Rue des Gourmets\n33000 Bordeaux") ?></textarea>
    <small class="text-muted">Astuce : une ligne par saut de ligne.</small>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Téléphone</label>
      <input class="form-control" name="caterer_phone" value="<?= htmlspecialchars($settings['caterer_phone'] ?? '') ?>" placeholder="06 ...">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Email</label>
      <input class="form-control" name="caterer_email" value="<?= htmlspecialchars($settings['caterer_email'] ?? '') ?>" placeholder="contact@...">
    </div>
  </div>

  <button class="btn btn-primary" type="submit">Enregistrer</button>
</form>


<?php
use App\Core\Session;
$days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$byDay = [];
foreach (($hours ?? []) as $h) { $byDay[(int)$h['day_of_week']] = $h; }
?>

<h2 class="h4 mt-4 mb-3">Horaires d'ouverture (affichés dans le footer)</h2>
<form method="post" action="<?= htmlspecialchars(url('administration/informations/horaires'), ENT_QUOTES, 'UTF-8') ?>" class="card p-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Jour</th>
          <th>Ouverture</th>
          <th>Fermeture</th>
          <th class="text-center">Fermé</th>
        </tr>
      </thead>
      <tbody>
        <?php for ($d=0; $d<=6; $d++):
          $h = $byDay[$d] ?? ['open_time'=>'09:00:00','close_time'=>'18:00:00','is_closed'=>0];
          $open = substr((string)$h['open_time'],0,5);
          $close = substr((string)$h['close_time'],0,5);
          $isClosed = (int)($h['is_closed'] ?? 0) === 1;
        ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($days[$d]) ?></td>
          <td style="min-width:140px"><input type="time" class="form-control" name="open_time[<?= $d ?>]" value="<?= htmlspecialchars($open) ?>" <?= $isClosed ? 'disabled' : '' ?>></td>
          <td style="min-width:140px"><input type="time" class="form-control" name="close_time[<?= $d ?>]" value="<?= htmlspecialchars($close) ?>" <?= $isClosed ? 'disabled' : '' ?>></td>
          <td class="text-center">
            <input class="form-check-input" type="checkbox" name="is_closed[<?= $d ?>]" value="1" <?= $isClosed ? 'checked' : '' ?>>
          </td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </div>

  <button class="btn btn-primary" type="submit">Enregistrer les horaires</button>
  <small class="text-muted mt-2 d-block">Astuce : coche "Fermé" pour masquer les heures d'un jour.</small>
</form>

<script>
(function(){
  function toggle(row){
    const cb = row.querySelector('input[type=checkbox]');
    const times = row.querySelectorAll('input[type=time]');
    times.forEach(i => i.disabled = cb.checked);
  }
  document.querySelectorAll('table tbody tr').forEach(tr => {
    const cb = tr.querySelector('input[type=checkbox]');
    if(!cb) return;
    toggle(tr);
    cb.addEventListener('change', ()=>toggle(tr));
  });
})();
</script>
