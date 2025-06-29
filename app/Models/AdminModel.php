<?php

namespace App\Models;

class AdminModel extends Model
{
    protected $table = 'admin'; // Database table name
    protected $primaryKey = 'ad_account_id'; // Primary key for the admin table

    // Specify fillable fields for mass assignment, if any, e.g., 'ad_office_no'
    protected $fillable = [
        'ad_office_no',
    ];

    // Timestamps
    protected $createdAtColumn = 'ad_created_at';
    protected $updatedAtColumn = 'ad_updated_at';
    // No soft deletes for this table as per schema
    // protected $deletedAtColumn = 'ad_deleted_at';

    protected $timestamps = true; // Enable automatic timestamp management for created_at and updated_at
    protected $useSoftDeletes = false; // Admin table does not have a deleted_at column in the provided schema

    // Update admin record by account ID.
    public function updateByAccountId($accountId, array $data)
    {
        if (empty($data)) {
            return false;
        }

        $expressions = [];
        if ($this->timestamps && $this->updatedAtColumn) {
            $expressions[$this->updatedAtColumn] = 'NOW()'; // Use NOW() for PostgreSQL
        }

        // Ensure only fillable fields are updated
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        if (empty($fillableData)) {
            // If only timestamps are being updated (e.g. by $expressions), still proceed if $expressions is not empty
            if(empty($expressions)) return false; 
        }

        $updateDetails = $this->formatUpdateData($fillableData, [], $expressions);
        
        $sql = "UPDATE {$this->table} 
                SET {$updateDetails['updateClause']} 
                WHERE {$this->primaryKey} = :account_id";
        
        $params = array_merge($updateDetails['filteredData'], ['account_id' => $accountId]);
        
        return $this->execute($sql, $params);
    }

    // Find an admin record by account ID.
    public function findByAccountId($accountId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :account_id";
        return $this->queryOne($sql, ['account_id' => $accountId]);
    }
    
    public function createAdmin(array $data)
    {
        if (empty($data[$this->primaryKey])) {
            // ad_account_id is required and usually comes from user_account table
            return false; 
        }

        $expressions = [];
        if ($this->timestamps) {
            if($this->createdAtColumn) $expressions[$this->createdAtColumn] = 'NOW()';
            if($this->updatedAtColumn) $expressions[$this->updatedAtColumn] = 'NOW()';
        }

        $insertData = $this->formatInsertData($data, [], $expressions);
        $sql = "INSERT INTO {$this->table} ({$insertData['columns']}) 
                VALUES ({$insertData['placeholders']})";

        if ($this->execute($sql, $insertData['filteredData'])) {
            // Since primary key is not auto-incrementing but a FK
            // we return the ad_account_id passed in data.
            return $data[$this->primaryKey];
        }
        return false;
    }

    // Get system-wide statistics for the admin dashboard/profile.
    public function getSystemStatistics()
    {
        $stats = [];

        // Get active customers count
        $sqlCustomers = "SELECT COUNT(ua.ua_id) 
                         FROM user_account ua 
                         INNER JOIN user_role ur ON ua.ua_role_id = ur.ur_id 
                         WHERE ur.ur_name = 'customer' AND ua.ua_is_active = TRUE AND ua.ua_deleted_at IS NULL";
        $stats['total_active_customers'] = (int)$this->queryScalar($sqlCustomers);

        // Get active technicians count
        $sqlTechnicians = "SELECT COUNT(ua.ua_id) 
                           FROM user_account ua 
                           INNER JOIN user_role ur ON ua.ua_role_id = ur.ur_id 
                           WHERE ur.ur_name = 'technician' AND ua.ua_is_active = TRUE AND ua.ua_deleted_at IS NULL";
        $stats['total_active_technicians'] = (int)$this->queryScalar($sqlTechnicians);

        // Get pending service requests count
        $sqlPendingServices = "SELECT COUNT(sb_id) 
                               FROM service_booking 
                               WHERE sb_status = 'pending' AND sb_deleted_at IS NULL";
        $stats['total_pending_service_requests'] = (int)$this->queryScalar($sqlPendingServices);

        // Get in-progress service requests count
        $sqlInProgressServices = "SELECT COUNT(sb_id) 
                                  FROM service_booking 
                                  WHERE sb_status = 'in-progress' AND sb_deleted_at IS NULL";
        $stats['total_inprogress_service_requests'] = (int)$this->queryScalar($sqlInProgressServices);

        // Get pending product orders count
        $sqlPendingProductOrders = "SELECT COUNT(pb_id) 
                                    FROM product_booking 
                                    WHERE pb_status = 'pending' AND pb_deleted_at IS NULL";
        $stats['total_pending_product_orders'] = (int)$this->queryScalar($sqlPendingProductOrders);

        // Get total registered users (not soft-deleted)
        $sqlTotalUsers = "SELECT COUNT(ua_id) 
                          FROM user_account 
                          WHERE ua_deleted_at IS NULL";
        $stats['total_registered_users'] = (int)$this->queryScalar($sqlTotalUsers);

        // Get total admin accounts (not soft-deleted)
        $sqlTotalAdmins = "SELECT COUNT(ua.ua_id) 
                           FROM user_account ua
                           INNER JOIN user_role ur ON ua.ua_role_id = ur.ur_id
                           WHERE ur.ur_name = 'admin' AND ua.ua_deleted_at IS NULL";
        $stats['total_admin_accounts'] = (int)$this->queryScalar($sqlTotalAdmins);

        // Get total products (not soft-deleted)
        $sqlTotalProducts = "SELECT COUNT(prod_id) 
                             FROM product 
                             WHERE prod_deleted_at IS NULL";
        $stats['total_products'] = (int)$this->queryScalar($sqlTotalProducts);

        return $stats;
    }
} 