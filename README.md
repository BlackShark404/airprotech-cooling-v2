
---

## Database Schema

- **User & Role Management:**  
  - `USER_ROLE`, `USER_ACCOUNT`, `CUSTOMER`, `ADMIN`, `TECHNICIAN`
- **Service Management:**  
  - `SERVICE_TYPE`, `SERVICE_BOOKING`, `BOOKING_ASSIGNMENT`
- **Product Management:**  
  - `PRODUCT`, `PRODUCT_FEATURE`, `PRODUCT_SPEC`, `PRODUCT_VARIANT`, `PRODUCT_BOOKING`, `PRODUCT_ASSIGNMENT`
- **Inventory & Warehouse:**  
  - `WAREHOUSE`, `INVENTORY`
- **Triggers:**  
  - Automatic creation of role-specific records on user creation
  - Automatic inventory deduction on product booking confirmation

See [`config/airprotech4_db.sql`](config/airprotech4_db.sql) for full schema and trigger logic.

---

## Setup & Installation

1. **Clone the Repository**
   ```bash
   git clone <your-repo-url>
   cd airprotech-cooling-system
   ```

2. **Set Up the Database**
   - Create a PostgreSQL database.
   - Import the schema:
     ```bash
     psql -U <username> -d <database> -f config/airprotech4_db.sql
     ```

3. **Configure Environment**
   - Set up your database credentials and environment variables as required by your PHP framework (e.g., in `.env` or `config` files).

4. **Run the Application**
   - Start XAMPP and ensure Apache and PostgreSQL are running.
   - Access the app via `http://localhost/airprotech-cooling-system/public` in your browser.

---

## Usage

- **Authentication:**  
  - Users can register and log in as customers, technicians, or admins.
- **Admin Panel:**  
  - Manage users, products, services, inventory, and assignments.
- **Customer Portal:**  
  - Book services, order products, and view order/service status.
- **Technician Portal:**  
  - View and manage assigned bookings and product installations.

---

## Contributing

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/YourFeature`).
3. Commit your changes (`git commit -am 'Add some feature'`).
4. Push to the branch (`git push origin feature/YourFeature`).
5. Create a new Pull Request.

---

## License

[MIT](LICENSE) (or specify your license here)

---

## Contact

For questions or support, please contact [your-email@example.com].
