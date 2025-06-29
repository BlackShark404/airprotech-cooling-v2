<?php

namespace App\Controllers;

use App\Models\WarehouseModel;
use App\Models\InventoryModel;

class WarehouseController extends BaseController
{
    private $warehouseModel;
    private $inventoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->warehouseModel = new WarehouseModel();
        $this->inventoryModel = new InventoryModel();
    }

    public function renderWarehouseManagement()
    {
        $this->render('admin/warehouse-management');
    }

    public function getAllWarehouses()
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        $warehouses = $this->warehouseModel->getWarehousesWithInventory(); // This already joins inventory

        $processedWarehouses = array_map(function ($warehouse) {
            $utilization = 0;
            if (isset($warehouse['whouse_storage_capacity']) && $warehouse['whouse_storage_capacity'] > 0) {
                $totalInventory = $warehouse['total_inventory'] ?? 0;
                $utilization = round(($totalInventory * 100.0) / $warehouse['whouse_storage_capacity'], 2);
            }
            $warehouse['utilization_percentage'] = $utilization;
            return $warehouse;
        }, $warehouses);

        $this->jsonSuccess($processedWarehouses);
    }

    public function getWarehouse($id)
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        $warehouse = $this->warehouseModel->getWarehouseById($id);
        
        if (!$warehouse) {
            $this->jsonError('Warehouse not found', 404);
            return;
        }

        // Get warehouse utilization
        $utilization = $this->warehouseModel->getWarehouseUtilization($id);
        if ($utilization) {
            $warehouse['utilization'] = $utilization;
        }
        
        $this->jsonSuccess($warehouse);
    }

    public function getWarehousesWithInventory()
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        $warehouses = $this->warehouseModel->getWarehousesWithInventory();
        $this->jsonSuccess($warehouses);
    }

    public function createWarehouse()
    {
        if (!$this->isAjax() || !$this->isPost()) {
            $this->renderError('Bad Request', 400);
            return;
        }
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        if (empty($data['WHOUSE_NAME']) || empty($data['WHOUSE_LOCATION'])) {
            $this->jsonError('Missing required warehouse fields', 400);
            return;
        }
        
        try {
            // Create warehouse
            $warehouseId = $this->warehouseModel->createWarehouse($data);
            
            if (!$warehouseId) {
                throw new \Exception("Failed to create warehouse");
            }
            
            $this->jsonSuccess(['warehouse_id' => $warehouseId], 'Warehouse created successfully');
            
        } catch (\Exception $e) {
            error_log("Error creating warehouse: " . $e->getMessage());
            $this->jsonError('Failed to create warehouse: ' . $e->getMessage(), 500);
        }
    }

    public function updateWarehouse($id)
    {
        if (!$this->isAjax() || !$this->isPut()) {
            $this->renderError('Bad Request', 400);
            return;
        }
        
        $data = $this->getJsonInput();
        
        // Check if warehouse exists
        $existingWarehouse = $this->warehouseModel->getWarehouseById($id);
        if (!$existingWarehouse) {
            $this->jsonError('Warehouse not found', 404);
            return;
        }
        
        try {
            // Update warehouse
            $result = $this->warehouseModel->updateWarehouse($id, $data);
            
            if ($result === false) {
                throw new \Exception("No changes to update");
            }
            
            $this->jsonSuccess(['warehouse_id' => $id], 'Warehouse updated successfully');
            
        } catch (\Exception $e) {
            error_log("Error updating warehouse: " . $e->getMessage());
            $this->jsonError('Failed to update warehouse: ' . $e->getMessage(), 500);
        }
    }

    public function deleteWarehouse($id)
    {
        if (!$this->isAjax() || !$this->isDelete()) {
            $this->renderError('Bad Request', 400);
            return;
        }
        
        // Check if warehouse exists
        $existingWarehouse = $this->warehouseModel->getWarehouseById($id);
        if (!$existingWarehouse) {
            $this->jsonError('Warehouse not found', 404);
            return;
        }
        
        // Check if warehouse has inventory
        $inventory = $this->inventoryModel->getWarehouseInventory($id);
        if (!empty($inventory)) {
            $this->jsonError('Cannot delete warehouse with existing inventory', 400);
            return;
        }
        
        // Delete the warehouse (soft delete)
        $result = $this->warehouseModel->deleteWarehouse($id);
        
        if ($result) {
            $this->jsonSuccess(null, 'Warehouse deleted successfully');
        } else {
            $this->jsonError('Failed to delete warehouse', 500);
        }
    }

    public function getWarehouseUtilization($id)
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        $utilization = $this->warehouseModel->getWarehouseUtilization($id);
        
        if (!$utilization) {
            $this->jsonError('Warehouse not found or has no utilization data', 404);
            return;
        }
        
        $this->jsonSuccess($utilization);
    }

    public function getWarehousesWithAvailableSpace()
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        $warehouses = $this->warehouseModel->getWarehousesWithAvailableSpace();
        $this->jsonSuccess($warehouses);
    }

    public function getProductDistribution($id)
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        // Check if warehouse exists
        $warehouse = $this->warehouseModel->getWarehouseById($id);
        if (!$warehouse) {
            $this->jsonError('Warehouse not found', 404);
            return;
        }

        // Get inventory in this warehouse
        $inventory = $this->inventoryModel->getWarehouseInventory($id);
        
        // Format data for distribution chart
        $distribution = [
            'warehouse_name' => $warehouse['WHOUSE_NAME'],
            'products' => $inventory
        ];
        
        $this->jsonSuccess($distribution);
    }

    public function searchWarehouses()
    {
        if (!$this->isAjax()) {
            $this->renderError('Bad Request', 400);
            return;
        }

        $query = $this->request('q', '');
        $limit = $this->request('limit', 10);
        
        // Implement search logic in your model
        // For simplicity, we're just returning all warehouses here
        $warehouses = $this->warehouseModel->getAllWarehouses();
        
        if ($query) {
            // Filter warehouses by name or location (case-insensitive)
            $filtered = array_filter($warehouses, function($warehouse) use ($query) {
                return (
                    stripos($warehouse['WHOUSE_NAME'], $query) !== false ||
                    stripos($warehouse['WHOUSE_LOCATION'], $query) !== false
                );
            });
            
            // Reindex array
            $warehouses = array_values($filtered);
        }
        
        // Limit results
        $warehouses = array_slice($warehouses, 0, $limit);
        
        $this->jsonSuccess($warehouses);
    }
} 