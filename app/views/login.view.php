<?php include 'partials/header.view.php' ?>

<article style="max-width: 26rem; margin-inline: auto;">
    <header>
        <h1>Sign in</h1>
    </header>

    <?php if (!empty($errors)): ?>
        <p><mark><?= esc(implode(' | ', $errors)) ?></mark></p>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= esc(old_value('email')) ?>" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Sign in</button>
    </form>
</article>

<?php include 'partials/footer.view.php' ?>
