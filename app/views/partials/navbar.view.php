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
                    hx-push-url="true">Sbox1</a>
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
        <li class="nav-menu-wrap" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
            <button type="button"
                class="contrast nav-menu-button"
                aria-label="Open account menu"
                x-on:click="open = !open"
                x-bind:aria-expanded="open.toString()">
                <img class="nav-menu-icon" src="<?= ROOT ?>/assets/icons/lucide/menu.svg" alt="" width="18" height="18">
                Menu
            </button>

            <div class="nav-menu-panel" x-cloak x-show="open" x-on:click.outside="open = false">
                <?php if (empty($_SESSION['USER'])): ?>
                    <a href="<?= ROOT ?>/login"
                        x-on:click="open = false"
                        hx-get="<?= ROOT ?>/login"
                        hx-target="#page-content"
                        hx-select="#page-content > *"
                        hx-select-oob="#site-nav"
                        hx-swap="innerHTML"
                        hx-push-url="true"
                        <?= $url === 'login' ? 'aria-current="page"' : '' ?>>Login</a>

                    <a href="<?= ROOT ?>/signup"
                        x-on:click="open = false"
                        hx-get="<?= ROOT ?>/signup"
                        hx-target="#page-content"
                        hx-select="#page-content > *"
                        hx-select-oob="#site-nav"
                        hx-swap="innerHTML"
                        hx-push-url="true"
                        <?= $url === 'signup' ? 'aria-current="page"' : '' ?>>Signup</a>
                <?php else: ?>
                    <a href="<?= ROOT ?>/password"
                        x-on:click="open = false"
                        hx-get="<?= ROOT ?>/password"
                        hx-target="#page-content"
                        hx-select="#page-content > *"
                        hx-select-oob="#site-nav"
                        hx-swap="innerHTML"
                        hx-push-url="true"
                        <?= $url === 'password' ? 'aria-current="page"' : '' ?>>Reset password</a>

                    <a href="<?= ROOT ?>/logout" x-on:click="open = false">Logout</a>
                <?php endif; ?>
            </div>
        </li>
    </ul>
</nav>
