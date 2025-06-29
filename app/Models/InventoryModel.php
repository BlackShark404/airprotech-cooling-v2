<?php

namespace App\Models;

class InventoryModel extends Model
{
    protected $table = 'INVENTORY';

    public function getAllInventory()
    {
        $sql = "SELECT 
                    i.*,
                    p.PROD_NAME,
                    p.PROD_IMAGE,
                    v.VAR_CAPACITY,
                    w.WHOUSE_NAME
                FROM {$this->table} i
                JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                JOIN PRODUCT p ON v.PROD_ID = p.PROD_ID AND p.PROD_DELETED_AT IS NULL
                JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID AND w.WHOUSE_DELETED_AT IS NULL
                WHERE i.INVE_DELETED_AT IS NULL
                ORDER BY i.INVE_UPDATED_AT DESC";
        
        return $this->query($sql);
    }

    public function getInventoryById($inventoryId)
    {
        error_log("[DEBUG] getInventoryById called with ID: " . $inventoryId);
        
        $sql = "SELECT 
                    i.*,
                    p.PROD_NAME,
                    p.PROD_IMAGE,
                    v.VAR_CAPACITY,
                    w.WHOUSE_NAME
                FROM {$this->table} i
                JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                JOIN PRODUCT p ON v.PROD_ID = p.PROD_ID
                JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID
                WHERE i.INVE_ID = :inventory_id AND i.INVE_DELETED_AT IS NULL";
        
        $result = $this->queryOne($sql, [':inventory_id' => $inventoryId]);
        
        if ($result) {
            error_log("[DEBUG] getInventoryById result: " . print_r($result, true));
            // Ensure the result has the QUANTITY field as uppercase (original DB format)
            if (!isset($result['QUANTITY']) && isset($result['quantity'])) {
                $result['QUANTITY'] = $result['quantity'];
            }
        } else {
            error_log("[DEBUG] getInventoryById returned no result for ID: " . $inventoryId);
        }
        
        return $result;
    }

    public function getProductInventory($productId)
    {
        // Ensure productId is treated as an integer to avoid case sensitivity issues
        $productId = intval($productId);
        
        $sql = "SELECT 
                    i.*,
                    v.VAR_ID,
                    v.VAR_CAPACITY,
                    w.WHOUSE_NAME,
                    w.WHOUSE_LOCATION
                FROM {$this->table} i
                JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID
                WHERE v.PROD_ID = :product_id AND i.INVE_DELETED_AT IS NULL
                ORDER BY w.WHOUSE_NAME, v.VAR_CAPACITY";
        
        return $this->query($sql, [':product_id' => $productId]);
    }

    public function getWarehouseInventory($warehouseId)
    {
        $sql = "SELECT 
                    i.*,
                    p.PROD_NAME,
                    p.PROD_IMAGE,
                    v.VAR_CAPACITY
                FROM {$this->table} i
                JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                JOIN PRODUCT p ON v.PROD_ID = p.PROD_ID
                WHERE i.WHOUSE_ID = :warehouse_id AND i.INVE_DELETED_AT IS NULL
                ORDER BY p.PROD_NAME, v.VAR_CAPACITY";
        
        return $this->query($sql, [':warehouse_id' => $warehouseId]);
    }

    public function getLowStockInventory()
    {
        $sql = "SELECT 
                    i.*,
                    p.PROD_ID,
                    p.PROD_NAME,
                    p.PROD_IMAGE,
                    v.VAR_ID as var_id,
                    v.VAR_CAPACITY,
                    w.WHOUSE_ID as whouse_id,
                    w.WHOUSE_NAME,
                    w.WHOUSE_RESTOCK_THRESHOLD
                FROM {$this->table} i
                JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                JOIN PRODUCT p ON v.PROD_ID = p.PROD_ID AND p.PROD_DELETED_AT IS NULL
                JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID AND w.WHOUSE_DELETED_AT IS NULL
                WHERE i.INVE_DELETED_AT IS NULL
                AND i.QUANTITY <= w.WHOUSE_RESTOCK_THRESHOLD
                AND w.WHOUSE_RESTOCK_THRESHOLD > 0
                ORDER BY i.QUANTITY ASC";
        
        return $this->query($sql);
    }

