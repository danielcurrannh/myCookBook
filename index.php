<?php
require_once __DIR__ . '/functions.php';
$mode = $_GET['mode'] ?? 'new';
$query = trim($_GET['q'] ?? '');
$recipes = fetch_recipes($mode, $query);
include __DIR__ . '/header.php';
?>
<section class="hero">
    <h1>Find your next favorite recipe</h1>
    <form class="search" method="get">
        <input type="search" name="q" placeholder="Search recipes or public users" value="<?= e($query) ?>">
        <select name="mode">
            <option value="new" <?= $mode === 'new' ? 'selected' : '' ?>>New recipes</option>
            <option value="popular" <?= $mode === 'popular' ? 'selected' : '' ?>>Popular recipes</option>
            <option value="hot" <?= $mode === 'hot' ? 'selected' : '' ?>>Hot recipes</option>
        </select>
        <button>Search</button>
    </form>
</section>
<div class="layout">
    <aside class="sidebar">
        <?php if ($currentUser): ?>
            <a class="button" href="recipe_create.php">Create new recipe</a>
            <a class="button secondary" href="user.php?id=<?= (int)$currentUser['fld_id'] ?>#favorites">Favorited recipes</a>
        <?php else: ?>
            <a class="button" href="login.php">Log in to create recipes</a>
        <?php endif; ?>
    </aside>
    <section class="cards">
        <?php foreach ($recipes as $recipe): ?>
            <?= recipe_card($recipe) ?>
        <?php endforeach; ?>
        <?php if (!$recipes): ?>
            <p>No recipes found.</p>
        <?php endif; ?>
    </section>
</div>
<?php include __DIR__ . '/footer.php'; ?>

