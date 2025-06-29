/**
 * ProductBookingManager Class
 * Handles creating product booking cards, managing the details modal,
 * filtering/searching bookings, and client-side pagination
 */
class ProductBookingManager {
    constructor(options = {}) {
        // Default configuration
        this.config = {
            bookingsEndpoint: '/api/user/product-bookings',
            containerSelector: '#bookings',
            modalId: 'bookingDetailModal',
            filterFormId: 'booking-filters',
            searchInputId: 'booking-search',
            dateFilterId: 'booking-date-filter',
            statusFilterId: 'booking-status-filter',
            cardTemplate: this.getDefaultCardTemplate(),
            itemsPerPage: 10,
            paginationContainerSelector: '#bookings-pagination-container',
            ...options
        };

        // Initialize modal elements references
        this.modal = {
            element: null, // Will be initialized in init()
            bookingId: null,
            productName: null,
            productImage: null,
            variant: null,
            quantity: null,
            unitPrice: null,
            totalAmount: null,
            status: null,
            bookingDate: null,
            preferredDate: null,
            preferredTime: null,
            address: null,
            description: null // Additional Instructions field
        };

        // Container for booking cards
        this.container = null;

        // Store all bookings for filtering
        this.allBookings = [];
        this.filteredBookings = [];

        // Pagination state
        this.currentPage = 1;
        this.itemsPerPage = this.config.itemsPerPage;

        // Bootstrap modal instance
        this.bsModal = null;
    }

    /**
     * Initialize the ProductBookingManager
     */
    init() {
        // Get container element
        this.container = document.querySelector(this.config.containerSelector);
        if (!this.container) {
            console.error(`Container element not found: ${this.config.containerSelector}`);
            return;
        }

        // Initialize modal elements
        this.modal.element = document.getElementById(this.config.modalId);

        if (this.modal.element) {
            this.modal.bookingId = document.getElementById('modal-booking-id');
            this.modal.productName = document.getElementById('modal-product-name');
            this.modal.productImage = document.getElementById('modal-product-image');
            this.modal.variant = document.getElementById('modal-variant');
            this.modal.quantity = document.getElementById('modal-quantity');
            this.modal.unitPrice = document.getElementById('modal-unit-price');
            this.modal.totalAmount = document.getElementById('modal-total-amount');
            this.modal.status = document.getElementById('modal-status');
            this.modal.bookingDate = document.getElementById('modal-booking-date');
            this.modal.preferredDate = document.getElementById('modal-preferred-date');
            this.modal.preferredTime = document.getElementById('modal-preferred-time');
            this.modal.address = document.getElementById('modal-delivery-address');
            this.modal.description = document.getElementById('modal-description');

            // Create Bootstrap modal instance
            this.bsModal = new bootstrap.Modal(this.modal.element);
        } else {
            console.warn(`Modal element not found: ${this.config.modalId}`);
        }

        // Initialize modal controls
        this.initModalControls();

        // Initialize filter and search
        this.initFilterAndSearch();

        // Fetch and render bookings
        this.fetchAndRenderBookings();
    }

