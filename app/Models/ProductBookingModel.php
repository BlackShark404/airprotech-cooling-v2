<?php

namespace App\Models;

class ProductBookingModel extends Model
{
    protected $table = 'PRODUCT_BOOKING';

    // Get all product bookings
    public function getAllBookings()
    {
        $sql = "SELECT 
                    pb.*,
                    ua.UA_FIRST_NAME || ' ' || ua.UA_LAST_NAME AS CUSTOMER_NAME,
                    ua.UA_EMAIL AS CUSTOMER_EMAIL,
                    ua.UA_PHONE_NUMBER AS CUSTOMER_PHONE,
                    ua.UA_PROFILE_URL AS CUSTOMER_PROFILE_URL,
                    pv.VAR_CAPACITY,
                    pv.VAR_SRP_PRICE,
                    pv.VAR_PRICE_FREE_INSTALL,
                    pv.VAR_PRICE_WITH_INSTALL,
                    p.PROD_NAME,
                    p.PROD_IMAGE
                FROM {$this->table} pb
                JOIN CUSTOMER c ON pb.PB_CUSTOMER_ID = c.CU_ACCOUNT_ID
                JOIN USER_ACCOUNT ua ON c.CU_ACCOUNT_ID = ua.UA_ID
                JOIN PRODUCT_VARIANT pv ON pb.PB_VARIANT_ID = pv.VAR_ID
                JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                WHERE pb.PB_DELETED_AT IS NULL
                ORDER BY pb.PB_ORDER_DATE DESC";
        
        return $this->query($sql);
    }

