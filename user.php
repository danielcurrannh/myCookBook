<?php
require_once __DIR__ . '/functions.php';
$currentUser = current_user();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT fld_id, fld_userName, fld_profilePublic FROM tbl_users WHERE fld_id = ?');
$stmt->execute([$id]);
$profile = $stmt->fetch();
if (!$profile) {
    http_response_code(404);
    exit('User not found.');
}
$isOwner = $currentUser && (int)$currentUser['fld_id'] === $id;
$recipeStmt = db()->prepare('SELECT tbl_recipes.*, tbl_users.fld_userName FROM tbl_recipes JOIN tbl_users ON tbl_users.fld_id = tbl_recipes.fld_userId WHERE tbl_recipes.fld_userId = ? AND (? = 1 OR tbl_recipes.fld_isPrivate = 0) ORDER BY tbl_recipes.fld_createdAt DESC');
$recipeStmt->execute([$id, $isOwner ? 1 : 0]);
$recipes = $recipeStmt->fetchAll();
$favStmt = db()->prepare('SELECT tbl_recipes.*, tbl_users.fld_userName FROM tbl_userSettings JOIN tbl_recipes ON tbl_recipes.fld_id = tbl_userSettings.fld_savedRecipeId JOIN tbl_users ON tbl_users.fld_id = tbl_recipes.fld_userId WHERE tbl_userSettings.fld_userId = ? AND tbl_recipes.fld_isPrivate = 0 ORDER BY tbl_userSettings.fld_createdAt DESC');
$favStmt->execute([$id]);
$favorites = $favStmt->fetchAll();
include __DIR__ . '/header.php';
?>
<h1><?= e($profile['fld_userName']) ?>'s recipes</h1>
<section class="cards"><?php foreach ($recipes as $recipe) echo recipe_card($recipe); ?></section>
<h2 id="favorites">Favorited recipes</h2>
<section class="cards"><?php foreach ($favorites as $recipe) echo recipe_card($recipe); ?></section>
<?php include __DIR__ . '/footer.php'; ?>

