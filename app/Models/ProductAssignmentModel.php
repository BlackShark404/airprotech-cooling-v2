<?php

namespace App\Models;

class ProductAssignmentModel extends Model
{
    protected $table = 'product_assignment';
    protected $primaryKey = 'pa_id';
    protected $useSoftDeletes = false;
    protected $timestamps = true;
    protected $createdAtColumn = 'pa_assigned_at';
    protected $updatedAtColumn = 'pa_updated_at';
    
    protected $fillable = [
        'pa_order_id',
        'pa_technician_id',
        'pa_status',
        'pa_notes',
        'pa_started_at',
        'pa_completed_at'
    ];

    // Get all product assignments for a specific technician
    public function getAssignmentsByTechnician($technicianId)
    {
        $sql = "SELECT product_assignment.*, product_booking.pb_variant_id, 
                product_booking.pb_quantity, product_booking.pb_unit_price,
                product_booking.pb_total_amount, product_booking.pb_status as booking_status,
                product_booking.pb_preferred_date, product_booking.pb_preferred_time,
                product_booking.pb_address, product_booking.pb_description,
                CONCAT(user_account.ua_first_name, ' ', user_account.ua_last_name) as customer_name,
                product.prod_name, product_variant.var_capacity
                FROM {$this->table}
                JOIN product_booking ON product_assignment.pa_order_id = product_booking.pb_id
                JOIN product_variant ON product_booking.pb_variant_id = product_variant.var_id
                JOIN product ON product_variant.prod_id = product.prod_id
                JOIN customer ON product_booking.pb_customer_id = customer.cu_account_id
                JOIN user_account ON customer.cu_account_id = user_account.ua_id
                WHERE product_assignment.pa_technician_id = :technicianId
                ORDER BY product_booking.pb_preferred_date DESC, product_booking.pb_preferred_time DESC";
        
        return $this->query($sql, ['technicianId' => $technicianId]);
    }

    // Get all assignments for a specific product booking
    public function getAssignmentsByBooking($bookingId)
    {
        $sql = "SELECT product_assignment.*, 
                CONCAT(user_account.ua_first_name, ' ', user_account.ua_last_name) as technician_name
                FROM {$this->table}
                JOIN technician ON product_assignment.pa_technician_id = technician.te_account_id
                JOIN user_account ON technician.te_account_id = user_account.ua_id
                WHERE product_assignment.pa_order_id = :bookingId";
        
        return $this->query($sql, ['bookingId' => $bookingId]);
    }
    
