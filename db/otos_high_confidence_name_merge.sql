SET NAMES utf8mb4;

START TRANSACTION;

CREATE TEMPORARY TABLE tmp_inventory_name_merge (
  old_name VARCHAR(255) NOT NULL,
  canonical_name VARCHAR(255) NOT NULL,
  PRIMARY KEY (old_name)
) ENGINE=Memory DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tmp_inventory_name_merge (old_name, canonical_name) VALUES
('Atty. Claudio A. Nistal', 'ATTY. CLAUDIO A. NISTAL JR.'),
('Atty. Claudio A. Nistal Jr.', 'ATTY. CLAUDIO A. NISTAL JR.'),
('Claudio A. Nistal, Jr.', 'ATTY. CLAUDIO A. NISTAL JR.'),
('Julian Ann M. Batohinog', 'Julie Ann Batohinog'),
('Rose L. Eben', 'Rosemarie L. Eben'),
('Rosemarie Eben', 'Rosemarie L. Eben'),
('Cleofe J. Aranas', 'Cleofie J. Aranas'),
('Francisco M, Mansalay Jr.', 'Francis M. Mansalay, Jr.'),
('Francisco M. Mansalay', 'Francis M. Mansalay, Jr.'),
('Luiz P. Gonzaga', 'Luis P. Gonzaga'),
('GENEVIVE CLEOFIE C. TACIO', 'Genevive Cleofe A. Tacio'),
('Herms L. Pelayo', 'Hermz Lorenz Pelayo'),
('Hermz L. Pelayo', 'Hermz Lorenz Pelayo'),
('Jennifer  E. Sumagaysay', 'Jennifer E. Sumagaysay'),
('Jennifer  E. Sumagaysay', 'Jennifer E. Sumagaysay'),
('Liez''l D. Prescillas', 'Liezl D. Prescillas'),
('Liez"l D. Prescillas', 'Liezl D. Prescillas'),
('Marissa L Baguhin', 'Marissa L. Baguhin'),
('Cherry Mae Pecjo', 'Cherry Mae G. Pecjo'),
('Fretzie Joy C. Espegadera', 'Fretzie Joey C. Espegadera'),
('Fretzie Joy Espegadera', 'Fretzie Joey C. Espegadera'),
('Gande Bagot', 'Gande G. Bagot'),
('Gjielda Belarmino', 'Gjielda Beb M. Belarmino'),
('Glenda Padullon', 'Glenda R. Padullon'),
('Irene Valdez', 'Irene O. Valdez'),
('Ivy Calooy', 'Ivy P. Calooy'),
('Jimboy Edig', 'Jimboy G. Edig'),
('Joan Ruales', 'Joan A. Ruales'),
('Joyla Malayan', 'Joyla M. Malayan'),
('Mae Rachel Zabate', 'Mae Rachel A. Zabate'),
('Marquitos Abundo', 'Marquitos F. Abundo'),
('Mary Grace Cepeda', 'Mary Grace B. Cepeda'),
('Rochelle Sajulga', 'Rochelle P. Sajulga'),
('Ruby M. Abesamis', 'Ruby Paz M. Abesamis'),
('Jeanelene D. Delos Reyes', 'Jeanelene D. De Los Reyes'),
('Jerramae Magdalera', 'Jerramae L. Magdalera'),
('Almar R. Rivera', 'Alma R. Rivera'),
('Analuo R. Rubi', 'Analou R. Rubi'),
('Analuo R.Rubi', 'Analou R. Rubi'),
('Carl Van Lucero', 'Carl Van E. Lucero'),
('Charlito Lopez', 'Charlito T. Lopez'),
('Elouiszabet S. Gupong', 'Elouiszabeth S. Gupong'),
('Elouiszabeth Gupong', 'Elouiszabeth S. Gupong'),
('Gerome Salvador', 'Gerome G. Salvador'),
('Hanzel Aleria', 'Hanzel R. Aleria'),
('Loenito C. Ramos', 'Leonito C. Ramos, Jr.'),
('Rayval Lanzaderas', 'Rayval A. Lanzaderas'),
('Celiri Sapid', 'Celiri J. Sapid'),
('Kristine Janne Suazo', 'Kristine Janne R. Suazo'),
('June Kathleen Cabrera', 'June Kathleen L. Cabrera'),
('Mary Hope Naranjo', 'Mary Hope M. Naranjo'),
('Alberto Cahilog Jr.', 'Alberto C. Cahilog, Jr'),
('Beverly Mae Aparre', 'Beverly Mae C. Aparre'),
('Edmark Damulo', 'Edmark A. Damulo'),
('Mark Anthony Lerom', 'Mark Anthony G. Lerom'),
('Jayson Paloquia', 'Jayson D. Paloquia'),
('Alona Bacayana', 'Alona M. Bacayana'),
('Elizabeth Boque', 'Elizabeth A. Boque'),
('ELIZABETH S. BOQUE', 'Elizabeth A. Boque'),
('Olive Ruth Esteban', 'Olive Ruth B. Esteban'),
('Maricar Siega', 'Maricar E. Siega'),
('Nancy Grana', 'Nancy S. Grana'),
('WENNELYN MUSICO', 'Wennielyn P. Musico'),
('WENNIELYN MUSICO', 'Wennielyn P. Musico');

