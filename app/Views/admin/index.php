
  <h1 class="h3 mb-4">Administration</h1>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Gérer les menus</h2>
          <p class="text-muted mb-3">Créer, modifier et organiser les menus.</p>
          <a class="btn btn-primary" href="<?= htmlspecialchars(url('administration/menus'), ENT_QUOTES, 'UTF-8') ?>">Ouvrir</a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Gérer les plats</h2>
          <p class="text-muted mb-3">Ajouter, modifier et catégoriser les plats.</p>
          <a class="btn btn-primary" href="<?= htmlspecialchars(url('administration/plats'), ENT_QUOTES, 'UTF-8') ?>">Ouvrir</a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Mentions légales / CGV</h2>
          <p class="text-muted mb-3">Modifier les pages obligatoires du site.</p>
          <a class="btn btn-primary" href="<?= htmlspecialchars(url('administration/pages'), ENT_QUOTES, 'UTF-8') ?>">Ouvrir</a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Informations du traiteur</h2>
          <p class="text-muted mb-3">Coordonnées, description, horaires, etc.</p>
          <a class="btn btn-primary" href="<?= htmlspecialchars(url('administration/informations'), ENT_QUOTES, 'UTF-8') ?>">Ouvrir</a>
        </div>
      </div>
    </div>
  </div>
