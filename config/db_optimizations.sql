-- Database Optimizations for AirProtech
-- Adds indexes and other performance improvements

-- --------------------------------------------------------
-- 1. Add indexes for foreign keys and commonly queried columns
-- --------------------------------------------------------

-- USER_ACCOUNT table indexes
CREATE INDEX IF NOT EXISTS idx_user_account_role ON USER_ACCOUNT(UA_ROLE_ID);
CREATE INDEX IF NOT EXISTS idx_user_account_email ON USER_ACCOUNT(UA_EMAIL);
CREATE INDEX IF NOT EXISTS idx_user_account_active ON USER_ACCOUNT(UA_IS_ACTIVE) WHERE UA_IS_ACTIVE = TRUE;
CREATE INDEX IF NOT EXISTS idx_user_account_deleted_at ON USER_ACCOUNT(UA_DELETED_AT) WHERE UA_DELETED_AT IS NULL;

-- SERVICE_BOOKING indexes
CREATE INDEX IF NOT EXISTS idx_service_booking_customer ON SERVICE_BOOKING(SB_CUSTOMER_ID);
CREATE INDEX IF NOT EXISTS idx_service_booking_type ON SERVICE_BOOKING(SB_SERVICE_TYPE_ID);
CREATE INDEX IF NOT EXISTS idx_service_booking_status ON SERVICE_BOOKING(SB_STATUS);
CREATE INDEX IF NOT EXISTS idx_service_booking_date ON SERVICE_BOOKING(SB_PREFERRED_DATE);
CREATE INDEX IF NOT EXISTS idx_service_booking_deleted_at ON SERVICE_BOOKING(SB_DELETED_AT) WHERE SB_DELETED_AT IS NULL;

-- BOOKING_ASSIGNMENT indexes
CREATE INDEX IF NOT EXISTS idx_booking_assignment_booking ON BOOKING_ASSIGNMENT(BA_BOOKING_ID);
CREATE INDEX IF NOT EXISTS idx_booking_assignment_technician ON BOOKING_ASSIGNMENT(BA_TECHNICIAN_ID);
CREATE INDEX IF NOT EXISTS idx_booking_assignment_status ON BOOKING_ASSIGNMENT(BA_STATUS);

-- PRODUCT indexes
CREATE INDEX IF NOT EXISTS idx_product_name ON PRODUCT(PROD_NAME);
CREATE INDEX IF NOT EXISTS idx_product_deleted_at ON PRODUCT(PROD_DELETED_AT) WHERE PROD_DELETED_AT IS NULL;

-- PRODUCT_FEATURE indexes
CREATE INDEX IF NOT EXISTS idx_product_feature_product ON PRODUCT_FEATURE(PROD_ID);
CREATE INDEX IF NOT EXISTS idx_product_feature_deleted_at ON PRODUCT_FEATURE(FEATURE_DELETED_AT) WHERE FEATURE_DELETED_AT IS NULL;

-- PRODUCT_SPEC indexes
CREATE INDEX IF NOT EXISTS idx_product_spec_product ON PRODUCT_SPEC(PROD_ID);
CREATE INDEX IF NOT EXISTS idx_product_spec_deleted_at ON PRODUCT_SPEC(SPEC_DELETED_AT) WHERE SPEC_DELETED_AT IS NULL;

-- PRODUCT_VARIANT indexes
CREATE INDEX IF NOT EXISTS idx_product_variant_product ON PRODUCT_VARIANT(PROD_ID);
CREATE INDEX IF NOT EXISTS idx_product_variant_deleted_at ON PRODUCT_VARIANT(VAR_DELETED_AT) WHERE VAR_DELETED_AT IS NULL;
CREATE INDEX IF NOT EXISTS idx_product_variant_price_range ON PRODUCT_VARIANT(VAR_SRP_PRICE);

-- WAREHOUSE indexes
CREATE INDEX IF NOT EXISTS idx_warehouse_deleted_at ON WAREHOUSE(WHOUSE_DELETED_AT) WHERE WHOUSE_DELETED_AT IS NULL;

