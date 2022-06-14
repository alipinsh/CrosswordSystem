<header>
    <div class="header-container">
        <div class="logo-container">
            <a href="/"><img class="logo-image" src="/img/logo.svg"></a>
        </div>
        <div class="search-container">
            <input type="text" id="search-query">
            <button id="search"><?= lang('Misc.search') ?></button>
            <script src="/js/search.js"></script>
        </div>
        <div class="auth-container">
            <?php if (session('userData.id')): ?>
                <?php if (session('userData.role') == 2): ?>
                <a href="<?= site_url('moderation') ?>"><?= lang('Moderation.moderation') ?></a>
                <?php endif; ?>
                <a href="<?= site_url('saves') ?>"><?= lang('Account.saves') ?></a>
                <a href="<?= site_url('account') ?>"><?= lang('Account.account') ?></a>
                <button class="create-crossword-button" onclick="window.open('<?= site_url('crossword/edit')?>', '_self')">
                    <?= lang('Crossword.createCrossword') ?>
                </button>
            <?php else: ?>
                <a href="<?= site_url('register') ?>"><?= lang('Account.register') ?></a>
                <a href="<?= site_url('login') ?>"><?= lang('Account.login') ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>
