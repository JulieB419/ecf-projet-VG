<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Commande #<?= (int)$order['id'] ?></h1>

<div class="card mb-3"><div class="card-body">
  <div class="fw-semibold"><?= htmlspecialchars($order['menu_title']) ?></div>
  <div class="small text-muted">Client : <?= htmlspecialchars($order['first_name'].' '.$order['last_name']) ?> — <?= htmlspecialchars($order['email']) ?></div>
  <hr>
  <div>Prestation : <?= htmlspecialchars($order['prestation_address']) ?>, <?= htmlspecialchars($order['prestation_city']) ?></div>
  <div>Date/heure : <?= htmlspecialchars($order['prestation_date']) ?> <?= htmlspecialchars($order['prestation_time']) ?></div>
  <div>Personnes : <?= (int)$order['people_count'] ?></div>
  <div class="mt-2"><span class="badge text-bg-light">Total : <?= number_format((float)$order['total_price'],2,',',' ') ?> €</span></div>
</div></div>

<h2 class="h5">Changer le statut</h2>
<form method="post" action="<?= htmlspecialchars(url('espace-employe/commandes'), ENT_QUOTES, 'UTF-8') ?>/<?= (int)$order['id'] ?>/statut" class="row g-2 mb-4">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <div class="col-md-6">
    <select name="status" class="form-select" required>
      <option value="acceptee">acceptée</option>
      <option value="en_preparation">en préparation</option>
      <option value="en_livraison">en livraison</option>
      <option value="livree">livrée</option>
      <option value="attente_retour_materiel">attente retour matériel</option>
      <option value="terminee">terminée</option>
    </select>
  </div>
  <div class="col-md-6"><button class="btn btn-primary">Mettre à jour</button></div>
</form>

<h2 class="h5">Annuler (contact obligatoire)</h2>
<form method="post" action="<?= htmlspecialchars(url('espace-employe/commandes'), ENT_QUOTES, 'UTF-8') ?>/<?= (int)$order['id'] ?>/annuler" class="row g-2 mb-4">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <div class="col-md-3">
    <label class="form-label">Mode</label>
    <select name="contact_mode" class="form-select" required>
      <option value="appel">Appel</option>
      <option value="mail">Mail</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Date</label>
    <input type="date" name="contact_date" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Motif</label>
    <input name="reason" class="form-control" required>
  </div>
  <div class="col-12">
    <div class="alert alert-warning small">Interdiction d'annuler sans contact (appel ou mail).</div>
    <button class="btn btn-outline-danger" onclick="return confirm('Annuler ?')">Annuler</button>
  </div>
</form>

<h2 class="h5">Historique</h2>
<ul class="list-group">
  <?php foreach ($order['history'] as $h): ?>
    <li class="list-group-item d-flex justify-content-between">
      <span><?= htmlspecialchars($h['status']) ?></span>
      <span class="small text-muted"><?= htmlspecialchars($h['changed_at']) ?></span>
    </li>
  <?php endforeach; ?>
</ul>
