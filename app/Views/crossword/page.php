<?= $this->extend('base') ?>
<?= $this->section('additionalStyle') ?>
<link rel="stylesheet" href="/css/crossword.css">
<?= $this->endSection() ?>
<?= $this->section('main') ?>
<?php if ($isModerator): ?>
    <div class="moderator-message">
        <?php if ($hasReports): ?><?= lang('Moderation.hasReports') ?><?php endif; ?>
        <?= lang('Moderation.moderateCrossword') ?>
        <button id="show-reason-form"><?= lang('Misc.show') ?></button>
        <div class="reason-form">
            <div class="reason-error"></div>
            <form id="reason" method="POST" action="<?= site_url('report'); ?>" accept-charset="UTF-8">
                <?= csrf_field() ?>
                <input required hidden type="text" name="crossword_id" value="<?= $crossword['id'] ?>" />
                <p>
                    <input type="radio" id="hide" name="moderation_action" value="hide" checked>
                    <label for="hide"><?= lang('Moderation.hideCrossword') ?></label><br>
                    <input type="radio" id="delete" name="moderation_action" value="delete">
                    <label for="delete"><?= lang('Moderation.deleteCrossword') ?></label><br>
                </p>
                <p>
                    <label class="reason-label"></label>
                    <textarea required name="reason_text" value=""></textarea>
                </p>
                <p>
                    <button name="postReason" form="reason" type="submit"><?= lang('Moderation.postReason') ?></button>
                </p>
            </form>
            <?php if ($hasReports): ?><button id="free-crossword" data-cid="<?= $crossword['id']?>"><?= lang('Moderation.freeCrossword') ?></button><?php endif; ?>
        </div>

    </div>
<?php endif; ?>
<?php if ($isMine): ?>
    <div class="author-message">
        <?= lang('Crossword.yourOwnCrossword') ?>
        <button class="edit-crossword-button" onclick="window.open('<?= site_url('crossword/edit/' . $crossword['id'])?>', '_self')">
            <?= lang('Crossword.edit') ?>
        </button>
    </div>
<?php endif; ?>

<div class="crossword-info">
    <div class="title"><?= $crossword['title'] ?></div>
    <div class="author">
        <?= lang('Crossword.author')?>:
        <a href="/profile/<?= $crossword['user'] ?>"><?= $crossword['user'] ?></a>
    </div>
    <div class="published-date">
        <?php
        if ($crossword['published_at']): echo lang('Crossword.publishedAt') . ' ' . $crossword['published_at'];
            if ($crossword['updated_at']): echo ', ' . mb_strtolower(lang('Crossword.updatedAt')) . ' ' . $crossword['updated_at']; endif;
        endif; ?>
    </div>
    <div class="dimensions"><?= lang('Crossword.size', [$crossword['width'], $crossword['height']]) ?></div>
    <div class="questions-count"><?= lang('Crossword.questions', [$crossword['questions']]) ?></div>
    <div class="favorites-count"><?= lang('Crossword.favorites', [$crossword['favorites']]) ?></div>
    <div class="tags">
        <?= lang('Crossword.tags')?>:
        <?php $tags = explode(',', $crossword['tags']); foreach ($tags as $t):?>
            <div class="tag"><a href="/crosswords/tag/<?= $t ?>"><?= $t ?></a></div>
        <?php endforeach; ?>
    </div>
    <div id="crossword-data"><?= $crossword['data'] ?></div>
</div>

<div class="reaction-buttons">
    <button id="favorite" <?php if ($favorited || !session('userData.id')): echo 'disabled'; endif; ?>>Favorite!</button>
    <button id="save-progress" <?php if (!session('userData.id')): echo 'disabled'; endif; ?>><?= lang('Crossword.saveProgress') ?></button>
    <button id="print"><?= lang('Crossword.print') ?></button>
    <button id="show-report-form"><?= lang('Crossword.showReportForm') ?></button>
</div>

<div class="report-form">
    <div class="report-error"></div>
    <form id="report" method="POST" action="<?= site_url('moderation/report'); ?>" accept-charset="UTF-8">
        <?= csrf_field() ?>
        <input required hidden type="text" name="crossword_id" value="<?= $crossword['id'] ?>" />
        <p>
            <label><?= lang('Crossword.reportText') ?></label>
            <textarea required name="report_text" value=""></textarea>
        </p>
        <p>
            <button name="postReport" form="report" type="submit"><?= lang('Crossword.postReport') ?></button>
        </p>
    </form>
</div>

<div class="crossword-player-tools">
    <div class="crossword-reveal-tools">
        <?= lang('Crossword.reveal') ?>
        <button id="reveal-letter"><?= lang('Crossword.letter') ?></button>
        <button id="reveal-word"><?= lang('Crossword.word') ?></button>
        <button id="reveal-grid"><?= lang('Crossword.grid') ?></button>
    </div>
    <div class="crossword-check-tools">
        <?= lang('Crossword.check') ?>
        <button id="check-letter"><?= lang('Crossword.letter') ?></button>
        <button id="check-word"><?= lang('Crossword.word') ?></button>
        <button id="check-grid"><?= lang('Crossword.grid') ?></button>
    </div>
</div>

<div class="question-preview"></div>
<div class="grid-container">
    <input class="dummy" type="text" autocapitalize="off" autocorrect="off" autocomplete="off" spellcheck="false">
    <div class="grid"></div>
    <div class="word-preview"></div>
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

<div class="get-comments-container">
    <button id="get-comments"><?= lang('Crossword.loadComments') ?></button>
</div>

<div class="comments">
    <div class="comment-form">
        <div class="comment-error"></div>
        <form id="comment" method="POST" action="<?= site_url('comment'); ?>" accept-charset="UTF-8">
            <?= csrf_field() ?>
            <input required hidden type="text" name="crossword_id" value="<?= $crossword['id'] ?>" />
            <p>
                <label><?= lang('Crossword.commentText') ?></label>
                <textarea required name="comment_text" value=""></textarea>
            </p>
            <p>
                <button name="postComment" form="comment" type="submit" <?php if (!session('userData.id')): echo 'disabled'; endif; ?>>
                    <?= lang('Crossword.postComment') ?>
                </button>
            </p>
        </form>
    </div>
    <div class="comments-list"></div>
    <div class="more-comments-container">
        <button id="more-comments"><?= lang('Crossword.loadMoreComments') ?></button>
    </div>
</div>

<?php if ($crosswordSaveData): ?>
<div id="save-data"><?= json_encode($crosswordSaveData) ?></div>
<?php endif; ?>

<script src="/js/player.js"></script>
<script src="/js/save.js"></script>
<script src="/js/favorite.js"></script>
<script src="/js/print.js"></script>
<script src="/js/report.js"></script>
<script src="/js/comment.js"></script>
<?php if ($isModerator): ?>
<script src="/js/moderation.js"></script>
<?php endif; ?>

<?= $this->endSection() ?>
