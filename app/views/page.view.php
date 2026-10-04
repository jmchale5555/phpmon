<?php include 'partials/header.view.php' ?>

<article>
    <header>
        <h1><?= esc($page->title) ?></h1>
    </header>

    <?php if (empty($content)): ?>
        <p>This page has no content yet.</p>
    <?php endif; ?>

    <?php foreach ($content as $key => $value): ?>
        <?php if (is_array($value)) { continue; } ?>
        <section>
            <h2><?= esc(ucwords(str_replace(['_', '-'], ' ', (string) $key))) ?></h2>
            <p><?= nl2br(esc((string) $value)) ?></p>
        </section>
    <?php endforeach; ?>
</article>

<?php include 'partials/footer.view.php' ?>
