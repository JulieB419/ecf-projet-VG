<?php
use App\Models\SiteSetting;
$siteSettings = SiteSetting::all();
use App\Core\Session;

/**
 * Base URL (ex: /vite-gourmand/public)
 * Sert à préfixer tous les liens (a, form, assets, fetch JS).
 */
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = rtrim(str_replace('/index.php', '', $scriptName), '/'); // ex: /vite-gourmand/public
if ($baseUrl === '') { $baseUrl = ''; }

/**
 * Helper pour générer une URL avec le bon préfixe.
 * url('menus') => /vite-gourmand/public/menus
 */
if (!function_exists('url')) {
    function url(string $path = ''): string {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseUrl = rtrim(str_replace('/index.php', '', $scriptName), '/');
        if ($baseUrl === '') { $baseUrl = ''; }

        if ($path === '') return $baseUrl !== '' ? $baseUrl . '/' : '/';
        return ($baseUrl !== '' ? $baseUrl . '/' : '/') . ltrim($path, '/');
    }
}

// Flash (si ton Session::flash() stocke dans $_SESSION['flash'])
$flashError = '';
$flashSuccess = '';
if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    if (!empty($_SESSION['flash']['error'])) {
        $flashError = is_array($_SESSION['flash']['error']) ? (string) end($_SESSION['flash']['error']) : (string) $_SESSION['flash']['error'];
    }
    if (!empty($_SESSION['flash']['success'])) {
        $flashSuccess = is_array($_SESSION['flash']['success']) ? (string) end($_SESSION['flash']['success']) : (string) $_SESSION['flash']['success'];
    }
    unset($_SESSION['flash']['error'], $_SESSION['flash']['success']);
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- IMPORTANT : base unique, termine par / -->
  <base href="<?= htmlspecialchars(url(''), ENT_QUOTES, 'UTF-8') ?>">

  <title>Vite &amp; Gourmand</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= htmlspecialchars(url('assets/styles.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>

<body data-base="<?= htmlspecialchars(url(''), ENT_QUOTES, 'UTF-8') ?>">
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
<script src="<?= htmlspecialchars(url('assets/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