    /**
     * Default card template for product bookings
     */
    getDefaultCardTemplate() {
        return (booking) => {
            // Handle both upper and lowercase field names from API
            const id = booking.PB_ID || booking.pb_id;
            const bookingDate = booking.PB_ORDER_DATE || booking.pb_order_date;
            const productName = booking.PROD_NAME || booking.prod_name || 'Unknown Product';
            const rawProductImage = booking.PROD_IMAGE || booking.prod_image || '/assets/images/product-placeholder.jpg';

            // Fix image path by ensuring it has the correct prefix
            const productImage = this.fixImagePath(rawProductImage);

            const variantCapacity = booking.VAR_CAPACITY || booking.var_capacity || 'N/A';
            const totalAmount = booking.PB_TOTAL_AMOUNT || booking.pb_total_amount || 0;
            const status = booking.PB_STATUS || booking.pb_status || 'pending';
            const preferredDate = booking.PB_PREFERRED_DATE || booking.pb_preferred_date;

            return `
                <div class="booking-item card shadow-sm mb-3">
                    <div class="card-body d-flex align-items-center p-4">
                        <img src="${productImage}" alt="${productName}" class="me-4 rounded" width="100" height="100">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">PB-${id} <span class="text-muted">${new Date(bookingDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span></p>
                                    <h5 class="fw-bold mb-1">${productName}</h5>
                                    <p class="text-muted mb-0">Variant: ${variantCapacity}</p>
                                    ${status === 'completed' && totalAmount && parseFloat(totalAmount) !== 0 ?
                    `<p class="fw-bold text-dark mb-0">₱${parseFloat(totalAmount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>` :
                    ''}
                                </div>
                                <div class="text-end">
                                    <p class="text-muted mb-1">Preferred Date: ${new Date(preferredDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</p>
                                    <span class="badge bg-${this.getStatusBadgeClass(status)}-subtle text-${this.getStatusBadgeClass(status)}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                                    <div class="mt-2">
                                        <button class="btn btn-primary view-product-details view-details" data-booking-id="${id}" onclick="console.log('Clicked booking ID:', ${id})">View Details</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        };
    }

    /**
     * Helper method to fix image paths
     */
    fixImagePath(path) {
        if (!path) return '/assets/images/product-placeholder.jpg';

        // If path already starts with http/https or /, return as is
        if (path.startsWith('http') || path.startsWith('/')) {
            return path;
        }

        // Otherwise, prepend /
        return '/' + path;
    }

    /**
     * Get badge class based on status
     */
    getStatusBadgeClass(status) {
        switch (status.toLowerCase()) {
            case 'pending': return 'warning';
            case 'confirmed': return 'primary';
            case 'in-progress': return 'primary';
            case 'completed': return 'success';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }

    /**
     * Initialize controls within the modal
     */
    initModalControls() {
        // Add event listener to all "View Details" buttons using event delegation
        document.addEventListener('click', (e) => {
            // Check if the clicked element or its parent is a "view-product-details" button 
            const viewDetailsButton = e.target.closest('.view-product-details');

            if (viewDetailsButton) {
                const bookingId = viewDetailsButton.getAttribute('data-booking-id');
                if (bookingId) {
                    this.openBookingModal(bookingId);
                }
            }
        });
    }

    /**
     * Initialize filter and search functionality
     */
    initFilterAndSearch() {
        // Get filter form, search input, and filter selects
        this.filterForm = document.getElementById(this.config.filterFormId);
        this.searchInput = document.getElementById(this.config.searchInputId);
        this.dateFilter = document.getElementById(this.config.dateFilterId);
        this.statusFilter = document.getElementById(this.config.statusFilterId);

        // Add event listeners for filter changes
        if (this.filterForm) {
            this.filterForm.addEventListener('change', () => this.applyFilters());
            this.filterForm.addEventListener('reset', () => {
                setTimeout(() => this.applyFilters(), 10);
            });
        }

        // Add event listener for search input
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.applyFilters(), 300);
            });
        }
    }

    /**
     * Apply all active filters and search
     */
    applyFilters() {
        if (!this.allBookings.length) return;

        this.filteredBookings = [...this.allBookings];

        // Apply date range filter
        if (this.dateFilter && this.dateFilter.value) {
            const now = new Date();
            let startDate;

            switch (this.dateFilter.value) {
                case 'Last 30 days':
                    startDate = new Date();
                    startDate.setDate(now.getDate() - 30);
                    break;
                case 'Last 60 days':
                    startDate = new Date();
                    startDate.setDate(now.getDate() - 60);
                    break;
                case 'Last 90 days':
                    startDate = new Date();
                    startDate.setDate(now.getDate() - 90);
                    break;
                case 'All time':
                default:
                    startDate = null;
            }

            if (startDate) {
                this.filteredBookings = this.filteredBookings.filter(booking => {
                    const dateField = booking.PB_ORDER_DATE || booking.pb_order_date;
                    return new Date(dateField) >= startDate;
                });
            }
        }

        // Apply status filter
        if (this.statusFilter && this.statusFilter.value && this.statusFilter.value !== 'All Status') {
            this.filteredBookings = this.filteredBookings.filter(booking => {
                const status = booking.PB_STATUS || booking.pb_status || '';
                return status.toLowerCase() === this.statusFilter.value.toLowerCase();
            });
        }

        // Apply search filter
        if (this.searchInput && this.searchInput.value.trim() !== '') {
            const searchTerm = this.searchInput.value.trim().toLowerCase();
            this.filteredBookings = this.filteredBookings.filter(booking => {
                const productName = booking.PROD_NAME || booking.prod_name || '';
                const variantCapacity = booking.VAR_CAPACITY || booking.var_capacity || '';
                const id = booking.PB_ID || booking.pb_id || '';

                return productName.toLowerCase().includes(searchTerm) ||
                    variantCapacity.toLowerCase().includes(searchTerm) ||
                    `PB-${id}`.toLowerCase().includes(searchTerm);
            });
        }

        // Reset to first page when filters change
        this.currentPage = 1;

        // Render filtered bookings with pagination
        this.renderBookings(this.filteredBookings);

        // Update results count if element exists
        const resultsCountElement = document.getElementById('booking-results-count');
        if (resultsCountElement) {
            resultsCountElement.textContent = `${this.filteredBookings.length} bookings found`;
        }
    }

    /**
     * Fetch bookings from API and render them
     */
    async fetchAndRenderBookings() {
        try {
            console.log('Fetching bookings from:', this.config.bookingsEndpoint);

            const response = await axios.get(this.config.bookingsEndpoint);
            console.log('Bookings API response:', response.data);

            // Check for success response structure with data field
            let bookings = [];
            if (response.data && response.data.success && Array.isArray(response.data.data)) {
                bookings = response.data.data;
            } else if (Array.isArray(response.data)) {
                bookings = response.data;
            }

            console.log('Processed bookings:', bookings);

            if (bookings.length > 0) {
                // Debug booking IDs
                console.log('Booking IDs:', bookings.map(b => b.PB_ID || b.pb_id));

                // Store all bookings for filtering
                this.allBookings = bookings;
                this.filteredBookings = [...bookings];

                // Render first page of bookings
                this.renderBookings(this.filteredBookings);
            } else {
                console.warn('No bookings found or invalid data format');
                if (this.container) {
                    this.container.innerHTML = '<div class="col-12"><p class="text-center">No bookings available.</p></div>';
                }
                this.renderPagination(0);
            }
        } catch (error) {
            console.error('Error fetching bookings:', error);
            if (this.container) {
                this.container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Failed to load bookings. Please try again later.</p></div>';
            }
            this.renderPagination(0);
        }
    }

    /**
     * Render booking cards with pagination
     */
    renderBookings(bookings) {
        if (!this.container) return;

        if (bookings.length === 0) {
            this.container.innerHTML = '<div class="col-12"><p class="text-center">No bookings match your filters. Try different criteria.</p></div>';
            this.renderPagination(0);
            return;
        }

        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const paginatedBookings = bookings.slice(startIndex, endIndex);

        let html = '';
        paginatedBookings.forEach(booking => {
            html += this.config.cardTemplate(booking);
        });

        this.container.innerHTML = html;

        this.renderPagination(bookings.length);
    }

    /**
     * Render pagination controls
     */
    renderPagination(totalItems) {
        const paginationContainer = document.querySelector(this.config.paginationContainerSelector);
        if (!paginationContainer) return;

        const totalPages = Math.ceil(totalItems / this.itemsPerPage);

        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <nav aria-label="Booking pagination">
                <ul class="pagination">
                    <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="prev"><i class="fas fa-chevron-left"></i></a>
                    </li>
        `;

        // Calculate visible page range
        const maxVisiblePages = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = startPage + maxVisiblePages - 1;

        if (endPage > totalPages) {
            endPage = totalPages;
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // Add first page and ellipsis if needed
        if (startPage > 1) {
            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
            `;

            if (startPage > 2) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <a class="page-link" href="#">...</a>
                    </li>
                `;
            }
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${this.currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Add last page and ellipsis if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <a class="page-link" href="#">...</a>
                    </li>
                `;
            }

            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                </li>
            `;
        }

        paginationHTML += `
                    <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="next"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        `;

        paginationContainer.innerHTML = paginationHTML;

        this.initPaginationControls();
    }

    /**
     * Initialize pagination controls
     */
    initPaginationControls() {
        const paginationContainer = document.querySelector(this.config.paginationContainerSelector);
        if (!paginationContainer) return;

        const paginationLinks = paginationContainer.querySelectorAll('.page-link');

        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();

                // Get the data-page attribute from the clicked element or its parent
                let pageAction;

                if (e.target.hasAttribute('data-page')) {
                    pageAction = e.target.getAttribute('data-page');
                } else if (e.target.parentElement.hasAttribute('data-page')) {
                    pageAction = e.target.parentElement.getAttribute('data-page');
                }

                if (pageAction) {
                    this.handlePageChange(pageAction);
                }
            });
        });
    }

