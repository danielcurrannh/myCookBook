<?php
require_once __DIR__ . '/functions.php';
$currentUser = current_user();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT tbl_recipes.*, tbl_users.fld_userName FROM tbl_recipes JOIN tbl_users ON tbl_users.fld_id = tbl_recipes.fld_userId WHERE tbl_recipes.fld_id = ?');
$stmt->execute([$id]);
$recipe = $stmt->fetch();
if (!$recipe || ($recipe['fld_isPrivate'] && (!$currentUser || (int)$currentUser['fld_id'] !== (int)$recipe['fld_userId']))) {
    http_response_code(404);
    exit('Recipe not found.');
}
$isCreator = $currentUser && (int)$currentUser['fld_id'] === (int)$recipe['fld_userId'];
db()->prepare('UPDATE tbl_recipes SET fld_viewCount = fld_viewCount + 1 WHERE fld_id = ?')->execute([$id]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_login();
    db()->prepare('INSERT IGNORE INTO tbl_userSettings (fld_userId, fld_savedRecipeId) VALUES (?, ?)')->execute([(int)$user['fld_id'], $id]);
    redirect('recipe.php?id=' . $id);
}
include __DIR__ . '/header.php';
?>
<article class="recipe-detail">
    <img class="detail-thumb" src="<?= e($recipe['fld_thumbnailImage'] ?: 'assets/placeholder.svg') ?>" alt="Recipe thumbnail">
    <h1><?= e($recipe['fld_recipeTitle']) ?></h1>
    <p>By <a href="user.php?id=<?= (int)$recipe['fld_userId'] ?>"><?= e($recipe['fld_userName']) ?></a></p>
    <?php if ($isCreator): ?><p><a class="button inline-button" href="recipe_edit.php?id=<?= (int)$recipe['fld_id'] ?>">Edit recipe</a></p><?php endif; ?>
    <?php if ($currentUser): ?><form method="post"><button>Save to favorites</button></form><?php endif; ?>
    <h2>Ingredients</h2>
    <p class="preline"><?= e($recipe['fld_recipeIngredients']) ?></p>
    <h2>Directions</h2>
    <p class="preline"><?= e($recipe['fld_recipeDirections']) ?></p>
</article>
<?php include __DIR__ . '/footer.php'; ?>

