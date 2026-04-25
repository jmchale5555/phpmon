<?php $url = URL(0); ?>

<nav>
    <ul>
        <li>
            <strong>
                <a href="<?= ROOT ?>/home">Sbox1</a>
            </strong>
        </li>
    </ul>
    <ul>
        <li>
            <a href="<?= ROOT ?>/home" <?= $url === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
        </li>
        <?php if (empty($_SESSION['USER'])): ?>
            <li>
                <a href="<?= ROOT ?>/login" <?= $url === 'login' ? 'aria-current="page"' : '' ?>>Login</a>
            </li>
            <li>
                <a href="<?= ROOT ?>/signup" <?= $url === 'signup' ? 'aria-current="page"' : '' ?>>Signup</a>
            </li>
        <?php else: ?>
            <li>
                <a href="<?= ROOT ?>/logout">Logout</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
