<?php

namespace App\Models;

class WarehouseModel extends Model
{
    protected $table = 'WAREHOUSE';

    public function getAllWarehouses()
    {
        $sql = "SELECT * FROM {$this->table} WHERE WHOUSE_DELETED_AT IS NULL ORDER BY WHOUSE_NAME";
        return $this->query($sql);
    }

    public function getWarehouseById($warehouseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE WHOUSE_ID = :warehouse_id AND WHOUSE_DELETED_AT IS NULL";
        return $this->queryOne($sql, [':warehouse_id' => $warehouseId]);
    }

    public function createWarehouse($data)
    {
        $sql = "INSERT INTO {$this->table} (WHOUSE_NAME, WHOUSE_LOCATION, WHOUSE_STORAGE_CAPACITY, WHOUSE_RESTOCK_THRESHOLD)
                VALUES (:name, :location, :storage_capacity, :restock_threshold)";
        
        $params = [
            ':name' => $data['WHOUSE_NAME'],
            ':location' => $data['WHOUSE_LOCATION'],
            ':storage_capacity' => $data['WHOUSE_STORAGE_CAPACITY'] ?? null,
            ':restock_threshold' => $data['WHOUSE_RESTOCK_THRESHOLD'] ?? null
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('warehouse_whouse_id_seq');
    }

    public function updateWarehouse($warehouseId, $data)
    {
        $setClauses = [];
        $params = [':warehouse_id' => $warehouseId];

        if (isset($data['WHOUSE_NAME'])) {
            $setClauses[] = "WHOUSE_NAME = :name";
            $params[':name'] = $data['WHOUSE_NAME'];
        }
        
        if (isset($data['WHOUSE_LOCATION'])) {
            $setClauses[] = "WHOUSE_LOCATION = :location";
            $params[':location'] = $data['WHOUSE_LOCATION'];
        }
        
        if (array_key_exists('WHOUSE_STORAGE_CAPACITY', $data)) {
            $setClauses[] = "WHOUSE_STORAGE_CAPACITY = :storage_capacity";
            $params[':storage_capacity'] = $data['WHOUSE_STORAGE_CAPACITY'];
        }
        
        if (array_key_exists('WHOUSE_RESTOCK_THRESHOLD', $data)) {
            $setClauses[] = "WHOUSE_RESTOCK_THRESHOLD = :restock_threshold";
            $params[':restock_threshold'] = $data['WHOUSE_RESTOCK_THRESHOLD'];
        }

        if (empty($setClauses)) {
            return false; // No fields to update
        }

        $setClauses[] = "WHOUSE_UPDATED_AT = CURRENT_TIMESTAMP";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE WHOUSE_ID = :warehouse_id AND WHOUSE_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    public function deleteWarehouse($warehouseId)
    {
        $sql = "UPDATE {$this->table} SET WHOUSE_DELETED_AT = CURRENT_TIMESTAMP 
                WHERE WHOUSE_ID = :warehouse_id";
        return $this->execute($sql, [':warehouse_id' => $warehouseId]);
    }

    public function getWarehouseUtilization($warehouseId)
    {
        // This query calculates warehouse utilization based on inventory quantities
        $sql = "SELECT 
                    w.WHOUSE_ID,
                    w.WHOUSE_NAME,
                    w.WHOUSE_STORAGE_CAPACITY,
                    COALESCE(SUM(i.QUANTITY), 0) AS TOTAL_INVENTORY,
                    CASE 
                        WHEN w.WHOUSE_STORAGE_CAPACITY > 0 THEN 
                            ROUND((COALESCE(SUM(i.QUANTITY), 0) * 100.0 / w.WHOUSE_STORAGE_CAPACITY), 2)
                        ELSE 0
                    END AS UTILIZATION_PERCENTAGE
                FROM {$this->table} w
                LEFT JOIN INVENTORY i ON w.WHOUSE_ID = i.WHOUSE_ID AND i.INVE_DELETED_AT IS NULL
                WHERE w.WHOUSE_ID = :warehouse_id AND w.WHOUSE_DELETED_AT IS NULL
                GROUP BY w.WHOUSE_ID, w.WHOUSE_NAME, w.WHOUSE_STORAGE_CAPACITY";
        
        return $this->queryOne($sql, [':warehouse_id' => $warehouseId]);
    }

    public function getWarehousesWithInventory()
    {
        $sql = "SELECT 
                    w.*,
                    COALESCE(SUM(i.QUANTITY), 0) AS TOTAL_INVENTORY,
                    COUNT(DISTINCT i.VAR_ID) AS UNIQUE_PRODUCTS
                FROM {$this->table} w
                LEFT JOIN INVENTORY i ON w.WHOUSE_ID = i.WHOUSE_ID AND i.INVE_DELETED_AT IS NULL
                WHERE w.WHOUSE_DELETED_AT IS NULL
                GROUP BY w.WHOUSE_ID
                ORDER BY w.WHOUSE_NAME";
                
        return $this->query($sql);
    }

    public function getWarehousesWithAvailableSpace()
    {
        $sql = "SELECT 
                    w.*,
                    w.WHOUSE_STORAGE_CAPACITY - COALESCE(SUM(i.QUANTITY), 0) AS AVAILABLE_CAPACITY
                FROM {$this->table} w
                LEFT JOIN INVENTORY i ON w.WHOUSE_ID = i.WHOUSE_ID AND i.INVE_DELETED_AT IS NULL
                WHERE w.WHOUSE_DELETED_AT IS NULL AND w.WHOUSE_STORAGE_CAPACITY > 0
                GROUP BY w.WHOUSE_ID
                HAVING w.WHOUSE_STORAGE_CAPACITY - COALESCE(SUM(i.QUANTITY), 0) > 0
                ORDER BY AVAILABLE_CAPACITY DESC";
                
        return $this->query($sql);
    }

    public function countActiveWarehouses()
    {
        $sql = "SELECT COUNT(WHOUSE_ID) AS total_warehouses FROM {$this->table} WHERE WHOUSE_DELETED_AT IS NULL";
        error_log("[WarehouseModel] SQL for count: " . $sql);
        $result = $this->queryOne($sql);
        error_log("[WarehouseModel] Raw result from queryOne for count: " . print_r($result, true));
        $count = ($result && isset($result['total_warehouses'])) ? (int)$result['total_warehouses'] : 0;
        error_log("[WarehouseModel] Calculated count: " . $count);
        return $count;
    }
} 