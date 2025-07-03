-- Database schema for AirProTech

-- --------------------------------------
-- 1. Core User and Role Tables
-- --------------------------------------
-- ROLES Table: Defines user roles (customer, technician, admin)
CREATE TABLE USER_ROLE (
    UR_ID    SERIAL PRIMARY KEY,
    UR_NAME  VARCHAR(20) UNIQUE NOT NULL
);


-- Insert role values
INSERT INTO USER_ROLE (UR_NAME) VALUES 
('customer'),
('technician'),
('admin');


-- USERS Table: Base table for all user types
CREATE TABLE USER_ACCOUNT (
    UA_ID                          SERIAL PRIMARY KEY,
    UA_PROFILE_URL                 VARCHAR(255),
    UA_FIRST_NAME                  VARCHAR(255) NOT NULL,
    UA_LAST_NAME                   VARCHAR(255) NOT NULL,
    UA_ADDRESS                     TEXT,
    UA_EMAIL                       VARCHAR(255) UNIQUE NOT NULL,
    UA_HASHED_PASSWORD             VARCHAR(255) NOT NULL,
    UA_PHONE_NUMBER                VARCHAR(20),
    UA_ROLE_ID                     INT NOT NULL,
    UA_IS_ACTIVE                   BOOLEAN DEFAULT TRUE,
    UA_REMEMBER_TOKEN              VARCHAR(255),
    UA_REMEMBER_TOKEN_EXPIRES_AT   TIMESTAMP,
    UA_LAST_LOGIN                  TIMESTAMP,
    UA_CREATED_AT                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UA_UPDATED_AT                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UA_DELETED_AT                  TIMESTAMP,
    CONSTRAINT FK_USER_ROLE FOREIGN KEY (UA_ROLE_ID)
        REFERENCES USER_ROLE(UR_ID) ON DELETE RESTRICT ON UPDATE CASCADE
);


