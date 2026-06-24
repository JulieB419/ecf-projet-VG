<?php
// Vue : recap commande (étape 2)
// Variables : $order, $menu

$csrf = $_SESSION['csrf'] ?? '';

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseUrl = rtrim(str_replace('/index.php', '', $scriptName), '/');
        if ($path === '') return $baseUrl ?: '/';
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

$menuId = (int)($menu['id'] ?? 0);
$minPeople = (int)($menu['min_people'] ?? 0);

// Infos sélectionnées
$people = (int)($order['people_count'] ?? 0);
$addr = (string)($order['prestation_address'] ?? '');
$city = (string)($order['prestation_city'] ?? '');
$date = (string)($order['prestation_date'] ?? '');
$time = (string)($order['prestation_time'] ?? '');
$distance = (float)($order['distance_km'] ?? 0);

$e = $order['entree'] ?? null;
$p = $order['plat'] ?? null;
$d = $order['dessert'] ?? null;

// Prix
$menuSubtotal = (float)($order['menu_subtotal'] ?? 0);
$discount = (float)($order['discount'] ?? 0);
$delivery = (float)($order['delivery_fee'] ?? 0);
$total = (float)($order['total'] ?? 0);

function euro(float $n): string {
    return number_format($n, 2, ',', ' ') . ' €';
}
?>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
  <div>
    <h1 class="mb-1">Récapitulatif</h1>
    <p class="text-muted mb-0">Vérifie, puis confirme ta commande.</p>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('commander/' . $menuId), ENT_QUOTES, 'UTF-8') ?>">← Modifier</a>
</div>

<hr class="my-4">

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h5">1) Évènement</h2>
        <ul class="list-unstyled mb-0">
          <li><strong>Adresse :</strong> <?= htmlspecialchars($addr, ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?></li>
          <li><strong>Date :</strong> <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></li>
          <li><strong>Heure :</strong> <?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></li>
          <li><strong>Distance estimée :</strong> <?= number_format($distance, 1, ',', ' ') ?> km</li>
        </ul>
      </div>
    </div>

    <div class="card shadow-sm mt-4">
      <div class="card-body">
        <h2 class="h5">2) Menu</h2>
        <p class="mb-1"><strong><?= htmlspecialchars((string)($menu['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p class="text-muted mb-2">Minimum recommandé : <?= $minPeople ?> personnes</p>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="border rounded p-2 h-100">
              <div class="fw-semibold">Entrée</div>
              <?php if ($e): ?>
                <div><?= htmlspecialchars((string)$e['name'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-muted"><?= htmlspecialchars((string)mb_strimwidth((string)$e['description'], 0, 110, '…'), ENT_QUOTES, 'UTF-8') ?></div>
              <?php else: ?>
                <div class="text-muted">—</div>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-2 h-100">
              <div class="fw-semibold">Plat</div>
              <?php if ($p): ?>
                <div><?= htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-muted"><?= htmlspecialchars((string)mb_strimwidth((string)$p['description'], 0, 110, '…'), ENT_QUOTES, 'UTF-8') ?></div>
              <?php else: ?>
                <div class="text-muted">—</div>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-2 h-100">
              <div class="fw-semibold">Dessert</div>
              <?php if ($d): ?>
                <div><?= htmlspecialchars((string)$d['name'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-muted"><?= htmlspecialchars((string)mb_strimwidth((string)$d['description'], 0, 110, '…'), ENT_QUOTES, 'UTF-8') ?></div>
              <?php else: ?>
                <div class="text-muted">—</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="mt-3"><strong>Personnes :</strong> <?= (int)$people ?></div>
      </div>
    </div>

    <div class="card shadow-sm mt-4">
      <div class="card-body">
        <h2 class="h5">3) Conditions & CGV</h2>
        <div class="small text-muted mb-2">
          <?= htmlspecialchars((string)($menu['conditions'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <form method="post" action="<?= htmlspecialchars(url('commander/' . $menuId), ENT_QUOTES, 'UTF-8') ?>" class="mt-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="confirm" value="1">
    <input type="hidden" name="people_count" value="<?= (int)$order['people_count'] ?>">
    <input type="hidden" name="prestation_address" value="<?= htmlspecialchars((string)$order['prestation_address'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="prestation_city" value="<?= htmlspecialchars((string)$order['prestation_city'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="prestation_date" value="<?= htmlspecialchars((string)$order['prestation_date'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="prestation_time" value="<?= htmlspecialchars((string)$order['prestation_time'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="distance_km" value="<?= htmlspecialchars((string)$order['distance_km'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="entree_id" value="<?= $order['entree'] ? (int)$order['entree']['id'] : 0 ?>">
    <input type="hidden" name="plat_id" value="<?= $order['plat'] ? (int)$order['plat']['id'] : 0 ?>">
    <input type="hidden" name="dessert_id" value="<?= $order['dessert'] ? (int)$order['dessert']['id'] : 0 ?>">

          <input type="hidden" name="step" value="confirm">

          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="accept_cgv" name="accept_cgv" required>
            <label class="form-check-label" for="accept_cgv">
              J'ai lu et j'accepte les <a href="<?= htmlspecialchars(url('cgv'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">CGV</a>.
            </label>
          </div>

          <button class="btn btn-success btn-lg mt-3" type="submit">Confirmer ma commande</button>
        </form>
      </div>
    </div>

  </div>

  <div class="col-lg-5">
    <div class="card shadow-sm position-sticky sticky-summary-card">
      <div class="card-body">
        <h2 class="h5">Facture</h2>

        <div class="border rounded p-3">
          <div class="d-flex justify-content-between">
            <span>Menu</span>
            <strong><?= euro($menuSubtotal) ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Réduction</span>
            <strong><?= $discount > 0 ? ('- ' . euro($discount)) : '—' ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Frais déplacement</span>
            <strong><?= $delivery > 0 ? euro($delivery) : '—' ?></strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between align-items-center">
            <span class="h6 mb-0">Total</span>
            <span class="h4 mb-0"><?= euro($total) ?></span>
          </div>
        </div>

        <?php if ($people < $minPeople): ?>
          <div class="alert alert-warning mt-3 mb-0">
            <strong>Attention :</strong> le menu est prévu pour un minimum de <?= $minPeople ?> personnes.
            Ta commande reste possible pour le projet, mais en vrai ce serait à confirmer avec l'entreprise.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
