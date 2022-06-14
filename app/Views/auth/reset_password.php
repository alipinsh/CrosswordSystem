<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<h1><?= lang('Account.resetPassword') ?></h1>

<?= view('_notifications') ?>

<form id="register-password" method="POST" action="<?= site_url('reset-password'); ?>" accept-charset="UTF-8">
    <?= csrf_field() ?>
    <p>
        <label><?= lang('Account.newPassword') ?></label><br />
        <input required type="password" name="password" value="" />
    </p>
    <p>
        <label><?= lang('Account.newPasswordAgain') ?></label><br />
        <input required type="password" name="password_confirm" value="" />
    </p>
    <p>
        <input type="hidden" name="token" value="<?= $token ?>" />
        <button type="submit"><?= lang('Account.resetPassword') ?></button>
    </p>
</form>

<?= $this->endSection() ?>
