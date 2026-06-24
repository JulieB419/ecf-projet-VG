<?php
use App\Core\Session;

/**
 * CSRF : ta classe Session a csrf() (et ton controller checkCsrf() derrière)
 */
$csrf = Session::csrf();

/**
 * Flash messages : on lit directement la session, car Session::has()/get() n'existent pas.
 * Session::flash('error', '...') et Session::flash('success','...') stockent généralement ici.
 */
$flashError = '';
$flashSuccess = '';

if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    if (!empty($_SESSION['flash']['error'])) {
        $flashError = is_array($_SESSION['flash']['error'])
            ? (string) end($_SESSION['flash']['error'])
            : (string) $_SESSION['flash']['error'];
        unset($_SESSION['flash']['error']); // on consomme
    }

    if (!empty($_SESSION['flash']['success'])) {
        $flashSuccess = is_array($_SESSION['flash']['success'])
            ? (string) end($_SESSION['flash']['success'])
            : (string) $_SESSION['flash']['success'];
        unset($_SESSION['flash']['success']); // on consomme
    }
}
?>

<h1 class="mb-4">Créer un compte</h1>

<?php if ($flashError !== ''): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if ($flashSuccess !== ''): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(url('inscription'), ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="last_name">Nom</label>
      <input class="form-control" type="text" id="last_name" name="last_name" required>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="first_name">Prénom</label>
      <input class="form-control" type="text" id="first_name" name="first_name" required>
    </div>
  </div>

  <div class="mt-3">
    <label class="form-label" for="email">Email</label>
    <input class="form-control" type="email" id="email" name="email" required>
  </div>

  <div class="mt-3">
    <label class="form-label" for="phone">Téléphone</label>
    <input class="form-control" type="text" id="phone" name="phone" required>
  </div>

  <div class="mt-3">
    <label class="form-label" for="address">Adresse</label>
    <input class="form-control" type="text" id="address" name="address" required>
  </div>

  <div class="row g-3 mt-3">
    <div class="col-md-6">
      <label class="form-label" for="password">Mot de passe</label>
      <input class="form-control" type="password" id="password" name="password" required>
      <div class="form-text">
        10 caractères min, 1 maj, 1 min, 1 chiffre, 1 caractère spécial.
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="password_confirm">Confirmer le mot de passe</label>
      <input class="form-control" type="password" id="password_confirm" name="password_confirm" required>
    </div>
  </div>

  <button class="btn btn-primary mt-4" type="submit">Créer</button>

  <div class="mt-3">
    <a href="<?= htmlspecialchars(url('connexion'), ENT_QUOTES, 'UTF-8') ?>">Déjà un compte ? Se connecter</a>
  </div>
</form>
