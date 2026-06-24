<?php
/** @var string $type */
/** @var array{slug:string,title:string,content:string,updated_at:string}|null $page */
/** @var bool $saved */

$label = $type === 'cgv' ? 'CGV' : 'Mentions légales';
?>

<div class="container admin-page-editor">
    <h1>Gérer <?= htmlspecialchars($label) ?></h1>

    <?php if (!empty($saved)): ?>
        <div class="alert alert-success admin-save-alert">
            Contenu enregistré ✅
        </div>
    <?php endif; ?>

    <form method="get" action="/administration/pages" class="admin-page-selector">
        <label for="type">Choisir le document :</label>
        <select id="type" name="type" onchange="this.form.submit()">
            <option value="mentions_legales" <?= $type==='mentions_legales'?'selected':'' ?>>Mentions légales</option>
            <option value="cgv" <?= $type==='cgv'?'selected':'' ?>>CGV</option>
        </select>
        <noscript><button type="submit">Afficher</button></noscript>
    </form>

    <form method="post" action="/administration/pages">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\Core\Session::csrf()) ?>">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

        <div class="admin-form-row">
            <label for="title">Titre</label><br>
            <input id="title" name="title" type="text" value="<?= htmlspecialchars($page['title'] ?? '') ?>" class="form-control" />
        </div>

        <div class="admin-form-row">
            <label for="content">Contenu (HTML autorisé)</label><br>
            <textarea id="content" name="content" rows="18" class="form-control admin-code-textarea"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="/administration" class="btn">Retour</a>
    </form>

    <hr class="admin-separator">
    <p>
        <strong>Aperçu :</strong>
        <?php if ($type==='cgv'): ?>
            <a href="/cgv" target="_blank" rel="noreferrer">ouvrir la page CGV</a>
        <?php else: ?>
            <a href="/mentions-legales" target="_blank" rel="noreferrer">ouvrir la page Mentions légales</a>
        <?php endif; ?>
    </p>
</div>
