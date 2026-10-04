<?php
$initial = [];
if (!empty($page) && !empty($page->content))
{
    $decoded = json_decode((string) $page->content, true);
    if (is_array($decoded))
    {
        $initial = $decoded;
    }
}
$initialJson = json_encode($initial ?: new stdClass(), JSON_UNESCAPED_SLASHES);
?>
<?php include 'partials/header.view.php' ?>
<?php include 'partials/admin-nav.view.php' ?>

<article>
    <header>
        <h1><?= $page ? 'Edit page' : 'New page' ?></h1>
    </header>

    <?php if (!empty($errors)): ?>
        <p><mark><?= esc(implode(' | ', $errors)) ?></mark></p>
    <?php endif; ?>

    <form method="post" x-data="pageEditor(<?= esc($initialJson) ?>)">
        <?= csrf_field() ?>

        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?= esc($page->title ?? old_value('title')) ?>" required>

        <label for="slug">Slug (URL) — leave blank to derive from the title</label>
        <input type="text" name="slug" id="slug" value="<?= esc($page->slug ?? old_value('slug')) ?>" placeholder="about">

        <label>
            <input type="checkbox" name="is_published" value="1" <?= (!$page || $page->is_published) ? 'checked' : '' ?>>
            Published
        </label>

        <fieldset>
            <legend>Content blocks</legend>
            <p><small>Each block is a key/value pair stored as JSON. Add or remove blocks freely — no
                database change is needed.</small></p>

            <template x-for="(field, index) in fields" :key="index">
                <div style="display:flex; gap:0.5rem; align-items:flex-start; margin-bottom:0.75rem;">
                    <input type="text" x-model="field.key" placeholder="key (e.g. hero_title)" style="margin:0; flex:0 0 12rem;">
                    <textarea x-model="field.value" placeholder="Text" rows="2" style="margin:0;"></textarea>
                    <button type="button" class="secondary" x-on:click="remove(index)" style="margin:0;">Remove</button>
                </div>
            </template>

            <button type="button" class="secondary" x-on:click="add()">Add block</button>
        </fieldset>

        <input type="hidden" name="content" x-bind:value="toJson()">

        <button type="submit">Save page</button>
        <a href="<?= ROOT ?>/admin/pages" role="button" class="secondary">Cancel</a>
    </form>
</article>

<script>
    function pageEditor(initial)
    {
        return {
            fields: Object.entries(initial || {}).map(([key, value]) => ({
                key: key,
                value: value === null ? '' : String(value)
            })),
            add()
            {
                this.fields.push({ key: '', value: '' });
            },
            remove(index)
            {
                this.fields.splice(index, 1);
            },
            toJson()
            {
                const out = {};
                for (const field of this.fields)
                {
                    const key = (field.key || '').trim();
                    if (key !== '')
                    {
                        out[key] = field.value;
                    }
                }
                return JSON.stringify(out);
            }
        };
    }
</script>

<?php include 'partials/footer.view.php' ?>
