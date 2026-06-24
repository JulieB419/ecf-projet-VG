<h1 class="h3 mb-3">Mon profil</h1>

<div class="card mb-4"><div class="card-body">
  <div><strong><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></strong></div>
  <div class="text-muted"><?= htmlspecialchars($user['email']) ?></div>
</div></div>

<h2 class="h5" id="commandes">Mes commandes</h2>
<?php if (empty($orders)): ?>
  <p class="text-muted">Aucune commande.</p>
<?php else: ?>
  <div class="list-group">
    <?php foreach ($orders as $o): ?>
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
         href="<?= htmlspecialchars(url('profil/commandes'), ENT_QUOTES, 'UTF-8') ?>/<?= (int)$o['id'] ?>">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($o['menu_title']) ?></div>
          <div class="small text-muted">#<?= (int)$o['id'] ?> — <?= htmlspecialchars($o['status']) ?></div>
        </div>
        <span class="badge text-bg-light"><?= number_format((float)$o['total_price'],2,',',' ') ?> €</span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
