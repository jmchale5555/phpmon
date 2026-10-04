<?php
$seg0 = URL(0);
$seg1 = URL(1);
?>
<nav>
    <ul>
        <li><a href="<?= ROOT ?>/admin" <?= ($seg0 === 'admin' && !$seg1) ? 'aria-current="page"' : '' ?>>Dashboard</a></li>
        <li><a href="<?= ROOT ?>/admin/pages" <?= $seg1 === 'pages' ? 'aria-current="page"' : '' ?>>Pages</a></li>
        <li><a href="<?= ROOT ?>/admin/settings" <?= $seg1 === 'settings' ? 'aria-current="page"' : '' ?>>Settings</a></li>
        <li><a href="<?= ROOT ?>/admin/media" <?= $seg1 === 'media' ? 'aria-current="page"' : '' ?>>Media</a></li>
    </ul>
    <ul>
        <li><a href="<?= ROOT ?>/password">Password</a></li>
        <li>
            <form method="post" action="<?= ROOT ?>/logout" style="display:inline; margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="secondary outline">Logout</button>
            </form>
        </li>
    </ul>
</nav>
