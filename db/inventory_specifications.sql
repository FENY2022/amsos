-- ICT-AMSOS structured inventory specifications
-- One structured specification row per inv_inventory record.

CREATE TABLE IF NOT EXISTS inventory_specifications (
    id INT NOT NULL AUTO_INCREMENT,
    inventory_id INT NOT NULL,
    hdd_capacity VARCHAR(100) NULL,
    ssd_capacity VARCHAR(100) NULL,
    ram_capacity VARCHAR(100) NULL,
    memory_capacity VARCHAR(100) NULL,
    processor_type VARCHAR(255) NULL,
    display_size VARCHAR(100) NULL,
    display_resolution VARCHAR(100) NULL,
    battery_capacity VARCHAR(100) NULL,
    os_type VARCHAR(150) NULL,
    os_status VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inventory_specifications_inventory_id (inventory_id),
    KEY idx_inventory_specifications_processor (processor_type),
    KEY idx_inventory_specifications_os (os_type),
    CONSTRAINT fk_inventory_specifications_inventory
        FOREIGN KEY (inventory_id) REFERENCES inv_inventory(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
