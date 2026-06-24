<?php
$source = (string)($agg['source'] ?? '');
$total = (float)($agg['total'] ?? 0);
$count = (int)($agg['count'] ?? 0);
$avg = (float)($agg['avg'] ?? 0);
$byMenu = $agg['by_menu'] ?? [];
$topMenu = $byMenu[0]['menu_title'] ?? 'Aucun pour le moment';
$topMenuCount = (int)($byMenu[0]['count'] ?? 0);
$topMenuTotal = (float)($byMenu[0]['total'] ?? 0);
$sourceLabel = $source === 'mongo' ? 'MongoDB' : ($source === 'file' ? 'Fichier local' : 'Aucune source');
?>

<h1 class="h3 mb-3">Tableau de bord des statistiques</h1>
<p class="text-muted mb-4">Suivez rapidement l'activité des commandes et les menus les plus demandés.</p>

<form class="row g-2 mb-4" method="get">
  <div class="col-md-4">
    <label class="form-label">Menu</label>
    <select name="menu_id" class="form-select">
      <option value="">Tous</option>
      <?php foreach ($menus as $m): ?>
        <option value="<?= (int)$m['id'] ?>" <?= ($menuId === (int)$m['id']?'selected':'') ?>><?= htmlspecialchars((string)$m['title']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Du</label>
    <input class="form-control" type="date" name="from" value="<?= htmlspecialchars((string)$from) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Au</label>
    <input class="form-control" type="date" name="to" value="<?= htmlspecialchars((string)$to) ?>">
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <button class="btn btn-primary w-100">Filtrer</button>
  </div>

  <div class="col-12">
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" id="include_cancelled" name="include_cancelled" value="1" <?= (!empty($includeCancelled) ? 'checked' : '') ?>>
      <label class="form-check-label" for="include_cancelled">Inclure les commandes annulées</label>
    </div>
  </div>
</form>

<div class="card mb-4 border-0 shadow-sm bg-light">
  <div class="card-body">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
      <div>
        <div class="fw-semibold mb-1">Mettre à jour les statistiques</div>
        <div class="small text-muted">Permet de mettre à jour les statistiques avec les dernières commandes enregistrées.</div>
        <div class="small text-muted mt-1">Source actuelle : <strong><?= htmlspecialchars($sourceLabel) ?></strong></div>
      </div>
      <form method="post" action="<?= htmlspecialchars(url('administration/stats/rebuild'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Mettre à jour les statistiques à partir des commandes enregistrées ?');">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\Core\Session::csrf()) ?>">
        <button class="btn btn-warning" type="submit">Mettre à jour les statistiques</button>
      </form>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body">
        <div class="text-muted small text-uppercase">Chiffre d'affaires</div>
        <div class="display-6 fw-semibold"><?= number_format($total, 2, ',', ' ') ?> €</div>
        <div class="small text-muted mt-2">Total des commandes sur la période sélectionnée.</div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body">
        <div class="text-muted small text-uppercase">Commandes</div>
        <div class="display-6 fw-semibold"><?= $count ?></div>
        <div class="small text-muted mt-2">Nombre de commandes prises en compte.</div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body">
        <div class="text-muted small text-uppercase">Panier moyen</div>
        <div class="display-6 fw-semibold"><?= number_format($avg, 2, ',', ' ') ?> €</div>
        <div class="small text-muted mt-2">Montant moyen par commande.</div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body">
        <div class="text-muted small text-uppercase">Menu le plus demandé</div>
        <div class="fw-semibold fs-5"><?= htmlspecialchars((string)$topMenu) ?></div>
        <div class="small text-muted mt-2"><?= $topMenuCount ?> commande<?= $topMenuCount > 1 ? 's' : '' ?> · <?= number_format($topMenuTotal, 2, ',', ' ') ?> €</div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h5 mb-0">Détail par menu</h2>
      <span class="badge text-bg-light"><?= count($byMenu) ?> menu<?= count($byMenu) > 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($byMenu)): ?>
      <p class="text-muted mb-0">Aucune donnée pour les filtres sélectionnés.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>Menu</th>
              <th class="text-end">Commandes</th>
              <th class="text-end">Chiffre d'affaires</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($byMenu as $row): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars((string)($row['menu_title'] ?? '')) ?></div>
                  <div class="small text-muted">Référence #<?= (int)($row['menu_id'] ?? 0) ?></div>
                </td>
                <td class="text-end"><?= (int)($row['count'] ?? 0) ?></td>
                <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2, ',', ' ') ?> €</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
