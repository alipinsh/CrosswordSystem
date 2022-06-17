<?= $this->extend('base') ?>
<?= $this->section('main') ?>
<h1><?= lang('Moderation.commentReports') ?></h1>
<?php foreach ($reports as $comment): ?>
<div class="reported-comment" data-comment="<?= $comment['comment_id'] ?>">
    <div class="comment-username"><strong><?= $comment['username'] ?></strong></div>
    <div class="comment-text"><?= $comment['text'] ?></div>
    <a href="/crossword/<?= $comment['crossword_id'] ?>"><?= lang('Moderation.crosswordLink')?></a>
    <button class="free-button"><?= lang('Moderation.freeComment') ?></button>
    <button class="delete-button"><?= lang('Moderation.deleteComment') ?></button>
</div>
<?php endforeach; ?>

<script src="/js/comment_moderation.js"></script>
<?= $this->endSection() ?>
