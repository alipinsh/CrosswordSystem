<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<h1><?= lang('Account.registration') ?></h1>

<?= view('_notifications') ?>

<form id="register" method="POST" action="<?= route_to('register'); ?>" accept-charset="UTF-8"
	onsubmit="registerButton.disabled = true; return true;">
	<?= csrf_field() ?>
	<p>
	    <label><?= lang('Account.name') ?></label><br />
	    <input required minlength="2" type="text" name="username" value="<?= old('username') ?>" />
	</p>
	<p>
	    <label><?= lang('Account.email') ?></label><br />
	    <input required type="email" name="email" value="<?= old('email') ?>" />
	</p>
	<p>
	    <label><?= lang('Account.password') ?></label><br />
	    <input required minlength="5" type="password" name="password" value="" />
	</p>
	<p>
	    <label><?= lang('Account.passwordAgain') ?></label><br />
	    <input required minlength="5" type="password" name="password_confirm" value="" />
	</p>
	<p>
	    <button name="registerButton" type="submit"><?= lang('Account.register') ?></button>
	</p>
	<p>
		<a href="<?= site_url('login'); ?>" class="float-right"><?= lang('Account.alreadyRegistered') ?></a>
	</p>
</form>

<?= $this->endSection() ?>