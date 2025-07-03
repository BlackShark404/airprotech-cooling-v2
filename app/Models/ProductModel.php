<?php

namespace App\Models;

class ProductModel extends Model
{
    protected $table = 'PRODUCT';

    public function getAllProducts()
    {
        $sql = "SELECT 
                    p.*,
                    (SELECT COALESCE(JSON_AGG(
                        json_build_object(
                            'var_id', v.VAR_ID,
                            'var_capacity', v.VAR_CAPACITY,
                            'var_srp_price', v.VAR_SRP_PRICE,
                            'var_price_free_install', v.VAR_PRICE_FREE_INSTALL,
                            'var_price_with_install1', v.VAR_PRICE_WITH_INSTALL1,
                            'var_price_with_install2', v.VAR_PRICE_WITH_INSTALL2,
                            'var_installation_fee', v.VAR_INSTALLATION_FEE,
                            'var_power_consumption', v.VAR_POWER_CONSUMPTION,
                            'inventory_quantity', COALESCE(
                                (SELECT SUM(i.QUANTITY) 
                                 FROM INVENTORY i 
                                 WHERE i.VAR_ID = v.VAR_ID 
                                 AND i.INVE_DELETED_AT IS NULL
                                 GROUP BY i.VAR_ID),
                                0
                            )
                        )
                    ), '[]'::json)
                    FROM PRODUCT_VARIANT v 
                    WHERE v.PROD_ID = p.PROD_ID 
                    AND v.VAR_DELETED_AT IS NULL) AS variants
                FROM {$this->table} p
                WHERE p.PROD_DELETED_AT IS NULL 
                ORDER BY p.PROD_CREATED_AT DESC";
        
        $products = $this->query($sql);
        
        // Process the products to transform the JSON data
        foreach ($products as &$product) {
            if (isset($product['variants'])) {
                $product['variants'] = json_decode($product['variants'], true) ?: [];
                
                // Calculate HAS_INVENTORY and inventory_count
                $product['HAS_INVENTORY'] = false;
                $product['inventory_count'] = 0;
                
                foreach ($product['variants'] as $variant) {
                    $inventoryQuantity = isset($variant['inventory_quantity']) ? (int)$variant['inventory_quantity'] : 0;
                    $product['inventory_count'] += $inventoryQuantity;
                    if ($inventoryQuantity > 0) {
                        $product['HAS_INVENTORY'] = true;
                    }
                }
            } else {
                $product['variants'] = [];
                $product['HAS_INVENTORY'] = false;
                $product['inventory_count'] = 0;
            }
        }
        
        return $products;
    }

    public function getProductById($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE PROD_ID = :product_id AND PROD_DELETED_AT IS NULL";
        return $this->queryOne($sql, [':product_id' => $productId]);
    }

    public function createProduct($data)
    {
        // Ensure all required fields are present
        $requiredFields = ['PROD_IMAGE', 'PROD_NAME', 'PROD_DESCRIPTION'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                error_log("Product creation failed: Missing field {$field}");
                return false;
            }
        }
        
