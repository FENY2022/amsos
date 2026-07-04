SET NAMES utf8mb4;

START TRANSACTION;

UPDATE inv_inventory
SET employeeName = CASE
        WHEN employeeName IN ('Mae Charmeline G. Salama?a', 'Mae Charmeline G. Salama�a', 'Mae Charmeline G.Mae Charmeline G. Salama�a Salama?a') THEN 'Mae Charmeline G. Salamaña'
        WHEN employeeName IN ('Edgilyn T. Ca?ete', 'Edgilyn T. Ca�ete') THEN 'Edgilyn T. Cañete'
        ELSE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(employeeName,
            'Aileen Bi?as', 'Aileen Biñas'),
            'Jca V. Qui?ones', 'Jca V. Quiñones'),
            'Melchie Campa?a', 'Melchie Campaña'),
            'Prisca O. Rosace?a', 'Prisca O. Rosaceña'),
            'Roselle D. Dela Pe?a', 'Roselle D. Dela Peña'),
            'Charmeline Mae G. Salama�a', 'Charmeline Mae G. Salamaña')
    END,
    accountablePerson = CASE
        WHEN accountablePerson IN ('Mae Charmeline G. Salama?a', 'Mae Charmeline G. Salama�a', 'Mae Charmeline G.Mae Charmeline G. Salama�a Salama?a') THEN 'Mae Charmeline G. Salamaña'
        WHEN accountablePerson IN ('Edgilyn T. Ca?ete', 'Edgilyn T. Ca�ete') THEN 'Edgilyn T. Cañete'
        ELSE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(accountablePerson,
            'Aileen Bi?as', 'Aileen Biñas'),
            'Jca V. Qui?ones', 'Jca V. Quiñones'),
            'Melchie Campa?a', 'Melchie Campaña'),
            'Prisca O. Rosace?a', 'Prisca O. Rosaceña'),
            'Roselle D. Dela Pe?a', 'Roselle D. Dela Peña'),
            'Charmeline Mae G. Salama�a', 'Charmeline Mae G. Salamaña')
    END,
    actualUser = CASE
        WHEN actualUser IN ('Mae Charmeline G. Salama?a', 'Mae Charmeline G. Salama�a', 'Mae Charmeline G.Mae Charmeline G. Salama�a Salama?a') THEN 'Mae Charmeline G. Salamaña'
        WHEN actualUser IN ('Edgilyn T. Ca?ete', 'Edgilyn T. Ca�ete') THEN 'Edgilyn T. Cañete'
        ELSE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(actualUser,
            'Aileen Bi?as', 'Aileen Biñas'),
            'Jca V. Qui?ones', 'Jca V. Quiñones'),
            'Melchie Campa?a', 'Melchie Campaña'),
            'Prisca O. Rosace?a', 'Prisca O. Rosaceña'),
            'Roselle D. Dela Pe?a', 'Roselle D. Dela Peña'),
            'Charmeline Mae G. Salama�a', 'Charmeline Mae G. Salamaña')
    END
WHERE employeeName LIKE '%?%'
   OR employeeName LIKE '%�%'
   OR accountablePerson LIKE '%?%'
   OR accountablePerson LIKE '%�%'
   OR actualUser LIKE '%?%'
   OR actualUser LIKE '%�%';

UPDATE inv_inventory SET employee_person_id = 46 WHERE employee_person_id IN (47, 48);
UPDATE inv_inventory SET accountable_person_id = 46 WHERE accountable_person_id IN (47, 48);
UPDATE inv_inventory SET actual_user_id = 46 WHERE actual_user_id IN (47, 48);

UPDATE inv_inventory SET employee_person_id = 277 WHERE employee_person_id = 278;
UPDATE inv_inventory SET accountable_person_id = 277 WHERE accountable_person_id = 278;
UPDATE inv_inventory SET actual_user_id = 277 WHERE actual_user_id = 278;

DELETE FROM inventory_people WHERE id IN (47, 48, 278);

