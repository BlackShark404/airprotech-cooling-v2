/**
 * ServiceRequestsManager Class
 * Handles creating service request cards, managing the details modal,
 * filtering/searching service requests, and client-side pagination
 */
class ServiceRequestsManager {
    constructor(options = {}) {
        // Default configuration
        this.config = {
            serviceRequestsEndpoint: '/api/user/service-bookings',
            containerSelector: '#service-requests-list-container',
            modalId: 'serviceRequestDetailModal',
            filterFormId: 'service-request-filters',
            searchInputId: 'service-request-search',
            dateFilterId: 'date-filter',
            statusFilterId: 'status-filter',
            itemsPerPage: 5,
            paginationContainerSelector: '#services-pagination-container',
            ...options
        };

        // Set card template after configuration is complete
        this.config.cardTemplate = this.config.cardTemplate || this.getDefaultCardTemplate();

        // Initialize modal elements references
        this.modal = {
            element: document.getElementById(this.config.modalId),
            serviceId: document.getElementById('modal-service-id'),
            serviceName: document.getElementById('modal-service-name'),
            serviceDescription: document.getElementById('modal-service-description'),
            requestedDate: document.getElementById('modal-requested-date'),
            requestedTime: document.getElementById('modal-requested-time'),
            address: document.getElementById('modal-address'),
            status: document.getElementById('modal-status'),
            estimatedCost: document.getElementById('modal-estimated-cost'),
            priority: document.getElementById('modal-priority'),
            notes: document.getElementById('modal-notes'),
            statusBadge: document.getElementById('modal-status-badge'),
            serviceIcon: document.getElementById('modal-service-icon')
        };

        // Container for service request cards
        this.container = document.querySelector(this.config.containerSelector);

        // Store all service requests for filtering
        this.allServiceRequests = [];
        this.filteredServiceRequests = [];

        // Pagination state
        this.currentPage = 1;
        this.itemsPerPage = this.config.itemsPerPage;

        // Initialize modal controls
        this.initModalControls();

        // Initialize filter and search
        this.initFilterAndSearch();
    }

    /**
     * Map service type codes to Font Awesome icons
     */
    getServiceIcon(serviceTypeCode) {
        const iconMap = {
            'checkup-repair': 'fas fa-tools fa-lg',
            'installation': 'fas fa-plug fa-lg',
            'ducting': 'fas fa-wind fa-lg',
            'cleaning-pms': 'fas fa-broom fa-lg',
            'survey-estimation': 'fas fa-search fa-lg',
            'project-quotations': 'fas fa-file-invoice-dollar fa-lg'
        };
        return iconMap[serviceTypeCode] || 'fas fa-cog fa-lg'; // Fallback icon
    }

