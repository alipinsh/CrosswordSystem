<?= $this->extend('base') ?>
<?= $this->section('main') ?>
<h1><?= lang('Moderation.moderation') ?></h1>
<?php if ($role >= 2): ?>
<p><a href="<?= site_url('moderation/comment') ?>"><?= lang('Moderation.commentModeration') ?></a></p>
<?php endif; ?>
<?php if ($role >= 3): ?>
<p><a href="<?= site_url('moderation/crossword') ?>"><?= lang('Moderation.crosswordModeration') ?></a></p>
<?php endif; ?>
<?php if ($role == 4): ?>
<p><a href="<?= site_url('moderation/user') ?>"><?= lang('Moderation.userModeration') ?></a></p>
<?php endif; ?>

<?= $this->endSection() ?>
