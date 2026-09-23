-- ICT-AMSOS local role overrides
-- Keeps AMSOS-specific roles separate from the OTOS useremployee.User_Role field.

CREATE TABLE IF NOT EXISTS amsos_user_roles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    otos_user_id INT NOT NULL,
    role VARCHAR(100) NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_amsos_user_roles_otos_user_id (otos_user_id),
    KEY idx_amsos_user_roles_role (role)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
