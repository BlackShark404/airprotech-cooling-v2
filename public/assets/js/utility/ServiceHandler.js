/**
 * Service Handler JS
 * Handles all service-related functionality for the Air Conditioning Services application
 */

// Service selection handler
function initializeServiceHandlers() {
    // Service buttons setup
    setupServiceButtons();

    // Form submission handlers
    setupBookingFormSubmission();
    setupContactFormSubmission();

    // Date picker event for time slot loading
    setupDatePicker();
}

/**
 * Set up service selection buttons
 */
function setupServiceButtons() {
    const serviceButtons = document.querySelectorAll('.service-select-btn');

    serviceButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            // Get service data
            const serviceId = this.dataset.service;

            // Open modal and select the service
            const modal = new bootstrap.Modal(document.getElementById('bookServiceModal'));
            modal.show();

            // Set the selected service in the dropdown
            const serviceSelect = document.getElementById('serviceType');
            if (serviceSelect) {
                serviceSelect.value = serviceId;

                // Trigger change event
                serviceSelect.dispatchEvent(new Event('change'));
            }
        });
    });
}

/**
 * Set up the booking form submission
 */
function setupBookingFormSubmission() {
    const form = document.getElementById('serviceBookingForm');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validate form fields
        if (!validateBookingForm(form)) return;

        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

        // Submit form
        axios.post('/service/book', data, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (response.data.success) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('bookServiceModal'));
                    if (modal) modal.hide();

                    // Show success message
                    showToast('Success', response.data.message, 'success');

                    // Reset form
                    form.reset();

                    // Optionally, refresh the page or show booking details
                    if (response.data.data && response.data.data.bookingId) {
                        // Could show booking details or confirmation
                        showBookingConfirmation(response.data.data.bookingId);
                    }
                }
            })
            .catch(error => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                // Show error message
                const errorMessage = error.response && error.response.data && error.response.data.message
                    ? error.response.data.message
                    : 'An error occurred while processing your request. Please try again.';

                showToast('Error', errorMessage, 'danger');
            });
    });
}

/**
 * Set up the contact form submission
 */
function setupContactFormSubmission() {
    const form = document.getElementById('contactForm');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

        // Submit form
        axios.post('/contact/submit', data, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (response.data.success) {
                    // Show success message
                    showToast('Success', 'Your message has been sent successfully!', 'success');

                    // Reset form
                    form.reset();
                }
            })
            .catch(error => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                // Show error message
                const errorMessage = error.response && error.response.data && error.response.data.message
                    ? error.response.data.message
                    : 'An error occurred while sending your message. Please try again.';

                showToast('Error', errorMessage, 'danger');
            });
    });
}

/**
 * Set up date picker for time slot loading
 */
function setupDatePicker() {
    const dateInput = document.getElementById('preferredDate');

    if (!dateInput) return;

    // Set min date to today
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    dateInput.min = `${yyyy}-${mm}-${dd}`;

    // Add change event listener
    dateInput.addEventListener('change', loadTimeSlots);
}

/**
 * Load available time slots for the selected date
 */
function loadTimeSlots() {
    const dateInput = document.getElementById('preferredDate');
    const timeSelect = document.getElementById('preferredTime');
    const spinner = document.getElementById('timeLoadingSpinner');

    if (!dateInput || !timeSelect) return;

    const selectedDate = dateInput.value;
    if (!selectedDate) return;

    // Clear the current options
    timeSelect.innerHTML = '<option value="" selected disabled>Loading available times...</option>';

    // Show loading spinner
    if (spinner) spinner.style.display = 'block';

    // Make API request to get available time slots
    axios.post('/service/time-slots', { date: selectedDate }, {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';

            // Clear select options
            timeSelect.innerHTML = '';

            if (response.data.success && response.data.data.timeSlots.length > 0) {
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = 'Select a time slot';
                timeSelect.appendChild(defaultOption);

                // Add available time slots
                response.data.data.timeSlots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.value;
                    option.textContent = slot.label;
                    timeSelect.appendChild(option);
                });
            } else {
                // No available slots
                const noSlotsOption = document.createElement('option');
                noSlotsOption.value = '';
                noSlotsOption.disabled = true;
                noSlotsOption.selected = true;
                noSlotsOption.textContent = 'No available times for this date';
                timeSelect.appendChild(noSlotsOption);
            }
        })
        .catch(error => {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';

            // Show error message
            timeSelect.innerHTML = '<option value="" selected disabled>Error loading time slots</option>';

            const errorMessage = error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : 'An error occurred. Please try again.';

            showToast('Error', errorMessage, 'danger');
        });
}

/**
 * Show booking confirmation
 */
function showBookingConfirmation(bookingId) {
    // Could show a confirmation message or redirect to a booking details page
    showToast('Booking Confirmed', `Your booking (ID: ${bookingId}) has been confirmed. You can view the details in your dashboard.`, 'success');
}

