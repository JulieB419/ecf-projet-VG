<?php
/** @var string $type */
/** @var array{slug:string,title:string,content:string,updated_at:string}|null $page */
/** @var bool $saved */

$label = $type === 'cgv' ? 'CGV' : 'Mentions légales';
?>

<div class="container" style="max-width: 900px; margin: 0 auto;">
    <h1>Gérer <?= htmlspecialchars($label) ?></h1>

    <?php if (!empty($saved)): ?>
        <div class="alert alert-success" style="margin: 12px 0; padding: 10px 12px; border: 1px solid #b7e3c7; background: #eefaf2;">
            Contenu enregistré ✅
        </div>
    <?php endif; ?>

    <form method="get" action="<?= htmlspecialchars(url('administration/pages'), ENT_QUOTES, 'UTF-8') ?>" style="margin: 12px 0 24px;">
        <label for="type">Choisir le document :</label>
        <select id="type" name="type" onchange="this.form.submit()">
            <option value="mentions_legales" <?= $type==='mentions_legales'?'selected':'' ?>>Mentions légales</option>
            <option value="cgv" <?= $type==='cgv'?'selected':'' ?>>CGV</option>
        </select>
        <noscript><button type="submit">Afficher</button></noscript>
    </form>

    <form method="post" action="<?= htmlspecialchars(url('administration/pages'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\Core\Session::csrf()) ?>">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

        <div style="margin-bottom: 12px;">
            <label for="title">Titre</label><br>
            <input id="title" name="title" type="text" value="<?= htmlspecialchars($page['title'] ?? '') ?>" style="width: 100%; padding: 10px;" />
        </div>

        <div style="margin-bottom: 12px;">
            <label for="content">Contenu (HTML autorisé)</label><br>
            <textarea id="content" name="content" rows="18" style="width: 100%; padding: 10px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="<?= htmlspecialchars(url('administration'), ENT_QUOTES, 'UTF-8') ?>" class="btn">Retour</a>
    </form>

    <hr style="margin: 26px 0;">
    <p>
        <strong>Aperçu :</strong>
        <?php if ($type==='cgv'): ?>
            <a href="<?= htmlspecialchars(url('cgv'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer">ouvrir la page CGV</a>
        <?php else: ?>
            <a href="<?= htmlspecialchars(url('mentions-legales'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer">ouvrir la page Mentions légales</a>
        <?php endif; ?>
    </p>
</div>
