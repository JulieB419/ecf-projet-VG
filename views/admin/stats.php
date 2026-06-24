<h1 class="h3 mb-3">Statistiques</h1>

<form class="row g-2 mb-4" method="get">
  <div class="col-md-4">
    <label class="form-label">Menu</label>
    <select name="menu_id" class="form-select">
      <option value="">Tous</option>
      <?php foreach ($menus as $m): ?>
        <option value="<?= (int)$m['id'] ?>" <?= ($menuId === (int)$m['id']?'selected':'') ?>><?= htmlspecialchars($m['title']) ?></option>
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
    <p class="small text-muted mb-0">Source: <strong><?= htmlspecialchars((string)($agg['source'] ?? '')) ?></strong> (MongoDB si disponible, sinon fallback fichier local).</p>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <div class="text-muted">Chiffre d'affaires</div>
      <div class="h4 mb-0"><?= number_format((float)($agg['total'] ?? 0),2,',',' ') ?> €</div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <div class="text-muted">Commandes</div>
      <div class="h4 mb-0"><?= (int)($agg['count'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <div class="text-muted">Panier moyen</div>
      <div class="h4 mb-0"><?= number_format((float)($agg['avg'] ?? 0),2,',',' ') ?> €</div>
    </div></div>
  </div>
</div>

<div class="card"><div class="card-body">
  <?php if (empty($agg['by_menu'])): ?>
    <p class="text-muted mb-0">Aucune donnée pour les filtres sélectionnés.</p>
  <?php else: ?>
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th>Menu</th>
          <th class="text-end">Commandes</th>
          <th class="text-end">CA</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($agg['by_menu'] as $row): ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars((string)($row['menu_title'] ?? '')) ?></div>
              <div class="small text-muted">#<?= (int)($row['menu_id'] ?? 0) ?></div>
            </td>
            <td class="text-end"><?= (int)($row['count'] ?? 0) ?></td>
            <td class="text-end"><?= number_format((float)($row['total'] ?? 0),2,',',' ') ?> €</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div></div>