    /**
     * Handle page change
     */
    handlePageChange(pageAction) {
        const totalPages = Math.ceil(this.filteredBookings.length / this.itemsPerPage);

        if (pageAction === 'prev' && this.currentPage > 1) {
            this.currentPage--;
        } else if (pageAction === 'next' && this.currentPage < totalPages) {
            this.currentPage++;
        } else if (!isNaN(pageAction)) {
            const pageNum = parseInt(pageAction);
            if (pageNum >= 1 && pageNum <= totalPages) {
                this.currentPage = pageNum;
            }
        }

        this.renderBookings(this.filteredBookings);
    }

    /**
     * Open booking modal with details
     */
    async openBookingModal(bookingId) {
        try {
            // Debug: Log the URL being called
            const url = `${this.config.bookingsEndpoint}/${bookingId}`;
            console.log('Fetching booking details from:', url);

            const response = await axios.get(url);

            // Debug: Log the response
            console.log('API Response:', response.data);

            // Handle different API response formats
            let booking;
            if (response.data && response.data.success && response.data.data) {
                booking = response.data.data;
            } else {
                booking = response.data;
            }

            if (!booking || (Array.isArray(booking) && booking.length === 0)) {
                console.error('No booking data returned from API');
                alert('Booking details could not be loaded. The booking may not exist.');
                return;
            }

            console.log('Processed booking data:', booking);
            this.currentBooking = booking;
            this.populateModal(booking);

            if (this.bsModal) {
                this.bsModal.show();
            } else if (this.modal.element) {
                // Fallback if bsModal wasn't initialized
                this.bsModal = new bootstrap.Modal(this.modal.element);
                this.bsModal.show();
            } else {
                console.error('Modal element not found');
            }
        } catch (error) {
            console.error('Error fetching booking details:', error);

            // Provide more specific error information
            if (error.response) {
                console.error('Response data:', error.response.data);
                console.error('Response status:', error.response.status);

                if (error.response.status === 404) {
                    alert('Booking not found. The booking may have been deleted or you do not have permission to view it.');
                } else {
                    alert(`Failed to load booking details. Server error: ${error.response.status}`);
                }
            } else if (error.request) {
                console.error('Request made but no response received');
                alert('Failed to load booking details. No response from server.');
            } else {
                alert(`Failed to load booking details: ${error.message}`);
            }
        }
    }