/**
 * Load user's existing bookings
 */
function loadUserBookings() {
    const bookingsContainer = document.getElementById('bookingsContainer');
    const spinner = document.getElementById('bookingsLoadingSpinner');

    if (!bookingsContainer) return;

    // Show loading spinner
    if (spinner) spinner.style.display = 'block';

    // Hide bookings container while loading
    bookingsContainer.style.display = 'none';

    // Fetch user's bookings
    axios.get('/service/my-bookings', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';

            // Show bookings container
            bookingsContainer.style.display = 'block';

            if (response.data.success && response.data.data.bookings && response.data.data.bookings.length > 0) {
                // Render bookings
                renderBookings(response.data.data.bookings, bookingsContainer);
            } else {
                // No bookings
                bookingsContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="far fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5>No Bookings Found</h5>
                    <p class="text-muted">You haven't made any service bookings yet.</p>
                    <button class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#bookServiceModal">
                        Book a Service
                    </button>
                </div>
            `;
            }
        })
        .catch(error => {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';

            // Show error message
            bookingsContainer.style.display = 'block';
            bookingsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Error loading bookings. Please try again.
            </div>
        `;

            const errorMessage = error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : 'An error occurred. Please try again.';

            showToast('Error', errorMessage, 'danger');
        });
}

/**
 * Render bookings in the container
 */
function renderBookings(bookings, container) {
    // Create bookings HTML
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Status class mapping
    const statusClasses = {
        'pending': 'status-pending',
        'confirmed': 'status-confirmed',
        'in-progress': 'status-in-progress',
        'completed': 'status-completed',
        'cancelled': 'status-cancelled'
    };

    // Add each booking row
    bookings.forEach(booking => {
        const statusClass = statusClasses[booking.SB_STATUS] || '';

        // Format date and time
        const date = new Date(booking.SB_PREFERRED_DATE);
        const formattedDate = date.toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        // Format time
        const timeParts = booking.SB_PREFERRED_TIME.split(':');
        let hours = parseInt(timeParts[0]);
        const minutes = timeParts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const formattedTime = `${hours}:${minutes} ${ampm}`;

        html += `
            <tr>
                <td>${booking.service_name}</td>
                <td>
                    <div>${formattedDate}</div>
                    <small class="text-muted">${formattedTime}</small>
                </td>
                <td><span class="status-badge ${statusClass}">${booking.SB_STATUS}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary view-booking-btn" data-booking-id="${booking.SB_ID}">
                        <i class="fas fa-eye"></i> View
                    </button>
                    
                    ${booking.SB_STATUS === 'pending' ? `
                        <button class="btn btn-sm btn-danger cancel-booking-btn" data-booking-id="${booking.SB_ID}">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    // Set the HTML
    container.innerHTML = html;

    // Add event listeners for view and cancel buttons
    const viewButtons = container.querySelectorAll('.view-booking-btn');
    const cancelButtons = container.querySelectorAll('.cancel-booking-btn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const bookingId = this.dataset.bookingId;
            viewBookingDetails(bookingId);
        });
    });

    cancelButtons.forEach(button => {
        button.addEventListener('click', function () {
            const bookingId = this.dataset.bookingId;
            cancelBooking(bookingId);
        });
    });
}

/**
 * View booking details
 */
