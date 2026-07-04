<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function recipe_card(array $recipe): string
{
    $thumb = $recipe['fld_thumbnailImage'] ?: 'assets/placeholder.svg';
    $description = $recipe['fld_recipeDescription'] ?: substr(strip_tags($recipe['fld_recipeIngredients']), 0, 140);
    return '<a class="recipe-card" href="recipe.php?id=' . (int)$recipe['fld_id'] . '">' .
        '<img src="' . e($thumb) . '" alt="Recipe thumbnail">' .
        '<div><h3>' . e($recipe['fld_recipeTitle']) . '</h3>' .
        '<p>' . e($description) . '</p>' .
        '<small>By ' . e($recipe['fld_userName'] ?? 'Unknown') . ' • ' . (int)$recipe['fld_viewCount'] . ' views</small></div>' .
        '</a>';
}

function fetch_recipes(string $mode = 'new', string $query = ''): array
{
    $where = 'WHERE tbl_recipes.fld_isPrivate = 0';
    $params = [];

    if ($query !== '') {
        $where .= ' AND (tbl_recipes.fld_recipeTitle LIKE ? OR tbl_recipes.fld_recipeDescription LIKE ? OR tbl_users.fld_userName LIKE ?)';
        $like = '%' . $query . '%';
        $params = [$like, $like, $like];
    }

    $order = match ($mode) {
        'popular' => 'tbl_recipes.fld_viewCount DESC, tbl_recipes.fld_createdAt DESC',
        'hot' => hot_score_sql() . ' DESC, tbl_recipes.fld_createdAt DESC',
        default => 'tbl_recipes.fld_createdAt DESC',
    };

    $sql = "SELECT tbl_recipes.*, tbl_users.fld_userName
            FROM tbl_recipes
            JOIN tbl_users ON tbl_users.fld_id = tbl_recipes.fld_userId
            $where
            ORDER BY $order
            LIMIT 36";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function handle_thumbnail_upload(): ?string
{
    if (empty($_FILES['thumbnail']['name'])) {
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = mime_content_type($_FILES['thumbnail']['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF thumbnails are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $target = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
        throw new RuntimeException('Thumbnail upload failed.');
    }

    return UPLOAD_URL . $filename;
}

