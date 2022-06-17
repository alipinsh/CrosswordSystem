<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<div class="welcome-message">
<?php if (session('userData.id')): ?>
    <?= lang('Home.welcomeMessageUser', [session('userData.username')]) ?>
<?php else: ?>
    <?= lang('Home.welcomeMessageGuest') ?>
<?php endif; ?>
</div>

<h2><?= lang('Home.latestCrosswords') ?></h2>
<div class="latest-crosswords">
<?php foreach ($latestCrosswords as $c):?>
    <div class="crossword-block">
        <div><a href="/crossword/<?= $c['id'] ?>"><?= $c['title'] ?></a><span class="flag-icon"><img src="/img/flag/<?= $c['language'] ?>.svg"></span></div>
        <div><?= $c['width'] ?> x <?= $c['height'] ?></div>
        <div><?= lang("Crossword.questions", [$c['questions']]) ?></div>
    </div>
<?php endforeach; ?>
<div><a href="/crosswords/all"><?= lang('Home.viewAllCrosswords') ?></a></div>
<div><a href="/tags"><?= lang('Home.viewAllTags') ?></a></div>
</div>

<?php if (isset($latestSaves) && !empty($latestSaves)): ?>
<h2><?= lang('Home.latestSaves') ?></h2>
<div class="latest-saves">
<?php foreach ($latestSaves as $s):?>
    <div class="save-block" data-sid="<?= $s['id']?>">
        <div><a href="/crossword/<?= $s['crossword_id'] ?>"><?= $s['title'] ?></a><span class="flag-icon"><img src="/img/flag/<?= $s['language'] ?>.svg"></span></div>
        <div><?= $s['width'] ?> x <?= $s['height'] ?></div>
        <div><?= lang("Crossword.questions", [$s['questions']]) ?></div>
        <div class="delete-save-button-container"><button class="delete-save-button"><?= lang('Account.deleteSave') ?></button></div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
