<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Connexion</h1>
<form method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <div class="col-12"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
  <div class="col-12"><label class="form-label">Mot de passe</label><input class="form-control" type="password" name="password" required></div>
  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary">Se connecter</button>
    <a class="btn btn-outline-secondary" href="<?= $baseUrl ?>/inscription">Créer un compte</a>
  </div>
  <div class="col-12"><a href="<?= $baseUrl ?>/mot-de-passe-oublie">Mot de passe oublié ?</a></div>
</form>
