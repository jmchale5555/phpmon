<?php $url = URL(0); ?>

<nav id="site-nav">
    <ul>
        <li>
            <strong>
                <a href="<?= ROOT ?>/home"
                    hx-get="<?= ROOT ?>/home"
                    hx-target="#page-content"
                    hx-select="#page-content > *"
                    hx-select-oob="#site-nav"
                    hx-swap="innerHTML"
                    hx-push-url="true"><?= esc(APP_NAME) ?></a>
            </strong>
        </li>
    </ul>
    <ul>
        <li>
            <a href="<?= ROOT ?>/home"
                hx-get="<?= ROOT ?>/home"
                hx-target="#page-content"
                hx-select="#page-content > *"
                hx-select-oob="#site-nav"
                hx-swap="innerHTML"
                hx-push-url="true"
                <?= $url === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
        </li>
    </ul>
</nav>
