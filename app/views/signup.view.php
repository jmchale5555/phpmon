<?php include 'partials/header.view.php' ?>

<article>
    <header>
        <h1>Create account</h1>
    </header>

    <?php if (!empty($errors)): ?>
        <p><mark><?= implode(' | ', $errors); ?></mark></p>
    <?php endif; ?>

    <form method="post">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" value="<?= esc(old_value('name')); ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= esc(old_value('email')); ?>" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm">Confirm password</label>
        <input type="password" name="confirm" id="confirm" required>

        <label>
            <input type="checkbox" required>
            I accept the terms.
        </label>

        <button type="submit">Create account</button>
    </form>
</article>

<?php include 'partials/footer.view.php' ?>
