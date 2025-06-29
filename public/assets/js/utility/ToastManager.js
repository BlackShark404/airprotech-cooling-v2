/**
 * ToastManager Class
 * A standalone class to handle toast notifications with various features:
 * - Multiple toast types (success, error, warning, info)
 * - Configurable position
 * - Auto-close functionality
 * - Progress bar
 * - Pause on hover
 * - Custom styling options
 */
class ToastManager {
  /**
   * @param {Object} options - Configuration options
   * @param {string} options.position - Toast position: top-right, top-left, bottom-right, bottom-left
   * @param {number} options.autoClose - Auto close after specified milliseconds (0 to disable)
   * @param {boolean} options.hideProgressBar - Whether to hide the progress bar
   * @param {boolean} options.closeOnClick - Whether to close when clicked
   * @param {boolean} options.pauseOnHover - Whether to pause countdown on hover
   * @param {boolean} options.draggable - Whether to allow dragging
   * @param {boolean} options.enableIcons - Whether to show icons
   */
  constructor(options = {}) {
    // Default options
    this.options = {
      position: 'bottom-right',
      autoClose: 4000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      enableIcons: true,
      ...options
    };
    
    // Initialize toast container
    this._initializeToastContainer();
  }
  
  /**
   * Initialize the Toast Container
   * @private
   */
  _initializeToastContainer() {
    // Check if toast container already exists
    if ($('#toastContainer').length === 0) {
      // Toast container styles
      const toastContainerStyles = `
        <style>
          #toastContainer {
            position: fixed;
            z-index: 9999;
            padding: 15px;
            pointer-events: none;
          }
          #toastContainer.top-right {
            top: 15px;
            right: 15px;
          }
          #toastContainer.top-left {
            top: 15px;
            left: 15px;
          }
          #toastContainer.bottom-right {
            bottom: 15px;
            right: 15px;
          }
          #toastContainer.bottom-left {
            bottom: 15px;
            left: 15px;
          }
          .toast {
            position: relative;
            max-width: 350px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: white;
            padding: 15px 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            pointer-events: auto;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease-in-out;
          }
          .toast.show {
            opacity: 1;
            transform: translateY(0);
          }
          .toast-icon {
            margin-right: 12px;
            font-size: 20px;
          }
          .toast-content {
            flex: 1;
          }
          .toast-title {
            font-weight: bold;
            margin-bottom: 5px;
          }
          .toast-message {
            font-size: 14px;
          }
          .toast-close {
            margin-left: 10px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            font-size: 18px;
            background: none;
            border: none;
            color: white;
          }
          .toast-close:hover {
            opacity: 1;
          }
          .toast-success {
            background-color: #4caf50;
          }
          .toast-error {
            background-color: #f44336;
          }
          .toast-warning {
            background-color: #ff9800;
          }
          .toast-info {
            background-color: #2196f3;
          }
          .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.3);
          }
          .toast-progress-bar {
            height: 100%;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.5);
            transition: width linear;
          }
        </style>
      `;
      
      // Add styles to document head
      $('head').append(toastContainerStyles);
      
      // Create toast container
      const position = this.options.position || 'top-right';
      $('body').append(`<div id="toastContainer" class="${position}"></div>`);
    } else {
      // Update container position if it already exists
      $('#toastContainer').attr('class', this.options.position);
    }
  }
  
