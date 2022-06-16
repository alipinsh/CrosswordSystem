<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<h1><?= lang('Misc.language') ?></h1>

<form id="language" method="POST" action="<?= site_url('language'); ?>" accept-charset="UTF-8">
    <?= csrf_field() ?>
    <p>
        <input type="radio" id="en" name="language" value="en" <?php if ($locale === 'en'): echo 'checked'; endif; ?>>
        <label for="en">English</label><br>
        <input type="radio" id="ru" name="language" value="ru" <?php if ($locale === 'ru'): echo 'checked'; endif; ?>>
        <label for="ru">Русский</label><br>
        <input type="radio" id="lv" name="language" value="lv" <?php if ($locale === 'lv'): echo 'checked'; endif; ?>>
        <label for="lv">Latviešu</label>
    </p>
    <p>
        <button type="submit"><?= lang('Misc.submit') ?></button>
    </p>
</form>

<?= $this->endSection() ?>
