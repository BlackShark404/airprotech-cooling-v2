<?php

namespace App\Models;

class TechnicianModel extends Model
{
    protected $table = 'technician';
    protected $primaryKey = 'te_account_id';

    protected $fillable = [
        'te_account_id',
        'te_is_available'
    ];
    
    // Update a technician record
    public function update($data, $where, $params = [])
    {
        $formattedUpdate = $this->formatUpdateData($data);
        
        if (empty($formattedUpdate['updateClause'])) {
            return true; // No data to update
        }
        
        $sql = "UPDATE {$this->table} SET {$formattedUpdate['updateClause']} WHERE {$where}";
        $allParams = array_merge($formattedUpdate['filteredData'], $params);
        
        return $this->execute($sql, $allParams) !== false;
    }
    
    // Get all available technicians
    public function getAvailableTechnicians()
    {
        $sql = "SELECT t.*, 
                u.ua_first_name, u.ua_last_name, u.ua_email, u.ua_phone_number,
                CONCAT(u.ua_first_name, ' ', u.ua_last_name) as full_name
                FROM {$this->table} t
                INNER JOIN user_account u ON t.te_account_id = u.ua_id
                WHERE t.te_is_available = true
                AND u.ua_is_active = true
                ORDER BY u.ua_first_name, u.ua_last_name";
                
        return $this->query($sql);
    }
    
    // Get technician's schedule for a date range
    public function getTechnicianSchedule($technicianId, $startDate, $endDate)
    {
        // Validate inputs
        if (!$technicianId || !$startDate || !$endDate) {
            return ['error' => 'Missing required parameters'];
        }
        
        $schedule = [];
        
        // Get service booking assignments
        $serviceSql = "SELECT 
                ba.ba_id, ba.ba_booking_id, ba.ba_status, ba.ba_notes,
                sb.sb_preferred_date, sb.sb_preferred_time, sb.sb_address,
                st.st_name as service_type,
                'service' as booking_type,
                CONCAT(ua.ua_first_name, ' ', ua.ua_last_name) as customer_name
            FROM booking_assignment ba
            JOIN service_booking sb ON ba.ba_booking_id = sb.sb_id
            JOIN service_type st ON sb.sb_service_type_id = st.st_id
            JOIN customer c ON sb.sb_customer_id = c.cu_account_id
            JOIN user_account ua ON c.cu_account_id = ua.ua_id
            WHERE ba.ba_technician_id = :technicianId
            AND ba.ba_status IN ('assigned', 'in-progress')
            AND sb.sb_preferred_date BETWEEN :startDate AND :endDate
            ORDER BY sb.sb_preferred_date ASC, sb.sb_preferred_time ASC";
        
        $serviceParams = [
            'technicianId' => $technicianId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
        $serviceBookings = $this->query($serviceSql, $serviceParams);
        
        if ($serviceBookings) {
            $schedule = array_merge($schedule, $serviceBookings);
        }
        
        // Get product booking assignments
        $productSql = "SELECT 
                pa.pa_id, pa.pa_order_id as booking_id, pa.pa_status, pa.pa_notes,
                pb.pb_preferred_date, pb.pb_preferred_time, pb.pb_address,
                CONCAT(p.prod_name, ' (', pv.var_capacity, ')') as product_info,
                'product' as booking_type,
                CONCAT(ua.ua_first_name, ' ', ua.ua_last_name) as customer_name
            FROM product_assignment pa
            JOIN product_booking pb ON pa.pa_order_id = pb.pb_id
            JOIN product_variant pv ON pb.pb_variant_id = pv.var_id
            JOIN product p ON pv.prod_id = p.prod_id
            JOIN customer c ON pb.pb_customer_id = c.cu_account_id
            JOIN user_account ua ON c.cu_account_id = ua.ua_id
            WHERE pa.pa_technician_id = :technicianId
            AND pa.pa_status IN ('assigned', 'in-progress')
            AND pb.pb_preferred_date BETWEEN :startDate AND :endDate
            ORDER BY pb.pb_preferred_date ASC, pb.pb_preferred_time ASC";
        
        $productBookings = $this->query($productSql, $serviceParams); // Reuse the same params
        
        if ($productBookings) {
            $schedule = array_merge($schedule, $productBookings);
        }
        
        // Sort the combined schedule by date and time
        usort($schedule, function($a, $b) {
            $dateA = $a['sb_preferred_date'] ?? $a['pb_preferred_date'];
            $dateB = $b['sb_preferred_date'] ?? $b['pb_preferred_date'];
            
            $timeA = $a['sb_preferred_time'] ?? $a['pb_preferred_time'];
            $timeB = $b['sb_preferred_time'] ?? $b['pb_preferred_time'];
            
            $datetimeA = $dateA . ' ' . $timeA;
            $datetimeB = $dateB . ' ' . $timeB;
            
            return strcmp($datetimeA, $datetimeB);
        });
        
        return $schedule;
    }
    
    // Create a new assignment for a technician
    public function createAssignment($data)
    {
        // Check if this is a service booking or product booking
        if (isset($data['ba_booking_id'])) {
            // Service booking assignment
            $bookingAssignmentModel = new BookingAssignmentModel();
            return $bookingAssignmentModel->addAssignment($data);
        } else if (isset($data['pa_order_id'])) {
            // Product booking assignment
            $productAssignmentModel = new ProductAssignmentModel();
            return $productAssignmentModel->createAssignment($data);
        }
        
        return false;
    }
}