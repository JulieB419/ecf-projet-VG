<?php
/** @var array $dishes */
use App\Core\Session;
$csrf = Session::csrf();
?>
<h1 class="mb-3">Plats</h1>

<div class="d-flex gap-2 mb-3">
  <a class="btn btn-primary" href="<?= htmlspecialchars(url('administration/plats/nouveau'), ENT_QUOTES, 'UTF-8') ?>">Ajouter un plat</a>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
</div>

<?php if (empty($dishes)): ?>
  <div class="alert alert-info">Aucun plat pour le moment.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th style="width:90px;">ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th style="width:240px;">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($dishes as $d): ?>
        <tr>
          <td><?= (int)$d['id'] ?></td>
          <td><?= htmlspecialchars((string)$d['name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars(mb_strimwidth((string)$d['description'], 0, 120, '…'), ENT_QUOTES, 'UTF-8') ?></td>
          <td class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(url('administration/plats/'.$d['id'].'/modifier'), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
            <form method="post" action="<?= htmlspecialchars(url('administration/plats/'.$d['id'].'/supprimer'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Supprimer ce plat ?');">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
