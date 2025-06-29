<?php

namespace App\Models;

class ProductOrderModel extends Model
{
    protected $table = 'PRODUCT_ORDER';

    public function getAllOrders()
    {
        $sql = "SELECT 
                    po.*,
                    ua.UA_FIRST_NAME || ' ' || ua.UA_LAST_NAME AS CUSTOMER_NAME,
                    pv.VAR_CAPACITY,
                    p.PROD_NAME,
                    p.PROD_IMAGE
                FROM {$this->table} po
                JOIN CUSTOMER c ON po.PO_CUSTOMER_ID = c.CU_ACCOUNT_ID
                JOIN USER_ACCOUNT ua ON c.CU_ACCOUNT_ID = ua.UA_ID
                JOIN PRODUCT_VARIANT pv ON po.PO_VARIANT_ID = pv.VAR_ID
                JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                WHERE po.PO_DELETED_AT IS NULL
                ORDER BY po.PO_ORDER_DATE DESC";
        
        return $this->query($sql);
    }

    public function getOrderById($orderId)
    {
        $sql = "SELECT 
                    po.*,
                    ua.UA_FIRST_NAME || ' ' || ua.UA_LAST_NAME AS CUSTOMER_NAME,
                    ua.UA_EMAIL AS CUSTOMER_EMAIL,
                    ua.UA_PHONE_NUMBER AS CUSTOMER_PHONE,
                    pv.VAR_CAPACITY,
                    p.PROD_NAME,
                    p.PROD_IMAGE
                FROM {$this->table} po
                JOIN CUSTOMER c ON po.PO_CUSTOMER_ID = c.CU_ACCOUNT_ID
                JOIN USER_ACCOUNT ua ON c.CU_ACCOUNT_ID = ua.UA_ID
                JOIN PRODUCT_VARIANT pv ON po.PO_VARIANT_ID = pv.VAR_ID
                JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                WHERE po.PO_ID = :order_id AND po.PO_DELETED_AT IS NULL";
        
        return $this->queryOne($sql, [':order_id' => $orderId]);
    }

    public function getOrdersByCustomerId($customerId)
    {
        $sql = "SELECT 
                    po.*,
                    pv.VAR_CAPACITY,
                    p.PROD_NAME,
                    p.PROD_IMAGE
                FROM {$this->table} po
                JOIN PRODUCT_VARIANT pv ON po.PO_VARIANT_ID = pv.VAR_ID
                JOIN PRODUCT p ON pv.PROD_ID = p.PROD_ID
                WHERE po.PO_CUSTOMER_ID = :customer_id AND po.PO_DELETED_AT IS NULL
                ORDER BY po.PO_ORDER_DATE DESC";
        
        return $this->query($sql, [':customer_id' => $customerId]);
    }

    public function createOrder($data)
    {
        $sql = "INSERT INTO {$this->table} (PO_CUSTOMER_ID, PO_VARIANT_ID, PO_QUANTITY, PO_UNIT_PRICE, PO_STATUS)
                VALUES (:customer_id, :variant_id, :quantity, :unit_price, :status)";
        
        $params = [
            ':customer_id' => $data['PO_CUSTOMER_ID'],
            ':variant_id' => $data['PO_VARIANT_ID'],
            ':quantity' => $data['PO_QUANTITY'],
            ':unit_price' => $data['PO_UNIT_PRICE'],
            ':status' => $data['PO_STATUS'] ?? 'pending'
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId('product_order_po_id_seq');
    }

    public function updateOrderStatus($orderId, $status, $paidDate = null)
    {
        $sql = "UPDATE {$this->table} SET 
                PO_STATUS = :status,
                PO_UPDATED_AT = CURRENT_TIMESTAMP";
        
        $params = [
            ':status' => $status,
            ':order_id' => $orderId
        ];
        
        if ($status === 'completed' && $paidDate) {
            $sql .= ", PO_PAID_DATE = :paid_date";
            $params[':paid_date'] = $paidDate;
        }
        
        $sql .= " WHERE PO_ID = :order_id AND PO_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    public function updateOrder($orderId, $data)
    {
        $setClauses = [];
        $params = [':order_id' => $orderId];

        if (isset($data['PO_QUANTITY'])) {
            $setClauses[] = "PO_QUANTITY = :quantity";
            $params[':quantity'] = $data['PO_QUANTITY'];
        }
        
        if (isset($data['PO_UNIT_PRICE'])) {
            $setClauses[] = "PO_UNIT_PRICE = :unit_price";
            $params[':unit_price'] = $data['PO_UNIT_PRICE'];
        }
        
        if (isset($data['PO_STATUS'])) {
            $setClauses[] = "PO_STATUS = :status";
            $params[':status'] = $data['PO_STATUS'];
            
            if ($data['PO_STATUS'] === 'completed' && !isset($data['PO_PAID_DATE'])) {
                $setClauses[] = "PO_PAID_DATE = CURRENT_TIMESTAMP";
            }
        }
        
        if (isset($data['PO_PAID_DATE'])) {
            $setClauses[] = "PO_PAID_DATE = :paid_date";
            $params[':paid_date'] = $data['PO_PAID_DATE'];
        }

        if (empty($setClauses)) {
            return false; // No fields to update
        }

        $setClauses[] = "PO_UPDATED_AT = CURRENT_TIMESTAMP";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE PO_ID = :order_id AND PO_DELETED_AT IS NULL";
        
        return $this->execute($sql, $params);
    }

    public function deleteOrder($orderId)
    {
        $sql = "UPDATE {$this->table} SET PO_DELETED_AT = CURRENT_TIMESTAMP 
                WHERE PO_ID = :order_id";
        return $this->execute($sql, [':order_id' => $orderId]);
    }

    public function getOrdersSummary()
    {
        $sql = "SELECT 
                    COUNT(*) AS TOTAL_ORDERS,
                    COUNT(CASE WHEN PO_STATUS = 'pending' THEN 1 END) AS PENDING_ORDERS,
                    COUNT(CASE WHEN PO_STATUS = 'confirmed' THEN 1 END) AS CONFIRMED_ORDERS,
                    COUNT(CASE WHEN PO_STATUS = 'completed' THEN 1 END) AS COMPLETED_ORDERS,
                    COUNT(CASE WHEN PO_STATUS = 'cancelled' THEN 1 END) AS CANCELLED_ORDERS,
                    SUM(PO_QUANTITY * PO_UNIT_PRICE) AS TOTAL_REVENUE
                FROM {$this->table}
                WHERE PO_DELETED_AT IS NULL";
        
        return $this->queryOne($sql);
    }
} 