// toast-notifications.js
// Module for creating Bootstrap toast notifications with Font Awesome icons

/**
 * Shows a toast notification with the specified title, message, and type
 * @param {string} title - The toast title
 * @param {string} message - The toast message content
 * @param {string} type - Type of toast: 'success', 'danger', 'warning', or 'info'
 */
function showToast(title, message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1050';
        document.body.appendChild(toastContainer);
    }

    // Create a unique ID for the toast
    const toastId = 'toast-' + Date.now();

    // Determine icon and color based on type
    let icon, bgColor;
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle me-2"></i>';
            bgColor = 'bg-success';
            break;
        case 'danger':
        case 'error':
            icon = '<i class="fas fa-exclamation-circle me-2"></i>';
            bgColor = 'bg-danger';
            type = 'danger'; // Normalize type
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle me-2"></i>';
            bgColor = 'bg-warning';
            break;
        case 'info':
        default:
            icon = '<i class="fas fa-info-circle me-2"></i>';
            bgColor = 'bg-info';
            type = 'info'; // Normalize type
            break;
    }

    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center border-0 text-white ${bgColor}`;
    toastEl.id = toastId;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.style.marginBottom = '10px'; // Add margin between stacked toasts

    // Create toast content with icon
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${icon}<strong>${title}:</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    // Add toast to container
    toastContainer.appendChild(toastEl);

    // Initialize and show the toast
    const toast = new bootstrap.Toast(toastEl, {
        animation: true,
        autohide: true,
        delay: 4000
    });
    toast.show();

    // Remove toast from DOM after it's hidden
    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

/**
 * Shows an information toast notification
 * @param {string} message - The toast message content
 */
function showInfoToast(message) {
    showToast('Information', message, 'info');
}

/**
 * Shows a success toast notification
 * @param {string} message - The toast message content
 */
function showSuccessToast(message) {
    showToast('Success', message, 'success');
}

/**
 * Shows a warning toast notification
 * @param {string} message - The toast message content
 */
function showWarningToast(message) {
    showToast('Warning', message, 'warning');
}

/**
 * Shows an error toast notification
 * @param {string} message - The toast message content
 */
function showErrorToast(message) {
    showToast('Error', message, 'danger');
}
