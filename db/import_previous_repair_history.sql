DELETE FROM `srf_repair_history`
WHERE `record_type` = 'Historical Repair';

INSERT INTO `srf_repair_history` (
  `record_type`,
  `source_id`,
  `srf_id`,
  `preventive_id`,
  `inventory_id`,
  `property_number`,
  `actual_user`,
  `equipment_type`,
  `brand`,
  `description`,
  `requestor_name`,
  `request_type`,
  `issue_description`,
  `status`,
  `action_staff`,
  `action_taken`,
  `date_recorded`,
  `time_recorded`
)
SELECT
  'Historical Repair' AS record_type,
  tracks.trackid AS source_id,
  NULL AS srf_id,
  NULL AS preventive_id,
  i.id AS inventory_id,
  i.propertyNumber AS property_number,
  i.actualUser AS actual_user,
  i.equipmentType AS equipment_type,
  i.brand AS brand,
  CONCAT(
    'Year Acquired: ', COALESCE(NULLIF(i.yearAcquired, ''), 'N/A'),
    ' | Shelf Life: ', COALESCE(NULLIF(i.shelfLife, ''), 'N/A'),
    ' | Inventory Remarks: ', COALESCE(NULLIF(i.remarks, ''), 'N/A')
  ) AS description,
  i.actualUser AS requestor_name,
  'Historical Repair' AS request_type,
  COALESCE(NULLIF(i.remarks, ''), 'No inventory remarks recorded') AS issue_description,
  COALESCE(NULLIF(sr.status, ''), 'Completed') AS status,
  COALESCE(NULLIF(r.actionstaff, ''), 'N/A') AS action_staff,
  COALESCE(NULLIF(r.action_taken, ''), 'No remarks recorded') AS action_taken,
  CASE
    WHEN r.date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
      THEN STR_TO_DATE(r.date, '%Y-%m-%d')
    WHEN r.date REGEXP '^[0-9]{1,2}/[0-9]{1,2}/[0-9]{4}$'
      THEN STR_TO_DATE(r.date, '%c/%e/%Y')
    ELSE NULL
  END AS date_recorded,
  COALESCE(NULLIF(r.time, ''), 'N/A') AS time_recorded
FROM `inv_inventory` i
INNER JOIN (
  SELECT DISTINCT equipment_id, trackid
  FROM `srfhistory`
  WHERE equipment_id <> 0
) tracks ON i.id = tracks.equipment_id
LEFT JOIN `srf` sr ON tracks.trackid = sr.id
LEFT JOIN `srfstaff_remarks` r ON tracks.trackid = r.track_id;
