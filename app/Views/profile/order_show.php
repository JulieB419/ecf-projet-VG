<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Commande #<?= (int)$order['id'] ?></h1>

<div class="card mb-3"><div class="card-body">
  <div class="fw-semibold"><?= htmlspecialchars($order['menu_title']) ?></div>
  <div class="small text-muted">Statut : <?= htmlspecialchars($order['status']) ?></div>
  <hr>
  <div>Prestation : <?= htmlspecialchars($order['prestation_address']) ?>, <?= htmlspecialchars($order['prestation_city']) ?></div>
  <div>Date/heure : <?= htmlspecialchars($order['prestation_date']) ?> <?= htmlspecialchars($order['prestation_time']) ?></div>
  <div>Personnes : <?= (int)$order['people_count'] ?></div>
  <div class="mt-2">
    <span class="badge text-bg-light">Total : <?= number_format((float)$order['total_price'],2,',',' ') ?> €</span>
  </div>
</div></div>

<h2 class="h5">Suivi</h2>
<ul class="list-group mb-4">
  <?php foreach ($order['history'] as $h): ?>
    <li class="list-group-item d-flex justify-content-between">
      <span><?= htmlspecialchars($h['status']) ?></span>
      <span class="small text-muted"><?= htmlspecialchars($h['changed_at']) ?></span>
    </li>
  <?php endforeach; ?>
</ul>

<?php if ($order['status']==='en_attente'): ?>
  <form method="post" action="<?= htmlspecialchars(url('profil/commandes'), ENT_QUOTES, 'UTF-8') ?>/<?= (int)$order['id'] ?>/annuler">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
    <button class="btn btn-outline-danger" onclick="return confirm('Annuler ?')">Annuler ma commande</button>
  </form>
<?php endif; ?>

<?php if ($order['status']==='terminee'): ?>
  <hr>
  <h2 class="h5">Donner mon avis</h2>
  <form method="post" action="<?= htmlspecialchars(url('profil/commandes'), ENT_QUOTES, 'UTF-8') ?>/<?= (int)$order['id'] ?>/avis" class="row g-3">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
    <div class="col-md-3"><label class="form-label">Note (1-5)</label><input class="form-control" type="number" min="1" max="5" name="rating" required></div>
    <div class="col-12"><label class="form-label">Commentaire</label><textarea class="form-control" name="comment" rows="3" required></textarea></div>
    <div class="col-12"><button class="btn btn-primary">Envoyer</button></div>
  </form>
<?php endif; ?>
