<?= $this->extend('base') ?>
<?= $this->section('main') ?>
<h1><?= lang('Moderation.allUsers') ?></h1>
<?php foreach ($users as $user): ?>
<div class="user" data-user="<?= $user['id'] ?>">
    <span class="username"><?= $user['username'] ?></span>
    <span class="role">
        <?php switch($user['role']): 
            case 1: ?>
                User
            <?php break; ?>
            <?php case 2: ?>
                Small Mod
            <?php break; ?>
            <?php case 3: ?>
                Big Mod
            <?php break; ?>
            <?php case 4: ?>
                Admin
            <?php break; ?>
        <?php endswitch; ?>
    </span>
    <span class="role-switch-buttons">
        <button class="role-switch-button" data-role="1">User</button>
        <button class="role-switch-button" data-role="2">Small Mod</button>
        <button class="role-switch-button" data-role="3">Big Mod</button>
        <button class="role-switch-button" data-role="4">Admin</button>
    </span>
    <button class="delete-button"><?= lang('Moderation.deleteUser') ?></button>
</div>
<?php endforeach; ?>

<script src="/js/user_moderation.js"></script>
<?= $this->endSection() ?>
