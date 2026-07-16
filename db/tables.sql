USE db_myCookBook;

CREATE TABLE IF NOT EXISTS tbl_users (
  fld_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fld_userName VARCHAR(50) NOT NULL UNIQUE,
  fld_passwordHash VARCHAR(255) NOT NULL,
  fld_email VARCHAR(255) NOT NULL UNIQUE,
  fld_profilePublic TINYINT(1) NOT NULL DEFAULT 1,
  fld_createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tbl_recipes (
  fld_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fld_userId INT UNSIGNED NOT NULL,
  fld_recipeTitle VARCHAR(150) NOT NULL,
  fld_recipeDescription VARCHAR(500) DEFAULT NULL,
  fld_recipeIngredients TEXT NOT NULL,
  fld_recipeDirections TEXT NOT NULL,
  fld_thumbnailImage VARCHAR(255) DEFAULT NULL,
  fld_isPrivate TINYINT(1) NOT NULL DEFAULT 0,
  fld_viewCount INT UNSIGNED NOT NULL DEFAULT 0,
  fld_createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fld_updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tbl_recipes_user FOREIGN KEY (fld_userId) REFERENCES tbl_users(fld_id) ON DELETE CASCADE,
  INDEX idx_tbl_recipes_title (fld_recipeTitle),
  INDEX idx_tbl_recipes_created (fld_createdAt),
  INDEX idx_tbl_recipes_views (fld_viewCount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tbl_userSettings (
  fld_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fld_userId INT UNSIGNED NOT NULL,
  fld_savedRecipeId INT UNSIGNED NOT NULL,
  fld_createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tbl_userSettings_user FOREIGN KEY (fld_userId) REFERENCES tbl_users(fld_id) ON DELETE CASCADE,
  CONSTRAINT fk_tbl_userSettings_recipe FOREIGN KEY (fld_savedRecipeId) REFERENCES tbl_recipes(fld_id) ON DELETE CASCADE,
  UNIQUE KEY uq_tbl_userSettings_saved (fld_userId, fld_savedRecipeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tbl_passwordResets (
  fld_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fld_userId INT UNSIGNED NOT NULL,
  fld_tokenHash CHAR(64) NOT NULL UNIQUE,
  fld_expiresAt DATETIME NOT NULL,
  fld_createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tbl_passwordResets_user FOREIGN KEY (fld_userId) REFERENCES tbl_users(fld_id) ON DELETE CASCADE,
  INDEX idx_tbl_passwordResets_expires (fld_expiresAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
