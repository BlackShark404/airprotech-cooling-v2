<?php

namespace App\Models;

class ProductVariantModel extends Model
{
    protected $table = 'PRODUCT_VARIANT';

    public function getVariantsByProductId($productId)
    {
        // Ensure productId is treated as an integer to avoid case sensitivity issues
        $productId = intval($productId);
        $sql = "SELECT * FROM {$this->table} WHERE PROD_ID = :product_id AND VAR_DELETED_AT IS NULL";
        return $this->query($sql, [':product_id' => $productId]);
    }

    public function getVariantById($variantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE VAR_ID = :variant_id AND VAR_DELETED_AT IS NULL";
        return $this->queryOne($sql, [':variant_id' => $variantId]);
    }

    public function createVariant($data)
    {
        $sql = "INSERT INTO {$this->table} (
                VAR_CAPACITY, 
                VAR_SRP_PRICE, 
                VAR_INSTALLATION_FEE, 
                VAR_POWER_CONSUMPTION, 
                PROD_ID,
                VAR_CREATED_AT,
                VAR_UPDATED_AT
            ) VALUES (
                :capacity, 
                :srp_price, 
                :installation_fee, 
                :power_consumption, 
                :product_id,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )";
        
        $params = [
            ':capacity' => $data['VAR_CAPACITY'],
            ':srp_price' => $data['VAR_SRP_PRICE'],
            ':installation_fee' => $data['VAR_INSTALLATION_FEE'] ?? 0.00,
            ':power_consumption' => $data['VAR_POWER_CONSUMPTION'] ?? null,
            ':product_id' => $data['PROD_ID']
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('product_variant_var_id_seq');
    }

    public function updateVariant($variantId, $data)
    {
        $setClauses = [];
        $params = [':variant_id' => $variantId];

        if (isset($data['VAR_CAPACITY'])) {
            $setClauses[] = "VAR_CAPACITY = :capacity";
            $params[':capacity'] = $data['VAR_CAPACITY'];
        }
        
        if (isset($data['VAR_SRP_PRICE'])) {
            $setClauses[] = "VAR_SRP_PRICE = :srp_price";
            $params[':srp_price'] = $data['VAR_SRP_PRICE'];
        }
        
        if (array_key_exists('VAR_INSTALLATION_FEE', $data)) {
            $setClauses[] = "VAR_INSTALLATION_FEE = :installation_fee";
            $params[':installation_fee'] = $data['VAR_INSTALLATION_FEE'];
        }
        
        if (array_key_exists('VAR_POWER_CONSUMPTION', $data)) {
            $setClauses[] = "VAR_POWER_CONSUMPTION = :power_consumption";
            $params[':power_consumption'] = $data['VAR_POWER_CONSUMPTION'];
        }

        if (empty($setClauses)) {
            return false; // No fields to update
        }

        $setClauses[] = "VAR_UPDATED_AT = CURRENT_TIMESTAMP";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE VAR_ID = :variant_id AND VAR_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    public function deleteVariant($variantId)
    {
        $sql = "UPDATE {$this->table} SET VAR_DELETED_AT = CURRENT_TIMESTAMP 
                WHERE VAR_ID = :variant_id";
        return $this->execute($sql, [':variant_id' => $variantId]);
    }

    public function deleteVariantsByProductId($productId)
    {
        $sql = "UPDATE {$this->table} SET VAR_DELETED_AT = CURRENT_TIMESTAMP 
                WHERE PROD_ID = :product_id";
        return $this->execute($sql, [':product_id' => $productId]);
    }
} 