        $sql = "INSERT INTO {$this->table} (
                PROD_IMAGE, 
                PROD_NAME, 
                PROD_DESCRIPTION, 
                PROD_DISCOUNT_FREE_INSTALL_PCT,
                PROD_DISCOUNT_WITH_INSTALL_PCT1,
                PROD_DISCOUNT_WITH_INSTALL_PCT2,
                PROD_HAS_FREE_INSTALL_OPTION,
                PROD_CREATED_AT, 
                PROD_UPDATED_AT
            ) VALUES (
                :prod_image, 
                :prod_name, 
                :prod_description, 
                :prod_discount_free_install_pct,
                :prod_discount_with_install_pct1,
                :prod_discount_with_install_pct2,
                :prod_has_free_install_option,
                CURRENT_TIMESTAMP, 
                CURRENT_TIMESTAMP
            )";
        
        $params = [
            ':prod_image' => $data['PROD_IMAGE'],
            ':prod_name' => $data['PROD_NAME'],
            ':prod_description' => $data['PROD_DESCRIPTION'],
            ':prod_discount_free_install_pct' => $data['PROD_DISCOUNT_FREE_INSTALL_PCT'] ?? 0.00,
            ':prod_discount_with_install_pct1' => $data['PROD_DISCOUNT_WITH_INSTALL_PCT1'] ?? 0.00,
            ':prod_discount_with_install_pct2' => $data['PROD_DISCOUNT_WITH_INSTALL_PCT2'] ?? 0.00,
            ':prod_has_free_install_option' => $data['PROD_HAS_FREE_INSTALL_OPTION'] ?? true,
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('product_prod_id_seq');
    }

    public function updateProduct($productId, $data)
    {
        $setClauses = [];
        $params = [':product_id' => $productId];

        if (isset($data['PROD_IMAGE'])) {
            $setClauses[] = "PROD_IMAGE = :prod_image";
            $params[':prod_image'] = $data['PROD_IMAGE'];
        }
        if (isset($data['PROD_NAME'])) {
            $setClauses[] = "PROD_NAME = :prod_name";
            $params[':prod_name'] = $data['PROD_NAME'];
        }
        if (isset($data['PROD_DESCRIPTION'])) {
            $setClauses[] = "PROD_DESCRIPTION = :prod_description";
            $params[':prod_description'] = $data['PROD_DESCRIPTION'];
        }
        if (isset($data['PROD_DISCOUNT_FREE_INSTALL_PCT'])) {
            $setClauses[] = "PROD_DISCOUNT_FREE_INSTALL_PCT = :prod_discount_free_install_pct";
            $params[':prod_discount_free_install_pct'] = $data['PROD_DISCOUNT_FREE_INSTALL_PCT'];
        }
        if (isset($data['PROD_DISCOUNT_WITH_INSTALL_PCT1'])) {
            $setClauses[] = "PROD_DISCOUNT_WITH_INSTALL_PCT1 = :prod_discount_with_install_pct1";
            $params[':prod_discount_with_install_pct1'] = $data['PROD_DISCOUNT_WITH_INSTALL_PCT1'];
        }
        if (isset($data['PROD_DISCOUNT_WITH_INSTALL_PCT2'])) {
            $setClauses[] = "PROD_DISCOUNT_WITH_INSTALL_PCT2 = :prod_discount_with_install_pct2";
            $params[':prod_discount_with_install_pct2'] = $data['PROD_DISCOUNT_WITH_INSTALL_PCT2'];
        }
        if (isset($data['PROD_HAS_FREE_INSTALL_OPTION'])) {
            $setClauses[] = "PROD_HAS_FREE_INSTALL_OPTION = :prod_has_free_install_option";
            $params[':prod_has_free_install_option'] = $data['PROD_HAS_FREE_INSTALL_OPTION'];
        }

        if (empty($setClauses)) {
            return false; // No fields to update
        }

        $setClauses[] = "PROD_UPDATED_AT = CURRENT_TIMESTAMP";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE PROD_ID = :product_id AND PROD_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    public function deleteProduct($productId)
    {
        // Soft delete by setting PROD_DELETED_AT
        $sql = "UPDATE {$this->table} SET PROD_DELETED_AT = CURRENT_TIMESTAMP WHERE PROD_ID = :product_id";
        return $this->execute($sql, [':product_id' => $productId]);
    }

    public function getProductWithDetails($productId)
    {
        $sql = "SELECT 
                    p.*,
                    (SELECT COALESCE(JSON_AGG(f.*), '[]'::json)
                    FROM PRODUCT_FEATURE f 
                    WHERE f.PROD_ID = p.PROD_ID 
                    AND f.FEATURE_DELETED_AT IS NULL) AS features,
                    
                    (SELECT COALESCE(JSON_AGG(s.*), '[]'::json)
                    FROM PRODUCT_SPEC s 
                    WHERE s.PROD_ID = p.PROD_ID 
                    AND s.SPEC_DELETED_AT IS NULL) AS specs,
                    
                    (SELECT COALESCE(JSON_AGG(
                        json_build_object(
                            'var_id', v.VAR_ID,
                            'var_capacity', v.VAR_CAPACITY,
                            'var_srp_price', v.VAR_SRP_PRICE,
                            'var_price_free_install', v.VAR_PRICE_FREE_INSTALL,
                            'var_price_with_install1', v.VAR_PRICE_WITH_INSTALL1,
                            'var_price_with_install2', v.VAR_PRICE_WITH_INSTALL2,
                            'var_installation_fee', v.VAR_INSTALLATION_FEE,
                            'var_power_consumption', v.VAR_POWER_CONSUMPTION,
                            'inventory_quantity', COALESCE(
                                (SELECT SUM(i.QUANTITY) 
                                 FROM INVENTORY i 
                                 WHERE i.VAR_ID = v.VAR_ID 
                                 AND i.INVE_DELETED_AT IS NULL
                                 GROUP BY i.VAR_ID),
                                0
                            ),
                            'inventory', (
                                SELECT COALESCE(JSON_AGG(
                                    json_build_object(
                                        'inve_id', i.INVE_ID,
                                        'whouse_id', i.WHOUSE_ID,
                                        'inve_type', i.INVE_TYPE,
                                        'quantity', i.QUANTITY,
                                        'whouse_name', w.WHOUSE_NAME,
                                        'whouse_location', w.WHOUSE_LOCATION
                                    )
                                ), '[]'::json)
                                FROM INVENTORY i
                                JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID
                                WHERE i.VAR_ID = v.VAR_ID
                                AND i.INVE_DELETED_AT IS NULL
                                AND w.WHOUSE_DELETED_AT IS NULL
                            )
                        )
                    ), '[]'::json)
                    FROM PRODUCT_VARIANT v 
                    WHERE v.PROD_ID = p.PROD_ID 
                    AND v.VAR_DELETED_AT IS NULL) AS variants,
                    
                    (SELECT COALESCE(JSON_AGG(
                        json_build_object(
                            'inve_id', i.INVE_ID,
                            'var_id', i.VAR_ID,
                            'whouse_id', i.WHOUSE_ID,
                            'inve_type', i.INVE_TYPE,
                            'quantity', i.QUANTITY,
                            'var_capacity', v.VAR_CAPACITY,
                            'whouse_name', w.WHOUSE_NAME,
                            'whouse_location', w.WHOUSE_LOCATION
                        )
                    ), '[]'::json)
                    FROM INVENTORY i
                    JOIN PRODUCT_VARIANT v ON i.VAR_ID = v.VAR_ID
                    JOIN WAREHOUSE w ON i.WHOUSE_ID = w.WHOUSE_ID
                    WHERE v.PROD_ID = p.PROD_ID
                    AND i.INVE_DELETED_AT IS NULL
                    AND v.VAR_DELETED_AT IS NULL
                    AND w.WHOUSE_DELETED_AT IS NULL) AS inventory
                    
                FROM {$this->table} p
                WHERE p.PROD_ID = :product_id 
                AND p.PROD_DELETED_AT IS NULL";
        
        $result = $this->queryOne($sql, [':product_id' => $productId]);
        
        if (!$result) {
            return null;
        }
        
        // Convert JSON strings to arrays
        $result['features'] = json_decode($result['features'], true) ?: [];
        $result['specs'] = json_decode($result['specs'], true) ?: [];
        $result['variants'] = json_decode($result['variants'], true) ?: [];
        $result['inventory'] = json_decode($result['inventory'], true) ?: [];
        
        // Calculate inventory statistics
        $result['HAS_INVENTORY'] = false;
        $result['inventory_count'] = 0;
        
        foreach ($result['variants'] as $variant) {
            $inventoryQuantity = isset($variant['inventory_quantity']) ? (int)$variant['inventory_quantity'] : 0;
            $result['inventory_count'] += $inventoryQuantity;
            if ($inventoryQuantity > 0) {
                $result['HAS_INVENTORY'] = true;
            }
        }
        
        return $result;
    }
    
    // Get summary statistics for products
    public function getProductSummary()
    {
        // Get all products
        $products = $this->getAllProducts();
        
        // Calculate summary statistics
        $totalProducts = count($products);
        $totalVariants = 0;
        
        $productVariantModel = new ProductVariantModel();
        
        foreach ($products as $product) {
            // Get variants count for this product
            if (isset($product['PROD_ID'])) {
                $variants = $productVariantModel->getVariantsByProductId($product['PROD_ID']);
                $totalVariants += count($variants);
            }
        }
        
        return [
            'total_products' => $totalProducts,
            'total_variants' => $totalVariants
        ];
    }

    // Count the number of active (non-deleted) products
    public function countActiveProducts()
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE PROD_DELETED_AT IS NULL";
        $result = $this->queryOne($sql);
        return ($result && isset($result['total'])) ? (int)$result['total'] : 0;
    }
} 