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

<?= $this->endSection() ?>