  /**
   * Show a toast notification
   * @param {string} type - Toast type (success, error, warning, info)
   * @param {string} title - Toast title
   * @param {string} message - Toast message
   * @param {Object} options - Additional options to override defaults
   * @returns {Object} Toast element
   */
  showToast(type, title, message, options = {}) {
    const toastOptions = { ...this.options, ...options };
    const toastId = `toast-${Date.now()}`;
    
    // Get the icon based on type
    let icon = '';
    switch (type) {
      case 'success':
        icon = '<i class="fas fa-check-circle"></i>';
        if (!toastOptions.enableIcons) icon = '✓';
        break;
      case 'error':
        icon = '<i class="fas fa-times-circle"></i>';
        if (!toastOptions.enableIcons) icon = '✗';
        break;
      case 'warning':
        icon = '<i class="fas fa-exclamation-triangle"></i>';
        if (!toastOptions.enableIcons) icon = '⚠';
        break;
      case 'info':
        icon = '<i class="fas fa-info-circle"></i>';
        if (!toastOptions.enableIcons) icon = 'ℹ';
        break;
      default:
        icon = '<i class="fas fa-bell"></i>';
        if (!toastOptions.enableIcons) icon = '🔔';
    }
    
    // Create toast HTML
    const toastHtml = `
      <div id="${toastId}" class="toast toast-${type}">
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
          <div class="toast-title">${title}</div>
          <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close">&times;</button>
        ${!toastOptions.hideProgressBar ? '<div class="toast-progress"><div class="toast-progress-bar"></div></div>' : ''}
      </div>
    `;
    
    // Append toast to container
    $('#toastContainer').append(toastHtml);
    const $toast = $(`#${toastId}`);
    
    // Show toast with animation
    setTimeout(() => {
      $toast.addClass('show');
      
      // Set progress bar animation if enabled
      if (!toastOptions.hideProgressBar) {
        $toast.find('.toast-progress-bar').css({
          'width': '0%',
          'transition': `width ${toastOptions.autoClose}ms linear`
        });
      }
      
      // Auto close if enabled
      if (toastOptions.autoClose) {
        setTimeout(() => {
          this._closeToast($toast);
        }, toastOptions.autoClose);
      }
    }, 10);
    
    // Attach close event
    $toast.find('.toast-close').on('click', () => {
      this._closeToast($toast);
    });
    
    // Close on click if enabled
    if (toastOptions.closeOnClick) {
      $toast.on('click', function(e) {
        if ($(e.target).hasClass('toast-close')) return;
        this._closeToast($toast);
      }.bind(this));
    }
    
    // Pause on hover if enabled
    if (toastOptions.pauseOnHover && toastOptions.autoClose) {
      let remainingTime = toastOptions.autoClose;
      let startTime;
      let timeoutId;
      
      $toast.on('mouseenter', function() {
        clearTimeout(timeoutId);
        remainingTime -= (Date.now() - startTime);
        
        // Pause progress bar animation
        if (!toastOptions.hideProgressBar) {
          const $progressBar = $toast.find('.toast-progress-bar');
          const currentWidth = $progressBar.width() / $progressBar.parent().width() * 100;
          $progressBar.css({
            'width': `${currentWidth}%`,
            'transition': 'none'
          });
        }
      });
      
      $toast.on('mouseleave', function() {
        startTime = Date.now();
        
        // Resume progress bar animation
        if (!toastOptions.hideProgressBar) {
          const $progressBar = $toast.find('.toast-progress-bar');
          $progressBar.css({
            'width': '0%',
            'transition': `width ${remainingTime}ms linear`
          });
        }
        
        timeoutId = setTimeout(() => {
          this._closeToast($toast);
        }, remainingTime);
      }.bind(this));
      
      startTime = Date.now();
    }
    
    return $toast;
  }
  
  /**
   * Show a success toast
   * @param {string} title - Toast title
   * @param {string} message - Toast message
   * @param {Object} options - Additional options
   * @returns {Object} Toast element
   */
  showSuccessToast(title, message, options = {}) {
    return this.showToast('success', title, message, options);
  }
  
  /**
   * Show an error toast
   * @param {string} title - Toast title
   * @param {string} message - Toast message
   * @param {Object} options - Additional options
   * @returns {Object} Toast element
   */
  showErrorToast(title, message, options = {}) {
    return this.showToast('error', title, message, options);
  }
  
  /**
   * Show a warning toast
   * @param {string} title - Toast title
   * @param {string} message - Toast message
   * @param {Object} options - Additional options
   * @returns {Object} Toast element
   */
  showWarningToast(title, message, options = {}) {
    return this.showToast('warning', title, message, options);
  }
  
  /**
   * Show an info toast
   * @param {string} title - Toast title
   * @param {string} message - Toast message
   * @param {Object} options - Additional options
   * @returns {Object} Toast element
   */
  showInfoToast(title, message, options = {}) {
    return this.showToast('info', title, message, options);
  }
  
  /**
   * Close a toast
   * @param {Object} $toast - Toast jQuery element
   * @private
   */
  _closeToast($toast) {
    $toast.removeClass('show');
    setTimeout(() => {
      $toast.remove();
    }, 300);
  }
  
  /**
   * Update toast container position
   * @param {string} position - New position (top-right, top-left, bottom-right, bottom-left)
   */
  updatePosition(position) {
    this.options.position = position;
    $('#toastContainer').attr('class', position);
  }
  
  /**
   * Clear all toasts
   */
  clearAll() {
    const $toasts = $('#toastContainer .toast');
    $toasts.removeClass('show');
    setTimeout(() => {
      $toasts.remove();
    }, 300);
  }
}

// Create a global instance for easy access
if (typeof window !== 'undefined') {
  window.toastManager = new ToastManager();
}