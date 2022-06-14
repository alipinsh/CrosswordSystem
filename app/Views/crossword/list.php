<?= $this->extend('base') ?>
<?= $this->section('main') ?>
<h1><?= $listTitle ?></h1>
<?php if (count($crosswords)): ?>
<?php foreach ($crosswords as $c):?>
<div class="crossword-block">
    <div><a href="/crossword/<?= $c['id'] ?>"><?= $c['title'] ?></a></div>
    <div><?= $c['width'] ?> x <?= $c['height'] ?></div>
    <div><?= lang("Crossword.questions", [$c['questions']]) ?></div>
</div>
<?php endforeach; ?>
<div class="pagination">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i == $currentPage): ?>
        <div class="pagination-link"><?= $i ?></div>
    <?php else: ?>
        <a class="pagination-link" href="<?= current_url() . "?p=" . $i ?>"><?= $i ?></a>
    <?php endif; ?>
<?php endfor; ?>
</div>
<?php else: ?>
<div class="empty-list">
    <?= lang('Crossword.emptyList') ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
