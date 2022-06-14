<?= $this->extend('base') ?>
<?= $this->section('additionalStyle') ?>
<link rel="stylesheet" href="/css/crossword.css">
<?= $this->endSection() ?>
<?= $this->section('main') ?>
<div class="save-form">
    <button id="print"><?= lang('Crossword.print') ?></button>
    <input type="button" id="save" value="<?= lang("Crossword.save") ?>">
</div>

<div class="info-error"></div>

<div class="info-form">
    <p>
        <label for="title"><?= lang("Crossword.title") ?></label>
        <input type="text" id="title">
    </p>
    <p>
        <label for="tags"><?= lang("Crossword.tags") ?></label>
        <textarea id="tags"></textarea>
    </p>
    <p>
        <label class="checkbox" for="is_public">
            <span><?= lang("Crossword.isPublic") ?></span>
            <input type="checkbox" id="is_public" name="is_public">
        </label>
    </p>
</div>

<div class="editor-bar">
    <div class="tools">
        <button class="tool-button" id="add"><?= lang("Crossword.add") ?></button>
        <button class="tool-button" id="edit"><?= lang("Crossword.edit") ?></button>
        <button class="tool-button" id="delete"><?= lang("Crossword.delete") ?></button>
        <button class="tool-button" id="move"><?= lang("Crossword.move") ?></button>
    </div>
    <div class="size-form">
        <input type="number" id="width" placeholder="width" min="0" max="100" step="1">
        <input type="number" id="height" placeholder="height" min="0" max="100" step="1">
        <input type="button" id="resize" value="<?= lang("Crossword.resize") ?>">
    </div>
</div>

<?php if (isset($crossword)): ?>
<div id="crossword-data"><?= json_encode($crossword) ?></div>
<?php endif; ?>

<div class="grid-container">
    <div class="grid"></div>
    <div class="word-preview"></div>
</div>

<div class="error"></div>

<div class="edit-modal">
    <p>
        <label for="question"><?= lang("Crossword.question") ?></label>
        <textarea name="question"></textarea>
    </p>
    <p>
        <label for="answer"><?= lang("Crossword.answer") ?></label>
        <input name="answer" type="text">
        <div class="letter-count">0</div>
    </p>
    <p>
        <button class="cancel-button"><?= lang("Crossword.cancel") ?></button>
        <button class="ok-button"><?= lang("Crossword.ok") ?></button>
    </p>
</div>

<div class="move-modal">
    <p>
        <label for="left"><?= lang("Crossword.left") ?></label>
        <input type="number" id="left" min="" max="" step="1">
    </p>
    <p>
        <label for="top"><?= lang("Crossword.top") ?></label>
        <input type="number" id="top" min="" max="" step="1">
    </p>
    <p>
        <button class="cancel-button"><?= lang("Crossword.cancel") ?></button>
        <button class="ok-button"><?= lang("Crossword.ok") ?></button>
    </p>
</div>

<div class="questions-titles">
    <div class="questions-title"><?= lang("Crossword.horizontal") ?></div>
    <div class="questions-title"><?= lang("Crossword.vertical") ?></div>
</div>
<div class="questions">
    <div class="horizontal-questions">
    </div>
    <div class="vertical-questions">
    </div>
</div>

<?php if (isset($crossword)): ?>
<div class="delete-form">
    <input type="button" id="delete" value="<?= lang("Crossword.delete") ?>">
    <div class="sure-delete">
        <?= lang('Crossword.areYouSureDelete') ?>
        <input type="button" id="yes-delete" value="<?= lang("Crossword.yesDelete") ?>">
        <input type="button" id="no-delete" value="<?= lang("Crossword.noDelete") ?>">
    </div>
</div>
<?php endif; ?>

<script src="/js/editor.js"></script>
<script src="/js/print.js"></script>

<?= $this->endSection() ?>
