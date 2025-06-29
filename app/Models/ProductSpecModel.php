<?php

namespace App\Models;

class ProductSpecModel extends Model
{
    protected $table = 'PRODUCT_SPEC';

    public function getSpecsByProductId($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE PROD_ID = :product_id AND SPEC_DELETED_AT IS NULL";
        return $this->query($sql, [':product_id' => $productId]);
    }

    public function getSpecById($specId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE SPEC_ID = :spec_id AND SPEC_DELETED_AT IS NULL";
        return $this->queryOne($sql, [':spec_id' => $specId]);
    }

    public function createSpec($data)
    {
        $sql = "INSERT INTO {$this->table} (SPEC_NAME, SPEC_VALUE, PROD_ID)
                VALUES (:spec_name, :spec_value, :product_id)";
        
        $params = [
            ':spec_name' => $data['SPEC_NAME'],
            ':spec_value' => $data['SPEC_VALUE'],
            ':product_id' => $data['PROD_ID']
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('product_spec_spec_id_seq');
    }

    public function updateSpec($specId, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                SPEC_NAME = :spec_name,
                SPEC_VALUE = :spec_value,
                SPEC_UPDATED_AT = CURRENT_TIMESTAMP
                WHERE SPEC_ID = :spec_id";
        
        $params = [
            ':spec_name' => $data['SPEC_NAME'],
            ':spec_value' => $data['SPEC_VALUE'],
            ':spec_id' => $specId
        ];
        
        return $this->execute($sql, $params);
    }

    public function deleteSpec($specId)
    {
        $sql = "UPDATE {$this->table} SET 
                SPEC_DELETED_AT = CURRENT_TIMESTAMP
                WHERE SPEC_ID = :spec_id";
        
        return $this->execute($sql, [':spec_id' => $specId]);
    }

    public function deleteSpecsByProductId($productId)
    {
        $sql = "UPDATE {$this->table} SET 
                SPEC_DELETED_AT = CURRENT_TIMESTAMP
                WHERE PROD_ID = :product_id";
        
        return $this->execute($sql, [':product_id' => $productId]);
    }
} 