<?php include 'partials/header.view.php' ?>

<article>
    <header>
        <h1><?= esc(APP_NAME) ?></h1>
        <p><?= esc(APP_DESC) ?></p>
    </header>

    <p>
        This is a no-build PHP MVC monolith with server-rendered pages.
        Add a controller in <code>app/controllers</code> and a matching view in
        <code>app/views</code> to start building.
    </p>
</article>

<?php include 'partials/footer.view.php' ?>
