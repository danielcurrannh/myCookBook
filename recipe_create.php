<?php
require_once __DIR__ . '/functions.php';
$user = require_login();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $directions = trim($_POST['directions'] ?? '');
        if ($title === '' || $ingredients === '' || $directions === '') {
            throw new RuntimeException('Title, ingredients, and directions are required.');
        }

        $thumb = handle_thumbnail_upload();
        $stmt = db()->prepare('INSERT INTO tbl_recipes (fld_userId, fld_recipeTitle, fld_recipeDescription, fld_recipeIngredients, fld_recipeDirections, fld_thumbnailImage, fld_isPrivate) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)$user['fld_id'],
            $title,
            trim($_POST['description'] ?? ''),
            $ingredients,
            $directions,
            $thumb,
            isset($_POST['private']) ? 1 : 0,
        ]);
        redirect('recipe.php?id=' . db()->lastInsertId());
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
include __DIR__ . '/header.php';
?>
<form class="recipe-form" method="post" enctype="multipart/form-data">
    <h1>Create recipe</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <label>Title <input name="title" required maxlength="150"></label>
    <label>Short description <textarea name="description" maxlength="500"></textarea></label>
    <label>Ingredients <textarea name="ingredients" required></textarea></label>
    <label>Directions <textarea name="directions" required></textarea></label>
    <label>Thumbnail image <input type="file" name="thumbnail" accept="image/*"></label>
    <label class="check"><input type="checkbox" name="private"> Make private</label>
    <button>Create recipe</button>
</form>
<?php include __DIR__ . '/footer.php'; ?>

