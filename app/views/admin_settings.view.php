<?php include 'partials/header.view.php' ?>
<?php include 'partials/admin-nav.view.php' ?>

<article>
    <header>
        <h1>Site settings</h1>
    </header>

    <?php $success = message(null, true); ?>
    <?php if (!empty($success)): ?>
        <p><mark><?= esc($success) ?></mark></p>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <?php foreach ($settings as $setting): ?>
            <label for="setting_<?= esc($setting->setting_key) ?>">
                <?= esc(ucwords(str_replace('_', ' ', $setting->setting_key))) ?>
            </label>
            <input type="text"
                id="setting_<?= esc($setting->setting_key) ?>"
                name="settings[<?= esc($setting->setting_key) ?>]"
                value="<?= esc($setting->setting_value ?? '') ?>">
        <?php endforeach; ?>

        <button type="submit">Save settings</button>
    </form>
</article>

<?php include 'partials/footer.view.php' ?>