UPDATE inventory_people
SET full_name = 'Mae Charmeline G. Salamaña', normalized_name = 'MAE CHARMELINE G. SALAMAÑA'
WHERE id = 46;

UPDATE inventory_people
SET full_name = 'Edgilyn T. Cañete', normalized_name = 'EDGILYN T. CAÑETE'
WHERE id = 277;

UPDATE inventory_people
SET full_name = 'Charmeline Mae G. Salamaña', normalized_name = 'CHARMELINE MAE G. SALAMAÑA'
WHERE id = 8;

UPDATE inventory_people
SET full_name = 'Aileen Biñas', normalized_name = 'AILEEN BIÑAS'
WHERE id = 371;

UPDATE inventory_people
SET full_name = 'Jca V. Quiñones', normalized_name = 'JCA V. QUIÑONES'
WHERE id = 286;

UPDATE inventory_people
SET full_name = 'Melchie Campaña', normalized_name = 'MELCHIE CAMPAÑA'
WHERE id = 151;

UPDATE inventory_people
SET full_name = 'Prisca O. Rosaceña', normalized_name = 'PRISCA O. ROSACEÑA'
WHERE id = 244;

UPDATE inventory_people
SET full_name = 'Roselle D. Dela Peña', normalized_name = 'ROSELLE D. DELA PEÑA'
WHERE id = 167;

UPDATE inv_inventory
SET employeeName = CONCAT('Mae Charmeline G. Salama', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    accountablePerson = CONCAT('Mae Charmeline G. Salama', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    actualUser = CONCAT('Mae Charmeline G. Salama', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    employee_person_id = 46,
    accountable_person_id = 46,
    actual_user_id = 46
WHERE id IN (803, 915);

UPDATE inventory_people
SET full_name = CONCAT('Mae Charmeline G. Salama', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    normalized_name = CONCAT('MAE CHARMELINE G. SALAMA', CONVERT(0xC391 USING utf8mb4), 'A')
WHERE id = 46;

UPDATE inventory_people
SET full_name = CONCAT('Edgilyn T. Ca', CONVERT(0xC3B1 USING utf8mb4), 'ete'),
    normalized_name = CONCAT('EDGILYN T. CA', CONVERT(0xC391 USING utf8mb4), 'ETE')
WHERE id = 277;

UPDATE inventory_people
SET full_name = CONCAT('Charmeline Mae G. Salama', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    normalized_name = CONCAT('CHARMELINE MAE G. SALAMA', CONVERT(0xC391 USING utf8mb4), 'A')
WHERE id = 8;

UPDATE inventory_people
SET full_name = CONCAT('Aileen Bi', CONVERT(0xC3B1 USING utf8mb4), 'as'),
    normalized_name = CONCAT('AILEEN BI', CONVERT(0xC391 USING utf8mb4), 'AS')
WHERE id = 371;

UPDATE inventory_people
SET full_name = CONCAT('Jca V. Qui', CONVERT(0xC3B1 USING utf8mb4), 'ones'),
    normalized_name = CONCAT('JCA V. QUI', CONVERT(0xC391 USING utf8mb4), 'ONES')
WHERE id = 286;

UPDATE inventory_people
SET full_name = CONCAT('Melchie Campa', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    normalized_name = CONCAT('MELCHIE CAMPA', CONVERT(0xC391 USING utf8mb4), 'A')
WHERE id = 151;

UPDATE inventory_people
SET full_name = CONCAT('Prisca O. Rosace', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    normalized_name = CONCAT('PRISCA O. ROSACE', CONVERT(0xC391 USING utf8mb4), 'A')
WHERE id = 244;

UPDATE inventory_people
SET full_name = CONCAT('Roselle D. Dela Pe', CONVERT(0xC3B1 USING utf8mb4), 'a'),
    normalized_name = CONCAT('ROSELLE D. DELA PE', CONVERT(0xC391 USING utf8mb4), 'A')
WHERE id = 167;

COMMIT;
