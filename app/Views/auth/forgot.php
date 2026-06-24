<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Mot de passe oublié</h1>
<form method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <div class="col-12"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
  <div class="col-12"><button class="btn btn-primary">Envoyer</button></div>
</form>
<p class="small text-muted mt-3">Lien écrit dans <code>storage/logs/mail.log</code> (mode ECF).</p>
