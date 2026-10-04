<?php include 'partials/header.view.php' ?>
<?php include 'partials/admin-nav.view.php' ?>

<article>
    <header>
        <h1>Dashboard</h1>
    </header>

    <div class="grid">
        <article><h2><?= (int) $pages ?></h2><p>Pages</p></article>
        <article><h2><?= (int) $media ?></h2><p>Media</p></article>
        <article><h2><?= (int) $settings ?></h2><p>Settings</p></article>
    </div>

    <footer>
        <a href="<?= ROOT ?>/admin/pages" role="button">Manage pages</a>
        <a href="<?= ROOT ?>/page" role="button" class="secondary" target="_blank">View site</a>
    </footer>
</article>

<?php include 'partials/footer.view.php' ?>
