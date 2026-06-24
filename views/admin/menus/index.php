<?php
/** @var array $menus */
use App\Core\Session;
$csrf = Session::csrf();
?>
<h1 class="mb-3">Menus</h1>

<div class="d-flex gap-2 mb-3">
  <a class="btn btn-primary" href="<?= htmlspecialchars(url('administration/menus/nouveau'), ENT_QUOTES, 'UTF-8') ?>">Créer un menu</a>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration'), ENT_QUOTES, 'UTF-8') ?>">Retour</a>
</div>

<?php if (empty($menus)): ?>
  <div class="alert alert-info">Aucun menu.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th class="col-id">ID</th>
          <th>Titre</th>
          <th>Thème</th>
          <th>Régime</th>
          <th class="col-min">Min</th>
          <th class="col-price">Prix</th>
          <th class="col-stock">Stock</th>
          <th class="col-active">Actif</th>
          <th class="col-actions-lg">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($menus as $m): ?>
        <tr>
          <td><?= (int)$m['id'] ?></td>
          <td><?= htmlspecialchars((string)$m['title'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string)$m['theme'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string)$m['diet'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int)$m['min_people'] ?></td>
          <td><?= number_format((float)$m['base_price'], 2, ',', ' ') ?> €</td>
          <td><?= (int)$m['stock_available'] ?></td>
          <td><?= ((int)$m['is_active']===1) ? 'Oui' : 'Non' ?></td>
          <td class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(url('administration/menus/'.$m['id'].'/modifier'), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
            <form method="post" action="<?= htmlspecialchars(url('administration/menus/'.$m['id'].'/toggle'), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
              <button class="btn btn-sm btn-outline-warning" type="submit">
                <?= ((int)$m['is_active']===1) ? 'Désactiver' : 'Activer' ?>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
