<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Espace employé</h1>

<form class="row g-2 mb-4" method="get">
  <div class="col-md-3">
    <select name="status" class="form-select">
      <option value="">Tous</option>
      <?php $statuses=['en_attente','acceptee','en_preparation','en_livraison','livree','attente_retour_materiel','terminee','annulee']; ?>
      <?php foreach ($statuses as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status']===$s?'selected':'') ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-5"><input class="form-control" name="q" placeholder="Recherche client" value="<?= htmlspecialchars($filters['q']) ?>"></div>
  <div class="col-md-4 d-flex gap-2">
    <button class="btn btn-primary">Filtrer</button>
    <a class="btn btn-outline-secondary" href="<?= $baseUrl ?>/espace-employe/horaires">Horaires</a>
  </div>
</form>

<h2 class="h5">Commandes</h2>
<div class="list-group mb-5">
  <?php foreach ($orders as $o): ?>
    <a class="list-group-item list-group-item-action" href="<?= $baseUrl ?>/espace-employe/commandes/<?= (int)$o['id'] ?>">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-semibold">#<?= (int)$o['id'] ?> — <?= htmlspecialchars($o['menu_title']) ?></div>
          <div class="small text-muted"><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?> — <?= htmlspecialchars($o['email']) ?></div>
        </div>
        <div class="text-end">
          <?php
            $status = (string)($o['status'] ?? '');
            $badge = 'text-bg-warning';
            if ($status === 'annulee') {
              $badge = 'text-bg-danger';
            } elseif ($status === 'terminee') {
              $badge = 'text-bg-success';
            }
          ?>
          <div class="badge <?= $badge ?>"><?= htmlspecialchars($status) ?></div>
          <div class="small text-muted"><?= number_format((float)$o['total_price'],2,',',' ') ?> €</div>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<h2 class="h5">Avis en attente</h2>
<div class="row g-3">
  <?php foreach ($pendingReviews as $r): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100"><div class="card-body">
        <div class="small text-muted mb-2"><?= htmlspecialchars($r['email']) ?></div>
        <div class="mb-2"><?= str_repeat('★',(int)$r['rating']) ?></div>
        <p><?= htmlspecialchars($r['comment']) ?></p>
        <div class="d-flex gap-2">
          <form method="post" action="<?= $baseUrl ?>/espace-employe/avis/<?= (int)$r['id'] ?>/valider">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
            <button class="btn btn-sm btn-success">Valider</button>
          </form>
          <form method="post" action="<?= $baseUrl ?>/espace-employe/avis/<?= (int)$r['id'] ?>/refuser">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
            <button class="btn btn-sm btn-outline-danger">Refuser</button>
          </form>
        </div>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>
