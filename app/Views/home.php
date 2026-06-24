<?php
// Vue : Accueil
// Variables attendues :
// - $reviews : liste d'avis validés (peut être vide)
?>

<section class="py-4">
  <div class="p-4 p-md-5 bg-light rounded-3 border">
    <div class="container-fluid">
      <h1 class="display-6 fw-bold mb-2">Vite &amp; Gourmand</h1>
      <p class="fs-5 mb-4">
        Traiteur événementiel : des menus configurables, une livraison maîtrisée, et des options adaptées à votre événement.
      </p>
      <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars(url('menus'), ENT_QUOTES, 'UTF-8') ?>">Découvrir les menus</a>
    </div>
  </div>
</section>

<section class="py-4">
  <div class="container">
    <h2 class="h3 mb-4">Qui sommes-nous ?</h2>

    <div class="row g-4 align-items-center">
      <div class="col-md-5">
        <img
          src="<?= htmlspecialchars(url('assets/img/about.jpg'), ENT_QUOTES, 'UTF-8') ?>"
          alt="Vite &amp; Gourmand – Traiteur événementiel"
         class="img-fluid rounded-4 shadow-sm"
        />

      </div>

      <div class="col-md-7">
        <p class="mb-3">
          Vite &amp; Gourmand, c’est un service traiteur qui rend l’organisation d’un repas simple, rapide et vraiment bon.
          Vous choisissez un menu, vous le composez selon vos envies (et vos régimes), et on s’occupe du reste : préparation, suivi et livraison.
          L’objectif : une expérience fluide, des plats maison, et une commande en quelques clics — pour un déjeuner pro, un événement familial ou un moment entre amis.
        </p>
        <p class="mb-4">
          Objectif : vous faire gagner du temps sans sacrifier le goût, avec des informations utiles dès la commande
          (allergènes, régimes, conditions, frais de déplacement).
        </p>

        <a class="btn btn-outline-primary" href="<?= htmlspecialchars(url('menus'), ENT_QUOTES, 'UTF-8') ?>">
          Voir les menus
        </a>
      </div>
    </div>
  </div>
</section>

<section class="py-4">
  <div class="container">
    <h2 class="mb-4">Ils ont été séduits</h2>

    <?php if (empty($reviews)): ?>
      <div class="alert alert-light border mb-0">
        <div class="text-muted">Aucun avis pour le moment.</div>
      </div>
    <?php else: ?>
      <?php $chunks = array_chunk($reviews, 3); ?>

      <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <?php foreach ($chunks as $i => $_chunk): ?>
            <button type="button" data-bs-target="#reviewsCarousel" data-bs-slide-to="<?= $i ?>"
                    class="<?= $i === 0 ? 'active' : '' ?>" <?= $i === 0 ? 'aria-current="true"' : '' ?>
                    aria-label="Avis slide <?= $i + 1 ?>"></button>
          <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
          <?php foreach ($chunks as $i => $chunk): ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
              <div class="row g-3">
                <?php foreach ($chunk as $review): ?>
                  <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                      <div class="card-body">
                        <div class="mb-2">
                          <?php for ($s=1;$s<=5;$s++): ?>
                            <span class="<?= $s <= (int)$review['rating'] ? 'text-warning' : 'text-muted' ?>">★</span>
                          <?php endfor; ?>
                        </div>

                        <?php if (!empty($review['comment'])): ?>
                          <p class="mb-3">“<?= htmlspecialchars($review['comment']) ?>”</p>
                        <?php else: ?>
                          <p class="mb-3 text-muted">Avis sans commentaire.</p>
                        <?php endif; ?>

                        <div class="small text-muted">— <?= htmlspecialchars(trim(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? ''))) ?></div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>

                <?php if (count($chunk) < 3): ?>
                  <?php for ($k = count($chunk); $k < 3; $k++): ?>
                    <div class="col-md-4 d-none d-md-block"></div>
                  <?php endfor; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Précédent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Suivant</span>
        </button>
      </div>

      
    <?php endif; ?>
  </div>
</section>

