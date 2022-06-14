<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<h1><?= lang('Account.forgottenPassword') ?></h1>

<?= view('_notifications') ?>

<form id="forgot-password" method="POST" action="<?= site_url('forgot-password'); ?>" accept-charset="UTF-8"
    onsubmit="submitButton.disabled = true; return true;">
    <?= csrf_field() ?>
    <p>
        <label><?= lang('Account.typeEmail') ?></label><br />
        <input required type="email" name="email" value="<?= old('email') ?>" />
    </p>
    <p>
        <button name="submitButton" type="submit"><?= lang('Account.setNewPassword') ?></button>
    </p>
</form>

<?= $this->endSection() ?>