    // Check for scheduling conflicts for a technician
    public function hasSchedulingConflict($technicianId, $productBookingId)
    {
        // First, get the details of the product booking we want to assign
        $sql = "SELECT pb_preferred_date, pb_preferred_time FROM product_booking 
                WHERE pb_id = :bookingId";
        
        $bookingDetails = $this->queryOne($sql, ['bookingId' => $productBookingId]);
        
        if (!$bookingDetails) {
            return ['conflict' => true, 'message' => 'Product booking not found'];
        }
        
        $bookingDate = $bookingDetails['pb_preferred_date'];
        $bookingTime = $bookingDetails['pb_preferred_time'];
        
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
        
        // Check for conflicts with other product bookings
        $sql = "SELECT pa.pa_id, pb.pb_preferred_date, pb.pb_preferred_time, 
                p.prod_name, pv.var_capacity
                FROM {$this->table} pa
                JOIN product_booking pb ON pa.pa_order_id = pb.pb_id
                JOIN product_variant pv ON pb.pb_variant_id = pv.var_id
                JOIN product p ON pv.prod_id = p.prod_id
                WHERE pa.pa_technician_id = :technicianId
                AND pa.pa_status IN ('assigned', 'in-progress')
                AND pb.pb_preferred_date = :bookingDate
                AND pb.pb_preferred_time BETWEEN :bufferStart AND :bufferEnd";
        
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
                'message' => 'Technician has a product booking conflict on this date and time',
                'conflicts' => $conflicts
            ];
        }
        
        // Check for conflicts with service bookings
        $sql = "SELECT ba.ba_id, sb.sb_preferred_date, sb.sb_preferred_time, 
                st.st_name as service_type
                FROM booking_assignment ba
                JOIN service_booking sb ON ba.ba_booking_id = sb.sb_id
                JOIN service_type st ON sb.sb_service_type_id = st.st_id
                WHERE ba.ba_technician_id = :technicianId
                AND ba.ba_status IN ('assigned', 'in-progress')
                AND sb.sb_preferred_date = :bookingDate
                AND sb.sb_preferred_time BETWEEN :bufferStart AND :bufferEnd";
        
        $serviceConflicts = $this->query($sql, $params);
        
        if (!empty($serviceConflicts)) {
            return [
                'conflict' => true, 
                'message' => 'Technician has a service booking conflict on this date and time',
                'conflicts' => $serviceConflicts
            ];
        }
        
        return ['conflict' => false];
    }
    
    // Get all assignments for a specific product booking order
    public function getAssignmentsByOrderId($orderId)
    {
        $sql = "SELECT product_assignment.*, 
                CONCAT(user_account.ua_first_name, ' ', user_account.ua_last_name) as technician_name
                FROM {$this->table}
                JOIN technician ON product_assignment.pa_technician_id = technician.te_account_id
                JOIN user_account ON technician.te_account_id = user_account.ua_id
                WHERE product_assignment.pa_order_id = :orderId";
        
        return $this->query($sql, ['orderId' => $orderId]);
    }

    // Create a new product assignment
    public function createAssignment($data)
    {
        $formattedInsert = $this->formatInsertData($data);
            
        $sql = "INSERT INTO {$this->table} ({$formattedInsert['columns']}) 
                VALUES ({$formattedInsert['placeholders']})";
        
        $result = $this->execute($sql, $formattedInsert['filteredData']);
        
        if ($result <= 0) {
            return false;
        }
        
        // Get the sequence name for PostgreSQL
        $sequenceName = "{$this->table}_{$this->primaryKey}_seq";
        
        return $this->lastInsertId($sequenceName);
    }

    // Update a product assignment
    public function updateAssignment($id, $data)
    {
        $formattedUpdate = $this->formatUpdateData($data);
        
        if (empty($formattedUpdate['updateClause'])) {
            return true;
        }
        
        $sql = "UPDATE {$this->table} 
                SET {$formattedUpdate['updateClause']}
                WHERE {$this->primaryKey} = :_primaryKeyValueBinding";
                
        $params = $formattedUpdate['filteredData'];
        $params['_primaryKeyValueBinding'] = $id;
        
        return $this->execute($sql, $params) > 0;
    }

    // Delete a product assignment
    public function deleteAssignment($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->execute($sql, ['id' => $id]) > 0;
    }
    
    // Delete a product assignment by booking ID and technician ID
    public function deleteAssignmentByOrderAndTechnician($bookingId, $technicianId)
    {
        $sql = "DELETE FROM {$this->table} WHERE pa_order_id = :booking_id AND pa_technician_id = :technician_id";
        $params = ['booking_id' => $bookingId, 'technician_id' => $technicianId];
        
        return $this->execute($sql, $params);
    }
    
    // Update notes for a specific assignment
    public function updateAssignmentNotes($bookingId, $technicianId, $data)
    {
        if (!isset($data['PA_NOTES'])) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET pa_notes = :notes
                WHERE pa_order_id = :booking_id AND pa_technician_id = :technician_id";
                
        $params = [
            'notes' => $data['PA_NOTES'],
            'booking_id' => $bookingId,
            'technician_id' => $technicianId
        ];
        
        return $this->execute($sql, $params) >= 0; // Allow 0 if notes are unchanged
    }
} 