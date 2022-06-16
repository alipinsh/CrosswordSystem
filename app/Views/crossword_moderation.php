<?= $this->extend('base') ?>
<?= $this->section('main') ?>
<h1><?= lang('Moderation.crosswordReports') ?></h1>
<?php foreach ($groupedReports as $crosswordId => $crossword): ?>
<div class="reports-block">
    <div class="report-title">
        <a href="/crossword/<?= $crosswordId ?>"><?= $crossword['title'] ?></a>
        ( id: <?= $crosswordId ?> )
    </div>
    <?php foreach ($crossword['reports'] as $report): ?>
    <div class="report-block">
        <?= $report ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
<?= $this->endSection() ?>
