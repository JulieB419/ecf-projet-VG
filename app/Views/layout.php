<?php
use App\Models\SiteSetting;
use App\Core\Url;

$siteSettings = SiteSetting::all();
$baseUrl = Url::to('');
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';
?>
<!doctype html>

<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">


  <title>Vite &amp; Gourmand</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= htmlspecialchars(Url::to('assets/styles.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>

<body data-base="<?= htmlspecialchars(Url::to(''), ENT_QUOTES, 'UTF-8') ?>">
<?php require __DIR__ . '/partials/nav.php'; ?>

<main class="container py-4">
  <?php if ($flashSuccess !== ''): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($flashError !== ''): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php require __DIR__ . '/' . $template . '.php'; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(Url::to('assets/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(Url::to('assets/order-create.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
