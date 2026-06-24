<h1 class="h3 mb-3"><?= htmlspecialchars($menu['title']) ?></h1>

<div class="row g-4">
  <div class="col-md-6">
    <?php if (!empty($menu['images'])): ?>
      <div id="carousel" class="carousel slide">
        <div class="carousel-inner">
          <?php foreach ($menu['images'] as $i=>$url): ?>
            <div class="carousel-item <?= $i===0?'active':'' ?>">
              <img src="<?= htmlspecialchars($url) ?>" class="d-block w-100" alt="Photo du menu">
            </div>
          <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    <?php else: ?>
      <div class="alert alert-secondary">Aucune image.</div>
    <?php endif; ?>
  </div>

  <div class="col-md-6">
    <div class="mb-2">
      <span class="badge text-bg-light"><?= htmlspecialchars($menu['theme']) ?></span>
      <span class="badge text-bg-light"><?= htmlspecialchars($menu['diet']) ?></span>
      <span class="badge text-bg-light">Min. <?= (int)$menu['min_people'] ?> pers.</span>
      <span class="badge text-bg-light"><?= number_format((float)$menu['base_price'],2,',',' ') ?> €</span>
    </div>

    <p class="text-muted"><?= nl2br(htmlspecialchars($menu['description'])) ?></p>

    <div class="alert alert-warning"><strong>Conditions :</strong><br><?= nl2br(htmlspecialchars($menu['conditions'])) ?></div>

    <p class="small text-muted">Stock : <?= (int)$menu['stock_available'] ?></p>

    <a class="btn btn-primary" href="<?= htmlspecialchars(url('commander/' . (int)$menu['id']), ENT_QUOTES, 'UTF-8') ?>">Commander</a>
  </div>
</div>

<hr class="my-4">
<h2 class="h5">Plats possibles</h2>
<div class="row g-3">
  <?php foreach ($menu['dishes'] as $d): ?>
    <div class="col-md-4">
      <div class="card h-100"><div class="card-body">
        <div class="small text-muted mb-1"><?= strtoupper(htmlspecialchars($d['category'])) ?></div>
        <div class="fw-semibold"><?= htmlspecialchars($d['name']) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($d['description']) ?></div>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>
