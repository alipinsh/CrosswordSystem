<?= $this->extend('base') ?>
<?= $this->section('additionalStyle') ?>
<link rel="stylesheet" href="/css/account.css">
<?= $this->endSection() ?>
<?= $this->section('main') ?>

<?= view('_notifications') ?>
<div class="account-info">
    <div class="avatar" style="background-image: url('/img/avatar/<?= $user['image'] ?>')"></div>
    <div class="account-stats">
        <div class="username"><?= $user['username'] ?></div>
        <div class="registered-on"><?= lang('Account.registeredOn', [$user['registeredOn']]) ?></div>
        <div class="favorited-count"><?= lang('Account.favorited', [$user['favoritedCount']]) ?></div>
        <div class="created-count"><?= lang('Account.created', [$user['createdCount']]) ?></div>
    </div>
</div>
    
<?php if ($isMine): ?>
<div class="account-config">
    <div class="account-config-options">
        <button class="tab-button" data-for="#change-preferences-tab"><?= lang('Account.changePreferences') ?></button>
        <button class="tab-button" data-for="#change-picture-tab"><?= lang('Account.changePicture') ?></button>
        <button class="tab-button" data-for="#change-email-tab"><?= lang('Account.changeEmail') ?></button>
        <button class="tab-button" data-for="#change-password-tab"><?= lang('Account.changePassword') ?></button>
        <button id="logout" onclick="window.open('<?= site_url('logout') ?>', '_self')"><?= lang('Account.logout') ?></button>
    </div>

    <div id="change-preferences-tab">
        <h2><?= lang('Account.changePreferences') ?></h2>
        <form id="change-email" method="POST" action="<?= site_url('change-preferences'); ?>" accept-charset="UTF-8"
            onsubmit="changePreferences.disabled = true; return true;">
            <?= csrf_field() ?>
            <p>
                <input type="checkbox" id="show_save_on_home" name="show_save_on_home" <?php if ($user['show_save_on_home']): echo 'checked'; endif; ?>>
                <label for="show_save_on_home"><?= lang('Account.showSaveOnHome') ?></label>
            </p>
            <p>
                <button name="changePreferences" type="submit"><?= lang('Account.update') ?></button>
            </p>
        </form>
    </div>

    <div id="change-picture-tab">
        <h2><?= lang('Account.changePicture') ?></h2>
        <div class="image-info"><?= lang('Account.imageInfo') ?></div>
        <div class="image-error"></div>
        <form id="change-picture">
            <?= csrf_field() ?>
            <p>
                <input type="file" name="image" accept="image/png,image/jpeg,image/gif,.png,.gif,.jpg,.jpeg,.jpe">
            </p>
            <p>
                <button name="uploadImage" type="submit"><?= lang('Account.imageUpload') ?></button>
            </p>
        </form>
    </div>

    <div id="change-email-tab">
        <h2><?= lang('Account.changeEmail') ?></h2>
        <form id="change-email" method="POST" action="<?= site_url('change-email'); ?>" accept-charset="UTF-8"
            onsubmit="changeEmail.disabled = true; return true;">
            <?= csrf_field() ?>
            <p>
                <label><?= lang('Account.newEmail') ?></label><br />
                <input required type="email" name="new_email" value="<?= old('new_email') ?>" />
            </p>
            <p>
                <label><?= lang('Account.currentPassword') ?></label><br />
                <input required type="password" name="password" value="" />
            </p>
            <p>
                <button name="changeEmail" type="submit"><?= lang('Account.update') ?></button>
            </p>
        </form>
    </div>

    <div id="change-password-tab">
        <h2><?= lang('Account.changePassword') ?></h2>
        <form id="change-password" method="POST" action="<?= site_url('change-password'); ?>" accept-charset="UTF-8"
            onsubmit="changePassword.disabled = true; return true;">
            <?= csrf_field() ?>
            <p>
                <label><?= lang('Account.currentPassword') ?></label><br />
                <input required type="password" minlength="5" name="password" value="" />
            </p>
            <p>
                <label><?= lang('Account.newPassword') ?></label><br />
                <input required type="password" minlength="5" name="new_password" value="" />
            </p>
            <p>
                <label><?= lang('Account.newPasswordAgain') ?></label><br />
                <input required type="password" minlength="5" name="new_password_confirm" value="" />
            </p>
            <p>
                <button name="changePassword" type="submit"><?= lang('Account.update') ?></button>
            </p>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="account-crossword-lists">
    <div class="created-crosswords">
    <h2><?= lang("Account.createdCrosswords")?></h2>
    <?php foreach ($user['createdCrosswords'] as $c):?>
        <div class="crossword-block">
            <div><a href="/crossword/<?= $c['id'] ?>"><?= $c['title'] ?></a></div>
            <div><?= $c['width'] ?> x <?= $c['height'] ?></div>
            <div><?= lang("Crossword.questions", [$c['questions']]) ?></div>
        </div>
    <?php endforeach; ?>
    <?php if (count($user['createdCrosswords']) > 0): ?>
        <div class="created-crosswords-more"><a href="/crosswords/u/<?= $user['username'] ?>/created"><?= lang("Account.viewAll") ?></a></div>
    <?php else: ?>
        <div class="created-crosswords-none"><?= lang("Misc.none") ?></div>
    <?php endif; ?>
    <?php if ($isMine): ?>
        <div class="account-show-privates"><a href="/crosswords/private"><?= lang('Account.showPrivates') ?></a></div>
    <?php endif; ?>
    </div>

    <div class="favorited-crosswords">
    <h2><?= lang("Account.favoritedCrosswords")?></h2>
    <?php foreach ($user['favoritedCrosswords'] as $c):?>
        <div class="crossword-block">
            <div><a href="/crossword/<?= $c['id'] ?>"><?= $c['title'] ?></a></div>
            <div><?= $c['width'] ?> x <?= $c['height'] ?></div>
            <div><?= lang("Crossword.questions", [$c['questions']]) ?></div>
        </div>
    <?php endforeach; ?>
    <?php if (count($user['favoritedCrosswords']) > 0): ?>
        <div class="favorited-crosswords-more"><a href="/crosswords/u/<?= $user['username'] ?>/favorited"><?= lang("Account.viewAll") ?></a></div>
    <?php else: ?>
        <div class="favorited-crosswords-none"><?= lang("Misc.none") ?></div>
    <?php endif; ?>
    </div>
</div>

<script src="/js/account.js"></script>

<?= $this->endSection() ?>
