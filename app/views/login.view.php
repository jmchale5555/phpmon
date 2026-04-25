<?php include 'partials/header.view.php' ?>

<article>
    <header>
        <h1>Sign in</h1>
    </header>

    <?php if (!empty($errors)): ?>
        <p><mark><?= implode(' | ', $errors); ?></mark></p>
    <?php endif; ?>

    <form method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Sign in</button>
    </form>

    <p>
        Need an account? <a href="<?= ROOT ?>/signup">Sign up</a>
    </p>
</article>

<?php include 'partials/footer.view.php' ?>