INSERT INTO inventory_people (full_name, normalized_name, office_id, office, officeDivision, employment_status, source)
SELECT
  m.canonical_name,
  UPPER(TRIM(m.canonical_name)),
  MIN(p.office_id),
  MIN(p.office),
  MIN(p.officeDivision),
  MIN(p.employment_status),
  'otos_merge'
FROM tmp_inventory_name_merge m
JOIN inventory_people p ON p.full_name = m.old_name OR p.full_name = m.canonical_name
GROUP BY m.canonical_name
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  office_id = COALESCE(inventory_people.office_id, VALUES(office_id)),
  office = IF(inventory_people.office = '', VALUES(office), inventory_people.office),
  officeDivision = IF(inventory_people.officeDivision = '', VALUES(officeDivision), inventory_people.officeDivision),
  employment_status = IF(inventory_people.employment_status = '', VALUES(employment_status), inventory_people.employment_status);

UPDATE inv_inventory i
JOIN tmp_inventory_name_merge m ON CONVERT(i.employeeName USING utf8mb4) COLLATE utf8mb4_general_ci = m.old_name
SET i.employeeName = m.canonical_name;

UPDATE inv_inventory i
JOIN tmp_inventory_name_merge m ON CONVERT(i.accountablePerson USING utf8mb4) COLLATE utf8mb4_general_ci = m.old_name
SET i.accountablePerson = m.canonical_name;

UPDATE inv_inventory i
JOIN tmp_inventory_name_merge m ON CONVERT(i.actualUser USING utf8mb4) COLLATE utf8mb4_general_ci = m.old_name
SET i.actualUser = m.canonical_name;

UPDATE inv_inventory i
LEFT JOIN inventory_people ep ON UPPER(TRIM(CONVERT(i.employeeName USING utf8mb4))) COLLATE utf8mb4_general_ci = ep.normalized_name
LEFT JOIN inventory_people ap ON UPPER(TRIM(CONVERT(i.accountablePerson USING utf8mb4))) COLLATE utf8mb4_general_ci = ap.normalized_name
LEFT JOIN inventory_people au ON UPPER(TRIM(CONVERT(i.actualUser USING utf8mb4))) COLLATE utf8mb4_general_ci = au.normalized_name
SET i.employee_person_id = ep.id,
    i.accountable_person_id = ap.id,
    i.actual_user_id = au.id;

DELETE p
FROM inventory_people p
JOIN tmp_inventory_name_merge m ON p.full_name = m.old_name
WHERE UPPER(TRIM(p.full_name)) <> UPPER(TRIM(m.canonical_name));

DROP TEMPORARY TABLE tmp_inventory_name_merge;

COMMIT;
