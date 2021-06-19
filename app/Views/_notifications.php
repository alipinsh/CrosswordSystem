<?php if (session()->has('success')) : ?>
    <div class="notification success">
        <?= session('success') ?>
    </div>
<?php endif ?>

<?php if (session()->has('error')) : ?>
    <div class="notification error">
        <?= session('error') ?>
    </div>
<?php endif ?>

<?php if (session()->has('errors')) : ?>
    <div class="notification error">
    <?php foreach (session('errors') as $error) : ?>
        <div><?= $error ?></div>
    <?php endforeach ?>
    </div>
<?php endif ?>