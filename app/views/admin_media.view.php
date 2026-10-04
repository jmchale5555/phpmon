<?php include 'partials/header.view.php' ?>
<?php include 'partials/admin-nav.view.php' ?>

<article>
    <header>
        <h1>Media</h1>
    </header>

    <?php $success = message(null, true); ?>
    <?php if (!empty($success)): ?>
        <p><mark><?= esc($success) ?></mark></p>
    <?php endif; ?>

    <?php if (!empty($errors['image'])): ?>
        <p><mark><?= esc($errors['image']) ?></mark></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="image">Upload image (jpg, png, gif, webp; max 5 MB)</label>
        <input type="file" name="image" id="image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <?php if (empty($media)): ?>
        <p>No images yet.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($media as $item): ?>
                <article>
                    <img src="<?= esc(get_image($item->path)) ?>" alt="<?= esc($item->alt ?? '') ?>">
                    <p><small><?= esc($item->path) ?></small></p>
                    <form method="post" action="<?= ROOT ?>/admin/deleteMedia/<?= (int) $item->id ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="secondary" onclick="return confirm('Delete this image?')">Delete</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>

<?php include 'partials/footer.view.php' ?>
