<?php include 'partials/header.view.php' ?>
<?php include 'partials/admin-nav.view.php' ?>

<article style="max-width: 26rem; margin-inline: auto;">
    <header>
        <h1>Change password</h1>
    </header>

    <?php $success = message(null, true); ?>
    <?php if (!empty($success)): ?>
        <p><mark><?= esc($success) ?></mark></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <p><mark><?= esc(implode(' | ', $errors)) ?></mark></p>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <label for="current_password">Current password</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="password">New password</label>
        <input type="password" name="password" id="password" minlength="8" required>

        <label for="confirm">Confirm new password</label>
        <input type="password" name="confirm" id="confirm" minlength="8" required>

        <button type="submit">Update password</button>
    </form>
</article>

<?php include 'partials/footer.view.php' ?>