    public function createInventory($data)
    {
        $sql = "INSERT INTO {$this->table} (VAR_ID, WHOUSE_ID, INVE_TYPE, QUANTITY)
                VALUES (:variant_id, :warehouse_id, :inventory_type, :quantity)";
        
        $params = [
            ':variant_id' => $data['VAR_ID'],
            ':warehouse_id' => $data['WHOUSE_ID'],
            ':inventory_type' => $data['INVE_TYPE'],
            ':quantity' => $data['QUANTITY']
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('inventory_inve_id_seq');
    }

    public function updateInventoryQuantity($inventoryId, $newQuantity)
    {
        $sql = "UPDATE {$this->table} SET 
                QUANTITY = :quantity,
                INVE_UPDATED_AT = CURRENT_TIMESTAMP
                WHERE INVE_ID = :inventory_id AND INVE_DELETED_AT IS NULL";
        
        return $this->execute($sql, [
            ':quantity' => $newQuantity,
            ':inventory_id' => $inventoryId
        ]);
    }

    public function updateInventory($inventoryId, $data)
    {
        $setClauses = [];
        $params = [':inventory_id' => $inventoryId];

        if (isset($data['WHOUSE_ID'])) {
            $setClauses[] = "WHOUSE_ID = :warehouse_id";
            $params[':warehouse_id'] = $data['WHOUSE_ID'];
        }

        if (isset($data['INVE_TYPE'])) {
            $setClauses[] = "INVE_TYPE = :inventory_type";
            $params[':inventory_type'] = $data['INVE_TYPE'];
        }

        if (isset($data['QUANTITY'])) {
            $setClauses[] = "QUANTITY = :quantity";
            $params[':quantity'] = $data['QUANTITY'];
        }

        // Always update the timestamp
        $setClauses[] = "INVE_UPDATED_AT = CURRENT_TIMESTAMP";
        
        if (empty($setClauses)) {
            return false; // No fields to update
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE INVE_ID = :inventory_id AND INVE_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    public function deleteInventory($inventoryId)
    {
        $sql = "UPDATE {$this->table} SET INVE_DELETED_AT = CURRENT_TIMESTAMP 
                WHERE INVE_ID = :inventory_id";
        return $this->execute($sql, [':inventory_id' => $inventoryId]);
    }

    public function getInventorySummary()
    {
        error_log("[InventoryModel] getInventorySummary called.");
        // Get total active warehouses from WarehouseModel
        $warehouseModel = new WarehouseModel(); // Instantiate WarehouseModel
        $totalWarehouses = $warehouseModel->countActiveWarehouses();
        error_log("[InventoryModel] Total warehouses from WarehouseModel: " . $totalWarehouses);

        // Get total active products (count all products, not just those in inventory)
        $productModel = new ProductModel();
        $totalProducts = $productModel->countActiveProducts();
        error_log("[InventoryModel] Total products from ProductModel: " . $totalProducts);

        // Get a direct count of total inventory and low stock items
        $sql = "SELECT 
                    COALESCE(SUM(i.QUANTITY), 0) AS total_inventory,
                    COUNT(DISTINCT CASE WHEN i.QUANTITY <= w.WHOUSE_RESTOCK_THRESHOLD AND w.WHOUSE_RESTOCK_THRESHOLD > 0 THEN i.INVE_ID END) AS low_stock_items
                FROM {$this->table} i
                LEFT JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID AND w.WHOUSE_DELETED_AT IS NULL
                WHERE i.INVE_DELETED_AT IS NULL";
        
        error_log("[InventoryModel] SQL for inventory stats: " . $sql);
        $inventoryStats = $this->queryOne($sql);
        error_log("[InventoryModel] Raw inventory stats from queryOne: " . print_r($inventoryStats, true));

        // Debug the case of the returned column names
        $keysString = '';
        if ($inventoryStats) {
            $keysString = implode(', ', array_keys($inventoryStats));
        }
        error_log("[InventoryModel] Keys in inventory stats: " . $keysString);

        // Combine the results, ensuring all are integers and default to 0 if null/not set
        // Using lowercase keys to match what's returned from the database
        $totalInventory = 0;
        $lowStockItems = 0;
        
        if ($inventoryStats) {
            // Try both uppercase and lowercase keys to be safe
            if (isset($inventoryStats['total_inventory'])) {
                $totalInventory = (int)$inventoryStats['total_inventory'];
            } elseif (isset($inventoryStats['TOTAL_INVENTORY'])) {
                $totalInventory = (int)$inventoryStats['TOTAL_INVENTORY']; 
            }
            
            if (isset($inventoryStats['low_stock_items'])) {
                $lowStockItems = (int)$inventoryStats['low_stock_items'];
            } elseif (isset($inventoryStats['LOW_STOCK_ITEMS'])) {
                $lowStockItems = (int)$inventoryStats['LOW_STOCK_ITEMS'];
            }
        }
        
        $summaryData = [
            'TOTAL_PRODUCTS' => (int)$totalProducts,
            'TOTAL_WAREHOUSES' => (int)$totalWarehouses,
            'TOTAL_INVENTORY' => $totalInventory,
            'LOW_STOCK_ITEMS' => $lowStockItems
        ];
        
        error_log("[InventoryModel] Final summary data: " . print_r($summaryData, true));
        return $summaryData;
    }

    public function getInventoryByProductAndWarehouse($variantId, $warehouseId, $inventoryType = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE VAR_ID = :variant_id 
                AND WHOUSE_ID = :warehouse_id 
                AND INVE_DELETED_AT IS NULL";
        
        $params = [
            ':variant_id' => $variantId,
            ':warehouse_id' => $warehouseId
        ];
        
        // Add inventory type to the query if provided
        if ($inventoryType !== null) {
            $sql .= " AND INVE_TYPE = :inventory_type";
            $params[':inventory_type'] = $inventoryType;
        }
        
        return $this->queryOne($sql, $params);
    }

    public function addStock($variantId, $warehouseId, $quantity, $inventoryType = 'Regular')
    {
        try {
            // First check if there's an existing inventory record with the same inventory type
            $existingInventory = $this->getInventoryByProductAndWarehouse($variantId, $warehouseId, $inventoryType);
            
            if ($existingInventory) {
                // Ensure QUANTITY key exists (handle both uppercase and lowercase)
                $currentQuantity = 0;
                if (isset($existingInventory['QUANTITY'])) {
                    $currentQuantity = $existingInventory['QUANTITY'];
                } elseif (isset($existingInventory['quantity'])) {
                    $currentQuantity = $existingInventory['quantity'];
                }

                // Ensure INVE_ID key exists (handle both uppercase and lowercase)
                $inventoryId = null;
                if (isset($existingInventory['INVE_ID'])) {
                    $inventoryId = $existingInventory['INVE_ID'];
                } elseif (isset($existingInventory['inve_id'])) {
                    $inventoryId = $existingInventory['inve_id'];
                } else {
                    error_log("[ERROR] Inventory record exists but ID is missing: " . print_r($existingInventory, true));
                    return false;
                }
                
                // Update existing inventory
                $newQuantity = $currentQuantity + $quantity;
                return $this->updateInventoryQuantity($inventoryId, $newQuantity);
            } else {
                // Create new inventory record
                return $this->createInventory([
                    'VAR_ID' => $variantId,
                    'WHOUSE_ID' => $warehouseId,
                    'INVE_TYPE' => $inventoryType,
                    'QUANTITY' => $quantity
                ]);
            }
        } catch (\PDOException $e) {
            // Check if this is a warehouse capacity exception from our trigger
            $errorMessage = $e->getMessage();
            if (stripos($errorMessage, 'exceed the warehouse capacity') !== false) {
                // Extract the custom message from our trigger
                $matches = [];
                if (preg_match('/ERROR:(.+)$/', $errorMessage, $matches)) {
                    error_log("[INFO] Warehouse capacity exceeded: " . trim($matches[1]));
                    throw new \Exception(trim($matches[1]));
                } else {
                    error_log("[INFO] Warehouse capacity exceeded but couldn't extract message: " . $errorMessage);
                    throw new \Exception("Cannot add inventory - warehouse capacity would be exceeded.");
                }
            }
            
            // For any other database errors, log and rethrow
            error_log("[ERROR] Database error in addStock: " . $errorMessage);
            throw $e;
        }
    }

    public function moveStock($sourceInventoryId, $targetWarehouseId, $quantity)
    {
        // Ensure parameters are integers
        $sourceInventoryId = intval($sourceInventoryId);
        $targetWarehouseId = intval($targetWarehouseId);
        $quantity = intval($quantity);
        
        error_log("[DEBUG] moveStock in Model - Parameters: sourceId={$sourceInventoryId}, targetId={$targetWarehouseId}, quantity={$quantity}");
        
        // Start a transaction - ensure we're not already in one
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit(); // Commit any existing transaction to start fresh
            error_log("[DEBUG] moveStock in Model - Committed existing transaction before starting new one");
        }
        
        $this->beginTransaction();
        error_log("[DEBUG] moveStock in Model - Transaction started");
        
        try {
            // Get source inventory record with fresh query to avoid stale data
            $sql = "SELECT * FROM {$this->table} 
                    WHERE INVE_ID = :inventory_id 
                    AND INVE_DELETED_AT IS NULL 
                    FOR UPDATE"; // Add FOR UPDATE to lock the row during transaction
            
            $sourceInventory = $this->queryOne($sql, [':inventory_id' => $sourceInventoryId]);
            error_log("[DEBUG] moveStock in Model - Source inventory (fresh query): " . print_r($sourceInventory, true));
            
            if (!$sourceInventory) {
                error_log("[DEBUG] moveStock in Model - Source inventory not found");
                $this->rollback();
                return false;
            }
            
            // Get variant ID with case insensitivity check before checking quantity
            $varId = null;
            if (isset($sourceInventory['VAR_ID'])) {
                $varId = $sourceInventory['VAR_ID'];
            } else if (isset($sourceInventory['var_id'])) {
                $varId = $sourceInventory['var_id'];
            }
            
            if (!$varId) {
                error_log("[DEBUG] moveStock in Model - Variant ID not found in source inventory");
                $this->rollback();
                return false;
            }
            
            // Check if we have source inventory and enough quantity
            // First check if QUANTITY exists (uppercase) - database convention
            if (isset($sourceInventory['QUANTITY'])) {
                $sourceQuantity = intval($sourceInventory['QUANTITY']);
            } 
            // Then check if quantity exists (lowercase) - might be normalized in the model
            else if (isset($sourceInventory['quantity'])) {
                $sourceQuantity = intval($sourceInventory['quantity']);
            } 
            // If neither exists, set to 0
            else {
                $sourceQuantity = 0;
            }
            
            error_log("[DEBUG] moveStock in Model - Source quantity: " . $sourceQuantity . ", Quantity to move: " . $quantity);
            
            if ($sourceQuantity < $quantity) {
                error_log("[DEBUG] moveStock in Model - Not enough stock. Available: " . $sourceQuantity . ", Requested: " . $quantity);
                $this->rollback();
                return false; // Not enough stock to move
            }
            
            // Get inventory type with case insensitivity check
            $inventoryType = 'Regular'; // Default
            if (isset($sourceInventory['INVE_TYPE'])) {
                $inventoryType = $sourceInventory['INVE_TYPE'];
            } else if (isset($sourceInventory['inve_type'])) {
                $inventoryType = $sourceInventory['inve_type'];
            }
            
            // First, check if target warehouse inventory already exists with the same inventory type
            $targetSql = "SELECT * FROM {$this->table} 
                         WHERE VAR_ID = :variant_id 
                         AND WHOUSE_ID = :warehouse_id 
                         AND INVE_TYPE = :inventory_type
                         AND INVE_DELETED_AT IS NULL
                         FOR UPDATE";
            
            $targetInventory = $this->queryOne($targetSql, [
                ':variant_id' => $varId,
                ':warehouse_id' => $targetWarehouseId,
                ':inventory_type' => $inventoryType
            ]);
            
            error_log("[DEBUG] moveStock in Model - Target inventory: " . print_r($targetInventory, true));
            
            // Begin with update to target warehouse (to ensure we don't lose stock if source update succeeds but target fails)
            if ($targetInventory) {
                // Get target inventory quantity with case sensitivity check
                $targetQuantity = 0;
                if (isset($targetInventory['QUANTITY'])) {
                    $targetQuantity = intval($targetInventory['QUANTITY']);
                } else if (isset($targetInventory['quantity'])) {
                    $targetQuantity = intval($targetInventory['quantity']);
                }
                
                // Get target inventory ID with case sensitivity check
                $targetInventoryId = null;
                if (isset($targetInventory['INVE_ID'])) {
                    $targetInventoryId = $targetInventory['INVE_ID'];
                } else if (isset($targetInventory['inve_id'])) {
                    $targetInventoryId = $targetInventory['inve_id'];
                } else {
                    error_log("[ERROR] moveStock in Model - Target inventory exists but INVE_ID not found: " . print_r($targetInventory, true));
                    $this->rollback();
                    return false;
                }
                
                $newTargetQuantity = $targetQuantity + $quantity;
                error_log("[DEBUG] moveStock in Model - Updating target inventory. Old quantity: " . $targetQuantity . ", New quantity: " . $newTargetQuantity);
                
                $updateTargetSql = "UPDATE {$this->table} SET 
                                  QUANTITY = :quantity,
                                  INVE_UPDATED_AT = CURRENT_TIMESTAMP
                                  WHERE INVE_ID = :inventory_id 
                                  AND INVE_DELETED_AT IS NULL";
                
                $targetUpdateResult = $this->execute($updateTargetSql, [
                    ':quantity' => $newTargetQuantity,
                    ':inventory_id' => $targetInventoryId
                ]);
                
                error_log("[DEBUG] moveStock in Model - Target update result: " . ($targetUpdateResult ? 'Success' : 'Failed'));
                
                if (!$targetUpdateResult) {
                    error_log("[ERROR] moveStock in Model - Failed to update target inventory");
                    $this->rollback();
                    return false;
                }
            } else {
                // Create new inventory at target
                error_log("[DEBUG] moveStock in Model - Creating new inventory at target warehouse with quantity: " . $quantity);
                
                $insertSql = "INSERT INTO {$this->table} (VAR_ID, WHOUSE_ID, INVE_TYPE, QUANTITY, INVE_CREATED_AT, INVE_UPDATED_AT)
                            VALUES (:variant_id, :warehouse_id, :inventory_type, :quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                
                $insertResult = $this->execute($insertSql, [
                    ':variant_id' => $varId,
                    ':warehouse_id' => $targetWarehouseId,
                    ':inventory_type' => $inventoryType,
                    ':quantity' => $quantity
                ]);
                
                error_log("[DEBUG] moveStock in Model - Insert result: " . ($insertResult ? 'Success' : 'Failed'));
                
                if (!$insertResult) {
                    error_log("[ERROR] moveStock in Model - Failed to create target inventory");
                    $this->rollback();
                    return false;
                }
            }
            
            // Now update source inventory quantity after target is updated successfully
            $newSourceQuantity = $sourceQuantity - $quantity;
            error_log("[DEBUG] moveStock in Model - Updating source inventory. Old quantity: " . $sourceQuantity . ", New quantity: " . $newSourceQuantity);
            
            $updateSourceSql = "UPDATE {$this->table} SET 
                               QUANTITY = :quantity,
                               INVE_UPDATED_AT = CURRENT_TIMESTAMP
                               WHERE INVE_ID = :inventory_id 
                               AND INVE_DELETED_AT IS NULL";
            
            $sourceUpdateResult = $this->execute($updateSourceSql, [
                ':quantity' => $newSourceQuantity,
                ':inventory_id' => $sourceInventoryId
            ]);
            
            error_log("[DEBUG] moveStock in Model - Source update result: " . ($sourceUpdateResult ? 'Success' : 'Failed'));
            
            if (!$sourceUpdateResult) {
                error_log("[ERROR] moveStock in Model - Failed to update source inventory");
                $this->rollback();
                return false;
            }
            
            // Verify both updates worked
            $verifySourceSql = "SELECT QUANTITY FROM {$this->table} WHERE INVE_ID = :inventory_id AND INVE_DELETED_AT IS NULL";
            $sourceVerifyResult = $this->queryOne($verifySourceSql, [':inventory_id' => $sourceInventoryId]);
            
            $verifyTargetSql = "SELECT SUM(QUANTITY) as total_quantity FROM {$this->table} WHERE VAR_ID = :variant_id AND INVE_DELETED_AT IS NULL";
            $targetVerifyResult = $this->queryOne($verifyTargetSql, [':variant_id' => $varId]);
            
            error_log("[DEBUG] moveStock in Model - Source verification: " . print_r($sourceVerifyResult, true));
            error_log("[DEBUG] moveStock in Model - Total quantity verification: " . print_r($targetVerifyResult, true));
            
            // Commit the transaction
            $commitResult = $this->commit();
            error_log("[DEBUG] moveStock in Model - Transaction committed: " . ($commitResult ? 'Success' : 'Failed'));
            
            return true;
        } catch (\PDOException $e) {
            // Check if this is a warehouse capacity exception from our trigger
            $errorMessage = $e->getMessage();
            if (stripos($errorMessage, 'exceed the warehouse capacity') !== false) {
                $this->rollback();
                error_log("[INFO] moveStock in Model - Warehouse capacity exceeded: " . $errorMessage);
                
                // Extract the custom message from our trigger
                $matches = [];
                if (preg_match('/ERROR:(.+)$/', $errorMessage, $matches)) {
                    throw new \Exception(trim($matches[1]));
                } else {
                    throw new \Exception("Cannot move stock - warehouse capacity would be exceeded.");
                }
            }
            
            // For other database errors, rollback and rethrow
            $this->rollback();
            error_log("[ERROR] moveStock in Model - Database error, transaction rolled back: " . $errorMessage);
            throw $e;
        } catch (Exception $e) {
            $this->rollback();
            error_log("[ERROR] Error moving stock: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    // Get inventory data for multiple variants at once
    public function getInventoryByVariantIds($variantIds)
    {
        if (empty($variantIds)) {
            return [];
        }
        
        // Normalize variant IDs to integers to avoid case sensitivity issues
        $normalizedIds = array_map('intval', $variantIds);
        
        // Create placeholders for the IN clause (:var0, :var1, etc.)
        $placeholders = [];
        $params = [];
        foreach ($normalizedIds as $index => $id) {
            $placeholders[] = ":var{$index}";
            $params[":var{$index}"] = $id;
        }
        
        $placeholderString = implode(',', $placeholders);
        
        $sql = "SELECT 
                    i.*,
                    v.VAR_CAPACITY,
                    w.WHOUSE_NAME
                FROM {$this->table} i
                JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID
                WHERE i.VAR_ID IN ({$placeholderString})
                AND i.INVE_TYPE = 'Regular'
                AND i.INVE_DELETED_AT IS NULL
                AND i.QUANTITY > 0"; // Only include items with quantity > 0
        
        return $this->query($sql, $params);
    }

    // Update inventory records to use new variant IDs after product update
    public function updateVariantReferences($oldVariantId, $newVariantId)
    {
        $sql = "UPDATE {$this->table} SET 
                VAR_ID = :new_variant_id,
                INVE_UPDATED_AT = CURRENT_TIMESTAMP
                WHERE VAR_ID = :old_variant_id AND INVE_DELETED_AT IS NULL";
        
        return $this->execute($sql, [
            ':new_variant_id' => $newVariantId,
            ':old_variant_id' => $oldVariantId
        ]);
    }
} 