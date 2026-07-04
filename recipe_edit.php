<?php
require_once __DIR__ . '/functions.php';

$user = require_login();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM tbl_recipes WHERE fld_id = ?');
$stmt->execute([$id]);
$recipe = $stmt->fetch();

if (!$recipe || (int)$recipe['fld_userId'] !== (int)$user['fld_id']) {
    http_response_code(403);
    exit('You can only edit recipes you created.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $directions = trim($_POST['directions'] ?? '');
        if ($title === '' || $ingredients === '' || $directions === '') {
            throw new RuntimeException('Title, ingredients, and directions are required.');
        }

        $thumb = handle_thumbnail_upload() ?? $recipe['fld_thumbnailImage'];
        $update = db()->prepare('UPDATE tbl_recipes
            SET fld_recipeTitle = ?,
                fld_recipeDescription = ?,
                fld_recipeIngredients = ?,
                fld_recipeDirections = ?,
                fld_thumbnailImage = ?,
                fld_isPrivate = ?
            WHERE fld_id = ? AND fld_userId = ?');
        $update->execute([
            $title,
            trim($_POST['description'] ?? ''),
            $ingredients,
            $directions,
            $thumb,
            isset($_POST['private']) ? 1 : 0,
            $id,
            (int)$user['fld_id'],
        ]);

        redirect('recipe.php?id=' . $id);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/header.php';
?>
<form class="recipe-form" method="post" enctype="multipart/form-data">
    <h1>Edit recipe</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <label>Title <input name="title" required maxlength="150" value="<?= e($recipe['fld_recipeTitle']) ?>"></label>
    <label>Short description <textarea name="description" maxlength="500"><?= e($recipe['fld_recipeDescription']) ?></textarea></label>
    <label>Ingredients <textarea name="ingredients" required><?= e($recipe['fld_recipeIngredients']) ?></textarea></label>
    <label>Directions <textarea name="directions" required><?= e($recipe['fld_recipeDirections']) ?></textarea></label>
    <?php if ($recipe['fld_thumbnailImage']): ?>
        <p>Current thumbnail:</p>
        <img class="edit-thumb" src="<?= e($recipe['fld_thumbnailImage']) ?>" alt="Current recipe thumbnail">
    <?php endif; ?>
    <label>Replace thumbnail image <input type="file" name="thumbnail" accept="image/*"></label>
    <label class="check"><input type="checkbox" name="private" <?= $recipe['fld_isPrivate'] ? 'checked' : '' ?>> Make private</label>
    <button>Save changes</button>
    <a class="button secondary inline-button" href="recipe.php?id=<?= (int)$recipe['fld_id'] ?>">Cancel</a>
</form>
<?php include __DIR__ . '/footer.php'; ?>

