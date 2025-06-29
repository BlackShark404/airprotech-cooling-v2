<?php

namespace App\Models;

class ServiceRequestModel extends Model
{
    protected $table = 'service_booking';
    protected $primaryKey = 'sb_id';

    // Properties for soft deletes and timestamps, managed by methods in this class
    protected $useSoftDeletes = true;
    protected $deletedAtColumn = 'sb_deleted_at';

    protected $timestamps = true;
    protected $createdAtColumn = 'sb_created_at';
    protected $updatedAtColumn = 'sb_updated_at';

    // Get service bookings for a specific customer.
    public function getCustomerBookings($customerId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE sb_customer_id = :customerId";
        
        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }
        
        $sql .= " ORDER BY sb_preferred_date DESC, sb_preferred_time DESC";
        
        return $this->query($sql, ['customerId' => $customerId]);
    }
    
    // Get a single service booking with customer and service type details.
    public function getBookingWithDetails($bookingId)
    {
        $sql = "SELECT 
                    sb.*, 
                    st.st_name as service_name, 
                    st.st_description as service_description,
                    ua.ua_first_name as customer_first_name,
                    ua.ua_last_name as customer_last_name,
                    ua.ua_email as customer_email,
                    ua.ua_phone_number as customer_phone
                FROM {$this->table} sb
                JOIN service_type st ON sb.sb_service_type_id = st.st_id
                JOIN customer c ON sb.sb_customer_id = c.cu_account_id
                JOIN user_account ua ON c.cu_account_id = ua.ua_id
                WHERE sb.{$this->primaryKey} = :bookingId";

        if ($this->useSoftDeletes) {
            $sql .= " AND sb.{$this->deletedAtColumn} IS NULL"; 
        }
        
        return $this->queryOne($sql, ['bookingId' => $bookingId]);
    }
    
    // Create a new service booking.
    public function createBooking($data)
    {
        if ($this->timestamps) {
            $currentTime = date('Y-m-d H:i:s');
            if (!isset($data[$this->createdAtColumn])) {
                $data[$this->createdAtColumn] = $currentTime;
            }
            if (!isset($data[$this->updatedAtColumn])) {
                $data[$this->updatedAtColumn] = $currentTime;
            }
        }
        
        $formattedInsert = $this->formatInsertData($data);
        
        $sql = "INSERT INTO {$this->table} ({$formattedInsert['columns']}) 
                VALUES ({$formattedInsert['placeholders']})";
        
        $this->execute($sql, $formattedInsert['filteredData']);
        return $this->lastInsertId($this->table . '_' . $this->primaryKey . '_seq'); // Specify sequence name for PostgreSQL
    }
    
    // Update an existing service booking.
    public function updateBooking($bookingId, $data)
    {
        if (empty($data)) {
            return true; // No data to update, considered a successful no-op.
        }

        if ($this->timestamps && !isset($data[$this->updatedAtColumn])) {
            $data[$this->updatedAtColumn] = date('Y-m-d H:i:s');
        }
        
        $formattedUpdate = $this->formatUpdateData($data);
        
        if (empty($formattedUpdate['updateClause'])) {
            return true; // No effective update to make.
        }

        $sql = "UPDATE {$this->table} 
                SET {$formattedUpdate['updateClause']}
                WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
        
        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }

        $params = $formattedUpdate['filteredData'];
        $params['_primaryKeyValueBinding'] = $bookingId; 
        
        return $this->execute($sql, $params) > 0;
    }
    
    // Update the status of a service booking.
    public function updateBookingStatus($bookingId, $status)
    {
        // Basic validation for status, though DB constraint will also check
        $allowedStatuses = ['pending', 'confirmed', 'in-progress', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $dataToUpdate = ['sb_status' => $status];
        if ($this->timestamps) {
            $dataToUpdate[$this->updatedAtColumn] = date('Y-m-d H:i:s');
        }
        
        $formattedUpdate = $this->formatUpdateData($dataToUpdate);
        
        $sql = "UPDATE {$this->table} 
                SET {$formattedUpdate['updateClause']}
                WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
        
        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }

        $params = $formattedUpdate['filteredData'];
        $params['_primaryKeyValueBinding'] = $bookingId;
        
        return $this->execute($sql, $params) > 0;
    }

    // Update the priority of a service booking.
    public function updateBookingPriority($bookingId, $priority)
    {
        $allowedPriorities = ['normal', 'moderate', 'urgent'];
        if (!in_array($priority, $allowedPriorities)) {
            // error_log("Invalid booking priority: " . $priority);
            return false;
        }

        $dataToUpdate = ['sb_priority' => $priority];
        if ($this->timestamps) {
            $dataToUpdate[$this->updatedAtColumn] = date('Y-m-d H:i:s');
        }
        
        $formattedUpdate = $this->formatUpdateData($dataToUpdate);
        
        $sql = "UPDATE {$this->table} 
                SET {$formattedUpdate['updateClause']}
                WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
        
        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }

        $params = $formattedUpdate['filteredData'];
        $params['_primaryKeyValueBinding'] = $bookingId;
        
        return $this->execute($sql, $params) > 0;
    }
    
    // Cancel a service booking by setting its status to 'cancelled'.
    public function cancelBooking($bookingId)
    {
        return $this->updateBookingStatus($bookingId, 'cancelled');
    }
    
    // Delete a service booking.
    // Performs a soft delete if $useSoftDeletes is true, otherwise a hard delete.
    public function deleteBooking($bookingId)
    {
        if ($this->useSoftDeletes) {
            $dataToUpdate = [$this->deletedAtColumn => date('Y-m-d H:i:s')];
            if ($this->timestamps) {
                $dataToUpdate[$this->updatedAtColumn] = date('Y-m-d H:i:s');
            }

            $formattedUpdate = $this->formatUpdateData($dataToUpdate);
            
            if (empty($formattedUpdate['updateClause'])) {
                return false; 
            }

            $sql = "UPDATE {$this->table} 
                    SET {$formattedUpdate['updateClause']}
                    WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
            // No "AND {$this->deletedAtColumn} IS NULL" for soft delete action itself.

            $params = $formattedUpdate['filteredData'];
            $params['_primaryKeyValueBinding'] = $bookingId;
            
            return $this->execute($sql, $params) > 0;

        } else {
            // Hard delete
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
            $params = ['_primaryKeyValueBinding' => $bookingId];
            return $this->execute($sql, $params) > 0;
        }
    }

    // Get a single service booking by its ID.
    public function getBookingById($bookingId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :bookingId";
        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }
        return $this->queryOne($sql, ['bookingId' => $bookingId]);
    }

    // Get all active (non-deleted) service bookings.
    public function getAllActiveBookings($orderBy = ['sb_preferred_date' => 'DESC', 'sb_preferred_time' => 'DESC'])
    {
        $sql = "SELECT * FROM {$this->table}";
        $whereClauses = [];
        $params = [];

        if ($this->useSoftDeletes) {
            $whereClauses[] = "{$this->deletedAtColumn} IS NULL";
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                if (preg_match('/^[a-zA-Z0-9_]+$/', $column) && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                    $orderParts[] = "{$column} {$direction}";
                }
            }
            if (!empty($orderParts)) {
                $sql .= " ORDER BY " . implode(", ", $orderParts);
            }
        }
        
        return $this->query($sql, $params);
    }

    // Get service bookings based on a set of criteria.
    public function getBookingsByCriteria(array $criteria, $orderBy = ['sb_preferred_date' => 'DESC', 'sb_preferred_time' => 'DESC'])
    {
        $sql = "SELECT DISTINCT sb.* FROM {$this->table} sb";
        $whereClauses = [];
        $params = [];

        $needTechnicianJoin = isset($criteria['technician_id']) || isset($criteria['has_technician']);
        if ($needTechnicianJoin) {
            $sql .= " LEFT JOIN booking_assignment ba ON sb.sb_id = ba.ba_booking_id";
        }

        if ($this->useSoftDeletes) {
            $whereClauses[] = "sb.{$this->deletedAtColumn} IS NULL";
        }

        $allowedFilterColumns = [
            'sb_customer_id', 'sb_service_type_id', 'sb_preferred_date', 
            'sb_status', 'sb_priority'
        ];

        foreach ($criteria as $column => $value) {
            if ($column === 'technician_id') {
                $whereClauses[] = "ba.ba_technician_id = :technician_id";
                $params[':technician_id'] = $value;
                continue;
            }

            if ($column === 'has_technician') {
                if ($value === true) {
                    $whereClauses[] = "ba.ba_id IS NOT NULL";
                } else {
                    $whereClauses[] = "ba.ba_id IS NULL";
                }
                continue;
            }

            $columnLower = strtolower($column);
            if (in_array($columnLower, $allowedFilterColumns)) {
                $actualColumn = $column;
                
                $paramKey = ":" . $columnLower . "_filt"; // e.g. :sb_status_filt

                if ($actualColumn === 'sb_preferred_date' && is_array($value) && isset($value['from']) && isset($value['to'])) {
                    $whereClauses[] = "{$actualColumn} BETWEEN {$paramKey}_from AND {$paramKey}_to";
                    $params["{$paramKey}_from"] = $value['from'];
                    $params["{$paramKey}_to"] = $value['to'];
                } elseif (is_array($value) && !empty($value)) { // For IN (...) clauses
                    $whereClauses[] = "{$actualColumn} IN ({$paramKey})"; // Base Model query() handles array to IN expansion
                    $params[$paramKey] = $value;
                } else {
                    $whereClauses[] = "sb.{$actualColumn} = {$paramKey}";
                    $params[$paramKey] = $value;
                }
            }
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                if (preg_match('/^[a-zA-Z0-9_.]+$/', $column) && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                    $orderParts[] = "sb.{$column} {$direction}";
                }
            }
            if (!empty($orderParts)) {
                $sql .= " ORDER BY " . implode(", ", $orderParts);
            }
        }
        
        return $this->query($sql, $params);
    }

    // Count service bookings, optionally filtered by criteria.
    public function countBookings(array $criteria = [])
    {
        $sql = "SELECT COUNT(DISTINCT sb.sb_id) FROM {$this->table} sb";
        $whereClauses = [];
        $params = [];

        // Add JOIN for technician filtering if needed
        $needTechnicianJoin = isset($criteria['technician_id']) || isset($criteria['has_technician']);
        if ($needTechnicianJoin) {
            $sql .= " LEFT JOIN booking_assignment ba ON sb.sb_id = ba.ba_booking_id";
        }

        if ($this->useSoftDeletes) {
            $whereClauses[] = "sb.{$this->deletedAtColumn} IS NULL";
        }
        
        $allowedFilterColumns = [
            'sb_customer_id', 'sb_service_type_id', 'sb_preferred_date', 
            'sb_status', 'sb_priority'
        ];

        foreach ($criteria as $column => $value) {
            if ($column === 'technician_id') {
                $whereClauses[] = "ba.ba_technician_id = :technician_id";
                $params[':technician_id'] = $value;
                continue;
            }

            if ($column === 'has_technician') {
                if ($value === true) {
                    $whereClauses[] = "ba.ba_id IS NOT NULL";
                } else {
                    $whereClauses[] = "ba.ba_id IS NULL";
                }
                continue;
            }
            
            $columnLower = strtolower($column);
            if (in_array($columnLower, $allowedFilterColumns)) {
                $actualColumn = $column;
                $paramKey = ":" . $columnLower . "_filt";

                if ($actualColumn === 'sb_preferred_date' && is_array($value) && isset($value['from']) && isset($value['to'])) {
                    $whereClauses[] = "sb.{$actualColumn} BETWEEN {$paramKey}_from AND {$paramKey}_to";
                    $params["{$paramKey}_from"] = $value['from'];
                    $params["{$paramKey}_to"] = $value['to'];
                } elseif (is_array($value) && !empty($value)) {
                    $whereClauses[] = "sb.{$actualColumn} IN ({$paramKey})";
                    $params[$paramKey] = $value;
                } else {
                    $whereClauses[] = "sb.{$actualColumn} = {$paramKey}";
                    $params[$paramKey] = $value;
                }
            }
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        return (int) $this->queryScalar($sql, $params);
    }

    // Get bookings by a specific status.
    public function getBookingsByStatus($status, $orderBy = ['sb_preferred_date' => 'DESC', 'sb_preferred_time' => 'DESC'])
    {
        return $this->getBookingsByCriteria(['sb_status' => $status], $orderBy);
    }

    // Get bookings for a specific service type.
    public function getBookingsByServiceType($serviceTypeId, $orderBy = ['sb_preferred_date' => 'DESC', 'sb_preferred_time' => 'DESC'])
    {
        return $this->getBookingsByCriteria(['sb_service_type_id' => $serviceTypeId], $orderBy);
    }

    // Get bookings within a specific date range (based on sb_preferred_date).
    public function getBookingsByDateRange($startDate, $endDate, $orderBy = ['sb_preferred_date' => 'DESC', 'sb_preferred_time' => 'DESC'])
    {
        $criteria = ['sb_preferred_date' => ['from' => $startDate, 'to' => $endDate]];
        return $this->getBookingsByCriteria($criteria, $orderBy);
    }

    // Check if a customer has an active, non-completed/cancelled booking at a specific date and time.
    public function hasConflictingBooking($customerId, $preferredDate, $preferredTime)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE sb_customer_id = :customerId 
                AND sb_preferred_date = :preferredDate 
                AND sb_preferred_time = :preferredTime
                AND sb_status NOT IN ('completed', 'cancelled')";

        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }

        $params = [
            'customerId' => $customerId,
            'preferredDate' => $preferredDate,
            'preferredTime' => $preferredTime
        ];
        
        return $this->queryScalar($sql, $params) > 0;
    }

    // Get service bookings assigned to a specific technician.
    public function getBookingsForTechnician($technicianId, $filters = [], $orderBy = ['sb.sb_preferred_date' => 'ASC', 'sb.sb_preferred_time' => 'ASC'])
    {
        $sql = "SELECT sb.* 
                FROM {$this->table} sb
                JOIN booking_assignment ba ON sb.sb_id = ba.ba_booking_id
                WHERE ba.ba_technician_id = :technicianId";
        
        $params = ['technicianId' => $technicianId];

        if ($this->useSoftDeletes) {
            // Alias sb is important here
            $sql .= " AND sb.{$this->deletedAtColumn} IS NULL";
        }

        if (!empty($filters['assignment_status'])) {
            $sql .= " AND ba.ba_status = :assignmentStatus";
            $params['assignmentStatus'] = $filters['assignment_status'];
        } else {
            // Default to only show active assignments if no specific status filter
            $sql .= " AND ba.ba_status IN ('assigned', 'in-progress')";
        }

        if (!empty($filters['booking_status'])) {
            $sql .= " AND sb.sb_status = :bookingStatus";
            $params['bookingStatus'] = $filters['booking_status'];
        }
        
        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                // Ensure column has alias if it could be ambiguous (e.g., sb.sb_id)
                if (preg_match('/^[a-zA-Z0-9_.]+$/', $column) && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                    $orderParts[] = "{$column} {$direction}";
                }
            }
            if (!empty($orderParts)) {
                $sql .= " ORDER BY " . implode(", ", $orderParts);
            }
        }
        
        return $this->query($sql, $params);
    }

    // Get service bookings that are not actively assigned to any technician.
    // "Actively assigned" means an assignment exists with status 'assigned' or 'in-progress'.
    public function getUnassignedBookings($bookingStatus = 'confirmed', $orderBy = ['sb.sb_preferred_date' => 'ASC', 'sb.sb_preferred_time' => 'ASC'])
    {
        $sql = "SELECT sb.*
                FROM {$this->table} sb
                WHERE sb.sb_status = :bookingStatus
                AND NOT EXISTS (
                    SELECT 1 
                    FROM booking_assignment ba 
                    WHERE ba.ba_booking_id = sb.sb_id 
                    AND ba.ba_status IN ('assigned', 'in-progress')
                )";

        if ($this->useSoftDeletes) {
            $sql .= " AND sb.{$this->deletedAtColumn} IS NULL";
        }
        
        $orderClause = "";
        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                if (preg_match('/^[a-zA-Z0-9_.]+$/', $column) && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                     $orderParts[] = "{$column} {$direction}";
                }
            }
            if (!empty($orderParts)) {
                $orderClause = " ORDER BY " . implode(", ", $orderParts);
            }
        }
        $sql .= $orderClause;
        
        return $this->query($sql, ['bookingStatus' => $bookingStatus]);
    }
}