<?php

namespace App\Models;

class BookingAssignmentModel extends Model
{
    protected $table = 'booking_assignment';
    protected $primaryKey = 'ba_id';

    protected $fillable = [
        'ba_booking_id',
        'ba_technician_id',
        'ba_assigned_at',
        'ba_status',
        'ba_notes',
        'ba_completed_at'
    ];

    protected $timestamps = true;
    protected $createdAtColumn = 'ba_assigned_at';
    protected $updatedAtColumn = null; // No updated_at column for this table

    // Get all assignments for a specific booking
    public function getAssignmentsForBooking($bookingId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ba_booking_id = :bookingId 
                AND ba_status IN ('assigned', 'in-progress', 'completed')
                ORDER BY ba_assigned_at DESC";
        return $this->query($sql, ['bookingId' => $bookingId]);
    }

    // Get all assignments for a specific technician
    public function getAssignmentsForTechnician($technicianId, $status = null)
    {
        $sql = "SELECT booking_assignment.*, 
                service_booking.sb_service_type_id, service_booking.sb_preferred_date, 
                service_booking.sb_preferred_time, service_booking.sb_address, 
                service_booking.sb_description, service_booking.sb_status as booking_status,
                service_type.st_name as service_type_name,
                CONCAT(user_account.ua_first_name, ' ', user_account.ua_last_name) as customer_name
                FROM {$this->table} booking_assignment
                INNER JOIN service_booking ON booking_assignment.ba_booking_id = service_booking.sb_id
                INNER JOIN service_type ON service_booking.sb_service_type_id = service_type.st_id
                INNER JOIN customer ON service_booking.sb_customer_id = customer.cu_account_id
                INNER JOIN user_account ON customer.cu_account_id = user_account.ua_id
                WHERE booking_assignment.ba_technician_id = :technicianId";
        
        $params = ['technicianId' => $technicianId];
        
        if ($status !== null) {
            $sql .= " AND booking_assignment.ba_status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY service_booking.sb_preferred_date DESC, service_booking.sb_preferred_time DESC";
        
        return $this->query($sql, $params);
    }

    // Check for scheduling conflicts for a technician
    public function hasSchedulingConflict($technicianId, $bookingId)
    {
        // First, get the details of the booking we want to assign
        $sql = "SELECT sb_preferred_date, sb_preferred_time FROM service_booking 
                WHERE sb_id = :bookingId";
        
        $bookingDetails = $this->queryOne($sql, ['bookingId' => $bookingId]);
        
        if (!$bookingDetails) {
            return ['conflict' => true, 'message' => 'Booking not found'];
        }
        
        $bookingDate = $bookingDetails['sb_preferred_date'];
        $bookingTime = $bookingDetails['sb_preferred_time'];
        
        // Define time buffer (3 hours before and after the booking time)
        $timeBuffer = 3; // hours
        
        // Calculate buffer times
        $bookingTimeObj = new \DateTime($bookingTime);
        $startTimeObj = clone $bookingTimeObj;
        $startTimeObj->modify("-{$timeBuffer} hours");
        $endTimeObj = clone $bookingTimeObj;
        $endTimeObj->modify("+{$timeBuffer} hours");
        
        $bufferStart = $startTimeObj->format('H:i:s');
        $bufferEnd = $endTimeObj->format('H:i:s');
        
        // Check for conflicts with other service bookings
        $sql = "SELECT ba.ba_id, sb.sb_preferred_date, sb.sb_preferred_time, st.st_name as service_type 
                FROM {$this->table} ba
                JOIN service_booking sb ON ba.ba_booking_id = sb.sb_id
                JOIN service_type st ON sb.sb_service_type_id = st.st_id
                WHERE ba.ba_technician_id = :technicianId
                AND ba.ba_status IN ('assigned', 'in-progress')
                AND sb.sb_preferred_date = :bookingDate
                AND sb.sb_preferred_time BETWEEN :bufferStart AND :bufferEnd";
        
        $params = [
            'technicianId' => $technicianId,
            'bookingDate' => $bookingDate,
            'bufferStart' => $bufferStart,
            'bufferEnd' => $bufferEnd
        ];
        
        $conflicts = $this->query($sql, $params);
        
        if (!empty($conflicts)) {
            return [
                'conflict' => true, 
                'message' => 'Technician has a service booking conflict on this date and time',
                'conflicts' => $conflicts
            ];
        }
        
        // Check for conflicts with product bookings
        $sql = "SELECT pa.pa_id, pb.pb_preferred_date, pb.pb_preferred_time,
                p.prod_name as product_name, pv.var_capacity as product_capacity
                FROM product_assignment pa
                JOIN product_booking pb ON pa.pa_order_id = pb.pb_id
                JOIN product_variant pv ON pb.pb_variant_id = pv.var_id
                JOIN product p ON pv.prod_id = p.prod_id
                WHERE pa.pa_technician_id = :technicianId
                AND pa.pa_status IN ('assigned', 'in-progress')
                AND pb.pb_preferred_date = :bookingDate
                AND pb.pb_preferred_time BETWEEN :bufferStart AND :bufferEnd";
        
        $productConflicts = $this->query($sql, $params);
        
        if (!empty($productConflicts)) {
            return [
                'conflict' => true, 
                'message' => 'Technician has a product booking conflict on this date and time',
                'conflicts' => $productConflicts
            ];
        }
        
        return ['conflict' => false];
    }

    // Add a new assignment
    public function addAssignment($data)
    {
        // Ensure required fields are present
        if (empty($data['ba_booking_id']) || empty($data['ba_technician_id'])) {
            error_log("Missing required fields for booking assignment");
            return false;
        }
        
        try {
            // Set default values if not provided
            if (!isset($data['ba_status'])) {
                $data['ba_status'] = 'assigned';
            }
            
            if (!isset($data['ba_assigned_at'])) {
                $data['ba_assigned_at'] = date('Y-m-d H:i:s');
            }
            
            // Check if assignment already exists
            $existing = $this->queryOne(
                "SELECT * FROM {$this->table} WHERE ba_booking_id = :bookingId AND ba_technician_id = :technicianId",
                [
                    'bookingId' => $data['ba_booking_id'],
                    'technicianId' => $data['ba_technician_id']
                ]
            );
            
            if ($existing) {
                // Debug log
                error_log("Assignment already exists for booking {$data['ba_booking_id']} and technician {$data['ba_technician_id']}");
                
                // If assignment exists but was cancelled, reactivate it
                if ($existing['ba_status'] === 'cancelled') {
                    error_log("Reactivating cancelled assignment");
                    return $this->updateAssignment($existing[$this->primaryKey], [
                        'ba_status' => 'assigned',
                        'ba_assigned_at' => date('Y-m-d H:i:s')
                    ]);
                }
                
                // Assignment already exists and is active
                return $existing[$this->primaryKey];
            }
            
            // Format insert data
            $formattedInsert = $this->formatInsertData($data);
            
            // Debug log
            error_log("Inserting new assignment: " . json_encode($formattedInsert));
            
            $sql = "INSERT INTO {$this->table} ({$formattedInsert['columns']}) 
                    VALUES ({$formattedInsert['placeholders']})";
            
            $result = $this->execute($sql, $formattedInsert['filteredData']);
            
            if ($result <= 0) {
                error_log("Failed to insert assignment record");
                return false;
            }
            
            // Get the sequence name for PostgreSQL
            $sequenceName = "{$this->table}_{$this->primaryKey}_seq";
            
            // Debug log
            error_log("Using sequence name: " . $sequenceName);
            
            $insertId = $this->lastInsertId($sequenceName);
            error_log("Inserted assignment with ID: " . $insertId);
            
            return $insertId;
        } catch (\Exception $e) {
            error_log("Error inserting booking assignment: " . $e->getMessage());
            return false;
        }
    }

    // Update an assignment
    public function updateAssignment($assignmentId, $data)
    {
        if (empty($data)) {
            return true; // No data to update, considered successful
        }
        
        $formattedUpdate = $this->formatUpdateData($data);
        
        if (empty($formattedUpdate['updateClause'])) {
            return true;
        }
        
        $sql = "UPDATE {$this->table} 
                SET {$formattedUpdate['updateClause']}
                WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
                
        $params = $formattedUpdate['filteredData'];
        $params['_primaryKeyValueBinding'] = $assignmentId;
        
        return $this->execute($sql, $params) > 0;
    }

    // Update assignment status
    public function updateAssignmentStatus($assignmentId, $status)
    {
        $allowedStatuses = ['assigned', 'in-progress', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }
        
        $data = ['ba_status' => $status];
        
        // Set completed_at timestamp if status is completed
        if ($status === 'completed' && empty($data['ba_completed_at'])) {
            $data['ba_completed_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->updateAssignment($assignmentId, $data);
    }

    // Remove an assignment between a booking and technician
    public function removeAssignment($bookingId, $technicianId)
    {
        $sql = "UPDATE {$this->table} 
                SET ba_status = 'cancelled'
                WHERE ba_booking_id = :bookingId 
                AND ba_technician_id = :technicianId
                AND ba_status IN ('assigned', 'in-progress')";
                
        $params = [
            'bookingId' => $bookingId,
            'technicianId' => $technicianId
        ];
        
        return $this->execute($sql, $params) > 0;
    }

    // Delete an assignment between a booking and technician (completely removes the record)
    public function deleteAssignment($bookingId, $technicianId)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE ba_booking_id = :bookingId 
                AND ba_technician_id = :technicianId";
                
        $params = [
            'bookingId' => $bookingId,
            'technicianId' => $technicianId
        ];
        
        return $this->execute($sql, $params) > 0;
    }
    
    // Remove all technicians from a booking (deletes the records)
    public function removeAllTechnicians($bookingId)
    {
        $sql = "DELETE FROM {$this->table} WHERE ba_booking_id = :bookingId";
        return $this->execute($sql, [':bookingId' => $bookingId]);
    }

    // Check if a technician is assigned to a booking
    public function isAssigned($bookingId, $technicianId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE ba_booking_id = :bookingId 
                AND ba_technician_id = :technicianId
                AND ba_status IN ('assigned', 'in-progress')";
                
        $params = [
            'bookingId' => $bookingId,
            'technicianId' => $technicianId
        ];
        
        return $this->queryScalar($sql, $params) > 0;
    }

    // Update notes for a specific assignment
    public function updateAssignmentNotes($bookingId, $technicianId, $notes)
    {
        $sql = "UPDATE {$this->table} 
                SET ba_notes = :notes
                WHERE ba_booking_id = :bookingId 
                AND ba_technician_id = :technicianId
                AND ba_status IN ('assigned', 'in-progress')"; // Ensure we only update active assignments
                
        $params = [
            'notes' => $notes,
            'bookingId' => $bookingId,
            'technicianId' => $technicianId
        ];
        
        return $this->execute($sql, $params) >= 0; // Allow 0 if notes are unchanged
    }
}