<?php

namespace App\Models;

class ServiceRequestTypeModel extends Model
{
    protected $table = 'service_type';
    protected $primaryKey = 'st_id';

    // Enable timestamps
    protected $timestamps = true;
    protected $createdAtColumn = 'st_created_at';
    protected $updatedAtColumn = 'st_updated_at';

    
    // Get all active service types
    public function getActiveServiceTypes()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE st_is_active = :isActive 
                ORDER BY st_name ASC";
        return $this->query($sql, ['isActive' => true]);
    }

    // Get a service type by code
    public function getServiceTypeByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE st_code = :code";
        return $this->queryOne($sql, ['code' => $code]);
    }

    // Get a service type by its primary key (ID)
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->queryOne($sql, ['id' => $id]);
     }
    
    // Get a service type by its primary key (ID) - alias for findById
    // Used by ServiceRequestController
    public function getServiceTypeById($id)
    {
        return $this->findById($id);
    }

    // Create a new service type using all provided data.
    public function createServiceType(array $data)
    {
        $dataForPlaceholders = $data; // Data that will become "column = :value"
        $expressions = [];          // Data that will become "column = SQL_EXPRESSION"

        if ($this->timestamps) {
            if ($this->createdAtColumn && !array_key_exists($this->createdAtColumn, $dataForPlaceholders)) {
                $expressions[$this->createdAtColumn] = 'CURRENT_TIMESTAMP';
                unset($dataForPlaceholders[$this->createdAtColumn]); // Ensure it's not also a placeholder
            }
            if ($this->updatedAtColumn && !array_key_exists($this->updatedAtColumn, $dataForPlaceholders)) {
                $expressions[$this->updatedAtColumn] = 'CURRENT_TIMESTAMP';
                unset($dataForPlaceholders[$this->updatedAtColumn]); // Ensure it's not also a placeholder
            }
        }
        
        $formatted = $this->formatInsertData($dataForPlaceholders, [], $expressions);
        
        if (empty($formatted['columns'])) {
             error_log("No columns to insert for service type. Original data: " . json_encode($data) . " Expressions: " . json_encode($expressions));
             return false;
        }

        $sql = "INSERT INTO {$this->table} ({$formatted['columns']}) VALUES ({$formatted['placeholders']})";

        // $formatted['filteredData'] contains values for placeholders derived from $dataForPlaceholders.
        if ($this->execute($sql, $formatted['filteredData']) > 0) {
            return $this->lastInsertId("{$this->table}_{$this->primaryKey}_seq");
        }
        return false;
    }

    // Update a service type using all provided data.
    public function updateServiceType($typeId, array $data)
    {
        $dataForPlaceholders = $data; // Data for "SET column = :value"
        $expressions = [];          // Data for "SET column = SQL_EXPRESSION"

        if ($this->timestamps && $this->updatedAtColumn) {
            if (!array_key_exists($this->updatedAtColumn, $dataForPlaceholders)) {
                $expressions[$this->updatedAtColumn] = 'CURRENT_TIMESTAMP';
                unset($dataForPlaceholders[$this->updatedAtColumn]); // Ensure it's not also a placeholder
            }
            // If $this->updatedAtColumn *is* in $dataForPlaceholders, it will be set via placeholder.
        }
        
        // If $dataForPlaceholders is empty AND $expressions is empty, then there's nothing to update.
        if (empty($dataForPlaceholders) && empty($expressions)) {
            
        }
        
        $formatted = $this->formatUpdateData($dataForPlaceholders, [], $expressions);
        
        if (empty($formatted['updateClause'])) {
             // This occurs if $dataForPlaceholders and $expressions are both empty.
             // error_log("Update clause is empty for service type ID: " . $typeId . ". Original data: " . json_encode($data));
             return true; // No operation performed, could be considered success of "no change needed".
        }

        $sql = "UPDATE {$this->table} SET {$formatted['updateClause']} WHERE {$this->primaryKey} = :pk_value";
        
        $params = array_merge($formatted['filteredData'], ['pk_value' => $typeId]);
        
        return $this->execute($sql, $params) > 0;
    }
    
    // Toggle the active status of a service type
    public function toggleServiceTypeStatus($typeId, $isActive)
    {
        $dataToUpdate = ['st_is_active' => (bool) $isActive];
        $expressions = [];

        if ($this->timestamps && $this->updatedAtColumn) {
            // If st_updated_at is not in $dataToUpdate (which it isn't here), set it as an expression
            $expressions[$this->updatedAtColumn] = 'CURRENT_TIMESTAMP';
            // No need to unset from $dataToUpdate as it was never there.
        }

        $formatted = $this->formatUpdateData($dataToUpdate, [], $expressions);

        if (empty($formatted['updateClause'])) {
             error_log("Update clause is empty for toggleServiceTypeStatus ID: " . $typeId);
             return false; // Should not happen if st_is_active or timestamp is changing.
        }

        $sql = "UPDATE {$this->table} SET {$formatted['updateClause']} WHERE {$this->primaryKey} = :pk_value";

        $params = array_merge($formatted['filteredData'], ['pk_value' => $typeId]);

        return $this->execute($sql, $params) > 0;
    }

    // Delete a service type by its ID
    public function deleteServiceType($typeId)
    {
        // Hard delete
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :pk_value";
        return $this->execute($sql, ['pk_value' => $typeId]) > 0;
    }
}