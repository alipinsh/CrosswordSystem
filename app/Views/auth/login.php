<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<h1><?= lang('Account.login') ?></h1>

<?= view('_notifications') ?>

<form id="login" method="POST" action="<?= site_url('login'); ?>" accept-charset="UTF-8">
    <?= csrf_field() ?>
    <p>
        <label><?= lang('Account.email') ?></label><br />
        <input required type="email" name="email" value="<?= old('email') ?>" />
    </p>
    <p>
        <label><?= lang('Account.password') ?></label><br />
        <input required minlength="5" type="password" name="password" value="" />
    </p>
    <p>
        <button type="submit"><?= lang('Account.login') ?></button>
    </p>
    <p>
    	<a href="<?= site_url('forgot-password'); ?>" class="float-right"><?= lang('Account.forgotYourPassword') ?></a>
    </p>
</form>

<?= $this->endSection() ?>