    /**
     * Get a specific product booking by ID
     */
    public function getBookingById($bookingId)
    {
        try {
            error_log("Fetching booking ID: $bookingId");
            
            $sql = "SELECT 
                        pb.*,
                        ua.UA_FIRST_NAME || ' ' || ua.UA_LAST_NAME AS CUSTOMER_NAME,
                        ua.UA_EMAIL AS CUSTOMER_EMAIL,
                        ua.UA_PHONE_NUMBER AS CUSTOMER_PHONE,
                        ua.UA_PROFILE_URL AS CUSTOMER_PROFILE_URL,
                        pv.VAR_CAPACITY,
                        pv.VAR_SRP_PRICE,
                        pv.VAR_PRICE_FREE_INSTALL,
                        pv.VAR_PRICE_WITH_INSTALL,
                        p.PROD_NAME,
                        p.PROD_IMAGE,
                        pb.PB_CUSTOMER_ID
                    FROM {$this->table} pb
                    LEFT JOIN CUSTOMER c ON pb.PB_CUSTOMER_ID = c.CU_ACCOUNT_ID
                    LEFT JOIN USER_ACCOUNT ua ON c.CU_ACCOUNT_ID = ua.UA_ID
                    LEFT JOIN PRODUCT_VARIANT pv ON pb.PB_VARIANT_ID = pv.VAR_ID
                    LEFT JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                    WHERE pb.PB_ID = :booking_id AND pb.PB_DELETED_AT IS NULL";
            
            $result = $this->queryOne($sql, [':booking_id' => $bookingId]);
            
            if (!$result) {
                error_log("No booking found with ID: $bookingId");
            } else {
                error_log("Found booking: " . json_encode($result));
                
                // Ensure PB_CUSTOMER_ID is set
                if (!isset($result['PB_CUSTOMER_ID'])) {
                    error_log("Warning: PB_CUSTOMER_ID is not set in the result");
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("Error fetching booking: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all bookings for a specific customer
     */
    public function getBookingsByCustomerId($customerId)
    {
        $sql = "SELECT 
                    pb.*,
                    pv.VAR_CAPACITY,
                    pv.VAR_SRP_PRICE,
                    pv.VAR_PRICE_FREE_INSTALL,
                    pv.VAR_PRICE_WITH_INSTALL,
                    p.PROD_NAME,
                    p.PROD_IMAGE
                FROM {$this->table} pb
                JOIN PRODUCT_VARIANT pv ON pb.PB_VARIANT_ID = pv.VAR_ID
                JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                WHERE pb.PB_CUSTOMER_ID = :customer_id AND pb.PB_DELETED_AT IS NULL
                ORDER BY pb.PB_ORDER_DATE DESC";
        
        return $this->query($sql, [':customer_id' => $customerId]);
    }

    // Create a new product booking
    public function createBooking($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    PB_CUSTOMER_ID, 
                    PB_VARIANT_ID, 
                    PB_QUANTITY, 
                    PB_UNIT_PRICE, 
                    PB_STATUS, 
                    PB_PRICE_TYPE,
                    PB_PREFERRED_DATE, 
                    PB_PREFERRED_TIME, 
                    PB_ADDRESS,
                    PB_DESCRIPTION,
                    PB_WAREHOUSE_ID,
                    PB_INVENTORY_DEDUCTED
                ) VALUES (
                    :customer_id, 
                    :variant_id, 
                    :quantity, 
                    :unit_price, 
                    :status, 
                    :price_type,
                    :preferred_date, 
                    :preferred_time, 
                    :address,
                    :description,
                    :warehouse_id,
                    :inventory_deducted
                )";
        
        $params = [
            ':customer_id' => $data['PB_CUSTOMER_ID'],
            ':variant_id' => $data['PB_VARIANT_ID'],
            ':quantity' => $data['PB_QUANTITY'],
            ':unit_price' => $data['PB_UNIT_PRICE'],
            ':status' => $data['PB_STATUS'] ?? 'pending',
            ':price_type' => $data['PB_PRICE_TYPE'] ?? 'free_install',
            ':preferred_date' => $data['PB_PREFERRED_DATE'],
            ':preferred_time' => $data['PB_PREFERRED_TIME'],
            ':address' => $data['PB_ADDRESS'],
            ':description' => $data['PB_DESCRIPTION'] ?? null,
            ':warehouse_id' => $data['PB_WAREHOUSE_ID'] ?? null,
            ':inventory_deducted' => $data['PB_INVENTORY_DEDUCTED'] ?? false
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('product_booking_pb_id_seq');
    }

    // Update a product booking status
    public function updateBookingStatus($bookingId, $status)
    {
        // Do not manually set PB_INVENTORY_DEDUCTED as it will be handled by the database trigger
        $sql = "UPDATE {$this->table} SET 
                PB_STATUS = :status,
                PB_UPDATED_AT = CURRENT_TIMESTAMP
                WHERE PB_ID = :booking_id AND PB_DELETED_AT IS NULL";
        
        $params = [
            ':status' => $status,
            ':booking_id' => $bookingId
        ];
        
        return $this->execute($sql, $params);
    }

    // Update an existing product booking
    public function updateBooking($bookingId, $data)
    {
        $setClauses = [];
        $params = [':booking_id' => $bookingId];

        if (isset($data['PB_QUANTITY'])) {
            $setClauses[] = "PB_QUANTITY = :quantity";
            $params[':quantity'] = $data['PB_QUANTITY'];
        }
        
        if (isset($data['PB_UNIT_PRICE'])) {
            $setClauses[] = "PB_UNIT_PRICE = :unit_price";
            $params[':unit_price'] = $data['PB_UNIT_PRICE'];
        }
        
        if (isset($data['PB_STATUS'])) {
            $setClauses[] = "PB_STATUS = :status";
            $params[':status'] = $data['PB_STATUS'];
        }
        
        if (isset($data['PB_PRICE_TYPE'])) {
            $setClauses[] = "PB_PRICE_TYPE = :price_type";
            $params[':price_type'] = $data['PB_PRICE_TYPE'];
        }
        
        if (isset($data['PB_PREFERRED_DATE'])) {
            $setClauses[] = "PB_PREFERRED_DATE = :preferred_date";
            $params[':preferred_date'] = $data['PB_PREFERRED_DATE'];
        }
        
        if (isset($data['PB_PREFERRED_TIME'])) {
            $setClauses[] = "PB_PREFERRED_TIME = :preferred_time";
            $params[':preferred_time'] = $data['PB_PREFERRED_TIME'];
        }
        
        if (isset($data['PB_ADDRESS'])) {
            $setClauses[] = "PB_ADDRESS = :address";
            $params[':address'] = $data['PB_ADDRESS'];
        }
        
        if (isset($data['PB_DESCRIPTION'])) {
            $setClauses[] = "PB_DESCRIPTION = :description";
            $params[':description'] = $data['PB_DESCRIPTION'];
        }
        
        if (isset($data['PB_WAREHOUSE_ID'])) {
            $setClauses[] = "PB_WAREHOUSE_ID = :warehouse_id";
            $params[':warehouse_id'] = $data['PB_WAREHOUSE_ID'];
        }

        if (empty($setClauses)) {
            return false; // No fields to update
        }

        $setClauses[] = "PB_UPDATED_AT = CURRENT_TIMESTAMP";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE PB_ID = :booking_id AND PB_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    // Soft delete a product booking
    public function deleteBooking($bookingId)
    {
        $sql = "UPDATE {$this->table} SET PB_DELETED_AT = CURRENT_TIMESTAMP 
                WHERE PB_ID = :booking_id";
        return $this->execute($sql, [':booking_id' => $bookingId]);
    }

    // Get booking summary statistics
    public function getBookingSummary()
    {
        $sql = "SELECT 
                    COUNT(*) AS TOTAL_BOOKINGS,
                    COUNT(CASE WHEN PB_STATUS = 'pending' THEN 1 END) AS PENDING_BOOKINGS,
                    COUNT(CASE WHEN PB_STATUS = 'confirmed' THEN 1 END) AS CONFIRMED_BOOKINGS,
                    COUNT(CASE WHEN PB_STATUS = 'completed' THEN 1 END) AS COMPLETED_BOOKINGS,
                    COUNT(CASE WHEN PB_STATUS = 'cancelled' THEN 1 END) AS CANCELLED_BOOKINGS,
                    SUM(PB_QUANTITY * PB_UNIT_PRICE) AS TOTAL_REVENUE
                FROM {$this->table}
                WHERE PB_DELETED_AT IS NULL";
        
        return $this->queryOne($sql);
    }

    // Get all bookings with filters
    public function getFilteredBookings($filters = [])
    {
        $whereConditions = ["pb.PB_DELETED_AT IS NULL"];
        $params = [];

        // Apply status filter
        if (!empty($filters['status'])) {
            $whereConditions[] = "pb.PB_STATUS = :status";
            $params[':status'] = $filters['status'];
        }

        // Apply product filter
        if (!empty($filters['product_id'])) {
            $whereConditions[] = "p.PROD_ID = :product_id";
            $params[':product_id'] = $filters['product_id'];
        }

        // Apply date range filter
        if (!empty($filters['date_range'])) {
            $now = new Date();
            $dateRange = $filters['date_range'];
            
            switch ($dateRange) {
                case 'today':
                    $whereConditions[] = "DATE(pb.PB_ORDER_DATE) = CURRENT_DATE";
                    break;
                case 'yesterday':
                    $whereConditions[] = "DATE(pb.PB_ORDER_DATE) = CURRENT_DATE - INTERVAL '1 day'";
                    break;
                case 'last7days':
                    $whereConditions[] = "pb.PB_ORDER_DATE >= CURRENT_DATE - INTERVAL '7 days'";
                    break;
                case 'last30days':
                    $whereConditions[] = "pb.PB_ORDER_DATE >= CURRENT_DATE - INTERVAL '30 days'";
                    break;
                case 'thisMonth':
                    $whereConditions[] = "EXTRACT(MONTH FROM pb.PB_ORDER_DATE) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM pb.PB_ORDER_DATE) = EXTRACT(YEAR FROM CURRENT_DATE)";
                    break;
                case 'lastMonth':
                    $whereConditions[] = "
                        (EXTRACT(MONTH FROM pb.PB_ORDER_DATE) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') AND 
                        EXTRACT(YEAR FROM pb.PB_ORDER_DATE) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month'))";
                    break;
            }
        }

        // Apply technician filter
        if (!empty($filters['technician_id'])) {
            $whereConditions[] = "pa.PA_TECHNICIAN_ID = :technician_id";
            $params[':technician_id'] = $filters['technician_id'];
        } elseif (isset($filters['has_technician'])) {
            if ($filters['has_technician']) {
                $whereConditions[] = "pa.PA_ID IS NOT NULL";
            } else {
                $whereConditions[] = "pa.PA_ID IS NULL";
            }
        }

        $sql = "SELECT 
                    pb.*,
                    ua.UA_FIRST_NAME || ' ' || ua.UA_LAST_NAME AS CUSTOMER_NAME,
                    ua.UA_EMAIL AS CUSTOMER_EMAIL,
                    ua.UA_PHONE_NUMBER AS CUSTOMER_PHONE,
                    ua.UA_PROFILE_URL AS CUSTOMER_PROFILE_URL,
                    pv.VAR_CAPACITY,
                    pv.VAR_SRP_PRICE,
                    pv.VAR_PRICE_FREE_INSTALL,
                    pv.VAR_PRICE_WITH_INSTALL,
                    p.PROD_NAME,
                    p.PROD_IMAGE
                FROM {$this->table} pb
                LEFT JOIN CUSTOMER c ON pb.PB_CUSTOMER_ID = c.CU_ACCOUNT_ID
                LEFT JOIN USER_ACCOUNT ua ON c.CU_ACCOUNT_ID = ua.UA_ID
                LEFT JOIN PRODUCT_VARIANT pv ON pb.PB_VARIANT_ID = pv.VAR_ID
                LEFT JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                LEFT JOIN PRODUCT_ASSIGNMENT pa ON pb.PB_ID = pa.PA_ORDER_ID";

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY pb.PB_ID, ua.UA_ID, pv.VAR_ID, p.PROD_ID ORDER BY pb.PB_ORDER_DATE DESC";

        return $this->query($sql, $params);
    }

    // Get assigned technicians for a product booking
    public function getAssignedTechnicians($bookingId)
    {
        $sql = "SELECT 
                    pa.PA_ID,
                    pa.PA_TECHNICIAN_ID as id,
                    ua.UA_FIRST_NAME || ' ' || ua.UA_LAST_NAME as name,
                    ua.UA_EMAIL as email,
                    ua.UA_PHONE_NUMBER as phone,
                    ua.UA_PROFILE_URL as profile_url,
                    pa.PA_NOTES as notes,
                    pa.PA_STATUS as status,
                    pa.PA_ASSIGNED_AT as assigned_at
                FROM PRODUCT_ASSIGNMENT pa
                JOIN TECHNICIAN t ON pa.PA_TECHNICIAN_ID = t.TE_ACCOUNT_ID
                JOIN USER_ACCOUNT ua ON t.TE_ACCOUNT_ID = ua.UA_ID
                WHERE pa.PA_ORDER_ID = :booking_id
                ORDER BY pa.PA_ASSIGNED_AT DESC";

        return $this->query($sql, [':booking_id' => $bookingId]);
    }

    // Remove all technicians from a product booking
    public function removeAllTechnicians($bookingId)
    {
        $sql = "DELETE FROM PRODUCT_ASSIGNMENT WHERE PA_ORDER_ID = :booking_id";
        return $this->execute($sql, [':booking_id' => $bookingId]);
    }

    // Assign a technician to a product booking
    public function assignTechnician($bookingId, $technicianId, $notes = '')
    {
        // Check if this technician is already assigned to this booking
        $sql = "SELECT PA_ID FROM PRODUCT_ASSIGNMENT 
                WHERE PA_ORDER_ID = :booking_id AND PA_TECHNICIAN_ID = :technician_id";
        
        $existing = $this->queryOne($sql, [
            ':booking_id' => $bookingId,
            ':technician_id' => $technicianId
        ]);
        
        if ($existing) {
            // Update notes for existing assignment
            $sql = "UPDATE PRODUCT_ASSIGNMENT 
                    SET PA_NOTES = :notes,
                        PA_UPDATED_AT = CURRENT_TIMESTAMP
                    WHERE PA_ORDER_ID = :booking_id AND PA_TECHNICIAN_ID = :technician_id";
            
            return $this->execute($sql, [
                ':booking_id' => $bookingId,
                ':technician_id' => $technicianId,
                ':notes' => $notes
            ]);
        } else {
            // Create new assignment
            $sql = "INSERT INTO PRODUCT_ASSIGNMENT 
                    (PA_ORDER_ID, PA_TECHNICIAN_ID, PA_STATUS, PA_NOTES)
                    VALUES (:booking_id, :technician_id, 'assigned', :notes)";
            
            return $this->execute($sql, [
                ':booking_id' => $bookingId,
                ':technician_id' => $technicianId,
                ':notes' => $notes
            ]);
        }
    }
} 