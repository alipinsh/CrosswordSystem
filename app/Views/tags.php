<?= $this->extend('base') ?>
<?= $this->section('main') ?>
<h1><?= lang('Misc.allTags') ?></h1>
<?php foreach ($tags as $t):?>
    <div class="tag"><a href="/crosswords/tag/<?= $t['tag'] ?>"><?= $t['tag'] ?></a></div>
<?php endforeach; ?>
<?= $this->endSection() ?>