    /**
     * Populate modal with booking details
     */
    populateModal(booking) {
        if (!booking) return;

        // Normalize field names (handle both upper and lowercase)
        const bookingData = {
            id: booking.PB_ID || booking.pb_id,
            variantId: booking.PB_VARIANT_ID || booking.pb_variant_id,
            quantity: booking.PB_QUANTITY || booking.pb_quantity,
            unitPrice: booking.PB_UNIT_PRICE || booking.pb_unit_price,
            totalAmount: booking.PB_TOTAL_AMOUNT || booking.pb_total_amount,
            status: booking.PB_STATUS || booking.pb_status,
            bookingDate: booking.PB_ORDER_DATE || booking.pb_order_date,
            preferredDate: booking.PB_PREFERRED_DATE || booking.pb_preferred_date,
            preferredTime: booking.PB_PREFERRED_TIME || booking.pb_preferred_time,
            address: booking.PB_ADDRESS || booking.pb_address,
            description: booking.PB_DESCRIPTION || booking.pb_description,
            productName: booking.PROD_NAME || booking.prod_name,
            productImage: booking.PROD_IMAGE || booking.prod_image,
            variantCapacity: booking.VAR_CAPACITY || booking.var_capacity
        };

        // Apply styling to modal elements
        if (this.modal.element) {
            // Style the modal dialog
            const modalDialog = this.modal.element.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.classList.add('modal-lg');
                modalDialog.classList.add('modal-dialog-centered');
            }

            // Style the modal header
            const modalHeader = this.modal.element.querySelector('.modal-header');
            if (modalHeader) {
                modalHeader.classList.add('bg-light');
                modalHeader.classList.add('border-0');
            }

            // Style the modal body
            const modalBody = this.modal.element.querySelector('.modal-body');
            if (modalBody) {
                modalBody.classList.add('p-4');
            }
        }