-- CUSTOMER Table: Specific attributes for customer users
CREATE TABLE CUSTOMER (
    CU_ACCOUNT_ID         INT PRIMARY KEY,
    CU_TOTAL_BOOKINGS     INT DEFAULT 0,
    CU_ACTIVE_BOOKINGS    INT DEFAULT 0,
    CU_PENDING_SERVICES   INT DEFAULT 0,
    CU_COMPLETED_SERVICES INT DEFAULT 0,
    CU_PRODUCT_ORDERS     INT DEFAULT 0,
    CU_CREATED_AT         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CU_UPDATED_AT         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_CUSTOMER_ACCOUNT FOREIGN KEY (CU_ACCOUNT_ID)
        REFERENCES USER_ACCOUNT(UA_ID) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ADMIN Table: Specific attributes for admin users
CREATE TABLE ADMIN (
    AD_ACCOUNT_ID       INT PRIMARY KEY,
    AD_OFFICE_NO        VARCHAR(20),
    AD_CREATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    AD_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_ADMIN_ACCOUNT FOREIGN KEY (AD_ACCOUNT_ID)
        REFERENCES USER_ACCOUNT(UA_ID) ON DELETE CASCADE ON UPDATE CASCADE
);


-- TECHNICIAN Table: Specific attributes for technician users
CREATE TABLE TECHNICIAN (
    TE_ACCOUNT_ID       INT PRIMARY KEY,
    TE_IS_AVAILABLE     BOOLEAN DEFAULT TRUE,
    TE_CREATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    TE_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_TECHNICIAN_ACCOUNT FOREIGN KEY (TE_ACCOUNT_ID)
        REFERENCES USER_ACCOUNT(UA_ID) ON DELETE CASCADE ON UPDATE CASCADE
);


-- --------------------------------------
-- 2. Service-Related Tables
-- --------------------------------------
-- SERVICE_TYPE Table: Defines available service types
CREATE TABLE SERVICE_TYPE (
    ST_ID          SERIAL PRIMARY KEY,
    ST_CODE        VARCHAR(50) UNIQUE NOT NULL,
    ST_NAME        VARCHAR(100) NOT NULL,
    ST_DESCRIPTION TEXT,
    ST_IS_ACTIVE   BOOLEAN DEFAULT TRUE,
    ST_CREATED_AT  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ST_UPDATED_AT  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert service types
INSERT INTO SERVICE_TYPE (ST_CODE, ST_NAME, ST_DESCRIPTION) VALUES
('checkup-repair', 'Aircon Check-up & Repair', 'Diagnostic and repair services for air conditioning units'),
('installation', 'Installation of Units', 'Professional installation of new air conditioning units'),
('ducting', 'Ducting Works', 'Installation and maintenance of air conditioning ducts'),
('cleaning-pms', 'General Cleaning & PMS', 'General cleaning and preventive maintenance services'),
('survey-estimation', 'Survey & Estimation', 'On-site evaluation and cost estimation for air conditioning needs'),
('project-quotations', 'Project Quotations', 'Detailed quotations for air conditioning projects');

-- SERVICE_BOOKING Table: Records customer service requests
CREATE TABLE SERVICE_BOOKING (
    SB_ID               SERIAL PRIMARY KEY,
    SB_CUSTOMER_ID      INT NOT NULL,
    SB_SERVICE_TYPE_ID  INT NOT NULL,
    SB_PREFERRED_DATE   DATE NOT NULL,
	SB_PREFERRED_TIME   TIME NOT NULL,
    SB_ADDRESS          TEXT NOT NULL,
    SB_DESCRIPTION      TEXT NOT NULL,
    SB_STATUS           VARCHAR(20) DEFAULT 'pending',
    SB_PRIORITY         VARCHAR(10) DEFAULT 'moderate',
    SB_ESTIMATED_COST   DECIMAL(10, 2) DEFAULT 0,
    SB_CREATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    SB_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    SB_DELETED_AT       TIMESTAMP,
    CONSTRAINT FK_BOOKING_CUSTOMER FOREIGN KEY (SB_CUSTOMER_ID)
        REFERENCES CUSTOMER(CU_ACCOUNT_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT FK_BOOKING_SERVICE_TYPE FOREIGN KEY (SB_SERVICE_TYPE_ID)
        REFERENCES SERVICE_TYPE(ST_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CK_BOOKING_STATUS CHECK (SB_STATUS IN ('pending', 'confirmed', 'in-progress', 'completed', 'cancelled')),
    CONSTRAINT CK_BOOKING_PRIORITY CHECK (SB_PRIORITY IN ('normal', 'moderate', 'urgent'))
);


-- BOOKING_ASSIGNMENT Table: Assigns technicians to service bookings
CREATE TABLE BOOKING_ASSIGNMENT (
    BA_ID               SERIAL PRIMARY KEY,
    BA_BOOKING_ID       INT NOT NULL,
    BA_TECHNICIAN_ID    INT NOT NULL,
    BA_STATUS           VARCHAR(20) DEFAULT 'assigned',
    BA_NOTES            TEXT,
    BA_ASSIGNED_AT      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    BA_STARTED_AT       TIMESTAMP,
    BA_COMPLETED_AT     TIMESTAMP,
    BA_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_ASSIGNMENT_BOOKING FOREIGN KEY (BA_BOOKING_ID)
        REFERENCES SERVICE_BOOKING(SB_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ASSIGNMENT_TECHNICIAN FOREIGN KEY (BA_TECHNICIAN_ID)
        REFERENCES TECHNICIAN(TE_ACCOUNT_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT UQ_BOOKING_TECHNICIAN UNIQUE (BA_BOOKING_ID, BA_TECHNICIAN_ID),
    CONSTRAINT CK_ASSIGNMENT_STATUS CHECK (BA_STATUS IN ('assigned', 'in-progress', 'completed', 'cancelled'))
);


-- --------------------------------------
-- 3. Product-Related Tables
-- --------------------------------------
-- PRODUCT Table: Stores product information
CREATE TABLE PRODUCT (
    PROD_ID                         SERIAL PRIMARY KEY,
    PROD_IMAGE                      TEXT NOT NULL,
    PROD_NAME                       VARCHAR(100) NOT NULL,
    PROD_DESCRIPTION                TEXT,
    -- Updated discount fields to handle multiple installation options
    PROD_DISCOUNT_FREE_INSTALL_PCT  DECIMAL(5, 2) DEFAULT 0.00,  -- e.g., 15.00
    PROD_DISCOUNT_WITH_INSTALL_PCT1 DECIMAL(5, 2) DEFAULT 0.00,  -- Primary discount rate (e.g., 25.00)
    PROD_DISCOUNT_WITH_INSTALL_PCT2 DECIMAL(5, 2) DEFAULT 0.00,  -- Secondary discount rate (e.g., 27.00)
    PROD_HAS_FREE_INSTALL_OPTION    BOOLEAN DEFAULT TRUE,        -- Indicates if product offers free installation option
    PROD_CREATED_AT                 TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PROD_UPDATED_AT                 TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PROD_DELETED_AT                 TIMESTAMP
);


-- PRODUCT_FEATURE Table: Stores product features
CREATE TABLE PRODUCT_FEATURE (
    FEATURE_ID         SERIAL PRIMARY KEY,
    FEATURE_NAME       VARCHAR(100) NOT NULL,
    PROD_ID            INTEGER NOT NULL,
    FEATURE_CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FEATURE_UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FEATURE_DELETED_AT TIMESTAMP,
    FOREIGN KEY (PROD_ID) REFERENCES PRODUCT(PROD_ID) ON DELETE CASCADE
);


-- PRODUCT_SPEC Table: Stores product specifications
CREATE TABLE PRODUCT_SPEC (
    SPEC_ID         SERIAL PRIMARY KEY,
    SPEC_NAME       VARCHAR(100) NOT NULL,
    SPEC_VALUE      VARCHAR(100) NOT NULL,
    PROD_ID         INTEGER NOT NULL,
    SPEC_CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    SPEC_UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    SPEC_DELETED_AT TIMESTAMP,
    FOREIGN KEY (PROD_ID) REFERENCES PRODUCT(PROD_ID) ON DELETE CASCADE
);


CREATE TABLE PRODUCT_VARIANT (
    VAR_ID                          SERIAL PRIMARY KEY,
    VAR_CAPACITY                    VARCHAR(20) NOT NULL,
    VAR_SRP_PRICE                   DECIMAL(10, 2) NOT NULL,

    -- Removed discount fields (moved to PRODUCT table)

    -- Installation Fee (used only for 'with install' pricing)
    VAR_INSTALLATION_FEE            DECIMAL(10, 2) DEFAULT 0.00,

    -- Computed Prices - modified to reference parent product's discount rates
    VAR_PRICE_FREE_INSTALL          DECIMAL(10, 2),

    VAR_PRICE_WITH_INSTALL1         DECIMAL(10, 2),

    VAR_PRICE_WITH_INSTALL2         DECIMAL(10, 2),
	
    VAR_POWER_CONSUMPTION           VARCHAR(20),
    PROD_ID                         INTEGER NOT NULL,
    VAR_CREATED_AT                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    VAR_UPDATED_AT                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    VAR_DELETED_AT                  TIMESTAMP,

    FOREIGN KEY (PROD_ID) REFERENCES PRODUCT(PROD_ID) ON DELETE CASCADE
);


CREATE TABLE WAREHOUSE (
	WHOUSE_ID               SERIAL PRIMARY KEY,
	WHOUSE_NAME             VARCHAR(100),
	WHOUSE_LOCATION         TEXT,
	WHOUSE_STORAGE_CAPACITY INT,
	WHOUSE_RESTOCK_THRESHOLD INT,
	WHOUSE_CREATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	WHOUSE_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	WHOUSE_DELETED_AT       TIMESTAMP
);


-- PRODUCT_BOOKING Table: Records customer product bookings (formerly PRODUCT_ORDER)
CREATE TABLE PRODUCT_BOOKING (
    PB_ID               SERIAL PRIMARY KEY,
    PB_CUSTOMER_ID      INT NOT NULL,
    PB_VARIANT_ID       INT NOT NULL,
    PB_QUANTITY         INT	 NOT NULL CHECK (PB_QUANTITY > 0),
    PB_UNIT_PRICE       DECIMAL(10, 2) NOT NULL,
    PB_TOTAL_AMOUNT     DECIMAL(10, 2) GENERATED ALWAYS AS (PB_QUANTITY * PB_UNIT_PRICE) STORED,
    PB_STATUS           VARCHAR(20) DEFAULT 'pending',
    PB_INVENTORY_DEDUCTED BOOLEAN DEFAULT FALSE,
    PB_WAREHOUSE_ID     INT,  -- The warehouse from which the products will be taken
    PB_HAS_FREE_INSTALL_OPTION BOOLEAN, -- Stores whether free installation was available for this product

    -- Updated to clarify all installation options:
    -- 'free_installation' - Installation is free (uses VAR_PRICE_FREE_INSTALL)
    -- 'with_installation1' - Primary paid installation option (uses VAR_PRICE_WITH_INSTALL1)
    -- 'with_installation2' - Secondary paid installation option (uses VAR_PRICE_WITH_INSTALL2)
    PB_PRICE_TYPE VARCHAR(20) NOT NULL CHECK (PB_PRICE_TYPE IN ('free_installation', 'with_installation1', 'with_installation2')),
   
    PB_ORDER_DATE       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PB_PREFERRED_DATE   DATE NOT NULL,
	PB_PREFERRED_TIME   TIME NOT NULL,
	PB_ADDRESS          TEXT NOT NULL,
	PB_DESCRIPTION      TEXT,
    PB_CREATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PB_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PB_DELETED_AT       TIMESTAMP,
    
    CONSTRAINT FK_BOOKING_CUSTOMER FOREIGN KEY (PB_CUSTOMER_ID)
        REFERENCES CUSTOMER(CU_ACCOUNT_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT FK_BOOKING_VARIANT FOREIGN KEY (PB_VARIANT_ID)
        REFERENCES PRODUCT_VARIANT(VAR_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT FK_BOOKING_WAREHOUSE FOREIGN KEY (PB_WAREHOUSE_ID)
        REFERENCES WAREHOUSE(WHOUSE_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT CK_BOOKING_STATUS CHECK (PB_STATUS IN ('pending', 'confirmed', 'in-progress', 'completed', 'cancelled'))
);


-- PRODUCT_ASSIGNMENT Table: Assigns technicians to product bookings
CREATE TABLE PRODUCT_ASSIGNMENT (
    PA_ID               SERIAL PRIMARY KEY,
    PA_ORDER_ID         INT NOT NULL,
    PA_TECHNICIAN_ID    INT NOT NULL,
    PA_STATUS           VARCHAR(20) DEFAULT 'assigned',
    PA_NOTES            TEXT,
    PA_ASSIGNED_AT      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PA_STARTED_AT       TIMESTAMP,
    PA_COMPLETED_AT     TIMESTAMP,
    PA_UPDATED_AT       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT FK_ASSIGNMENT_BOOKING FOREIGN KEY (PA_ORDER_ID)
        REFERENCES PRODUCT_BOOKING(PB_ID) ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT FK_ASSIGNMENT_TECHNICIAN FOREIGN KEY (PA_TECHNICIAN_ID)
        REFERENCES TECHNICIAN(TE_ACCOUNT_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT UQ_ORDER_TECHNICIAN UNIQUE (PA_ORDER_ID, PA_TECHNICIAN_ID),
    
    CONSTRAINT CK_ASSIGNMENT_STATUS CHECK (PA_STATUS IN ('assigned', 'in-progress', 'completed', 'cancelled'))
);


-- --------------------------------------
-- 4. Inventory Management Tables
-- --------------------------------------
-- INVENTORY Table: Tracks product stock in warehouses
CREATE TABLE INVENTORY (
    INVE_ID         SERIAL PRIMARY KEY,
    VAR_ID          INT REFERENCES PRODUCT_VARIANT(VAR_ID) ON DELETE CASCADE,
    WHOUSE_ID       INT REFERENCES WAREHOUSE(WHOUSE_ID) ON DELETE CASCADE,
    INVE_TYPE       VARCHAR(50) CHECK (
        INVE_TYPE IN (
            'Regular', 
            'Display', 
            'Reserve', 
            'Damaged', 
            'Returned', 
            'Quarantine'
        )
    ),
    QUANTITY        INT CHECK (QUANTITY >= 0),
    INVE_CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INVE_UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INVE_DELETED_AT TIMESTAMP
);


-- --------------------------------------
-- 1. Triggers for User and Role Management
-- --------------------------------------
-- Trigger to create role-specific record when a user is created
CREATE OR REPLACE FUNCTION create_role_specific_record()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.UA_ROLE_ID = (SELECT UR_ID FROM USER_ROLE WHERE UR_NAME = 'customer') THEN
        INSERT INTO CUSTOMER (CU_ACCOUNT_ID) VALUES (NEW.UA_ID);
    ELSIF NEW.UA_ROLE_ID = (SELECT UR_ID FROM USER_ROLE WHERE UR_NAME = 'technician') THEN
        INSERT INTO TECHNICIAN (TE_ACCOUNT_ID) VALUES (NEW.UA_ID);
    ELSIF NEW.UA_ROLE_ID = (SELECT UR_ID FROM USER_ROLE WHERE UR_NAME = 'admin') THEN
        INSERT INTO ADMIN (AD_ACCOUNT_ID) VALUES (NEW.UA_ID);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_role_record_after_user_insert
AFTER INSERT ON USER_ACCOUNT
FOR EACH ROW
EXECUTE FUNCTION create_role_specific_record();


-- --------------------------------------
-- 2. Triggers for Computing discounts
-- --------------------------------------
CREATE OR REPLACE FUNCTION compute_variant_prices() RETURNS TRIGGER AS $$
DECLARE
  discount_free_install DECIMAL(5, 2);
  discount_with_install1 DECIMAL(5, 2);
  discount_with_install2 DECIMAL(5, 2);
BEGIN
  -- Get the discount rates from the product
  SELECT 
    COALESCE(p.PROD_DISCOUNT_FREE_INSTALL_PCT, 0),
    COALESCE(p.PROD_DISCOUNT_WITH_INSTALL_PCT1, 0),
    COALESCE(p.PROD_DISCOUNT_WITH_INSTALL_PCT2, 0)
  INTO
    discount_free_install,
    discount_with_install1,
    discount_with_install2
  FROM PRODUCT p
  WHERE p.PROD_ID = NEW.PROD_ID;

  -- Compute the final prices using the discount rates
  NEW.VAR_PRICE_FREE_INSTALL := NEW.VAR_SRP_PRICE * (1 - discount_free_install / 100);
  NEW.VAR_PRICE_WITH_INSTALL1 := (NEW.VAR_SRP_PRICE * (1 - discount_with_install1 / 100)) + NEW.VAR_INSTALLATION_FEE;
  NEW.VAR_PRICE_WITH_INSTALL2 := (NEW.VAR_SRP_PRICE * (1 - discount_with_install2 / 100)) + NEW.VAR_INSTALLATION_FEE;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_compute_variant_prices
BEFORE INSERT OR UPDATE ON PRODUCT_VARIANT
FOR EACH ROW
EXECUTE FUNCTION compute_variant_prices();


-- --------------------------------------
-- 3. Triggers for Inventory Management
-- --------------------------------------
-- Trigger to deduct inventory when product booking status is set to confirmed
CREATE OR REPLACE FUNCTION deduct_inventory_on_booking_confirmation()
RETURNS TRIGGER AS $$
DECLARE
    available_quantity INT;
    warehouse_id INT;
    has_free_install BOOLEAN;
    discount_with_install1 DECIMAL(5, 2);
    discount_with_install2 DECIMAL(5, 2);
    variant_product_id INT;
BEGIN
    -- Proceed only if:
    -- (a) New status is 'confirmed'
    -- (b) Was not already confirmed OR inventory not deducted yet
    IF (
        NEW.PB_STATUS = 'confirmed' AND 
        NEW.PB_INVENTORY_DEDUCTED = FALSE
    ) THEN
        -- Get the product ID associated with this variant for checking installation options
        SELECT 
            PROD_ID, 
            PROD_HAS_FREE_INSTALL_OPTION,
            PROD_DISCOUNT_WITH_INSTALL_PCT1,
            PROD_DISCOUNT_WITH_INSTALL_PCT2
        INTO 
            variant_product_id, 
            has_free_install,
            discount_with_install1,
            discount_with_install2
        FROM PRODUCT_VARIANT
        JOIN PRODUCT ON PRODUCT_VARIANT.PROD_ID = PRODUCT.PROD_ID
        WHERE VAR_ID = NEW.PB_VARIANT_ID;
        
        -- Store the free installation option flag from the product
        NEW.PB_HAS_FREE_INSTALL_OPTION := has_free_install;
        
        -- Validate the price type against the product's installation options
        IF NEW.PB_PRICE_TYPE = 'free_installation' AND NOT has_free_install THEN
            RAISE EXCEPTION 'Product does not offer free installation option';
        END IF;
        
        -- Check if second installation discount is applicable (if discount2 is 0, option isn't available)
        IF NEW.PB_PRICE_TYPE = 'with_installation2' AND discount_with_install2 = 0 THEN
            RAISE EXCEPTION 'Product does not offer secondary installation discount option';
        END IF;
    
        -- If PB_WAREHOUSE_ID was specified, use it
        IF NEW.PB_WAREHOUSE_ID IS NOT NULL THEN
            SELECT QUANTITY INTO available_quantity
            FROM INVENTORY
            WHERE VAR_ID = NEW.PB_VARIANT_ID
              AND WHOUSE_ID = NEW.PB_WAREHOUSE_ID
              AND QUANTITY >= NEW.PB_QUANTITY
              AND INVE_DELETED_AT IS NULL;

            IF available_quantity IS NULL THEN
                RAISE EXCEPTION 'Warehouse ID % does not have enough stock for variant ID %',
                    NEW.PB_WAREHOUSE_ID, NEW.PB_VARIANT_ID;
            END IF;

            warehouse_id := NEW.PB_WAREHOUSE_ID;
        ELSE
            -- Auto-select any warehouse with sufficient inventory
            SELECT WHOUSE_ID, QUANTITY INTO warehouse_id, available_quantity
            FROM INVENTORY
            WHERE VAR_ID = NEW.PB_VARIANT_ID
              AND QUANTITY >= NEW.PB_QUANTITY
              AND INVE_DELETED_AT IS NULL
            LIMIT 1;

            IF warehouse_id IS NULL THEN
                RAISE EXCEPTION 'No warehouse has enough stock for product variant ID %',
                    NEW.PB_VARIANT_ID;
            END IF;

            NEW.PB_WAREHOUSE_ID := warehouse_id;
        END IF;

        -- Deduct inventory
        UPDATE INVENTORY
        SET QUANTITY = QUANTITY - NEW.PB_QUANTITY,
            INVE_UPDATED_AT = CURRENT_TIMESTAMP
        WHERE VAR_ID = NEW.PB_VARIANT_ID
          AND WHOUSE_ID = NEW.PB_WAREHOUSE_ID
          AND INVE_DELETED_AT IS NULL;

        -- Mark as deducted
        NEW.PB_INVENTORY_DEDUCTED := TRUE;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Create the trigger
CREATE TRIGGER deduct_inventory_after_booking_confirmation
BEFORE UPDATE ON PRODUCT_BOOKING
FOR EACH ROW
EXECUTE FUNCTION deduct_inventory_on_booking_confirmation();

-- Function to set free installation option flag on insert
CREATE OR REPLACE FUNCTION set_free_installation_option_on_insert()
RETURNS TRIGGER AS $$
DECLARE
    has_free_install BOOLEAN;
BEGIN
    -- Get the free installation option from the product
    SELECT PROD_HAS_FREE_INSTALL_OPTION
    INTO has_free_install
    FROM PRODUCT_VARIANT
    JOIN PRODUCT ON PRODUCT_VARIANT.PROD_ID = PRODUCT.PROD_ID
    WHERE VAR_ID = NEW.PB_VARIANT_ID;
    
    -- Set the free installation option flag in the booking
    NEW.PB_HAS_FREE_INSTALL_OPTION := has_free_install;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to set free installation option on insert
CREATE TRIGGER set_free_installation_option_on_insert
BEFORE INSERT ON PRODUCT_BOOKING
FOR EACH ROW
EXECUTE FUNCTION set_free_installation_option_on_insert();