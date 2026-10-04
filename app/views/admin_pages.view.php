<?php include 'partials/header.view.php' ?>
<?php include 'partials/admin-nav.view.php' ?>

<article>
    <header style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
        <h1 style="margin:0;">Pages</h1>
        <a href="<?= ROOT ?>/admin/editPage" role="button">New page</a>
    </header>

    <?php $success = message(null, true); ?>
    <?php if (!empty($success)): ?>
        <p><mark><?= esc($success) ?></mark></p>
    <?php endif; ?>

    <?php if (empty($pages)): ?>
        <p>No pages yet.</p>
    <?php else: ?>
        <figure>
            <table>
                <thead>
                    <tr><th>Title</th><th>Slug</th><th>Published</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td><?= esc($page->title) ?></td>
                            <td><a href="<?= ROOT ?>/page/<?= esc($page->slug) ?>" target="_blank"><?= esc($page->slug) ?></a></td>
                            <td><?= $page->is_published ? 'Yes' : 'No' ?></td>
                            <td style="white-space:nowrap;">
                                <a href="<?= ROOT ?>/admin/editPage/<?= (int) $page->id ?>" role="button" class="secondary">Edit</a>
                                <form method="post" action="<?= ROOT ?>/admin/deletePage/<?= (int) $page->id ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="secondary" onclick="return confirm('Delete this page?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </figure>
    <?php endif; ?>
</article>

<?php include 'partials/footer.view.php' ?>