        // Check if modal elements exist before updating them
        if (this.modal.bookingId) {
            this.modal.bookingId.textContent = `PB-${bookingData.id}`;
            this.modal.bookingId.classList.add('fw-bold');
        }

        if (this.modal.productName) {
            this.modal.productName.textContent = bookingData.productName || 'N/A';
            this.modal.productName.classList.add('fs-4');
            this.modal.productName.classList.add('fw-bold');
            this.modal.productName.classList.add('text-primary');
        }

        if (this.modal.productImage) {
            // Fix image path
            this.modal.productImage.src = this.fixImagePath(bookingData.productImage);
            this.modal.productImage.alt = bookingData.productName || 'Product Image';
            this.modal.productImage.classList.add('img-fluid');
            this.modal.productImage.classList.add('rounded');
            this.modal.productImage.classList.add('shadow-sm');
        }

        if (this.modal.variant) {
            this.modal.variant.textContent = bookingData.variantCapacity || 'N/A';
            this.modal.variant.classList.add('badge');
            this.modal.variant.classList.add('bg-secondary');
            this.modal.variant.classList.add('rounded-pill');
            this.modal.variant.classList.add('px-3');
        }

        if (this.modal.quantity) {
            this.modal.quantity.textContent = bookingData.quantity;
            this.modal.quantity.classList.add('fw-bold');
        }

        if (this.modal.unitPrice) {
            this.modal.unitPrice.textContent = bookingData.status === 'completed' && bookingData.unitPrice && parseFloat(bookingData.unitPrice) !== 0
                ? `₱${parseFloat(bookingData.unitPrice).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                : 'Price pending';
            this.modal.unitPrice.classList.add('fw-bold');
        }

        if (this.modal.totalAmount) {
            this.modal.totalAmount.textContent = bookingData.status === 'completed' && bookingData.totalAmount && parseFloat(bookingData.totalAmount) !== 0
                ? `₱${parseFloat(bookingData.totalAmount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                : 'Price pending';
            this.modal.totalAmount.classList.add('fs-4');
            this.modal.totalAmount.classList.add('fw-bold');
            this.modal.totalAmount.classList.add('text-primary');
        }

        if (this.modal.status) {
            const statusText = bookingData.status.charAt(0).toUpperCase() + bookingData.status.slice(1);
            this.modal.status.textContent = statusText;

            // Add appropriate status class
            this.modal.status.className = ''; // Clear existing classes
            this.modal.status.classList.add('badge');

            const statusClass = this.getStatusBadgeClass(bookingData.status);
            this.modal.status.classList.add(`bg-${statusClass}`);
            this.modal.status.classList.add('rounded-pill');
            this.modal.status.classList.add('px-3');
            this.modal.status.classList.add('py-2');
        }

        if (this.modal.bookingDate) {
            this.modal.bookingDate.textContent = new Date(bookingData.bookingDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            this.modal.bookingDate.classList.add('text-muted');
        }

        if (this.modal.preferredDate) {
            this.modal.preferredDate.textContent = new Date(bookingData.preferredDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            this.modal.preferredDate.classList.add('fw-bold');
        }

        if (this.modal.preferredTime) {
            // Format time from HH:MM:SS to HH:MM AM/PM
            let timeText = 'N/A';
            if (bookingData.preferredTime) {
                const timeParts = bookingData.preferredTime.split(':');
                if (timeParts.length >= 2) {
                    const hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1];
                    const period = hours >= 12 ? 'PM' : 'AM';
                    const displayHours = hours % 12 || 12;
                    timeText = `${displayHours}:${minutes} ${period}`;
                }
            }
            this.modal.preferredTime.textContent = timeText;
            this.modal.preferredTime.classList.add('fw-bold');
        }

        if (this.modal.address) {
            this.modal.address.textContent = bookingData.address || 'N/A';
            this.modal.address.classList.add('text-muted');
        }

        if (this.modal.description) {
            this.modal.description.textContent = bookingData.description || 'No additional instructions provided';
            this.modal.description.classList.add('text-muted');
        }
    }
}