    /**
     * Default card template for service requests
     */
    getDefaultCardTemplate() {
        return (service) => `
            <div class="booking-item card shadow-sm mb-3">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="service-icon me-4">
                        <i class="${this.getServiceIcon(service.ST_CODE)}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">SRV-${service.SB_ID} <span class="text-muted">${new Date(service.SB_CREATED_AT).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span></p>
                                <h5 class="fw-bold mb-1">${service.ST_NAME}</h5>
                                <p class="text-muted mb-0">Service: ${service.ST_DESCRIPTION || 'N/A'}</p>
                                ${service.SB_STATUS === 'completed' && service.SB_ESTIMATED_COST && parseFloat(service.SB_ESTIMATED_COST) !== 0
                ? `<p class="fw-bold text-dark mb-0">Cost: ₱${parseFloat(service.SB_ESTIMATED_COST).toFixed(2)}</p>`
                : ''}
                            </div>
                            <div class="text-end">
                                <p class="text-muted mb-1">Requested on: ${new Date(service.SB_CREATED_AT).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</p>
                                <span class="badge bg-${this.getStatusBadgeClass(service.SB_STATUS)}-subtle text-${this.getStatusBadgeClass(service.SB_STATUS)}">${service.SB_STATUS ? service.SB_STATUS.charAt(0).toUpperCase() + service.SB_STATUS.slice(1) : 'Unknown'}</span>
                                <div class="mt-2">
                                    <button class="btn btn-danger view-service-details view-details" data-service-id="${service.SB_ID}">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Get badge class based on status
     */
    getStatusBadgeClass(status) {
        if (!status) return 'secondary';

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
            // Check if the clicked element or its parent is a "view-service-details" button
            const viewDetailsButton = e.target.closest('.view-service-details');
            if (viewDetailsButton) {
                const serviceId = viewDetailsButton.getAttribute('data-service-id');
                if (serviceId) {
                    this.openServiceRequestModal(serviceId);
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
                // Use a short timeout to allow form elements to reset before applying filters
                setTimeout(() => this.applyFilters(), 10);
            });
        }

        // Add event listener for search input
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.applyFilters(), 300); // Debounce search
            });
        }
    }

    /**
     * Fetch service requests from the API and render them
     */
    fetchAndRenderServiceRequests() {
        // Show loading state
        if (this.container) {
            this.container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading service requests...</p></div>';
        }

        // Fetch service requests from the API
        axios.get(this.config.serviceRequestsEndpoint)
            .then(response => {
                if (response.data && response.data.success) {
                    this.allServiceRequests = response.data.data || [];
                    this.filteredServiceRequests = [...this.allServiceRequests];
                    this.renderServiceRequests();

                    // Update results count
                    const resultsCountElement = document.getElementById('service-results-count');
                    if (resultsCountElement) {
                        resultsCountElement.textContent = `Showing ${this.filteredServiceRequests.length} of ${this.allServiceRequests.length} service requests`;
                    }
                } else {
                    this.handleError('Failed to load service requests');
                }
            })
            .catch(error => {
                console.error('Error fetching service requests:', error);
                this.handleError('Error loading service requests. Please try again later.');
            });
    }

    /**
     * Render service requests with pagination
     */
    renderServiceRequests() {
        if (!this.container) return;

        // Calculate pagination
        const totalPages = Math.ceil(this.filteredServiceRequests.length / this.itemsPerPage);
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const currentPageItems = this.filteredServiceRequests.slice(startIndex, endIndex);

        // Clear container
        this.container.innerHTML = '';

        // Check if there are any service requests to display
        if (currentPageItems.length === 0) {
            this.container.innerHTML = '<div class="alert alert-info text-center">No service requests found matching your criteria.</div>';
            this.renderPagination(totalPages);
            return;
        }

        // Render service request cards
        currentPageItems.forEach(service => {
            const cardHtml = this.config.cardTemplate(service);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = cardHtml;
            this.container.appendChild(tempDiv.firstElementChild);
        });

        // Render pagination
        this.renderPagination(totalPages);
    }

    /**
     * Render pagination controls
     */
    renderPagination(totalPages) {
        const paginationContainer = document.querySelector(this.config.paginationContainerSelector);
        if (!paginationContainer) return;

        // Clear pagination container
        paginationContainer.innerHTML = '';

        // Don't show pagination if there's only one page
        if (totalPages <= 1) return;

        // Create pagination nav
        const nav = document.createElement('nav');
        nav.setAttribute('aria-label', 'Service requests pagination');
        const ul = document.createElement('ul');
        ul.className = 'pagination';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${this.currentPage === 1 ? 'disabled' : ''}`;
        const prevLink = document.createElement('a');
        prevLink.className = 'page-link';
        prevLink.href = '#';
        prevLink.setAttribute('aria-label', 'Previous');
        prevLink.innerHTML = '<span aria-hidden="true">&laquo;</span>';
        prevLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (this.currentPage > 1) {
                this.currentPage--;
                this.renderServiceRequests();
            }
        });
        prevLi.appendChild(prevLink);
        ul.appendChild(prevLi);

        // Page numbers
        const maxPagesToShow = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        // Adjust if we're near the end
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === this.currentPage ? 'active' : ''}`;
            const link = document.createElement('a');
            link.className = 'page-link';
            link.href = '#';
            link.textContent = i;
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.currentPage = i;
                this.renderServiceRequests();
            });
            li.appendChild(link);
            ul.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${this.currentPage === totalPages ? 'disabled' : ''}`;
        const nextLink = document.createElement('a');
        nextLink.className = 'page-link';
        nextLink.href = '#';
        nextLink.setAttribute('aria-label', 'Next');
        nextLink.innerHTML = '<span aria-hidden="true">&raquo;</span>';
        nextLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.renderServiceRequests();
            }
        });
        nextLi.appendChild(nextLink);
        ul.appendChild(nextLi);

        nav.appendChild(ul);
        paginationContainer.appendChild(nav);
    }

    /**
     * Open the service request detail modal
     */
    openServiceRequestModal(serviceId) {
        try {
            // Find the service request in our existing list.
            // serviceId is a string from data-attribute, SB_ID might be a number or string.
            // Using '==' for comparison handles type coercion (e.g., 123 == "123" is true).
            const service = this.allServiceRequests.find(s => s.SB_ID == serviceId);

            if (!service) {
                // This case implies that the serviceId clicked does not exist in the
                // pre-loaded data. This could indicate a data inconsistency or a stale UI.
                console.error(`Service request with ID ${serviceId} not found in this.allServiceRequests. This might indicate that the local data is incomplete or out of sync with the UI elements.`);
                alert('Error: Could not find details for the selected service. The information may be unavailable or out of date.');
                return;
            }

            // this.currentServiceRequest = service; // Store if needed for other modal interactions

            this.populateModal(service);

            if (!this.modal.element) {
                console.error(`Modal element (ID: ${this.config.modalId}) not found in the DOM.`);
                alert('Error: The details view component is missing. Please contact support.');
                return;
            }

            if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
                console.error('Bootstrap Modal component is not loaded or bootstrap is not defined.');
                alert('Error: A required UI component (Modal) is not available. Please ensure Bootstrap JavaScript is loaded.');
                return;
            }

            const bsModal = bootstrap.Modal.getOrCreateInstance(this.modal.element);
            bsModal.show();

        } catch (error) {
            console.error('Error in openServiceRequestModal:', error);
            alert('An unexpected error occurred while trying to display the service details. Please try again.');
        }
    }

    /**
     * Handle error in the main container
     */
    handleError(message) {
        if (this.container) {
            this.container.innerHTML = `<div class="alert alert-danger">${message}</div>`;
        }
    }

    /**
     * Handle error in the modal
     */
    handleModalError(message) {
        this.modal.serviceName.textContent = 'Error';
        this.modal.serviceDescription.textContent = message;
        this.modal.requestedDate.textContent = '';
        this.modal.requestedTime.textContent = '';
        this.modal.address.textContent = '';
        this.modal.status.textContent = '';
        this.modal.estimatedCost.textContent = '';
        this.modal.priority.textContent = '';
        this.modal.notes.textContent = '';
    }

    /**
     * Apply all active filters and search
     */
    applyFilters() {
        if (!this.allServiceRequests.length) {
            // Data might not be loaded yet if filters are applied before initial fetch completes
            return;
        }

        let filteredServiceRequests = [...this.allServiceRequests];

        // Apply date range filter
        if (this.dateFilter && this.dateFilter.value && this.dateFilter.value !== 'All time') {
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
                // No 'All time' case here, as it implies no date filtering.
            }
            if (startDate) {
                // Normalize start of day for comparison
                startDate.setHours(0, 0, 0, 0);
                filteredServiceRequests = filteredServiceRequests.filter(service =>
                    service.SB_CREATED_AT && new Date(service.SB_CREATED_AT) >= startDate
                );
            }
        }

        // Apply status filter
        if (this.statusFilter && this.statusFilter.value && this.statusFilter.value !== 'All Status') {
            filteredServiceRequests = filteredServiceRequests.filter(service =>
                service.SB_STATUS && service.SB_STATUS.toLowerCase() === this.statusFilter.value.toLowerCase()
            );
        }

        // Apply search filter
        if (this.searchInput && this.searchInput.value.trim() !== '') {
            const searchTerm = this.searchInput.value.trim().toLowerCase();
            filteredServiceRequests = filteredServiceRequests.filter(service =>
                (service.ST_NAME && service.ST_NAME.toLowerCase().includes(searchTerm)) ||
                (service.ST_DESCRIPTION && service.ST_DESCRIPTION.toLowerCase().includes(searchTerm)) ||
                (service.SB_ID && `srv-${service.SB_ID}`.toLowerCase().includes(searchTerm)) // Standardize search for ID
            );
        }

        this.filteredServiceRequests = filteredServiceRequests;
        this.currentPage = 1; // Reset to first page
        this.renderServiceRequests(this.filteredServiceRequests);

        const resultsCountElement = document.getElementById('service-results-count');
        if (resultsCountElement) {
            resultsCountElement.textContent = `${filteredServiceRequests.length} service request${filteredServiceRequests.length !== 1 ? 's' : ''} found`;
        }
    }

    /**
     * Fetch service requests from API and render them
     */
    async fetchAndRenderServiceRequests() {
        // Ensure container exists before trying to manipulate it
        if (!this.container) {
            console.error(`Container with selector '${this.config.containerSelector}' not found.`);
            return;
        }

        try {
            const response = await axios.get(this.config.serviceRequestsEndpoint);

            // Check if response has the expected structure with success and data properties
            if (response.data && response.data.success && Array.isArray(response.data.data)) {
                const serviceRequests = response.data.data;
                this.allServiceRequests = serviceRequests;
                this.filteredServiceRequests = [...serviceRequests]; // Initialize filtered list

                if (serviceRequests.length > 0) {
                    this.applyFilters(); // Apply any default/pre-set filters before initial render
                } else {
                    this.container.innerHTML = '<div class="col-12"><p class="text-center">No service requests available at the moment.</p></div>';
                    this.renderPagination(0);
                }
            } else {
                console.error('Invalid data format received. Expected an array of service requests.', response.data);
                this.container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Could not load service requests due to invalid data format.</p></div>';
                this.renderPagination(0);
            }
        } catch (error) {
            console.error('Error fetching service requests:', error);
            this.container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Failed to load service requests. Please try again later.</p></div>';
            this.renderPagination(0);
        }
    }

    /**
     * Render service request cards with pagination
     */
    renderServiceRequests(serviceRequests) {
        if (!this.container) {
            console.error('Service request container not found for rendering.');
            return;
        }

        if (serviceRequests.length === 0) {
            this.container.innerHTML = '<div class="col-12"><p class="text-center">No service requests match your criteria.</p></div>';
            this.renderPagination(0);
            return;
        }

        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const paginatedServiceRequests = serviceRequests.slice(startIndex, endIndex);

        this.container.innerHTML = paginatedServiceRequests.map(service => this.config.cardTemplate(service)).join('');
        this.renderPagination(serviceRequests.length);
    }

    /**
     * Render pagination controls
     */
    renderPagination(totalItems) {
        const paginationContainer = document.querySelector(this.config.paginationContainerSelector);
        if (!paginationContainer) return;

        const totalPages = Math.ceil(totalItems / this.itemsPerPage);

        if (totalPages <= 1) { // Also hide if only one page
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <nav aria-label="Service request pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="prev" aria-label="Previous"><span aria-hidden="true">«</span></a>
                    </li>
        `;

        const maxPageButtons = 5; // Max number of page buttons to show
        let startPage = Math.max(1, this.currentPage - Math.floor(maxPageButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

        // Adjust startPage if endPage is at the limit and there are fewer than maxPageButtons
        if (endPage - startPage + 1 < maxPageButtons) {
            startPage = Math.max(1, endPage - maxPageButtons + 1);
        }

        if (startPage > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${this.currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        paginationHTML += `
                    <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="next" aria-label="Next"><span aria-hidden="true">»</span></a>
                    </li>
                </ul>
            </nav>
        `;

        paginationContainer.innerHTML = paginationHTML;
        this.initPaginationControls(); // Re-initialize controls as HTML is replaced
    }

    /**
     * Initialize pagination controls
     */
    initPaginationControls() {
        const paginationContainer = document.querySelector(this.config.paginationContainerSelector);
        if (!paginationContainer) return;

        // Remove previous listeners to avoid multiple bindings if called repeatedly
        // A more robust way is to attach one listener to the container.
        // For simplicity, if this function is only called after innerHTML overwrite, it's fine.

        paginationContainer.addEventListener('click', (e) => {
            e.preventDefault();
            const link = e.target.closest('.page-link');
            if (link && !link.closest('.page-item.disabled') && !link.closest('.page-item.active')) {
                const pageAction = link.getAttribute('data-page');
                if (pageAction) {
                    this.handlePageChange(pageAction);
                }
            }
        });
    }

    /**
     * Handle page change
     */
    handlePageChange(pageAction) {
        const totalPages = Math.ceil(this.filteredServiceRequests.length / this.itemsPerPage);

        if (pageAction === 'prev') {
            if (this.currentPage > 1) this.currentPage--;
        } else if (pageAction === 'next') {
            if (this.currentPage < totalPages) this.currentPage++;
        } else {
            const pageNum = parseInt(pageAction, 10);
            if (pageNum >= 1 && pageNum <= totalPages) {
                this.currentPage = pageNum;
            }
        }

        this.renderServiceRequests(this.filteredServiceRequests);

        if (this.container) {
            this.container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Populate modal with service request details
     */
    populateModal(service) {
        if (!service) return;

        const safeSetText = (element, text) => {
            if (element) element.textContent = text !== null && text !== undefined ? String(text) : 'N/A';
        };

        safeSetText(this.modal.serviceId, `SRV-${service.SB_ID}`);
        safeSetText(this.modal.serviceName, service.ST_NAME);
        safeSetText(this.modal.serviceDescription, service.ST_DESCRIPTION || 'No description available');

        if (this.modal.requestedDate) {
            try {
                const date = service.SB_PREFERRED_DATE ? new Date(service.SB_PREFERRED_DATE) : null;
                this.modal.requestedDate.textContent = date ? date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                }) : 'N/A';
            } catch (e) {
                console.warn('Error formatting requestedDate:', e);
                this.modal.requestedDate.textContent = 'Invalid Date';
            }
        }

        // Format the time in 12-hour format with AM/PM
        if (this.modal.requestedTime && service.SB_PREFERRED_TIME) {
            try {
                // Parse the time string (expected format: HH:MM:SS)
                const timeParts = service.SB_PREFERRED_TIME.split(':');
                if (timeParts.length >= 2) {
                    const hours = parseInt(timeParts[0], 10);
                    const minutes = parseInt(timeParts[1], 10);
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    const hours12 = hours % 12 || 12; // Convert to 12-hour format (0 becomes 12)

                    // Format as hours:minutes AM/PM
                    this.modal.requestedTime.textContent = `${hours12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
                } else {
                    safeSetText(this.modal.requestedTime, service.SB_PREFERRED_TIME);
                }
            } catch (e) {
                console.warn('Error formatting requestedTime:', e);
                safeSetText(this.modal.requestedTime, service.SB_PREFERRED_TIME);
            }
        } else {
            safeSetText(this.modal.requestedTime, service.SB_PREFERRED_TIME);
        }

        // Ensure we use the proper field name for address
        safeSetText(this.modal.address, service.SB_ADDRESS || service.sb_address);

        // Set the service icon
        if (this.modal.serviceIcon) {
            this.modal.serviceIcon.innerHTML = `<i class="${this.getServiceIcon(service.ST_CODE)}"></i>`;
        }

        // Set status with badge style
        if (this.modal.statusBadge && service.SB_STATUS) {
            const statusClass = this.getStatusBadgeClass(service.SB_STATUS);
            const statusText = service.SB_STATUS.charAt(0).toUpperCase() + service.SB_STATUS.slice(1);
            this.modal.statusBadge.className = `badge bg-${statusClass}-subtle text-${statusClass}`;
            this.modal.statusBadge.textContent = statusText;
        }

        // Keep the old status field updated for backward compatibility
        if (this.modal.status && service.SB_STATUS) {
            this.modal.status.textContent = service.SB_STATUS.charAt(0).toUpperCase() + service.SB_STATUS.slice(1);
        } else if (this.modal.status) {
            this.modal.status.textContent = 'N/A';
        }

        if (this.modal.estimatedCost) {
            this.modal.estimatedCost.textContent =
                service.SB_STATUS === 'completed' && service.SB_ESTIMATED_COST && parseFloat(service.SB_ESTIMATED_COST) !== 0
                    ? `₱${parseFloat(service.SB_ESTIMATED_COST).toFixed(2)}`
                    : 'Estimate pending';
        }


        if (this.modal.priority && service.SB_PRIORITY) {
            this.modal.priority.textContent = service.SB_PRIORITY.charAt(0).toUpperCase() + service.SB_PRIORITY.slice(1);
        } else if (this.modal.priority) {
            this.modal.priority.textContent = 'N/A';
        }

        // Use SB_DESCRIPTION for the notes field
        safeSetText(this.modal.notes, service.SB_DESCRIPTION || service.sb_description || 'No additional notes');
    }
}