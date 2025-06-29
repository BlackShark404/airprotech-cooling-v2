<?php

namespace App\Models;

class ProductFeatureModel extends Model
{
    protected $table = 'PRODUCT_FEATURE';

    public function getFeaturesByProductId($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE PROD_ID = :product_id AND FEATURE_DELETED_AT IS NULL";
        return $this->query($sql, [':product_id' => $productId]);
    }

    public function getFeatureById($featureId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE FEATURE_ID = :feature_id AND FEATURE_DELETED_AT IS NULL";
        return $this->queryOne($sql, [':feature_id' => $featureId]);
    }

    public function createFeature($data)
    {
        $sql = "INSERT INTO {$this->table} (FEATURE_NAME, PROD_ID)
                VALUES (:feature_name, :product_id)";
        
        $params = [
            ':feature_name' => $data['FEATURE_NAME'],
            ':product_id' => $data['PROD_ID']
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('product_feature_feature_id_seq');
    }

    public function updateFeature($featureId, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                FEATURE_NAME = :feature_name,
                FEATURE_UPDATED_AT = CURRENT_TIMESTAMP
                WHERE FEATURE_ID = :feature_id";
        
        $params = [
            ':feature_name' => $data['FEATURE_NAME'],
            ':feature_id' => $featureId
        ];
        
        return $this->execute($sql, $params);
    }

    public function deleteFeature($featureId)
    {
        $sql = "UPDATE {$this->table} SET 
                FEATURE_DELETED_AT = CURRENT_TIMESTAMP
                WHERE FEATURE_ID = :feature_id";
        
        return $this->execute($sql, [':feature_id' => $featureId]);
    }

    public function deleteFeaturesByProductId($productId)
    {
        $sql = "UPDATE {$this->table} SET 
                FEATURE_DELETED_AT = CURRENT_TIMESTAMP
                WHERE PROD_ID = :product_id";
        
        return $this->execute($sql, [':product_id' => $productId]);
    }
} 