<?php use App\Core\Session; ?>
<h1 class="h3 mb-3">Contact</h1>

<form method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
  <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off">

  <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
  <div class="col-12"><label class="form-label">Titre</label><input class="form-control" name="title" required></div>
  <div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
  <div class="col-12"><button class="btn btn-primary">Envoyer</button></div>
</form>

