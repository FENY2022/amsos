CREATE TABLE IF NOT EXISTS office_divisions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  office VARCHAR(255) NOT NULL,
  officeDivision VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_office_division (office, officeDivision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS inventory_people (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  normalized_name VARCHAR(255) NOT NULL,
  office_id INT NULL,
  office VARCHAR(255) NOT NULL DEFAULT '',
  officeDivision VARCHAR(255) NOT NULL DEFAULT '',
  employment_status VARCHAR(255) NOT NULL DEFAULT '',
  source VARCHAR(50) NOT NULL DEFAULT 'inventory',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_inventory_person_name (normalized_name),
  KEY idx_inventory_people_office_id (office_id),
  KEY idx_inventory_people_office_division (office, officeDivision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO office_divisions (office, officeDivision)
SELECT MIN(TRIM(office)), MIN(TRIM(officeDivision))
FROM inv_inventory
WHERE office IS NOT NULL
  AND TRIM(office) <> ''
  AND officeDivision IS NOT NULL
  AND TRIM(officeDivision) <> ''
GROUP BY UPPER(TRIM(office)), UPPER(TRIM(officeDivision));

ALTER TABLE inv_inventory
  ADD COLUMN IF NOT EXISTS office_id INT NULL AFTER office,
  ADD COLUMN IF NOT EXISTS employee_person_id INT NULL AFTER employeeName,
  ADD COLUMN IF NOT EXISTS accountable_person_id INT NULL AFTER accountablePerson,
  ADD COLUMN IF NOT EXISTS actual_user_id INT NULL AFTER actualUser;

CREATE INDEX IF NOT EXISTS idx_inv_office_id ON inv_inventory (office_id);
CREATE INDEX IF NOT EXISTS idx_inv_employee_person_id ON inv_inventory (employee_person_id);
CREATE INDEX IF NOT EXISTS idx_inv_accountable_person_id ON inv_inventory (accountable_person_id);
CREATE INDEX IF NOT EXISTS idx_inv_actual_user_id ON inv_inventory (actual_user_id);

UPDATE inv_inventory i
JOIN office_divisions o
  ON UPPER(TRIM(i.office)) = UPPER(o.office)
 AND UPPER(TRIM(i.officeDivision)) = UPPER(o.officeDivision)
SET i.office_id = o.id;

INSERT INTO inventory_people (full_name, normalized_name, office_id, office, officeDivision, employment_status, source)
SELECT p.full_name, p.normalized_name, o.id, p.office, p.officeDivision, p.employment_status, p.source
FROM (
  SELECT
    MIN(person_name) AS full_name,
    UPPER(TRIM(person_name)) AS normalized_name,
    MIN(office) AS office,
    MIN(officeDivision) AS officeDivision,
    MIN(employment_status) AS employment_status,
    GROUP_CONCAT(DISTINCT source ORDER BY source SEPARATOR ',') AS source
  FROM (
    SELECT TRIM(employeeName) AS person_name, TRIM(office) AS office, TRIM(officeDivision) AS officeDivision, TRIM(statusOfEmployment) AS employment_status, 'employeeName' AS source
    FROM inv_inventory
    WHERE employeeName IS NOT NULL AND TRIM(employeeName) <> '' AND UPPER(TRIM(employeeName)) NOT IN ('N/A','NA','0','NOT FOUND') AND TRIM(employeeName) NOT REGEXP '^[0-9]'
    UNION ALL
    SELECT TRIM(accountablePerson), TRIM(office), TRIM(officeDivision), TRIM(statusOfEmployment), 'accountablePerson'
    FROM inv_inventory
    WHERE accountablePerson IS NOT NULL AND TRIM(accountablePerson) <> '' AND UPPER(TRIM(accountablePerson)) NOT IN ('N/A','NA','0','NOT FOUND') AND TRIM(accountablePerson) NOT REGEXP '^[0-9]'
    UNION ALL
    SELECT TRIM(actualUser), TRIM(office), TRIM(officeDivision), TRIM(actualUserStatusOfEmployment), 'actualUser'
    FROM inv_inventory
    WHERE actualUser IS NOT NULL AND TRIM(actualUser) <> '' AND UPPER(TRIM(actualUser)) NOT IN ('N/A','NA','0','NOT FOUND') AND TRIM(actualUser) NOT REGEXP '^[0-9]'
  ) people
  GROUP BY UPPER(TRIM(person_name))
) p
LEFT JOIN office_divisions o
  ON UPPER(p.office) = UPPER(o.office)
 AND UPPER(p.officeDivision) = UPPER(o.officeDivision)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  office_id = VALUES(office_id),
  office = VALUES(office),
  officeDivision = VALUES(officeDivision),
  employment_status = VALUES(employment_status),
  source = VALUES(source);

UPDATE inv_inventory i
LEFT JOIN inventory_people ep ON UPPER(TRIM(i.employeeName)) = ep.normalized_name
LEFT JOIN inventory_people ap ON UPPER(TRIM(i.accountablePerson)) = ap.normalized_name
LEFT JOIN inventory_people au ON UPPER(TRIM(i.actualUser)) = au.normalized_name
SET i.employee_person_id = ep.id,
    i.accountable_person_id = ap.id,
    i.actual_user_id = au.id;
