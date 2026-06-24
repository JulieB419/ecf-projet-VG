<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Employés</h1>

<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Recherche..."></div>
  <div class="col-md-6 d-flex gap-2">
    <button class="btn btn-primary">Rechercher</button>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('administration/employes/nouveau'), ENT_QUOTES, 'UTF-8') ?>">Créer</a>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead><tr><th>Email</th><th>Nom</th><th>Actif</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($employees as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['email']) ?></td>
          <td><?= htmlspecialchars(trim($e['first_name'].' '.$e['last_name'])) ?></td>
          <td><?= ((int)$e['is_active']===1?'Oui':'Non') ?></td>
          <td class="text-end">
            <?php if ((int)$e['is_active']===1): ?>
              <form method="post" action="<?= htmlspecialchars(url('administration/employes'), ENT_QUOTES, 'UTF-8') ?>/<?= (int)$e['id'] ?>/desactiver">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Désactiver ?')">Désactiver</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
