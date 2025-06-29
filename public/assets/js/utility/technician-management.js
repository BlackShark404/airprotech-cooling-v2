/**
 * Technician Management JavaScript
 * 
 * This script handles the technician management interface using DataTablesManager
 * to provide advanced functionality for listing, viewing, adding, editing, and 
 * deleting technicians, as well as assigning them to service bookings.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables for Technician Management
    const technicianTable = new DataTablesManager('technicianTable', {
        ajaxUrl: '/admin/technicians/api',
        columns: [
            {
                data: null,
                title: "Technician",
                render: function (data) {
                    return `
                        <div class="d-flex align-items-center">
                            <img src="${data.profile_url}" class="technician-avatar me-3" alt="${data.full_name}">
                            <div>
                                <div class="fw-bold">${data.full_name}</div>
                                <div class="text-muted small">${data.email}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'is_available',
                title: "Status",
                badge: {
                    valueMap: {
                        true: {
                            display: 'Available',
                            type: 'success'
                        },
                        false: {
                            display: 'Unavailable',
                            type: 'danger'
                        }
                    },
                    pill: true
                }
            },
            {
                data: 'is_active',
                title: "Account Status",
                badge: {
                    valueMap: {
                        true: {
                            display: 'Active',
                            type: 'success'
                        },
                        false: {
                            display: 'Inactive',
                            type: 'secondary'
                        }
                    },
                    pill: true
                }
            },
            { data: 'address', title: "Address" },
            { data: 'phone', title: "Phone" },
            {
                data: null,
                title: "Actions",
                render: function (data) {
                    return `
                        <div class="action-icons">
                            <a href="#" class="action-icon view-icon" data-id="${data.id}" data-bs-toggle="tooltip" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="#" class="action-icon edit-icon" data-id="${data.id}" data-bs-toggle="tooltip" title="Edit Technician">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="#" class="action-icon delete-icon" data-id="${data.id}" data-bs-toggle="tooltip" title="Delete Technician">
                                <i class="bi bi-trash"></i>
                            </a>
                            <a href="#" class="action-icon assign-icon" data-id="${data.id}" data-bs-toggle="tooltip" title="Assign Service">
                                <i class="bi bi-clipboard-check"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        customButtons: {
            refreshButton: {
                text: '<i class="bi bi-arrow-clockwise"></i> Refresh',
                className: 'btn-outline-primary',
                action: function () {
                    technicianTable.refresh();
                }
            }
        },
        // Toast notification options
        toastOptions: {
            position: 'bottom-right',
            autoClose: 3000
        }
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // -----------------------------------------
    // Event Listeners for CRUD Operations
    // -----------------------------------------

    // Add Technician Button Click
    document.getElementById('addTechnicianBtn').addEventListener('click', function () {
        // Reset form
        document.getElementById('addTechnicianForm').reset();

        // Show modal
        const addModal = new bootstrap.Modal(document.getElementById('addTechnicianModal'));
        addModal.show();
    });

    // Add Technician Form Submit
    document.getElementById('addTechnicianForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);
        const technicianData = {
            first_name: formData.get('firstName'),
            last_name: formData.get('lastName'),
            email: formData.get('email'),
            password: formData.get('password'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            is_available: formData.get('isAvailable') === 'on',
            is_active: formData.get('isActive') === 'on'
        };

        // Send AJAX request
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'create_technician',
                ...technicianData
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('addTechnicianModal')).hide();

                    // Show success message
                    technicianTable.showSuccessToast('Success', 'Technician added successfully');

                    // Refresh table
                    technicianTable.refresh();
                } else {
                    // Show error message
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                technicianTable.showErrorToast('Error', 'An error occurred while adding technician');
                console.error('Error:', error);
            });
    });

    // View Technician Click
    document.getElementById('technicianTable').addEventListener('click', function (e) {
        // Find closest view-icon if clicked on icon or its container
        const viewButton = e.target.closest('.view-icon');
        if (!viewButton) return;

        e.preventDefault();

        const technicianId = viewButton.dataset.id;

        // Send AJAX request to get technician details
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'get_technician',
                id: technicianId
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const technician = result.data;

                    // Populate modal with technician details
                    document.getElementById('viewTechnicianName').textContent = technician.full_name;
                    document.getElementById('viewTechnicianEmail').textContent = technician.email;
                    document.getElementById('viewTechnicianPhone').textContent = technician.phone;
                    document.getElementById('viewTechnicianAddress').textContent = technician.address;

                    // Set status badges
                    const availabilityBadge = document.getElementById('viewTechnicianAvailability');
                    availabilityBadge.textContent = technician.is_available ? 'Available' : 'Unavailable';
                    availabilityBadge.className = technician.is_available ?
                        'badge bg-success rounded-pill' : 'badge bg-danger rounded-pill';

                    const accountBadge = document.getElementById('viewTechnicianAccountStatus');
                    accountBadge.textContent = technician.is_active ? 'Active' : 'Inactive';
                    accountBadge.className = technician.is_active ?
                        'badge bg-success rounded-pill' : 'badge bg-secondary rounded-pill';

                    // Set avatar
                    document.getElementById('viewTechnicianAvatar').src = technician.profile_url;

                    // Set statistics
                    if (technician.stats) {
                        document.getElementById('viewTechnicianTotalAssignments').textContent = technician.stats.total_assignments;
                        document.getElementById('viewTechnicianCompletedAssignments').textContent = technician.stats.completed_assignments;
                        document.getElementById('viewTechnicianCurrentWorkload').textContent = technician.stats.current_workload;
                        document.getElementById('viewTechnicianCompletionRate').textContent = technician.stats.completion_rate + '%';
                    }

                    // Show assignments if any
                    const assignmentsList = document.getElementById('viewTechnicianAssignments');
                    assignmentsList.innerHTML = '';

                    if (technician.assignments && technician.assignments.length > 0) {
                        technician.assignments.forEach(assignment => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item';

                            // Format date and time
                            const requestDate = new Date(assignment.sb_preferred_date + ' ' + assignment.sb_preferred_time);
                            const formattedDate = requestDate.toLocaleDateString('en-US', {
                                weekday: 'short',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            // Create priority badge
                            const priorityBadge = document.createElement('span');
                            priorityBadge.className = 'badge rounded-pill float-end ms-1 ';

                            switch (assignment.sb_priority) {
                                case 'urgent':
                                    priorityBadge.className += 'bg-danger';
                                    break;
                                case 'moderate':
                                    priorityBadge.className += 'bg-warning';
                                    break;
                                default:
                                    priorityBadge.className += 'bg-info';
                            }

                            priorityBadge.textContent = assignment.sb_priority;

                            // Create status badge
                            const statusBadge = document.createElement('span');
                            statusBadge.className = 'badge rounded-pill float-end ms-1 ';

                            switch (assignment.ba_status) {
                                case 'assigned':
                                    statusBadge.className += 'bg-primary';
                                    break;
                                case 'in-progress':
                                    statusBadge.className += 'bg-info';
                                    break;
                                case 'completed':
                                    statusBadge.className += 'bg-success';
                                    break;
                                default:
                                    statusBadge.className += 'bg-secondary';
                            }

                            statusBadge.textContent = assignment.ba_status;

                            listItem.innerHTML = `
                            <strong>${assignment.service_type_name}</strong><br>
                            <small>${formattedDate} - ${assignment.customer_name}</small><br>
                            <small>Address: ${assignment.sb_address}</small>
                        `;

                            listItem.appendChild(priorityBadge);
                            listItem.appendChild(statusBadge);

                            assignmentsList.appendChild(listItem);
                        });
                    } else {
                        const listItem = document.createElement('li');
                        listItem.className = 'list-group-item text-center';
                        listItem.textContent = 'No current assignments';
                        assignmentsList.appendChild(listItem);
                    }

                    // Show modal
                    const viewModal = new bootstrap.Modal(document.getElementById('viewTechnicianModal'));
                    viewModal.show();
                } else {
                    // Show error message
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                technicianTable.showErrorToast('Error', 'An error occurred while loading technician details');
                console.error('Error:', error);
            });
    });

    // Edit Technician Click
    document.getElementById('technicianTable').addEventListener('click', function (e) {
        // Find closest edit-icon if clicked on icon or its container
        const editButton = e.target.closest('.edit-icon');
        if (!editButton) return;

        e.preventDefault();

        const technicianId = editButton.dataset.id;

        // Send AJAX request to get technician details
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'get_technician',
                id: technicianId
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const technician = result.data;

                    // Populate form with technician details
                    const form = document.getElementById('editTechnicianForm');
                    form.querySelector('input[name="technicianId"]').value = technician.id;
                    form.querySelector('input[name="firstName"]').value = technician.first_name;
                    form.querySelector('input[name="lastName"]').value = technician.last_name;
                    form.querySelector('input[name="email"]').value = technician.email;
                    form.querySelector('input[name="phone"]').value = technician.phone;
                    form.querySelector('textarea[name="address"]').value = technician.address;
                    form.querySelector('input[name="isAvailable"]').checked = technician.is_available;
                    form.querySelector('input[name="isActive"]').checked = technician.is_active;

                    // Clear password field
                    form.querySelector('input[name="password"]').value = '';

                    // Show modal
                    const editModal = new bootstrap.Modal(document.getElementById('editTechnicianModal'));
                    editModal.show();
                } else {
                    // Show error message
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                technicianTable.showErrorToast('Error', 'An error occurred while loading technician details');
                console.error('Error:', error);
            });
    });

    // Edit Technician Form Submit
    document.getElementById('editTechnicianForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);
        const technicianData = {
            id: formData.get('technicianId'),
            first_name: formData.get('firstName'),
            last_name: formData.get('lastName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            is_available: formData.get('isAvailable') === 'on',
            is_active: formData.get('isActive') === 'on'
        };

        // Add password only if provided
        if (formData.get('password')) {
            technicianData.password = formData.get('password');
        }

        // Send AJAX request
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'update_technician',
                id: technicianData.id,
                ...technicianData
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('editTechnicianModal')).hide();

                    // Show success message
                    technicianTable.showSuccessToast('Success', 'Technician updated successfully');

                    // Refresh table
                    technicianTable.refresh();
                } else {
                    // Show error message
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                technicianTable.showErrorToast('Error', 'An error occurred while updating technician');
                console.error('Error:', error);
            });
    });

    // Delete Technician Click
    document.getElementById('technicianTable').addEventListener('click', function (e) {
        // Find closest delete-icon if clicked on icon or its container
        const deleteButton = e.target.closest('.delete-icon');
        if (!deleteButton) return;

        e.preventDefault();

        const technicianId = deleteButton.dataset.id;
        document.getElementById('deleteTechnicianId').value = technicianId;

        // Show confirmation modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteTechnicianModal'));
        deleteModal.show();
    });

    // Delete Technician Confirmation
    document.getElementById('confirmDeleteTechnician').addEventListener('click', function () {
        const technicianId = document.getElementById('deleteTechnicianId').value;

        // Send AJAX request
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete_technician',
                id: technicianId
            })
        })
            .then(response => response.json())
            .then(result => {
                // Hide modal
                bootstrap.Modal.getInstance(document.getElementById('deleteTechnicianModal')).hide();

                if (result.success) {
                    // Show success message
                    technicianTable.showSuccessToast('Success', 'Technician deleted successfully');

                    // Refresh table
                    technicianTable.refresh();
                } else {
                    // Show error message
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                bootstrap.Modal.getInstance(document.getElementById('deleteTechnicianModal')).hide();
                technicianTable.showErrorToast('Error', 'An error occurred while deleting technician');
                console.error('Error:', error);
            });
    });

    // Assign Service Click
    document.getElementById('technicianTable').addEventListener('click', function (e) {
        // Find closest assign-icon if clicked on icon or its container
        const assignButton = e.target.closest('.assign-icon');
        if (!assignButton) return;

        e.preventDefault();

        const technicianId = assignButton.dataset.id;

        // Send AJAX request to get technician details
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'get_technician',
                id: technicianId
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const technician = result.data;

                    // Set technician info in the modal
                    document.getElementById('assignTechnicianName').textContent = technician.full_name;
                    document.getElementById('assignTechnicianId').value = technician.id;

                    // Get pending bookings
                    return fetch('/admin/technicians/api', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            action: 'get_pending_bookings'
                        })
                    });
                } else {
                    throw new Error(result.message);
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const bookings = result.data;
                    const bookingSelect = document.getElementById('assignBookingId');

                    // Clear existing options
                    bookingSelect.innerHTML = '';

                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = '-- Select a Service Request --';
                    bookingSelect.appendChild(defaultOption);

                    // Add booking options
                    if (bookings.length > 0) {
                        bookings.forEach(booking => {
                            const option = document.createElement('option');
                            option.value = booking.id;

                            const date = new Date(booking.requested_date + ' ' + booking.requested_time);
                            const formattedDate = date.toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            let priorityBadge = '';
                            switch (booking.priority) {
                                case 'urgent':
                                    priorityBadge = '<span class="text-danger">[URGENT]</span> ';
                                    break;
                                case 'moderate':
                                    priorityBadge = '<span class="text-warning">[MODERATE]</span> ';
                                    break;
                            }

                            option.innerHTML = `${priorityBadge}${booking.service_type} - ${booking.customer_name} (${formattedDate})`;
                            bookingSelect.appendChild(option);
                        });

                        // Enable the booking select and submit button
                        bookingSelect.disabled = false;
                        document.getElementById('confirmAssignTechnician').disabled = false;
                    } else {
                        // No bookings available
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No pending service requests available';
                        bookingSelect.appendChild(option);

                        // Disable the booking select and submit button
                        bookingSelect.disabled = true;
                        document.getElementById('confirmAssignTechnician').disabled = true;
                    }

                    // Show modal
                    const assignModal = new bootstrap.Modal(document.getElementById('assignServiceModal'));
                    assignModal.show();
                } else {
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                technicianTable.showErrorToast('Error', 'An error occurred while loading assignment data');
                console.error('Error:', error);
            });
    });

    // Booking selection change
    document.getElementById('assignBookingId').addEventListener('change', function () {
        // Enable/disable submit button based on selection
        const submitButton = document.getElementById('confirmAssignTechnician');
        submitButton.disabled = !this.value;
    });

    // Assign Service Form Submit
    document.getElementById('assignServiceForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);
        const assignmentData = {
            technician_id: formData.get('technicianId'),
            booking_id: formData.get('bookingId'),
            notes: formData.get('notes')
        };

        // Validate required fields
        if (!assignmentData.booking_id) {
            technicianTable.showErrorToast('Error', 'Please select a service request');
            return;
        }

        // Send AJAX request
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'assign_technician',
                ...assignmentData
            })
        })
            .then(response => response.json())
            .then(result => {
                // Hide modal
                bootstrap.Modal.getInstance(document.getElementById('assignServiceModal')).hide();

                if (result.success) {
                    // Show success message
                    technicianTable.showSuccessToast('Success', 'Service request assigned successfully');

                    // Refresh table
                    technicianTable.refresh();
                } else {
                    // Show error message
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                bootstrap.Modal.getInstance(document.getElementById('assignServiceModal')).hide();
                technicianTable.showErrorToast('Error', 'An error occurred while assigning service request');
                console.error('Error:', error);
            });
    });

    // Initialize the DataTable
    refreshTechnicianTable();

    // Function to refresh the technician table
    function refreshTechnicianTable() {
        fetch('/admin/technicians/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'get_technicians'
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Update the table with new data
                    technicianTable.refresh(result.data);
                } else {
                    technicianTable.showErrorToast('Error', result.message);
                }
            })
            .catch(error => {
                technicianTable.showErrorToast('Error', 'An error occurred while loading technicians');
                console.error('Error:', error);
            });
    }
});