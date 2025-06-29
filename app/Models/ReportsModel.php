<?php

namespace App\Models;

class ReportsModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    // Get service request statistics by status
    public function getServiceRequestsByStatus()
    {
        $sql = "SELECT 
                    sb_status as status, 
                    COUNT(*) as count 
                FROM service_booking 
                WHERE sb_deleted_at IS NULL 
                GROUP BY sb_status 
                ORDER BY count DESC";
        
        try {
            return $this->query($sql);
        } catch (\Exception $e) {
            error_log("Error getting service requests by status: " . $e->getMessage());
            return [];
        }
    }
    
    // Get service request statistics by type
    public function getServiceRequestsByType()
    {
        $sql = "SELECT 
                    st.st_name as type_name, 
                    COUNT(sb.sb_id) as count 
                FROM service_booking sb
                JOIN service_type st ON sb.sb_service_type_id = st.st_id
                WHERE sb.sb_deleted_at IS NULL 
                GROUP BY st.st_name
                ORDER BY count DESC";
        
        try {
            return $this->query($sql);
        } catch (\Exception $e) {
            error_log("Error getting service requests by type: " . $e->getMessage());
            return [];
        }
    }
    
    // Get service requests count by month for a given year
    public function getServiceRequestsByMonth($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        
        $sql = "SELECT 
                    EXTRACT(MONTH FROM sb_created_at) as month,
                    COUNT(*) as count
                FROM service_booking
                WHERE 
                    EXTRACT(YEAR FROM sb_created_at) = :year
                    AND sb_deleted_at IS NULL
                GROUP BY month
                ORDER BY month";
        
        try {
            return $this->query($sql, ['year' => $year]);
        } catch (\Exception $e) {
            error_log("Error getting service requests by month: " . $e->getMessage());
            return [];
        }
    }
    
    // Get product booking statistics by status
    public function getProductBookingsByStatus()
    {
        $sql = "SELECT 
                    pb_status as status, 
                    COUNT(*) as count 
                FROM product_booking 
                WHERE pb_deleted_at IS NULL 
                GROUP BY pb_status 
                ORDER BY count DESC";
        
        try {
            return $this->query($sql);
        } catch (\Exception $e) {
            error_log("Error getting product bookings by status: " . $e->getMessage());
            return [];
        }
    }
    
    // Get product bookings count by month for a given year
    public function getProductBookingsByMonth($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        
        $sql = "SELECT 
                    EXTRACT(MONTH FROM pb_order_date) as month,
                    COUNT(*) as count
                FROM product_booking
                WHERE 
                    EXTRACT(YEAR FROM pb_order_date) = :year
                    AND pb_deleted_at IS NULL
                GROUP BY month
                ORDER BY month";
        
        try {
            return $this->query($sql, ['year' => $year]);
        } catch (\Exception $e) {
            error_log("Error getting product bookings by month: " . $e->getMessage());
            return [];
        }
    }
    
    // Get top selling products
    public function getTopSellingProducts($limit = 5)
    {
        $sql = "SELECT 
                    p.prod_name as product_name,
                    SUM(pb.pb_quantity) as total_quantity
                FROM product_booking pb
                JOIN product_variant pv ON pb.pb_variant_id = pv.var_id
                JOIN product p ON pv.prod_id = p.prod_id
                WHERE pb.pb_deleted_at IS NULL
                GROUP BY p.prod_name
                ORDER BY total_quantity DESC
                LIMIT :limit";
        
        try {
            return $this->query($sql, ['limit' => $limit]);
        } catch (\Exception $e) {
            error_log("Error getting top selling products: " . $e->getMessage());
            return [];
        }
    }
    
    // Get technician performance statistics
    public function getTechnicianPerformance()
    {
        $sql = "SELECT 
                    technician_name,
                    SUM(total_assignments) as total_assignments,
                    SUM(completed_assignments) as completed_assignments
                FROM (
                    -- Service booking assignments
                    SELECT 
                        CONCAT(ua.ua_first_name, ' ', ua.ua_last_name) as technician_name,
                        COUNT(ba.ba_id) as total_assignments,
                        SUM(CASE WHEN ba.ba_status = 'completed' THEN 1 ELSE 0 END) as completed_assignments
                    FROM booking_assignment ba
                    JOIN technician t ON ba.ba_technician_id = t.te_account_id
                    JOIN user_account ua ON t.te_account_id = ua.ua_id
                    GROUP BY technician_name
                    
                    UNION ALL
                    
                    -- Product booking assignments
                    SELECT 
                        CONCAT(ua.ua_first_name, ' ', ua.ua_last_name) as technician_name,
                        COUNT(pa.pa_id) as total_assignments,
                        SUM(CASE WHEN pa.pa_status = 'completed' THEN 1 ELSE 0 END) as completed_assignments
                    FROM product_assignment pa
                    JOIN technician t ON pa.pa_technician_id = t.te_account_id
                    JOIN user_account ua ON t.te_account_id = ua.ua_id
                    GROUP BY technician_name
                ) as combined_assignments
                GROUP BY technician_name
                ORDER BY completed_assignments DESC";
        
        try {
            return $this->query($sql);
        } catch (\Exception $e) {
            error_log("Error getting technician performance: " . $e->getMessage());
            return [];
        }
    }
    
    // Get revenue statistics by month
    public function getRevenueByMonth($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        
        $sql = "WITH product_revenue AS (
                    SELECT 
                        EXTRACT(MONTH FROM pb_order_date) as month,
                        COALESCE(SUM(pb_total_amount), 0) as total_revenue
                    FROM product_booking
                    WHERE 
                        EXTRACT(YEAR FROM pb_order_date) = :year
                        AND pb_status = 'completed'
                        AND pb_deleted_at IS NULL
                    GROUP BY month
                ),
                service_revenue AS (
                    SELECT 
                        EXTRACT(MONTH FROM sb_created_at) as month,
                        COALESCE(SUM(sb_estimated_cost), 0) as total_revenue
                    FROM service_booking
                    WHERE 
                        EXTRACT(YEAR FROM sb_created_at) = :year
                        AND sb_status = 'completed'
                        AND sb_deleted_at IS NULL
                    GROUP BY month
                )
                SELECT
                    COALESCE(p.month, s.month) as month,
                    COALESCE(p.total_revenue, 0) + COALESCE(s.total_revenue, 0) as total_revenue
                FROM
                    product_revenue p
                FULL OUTER JOIN
                    service_revenue s ON p.month = s.month
                ORDER BY month";
        
        try {
            return $this->query($sql, ['year' => $year]);
        } catch (\Exception $e) {
            error_log("Error getting revenue by month: " . $e->getMessage());
            return [];
        }
    }
} 