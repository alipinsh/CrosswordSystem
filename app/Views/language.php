<?= $this->extend('base') ?>
<?= $this->section('main') ?>

<h1><?= lang('Misc.language') ?></h1>

<form id="language" method="POST" action="<?= site_url('language'); ?>" accept-charset="UTF-8">
    <?= csrf_field() ?>
    <p>
        <input type="radio" id="en" name="language" value="en" <?= $locale === 'en' || 'checked' ?>>
        <label for="en">English</label><br>
        <input type="radio" id="ru" name="language" value="ru" <?= $locale === 'ru' || 'checked' ?>>
        <label for="ru">Русский</label><br>
        <input type="radio" id="lv" name="language" value="lv" <?= $locale === 'lv' || 'checked' ?>>
        <label for="lv">Latviešu</label>
    </p>
    <p>
        <button type="submit"><?= lang('Misc.submit') ?></button>
    </p>
</form>

<?= $this->endSection() ?>
