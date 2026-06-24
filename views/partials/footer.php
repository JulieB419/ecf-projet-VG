<?php
use App\Models\OpeningHours;
$hours = OpeningHours::all();
$days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
?>
<footer class="border-top bg-light mt-5">
  <div class="container py-4">
    <div class="row g-3">
      <div class="col-md-4">
        <h2 class="h6">Menu</h2>
        <ul class="list-unstyled mb-0">
          <li><a href="<?= $baseUrl ?>/">Accueil</a></li>
          <li><a href="<?= $baseUrl ?>/menus">Nos menus</a></li>
          <li><a href="<?= $baseUrl ?>/contact">Contact</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h2 class="h6">Où nous trouver</h2>
        <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($siteSettings['caterer_address'] ?? "12 Rue des Gourmets\n33000 Bordeaux")) ?><br>
Tél : <?= htmlspecialchars($siteSettings['caterer_phone'] ?? "06 12 34 56 78") ?><br>
Email : <?= htmlspecialchars($siteSettings['caterer_email'] ?? "contact@vite-gourmand.com") ?></p>
      </div>
      <div class="col-md-4">
        <h2 class="h6">Horaires</h2>
        <ul class="list-unstyled mb-0 small">
          <?php foreach ($hours as $h): ?>
            <li>
              <?= $days[(int)$h['day_of_week']] ?> :
              <?php if ((int)$h['is_closed']===1): ?>Fermé<?php else: ?>
                <?= htmlspecialchars($h['open_time']) ?> - <?= htmlspecialchars($h['close_time']) ?>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="border-top pt-3 mt-3 d-flex gap-3 small">
      <a href="<?= $baseUrl ?>/mentions-legales">Mentions légales</a>
      <a href="<?= $baseUrl ?>/cgv">CGV</a>
    </div>
  </div>
</footer>
