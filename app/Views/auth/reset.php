<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Réinitialiser le mot de passe</h1>
<form method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
  <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-12"><label class="form-label">Nouveau mot de passe</label><input class="form-control" type="password" name="password" required></div>
  <div class="col-12"><button class="btn btn-primary">Valider</button></div>
</form>