function viewBookingDetails(bookingId) {
    const detailsContainer = document.getElementById('serviceDetailsContainer');
    const spinner = document.getElementById('detailsLoadingSpinner');

    // Hide the bookings modal
    const bookingsModal = bootstrap.Modal.getInstance(document.getElementById('myBookingsModal'));
    if (bookingsModal) bookingsModal.hide();

    // Show the details modal
    const detailsModal = new bootstrap.Modal(document.getElementById('serviceDetailsModal'));
    detailsModal.show();

    // Show loading spinner
    if (spinner) spinner.style.display = 'block';

    // Hide details container while loading
    if (detailsContainer) detailsContainer.style.display = 'none';

    // Fetch booking details
    axios.get(`/service/booking/${bookingId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';

            // Show details container
            if (detailsContainer) detailsContainer.style.display = 'block';

            if (response.data.success && response.data.data.booking) {
                // Render booking details
                renderBookingDetails(response.data.data.booking, detailsContainer);
            } else {
                // No details
                detailsContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Booking details not found.
                </div>
            `;
            }
        })
        .catch(error => {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';

            // Show error message
            if (detailsContainer) {
                detailsContainer.style.display = 'block';
                detailsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error loading booking details. Please try again.
                </div>
            `;
            }

            const errorMessage = error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : 'An error occurred. Please try again.';

            showToast('Error', errorMessage, 'danger');
        });
}

/**
 * Render booking details
 */
function renderBookingDetails(booking, container) {
    // Status class mapping
    const statusClasses = {
        'pending': 'badge bg-warning text-dark',
        'confirmed': 'badge bg-primary',
        'in-progress': 'badge bg-purple',
        'completed': 'badge bg-success',
        'cancelled': 'badge bg-danger'
    };

    const statusClass = statusClasses[booking.SB_STATUS] || 'badge bg-secondary';

    // Format date and time
    const date = new Date(booking.SB_PREFERRED_DATE);
    const formattedDate = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Format time
    const timeParts = booking.SB_PREFERRED_TIME.split(':');
    let hours = parseInt(timeParts[0]);
    const minutes = timeParts[1];
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const formattedTime = `${hours}:${minutes} ${ampm}`;

    // Create HTML
    const html = `
        <div class="booking-details">
            <div class="mb-4">
                <span class="${statusClass} mb-2">${booking.SB_STATUS}</span>
                <h5 class="mt-2">${booking.service_name}</h5>
                <p class="text-muted">${booking.service_description || ''}</p>
            </div>
            
            <div class="mb-3">
                <h6 class="text-primary">Appointment Details</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Date:</strong> ${formattedDate}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Time:</strong> ${formattedTime}</p>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <h6 class="text-primary">Service Location</h6>
                <p>${booking.SB_ADDRESS}</p>
            </div>
            
            <div class="mb-3">
                <h6 class="text-primary">Service Description</h6>
                <p>${booking.SB_DESCRIPTION}</p>
            </div>
            
            <div class="mb-3">
                <h6 class="text-primary">Booking Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Booking ID:</strong> ${booking.SB_ID}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created:</strong> ${new Date(booking.SB_CREATED_AT).toLocaleDateString()}</p>
                    </div>
                </div>
            </div>
            
            ${booking.SB_STATUS === 'pending' ? `
                <div class="mt-4 border-top pt-3">
                    <button class="btn btn-danger cancel-detail-booking-btn" data-booking-id="${booking.SB_ID}">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                </div>
            ` : ''}
        </div>
    `;

    // Set the HTML
    container.innerHTML = html;

    // Add event listener for cancel button
    const cancelButton = container.querySelector('.cancel-detail-booking-btn');
    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            const bookingId = this.dataset.bookingId;
            cancelBooking(bookingId);
        });
    }
}

/**
 * Cancel a booking
 */
function cancelBooking(bookingId) {
    // Show confirmation dialog
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }

    // Send cancellation request
    axios.post(`/service/cancel/${bookingId}`, {}, {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (response.data.success) {
                // Show success message
                showToast('Success', 'Your booking has been cancelled successfully.', 'success');

                // Close modals
                const detailsModal = bootstrap.Modal.getInstance(document.getElementById('serviceDetailsModal'));
                if (detailsModal) detailsModal.hide();

                const bookingsModal = bootstrap.Modal.getInstance(document.getElementById('myBookingsModal'));
                if (bookingsModal) bookingsModal.hide();

                // Reload bookings after a short delay
                setTimeout(() => {
                    // Show bookings modal and reload data
                    const newBookingsModal = new bootstrap.Modal(document.getElementById('myBookingsModal'));
                    newBookingsModal.show();
                    loadUserBookings();
                }, 500);
            }
        })
        .catch(error => {
            // Show error message
            const errorMessage = error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : 'An error occurred while cancelling your booking. Please try again.';

            showToast('Error', errorMessage, 'danger');
        });
}

/**
 * Validate the booking form
 */
function validateBookingForm(form) {
    // Get form fields
    const serviceType = form.querySelector('#serviceType');
    const preferredDate = form.querySelector('#preferredDate');
    const preferredTime = form.querySelector('#preferredTime');
    const serviceDescription = form.querySelector('#serviceDescription');
    const address = form.querySelector('#address');

    // Check if fields are valid
    let isValid = true;
    let errorMessage = '';

    if (!serviceType.value) {
        isValid = false;
        errorMessage = 'Please select a service type.';
        serviceType.focus();
    } else if (!preferredDate.value) {
        isValid = false;
        errorMessage = 'Please select a preferred date.';
        preferredDate.focus();
    } else if (!preferredTime.value) {
        isValid = false;
        errorMessage = 'Please select a preferred time.';
        preferredTime.focus();
    } else if (!address.value.trim()) {
        isValid = false;
        errorMessage = 'Please provide a service address.';
        address.focus();
    }

    if (!isValid) {
        showToast('Error', errorMessage, 'danger');
    }

    return isValid;
}

/**
 * Show a toast notification
 */
function showToast(title, message, type) {

    if (!toastContainer) {
        // Create toast container if it doesn't exist
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
    }

    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0 mb-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    const toastContainer = document.querySelector('.toast-container');
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
    toast.show();

    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

// Initialize handlers when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeServiceHandlers);