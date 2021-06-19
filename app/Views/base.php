<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Xword System</title>
    <link rel="stylesheet" href="/css/main.css">
    <?= $this->renderSection('additionalStyle') ?>
    <script src="/js/translation/<?= config('App')->defaultLocale ?>.js"></script>
</head>
<body>
    <?php echo view('header'); ?>
    <main>
        <?= $this->renderSection('main') ?>
    </main>
    <?php echo view('footer'); ?>
</body>
</html>