-- INVENTORY indexes
CREATE INDEX IF NOT EXISTS idx_inventory_variant ON INVENTORY(VAR_ID);
CREATE INDEX IF NOT EXISTS idx_inventory_warehouse ON INVENTORY(WHOUSE_ID);
-- This composite index helps with warehouse inventory lookups
CREATE INDEX IF NOT EXISTS idx_inventory_variant_warehouse ON INVENTORY(VAR_ID, WHOUSE_ID);
-- This index helps with inventory type filtering
CREATE INDEX IF NOT EXISTS idx_inventory_type ON INVENTORY(INVE_TYPE);
-- This index helps with quantity filtering
CREATE INDEX IF NOT EXISTS idx_inventory_quantity ON INVENTORY(QUANTITY) WHERE QUANTITY > 0;
-- This index helps with deleted record filtering
CREATE INDEX IF NOT EXISTS idx_inventory_deleted_at ON INVENTORY(INVE_DELETED_AT) WHERE INVE_DELETED_AT IS NULL;

-- PRODUCT_BOOKING indexes
CREATE INDEX IF NOT EXISTS idx_product_booking_customer ON PRODUCT_BOOKING(PB_CUSTOMER_ID);
CREATE INDEX IF NOT EXISTS idx_product_booking_variant ON PRODUCT_BOOKING(PB_VARIANT_ID);
CREATE INDEX IF NOT EXISTS idx_product_booking_warehouse ON PRODUCT_BOOKING(PB_WAREHOUSE_ID);
CREATE INDEX IF NOT EXISTS idx_product_booking_status ON PRODUCT_BOOKING(PB_STATUS);
CREATE INDEX IF NOT EXISTS idx_product_booking_date ON PRODUCT_BOOKING(PB_PREFERRED_DATE);
CREATE INDEX IF NOT EXISTS idx_product_booking_deleted_at ON PRODUCT_BOOKING(PB_DELETED_AT) WHERE PB_DELETED_AT IS NULL;

-- PRODUCT_ASSIGNMENT indexes
CREATE INDEX IF NOT EXISTS idx_product_assignment_order ON PRODUCT_ASSIGNMENT(PA_ORDER_ID);
CREATE INDEX IF NOT EXISTS idx_product_assignment_technician ON PRODUCT_ASSIGNMENT(PA_TECHNICIAN_ID);
CREATE INDEX IF NOT EXISTS idx_product_assignment_status ON PRODUCT_ASSIGNMENT(PA_STATUS);

-- --------------------------------------------------------
-- 2. Add useful composite indexes for common query patterns
-- --------------------------------------------------------

-- Helps with queries that filter by customer and status
CREATE INDEX IF NOT EXISTS idx_booking_customer_status ON PRODUCT_BOOKING(PB_CUSTOMER_ID, PB_STATUS);

-- Helps with queries that filter by product and check inventory
CREATE INDEX IF NOT EXISTS idx_inventory_product_quantity ON INVENTORY(VAR_ID, QUANTITY) 
WHERE QUANTITY > 0 AND INVE_DELETED_AT IS NULL;

-- Helps with queries that find available products in warehouses
CREATE INDEX IF NOT EXISTS idx_inventory_warehouse_variant_quantity ON INVENTORY(WHOUSE_ID, VAR_ID, QUANTITY) 
WHERE QUANTITY > 0 AND INVE_DELETED_AT IS NULL;

-- Helps with filtering technicians by availability
CREATE INDEX IF NOT EXISTS idx_technician_availability ON TECHNICIAN(TE_IS_AVAILABLE) 
WHERE TE_IS_AVAILABLE = TRUE;

-- --------------------------------------------------------
-- 3. Add ANALYZE and other maintenance commands
-- --------------------------------------------------------

-- Update statistics for all tables
ANALYZE;

-- Add comment on the optimization script
COMMENT ON DATABASE current_database() IS 'Optimized with performance indexes on ' || CURRENT_DATE; 