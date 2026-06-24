<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Créer un compte employé</h1>

<div class="alert alert-info">Le mot de passe n'est pas envoyé par mail (à transmettre en direct).</div>

<form method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
  <div class="col-md-6"><label class="form-label">Mot de passe provisoire</label><input class="form-control" type="password" name="password" required></div>
  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary">Créer</button>
    <a class="btn btn-outline-secondary" href="<?= $baseUrl ?>/administration/employes">Retour</a>
  </div>
</form>
