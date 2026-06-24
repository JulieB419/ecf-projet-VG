<?php
use App\Core\Auth;
use App\Core\Session;
$u = Auth::user();
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container">
   <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('') ?>">
  <img src="<?= htmlspecialchars(url('assets/img/logo.png'), ENT_QUOTES, 'UTF-8') ?>"
       alt="Vite & Gourmand" height="36">
</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url(''), ENT_QUOTES, 'UTF-8') ?>">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url('menus'), ENT_QUOTES, 'UTF-8') ?>">Nos menus</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url('contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a></li>
      </ul>

      <ul class="navbar-nav">
        <?php if (!$u): ?>
          <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url('connexion'), ENT_QUOTES, 'UTF-8') ?>">Se connecter</a></li>
        <?php else: ?>
          <?php if (($u['role'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url('administration'), ENT_QUOTES, 'UTF-8') ?>">Administration</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url('espace-employe'), ENT_QUOTES, 'UTF-8') ?>">Mon espace</a></li>
            <li class="nav-item">
              <form method="post" action="<?= htmlspecialchars(url('deconnexion'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
                <button class="btn btn-sm btn-outline-secondary">Déconnexion</button>
              </form>
            </li>
          <?php elseif (($u['role'] ?? '') === 'employee'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(url('espace-employe'), ENT_QUOTES, 'UTF-8') ?>">Mon espace</a></li>
            <li class="nav-item">
              <form method="post" action="<?= htmlspecialchars(url('deconnexion'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
                <button class="btn btn-sm btn-outline-secondary">Déconnexion</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars(trim(($u['firstname'] ?? '').' '.($u['lastname'] ?? ''))) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= htmlspecialchars(url('profil'), ENT_QUOTES, 'UTF-8') ?>">Mon profil</a></li>
                <li><a class="dropdown-item" href="<?= htmlspecialchars(url('profil#commandes'), ENT_QUOTES, 'UTF-8') ?>">Mon historique de commandes</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="post" action="<?= htmlspecialchars(url('deconnexion'), ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
                    <button class="btn btn-sm btn-outline-secondary w-100">Se déconnecter</button>
                  </form>
                </li>
              </ul>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
