<?php
/** @var array{slug:string,title:string,content:string,updated_at:string}|null $page */

$title = $page['title'] ?? 'Conditions générales de vente';
$updatedAt = $page['updated_at'] ?? null;
$content = $page['content'] ?? '';

$daysAgo = null;
$datePretty = null;
if ($updatedAt) {
    try {
        $dt = new DateTime($updatedAt);
        $now = new DateTime('now');
        $diff = $now->diff($dt);
        $daysAgo = (int)$diff->format('%a');
        $datePretty = $dt->format('d/m/Y');
    } catch (Throwable $e) {
        $daysAgo = null;
        $datePretty = null;
    }
}
?>

<h1><?= htmlspecialchars($title) ?></h1>

<?php if ($updatedAt && $daysAgo !== null && $datePretty): ?>
    <p><em>Modifié il y a <?= (int)$daysAgo ?> jour<?= $daysAgo === 1 ? '' : 's' ?> (<?= htmlspecialchars($datePretty) ?>)</em></p>
<?php endif; ?>

<div class="legal-content">
    <?= $content /* content is editable by admin; considered trusted HTML */ ?>
</div>
