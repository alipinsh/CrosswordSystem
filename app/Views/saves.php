<?= $this->extend('base') ?>
<?= $this->section('additionalStyle') ?>
<link rel="stylesheet" href="/css/account.css">
<?= $this->endSection() ?>
<?= $this->section('main') ?>

<h1><?= lang('Account.saves') ?></h1>
<?php if (count($saves)): ?>
    <?php foreach ($saves as $s):?>
        <div class="save-block" data-sid="<?= $s['id']?>">
            <div><a href="/crossword/<?= $s['crossword_id'] ?>"><?= $s['title'] ?></a></div>
            <div><?= $s['width'] ?> x <?= $s['height'] ?></div>
            <div><?= lang("Crossword.questions", [$s['questions']]) ?></div>
            <div class="delete-save-button-container"><button class="delete-save-button"><?= lang('Account.deleteSave') ?></button></div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-list">
        <?= lang('Crossword.emptyList') ?>
    </div>
<?php endif; ?>

<script src="/js/saves.js"></script>

<?= $this->endSection() ?>
