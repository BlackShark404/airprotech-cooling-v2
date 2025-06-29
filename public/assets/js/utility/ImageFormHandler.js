// Add to handleImageUpload function in ImageFormHandler.js to manage the loading state
function handleImageUpload(formId, imageInputId, previewId, endpoint, options = {}) {
    const form = document.getElementById(formId);
    const imageInput = document.getElementById(imageInputId);
    const preview = document.getElementById(previewId);
    
    // Default options
    const defaultOptions = {
        closeModal: true,
        modalId: null,
        reloadPage: true,
        reloadDelay: 1500,
        onSuccess: null,
        onError: null,
        loadingText: 'Uploading...'  // Default loading text
    };
    
    // Merge provided options with defaults
    const settings = { ...defaultOptions, ...options };
    
    // Set up image preview functionality
    if (imageInput && preview) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Handle form submission
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Validate file input
            if (!imageInput || !imageInput.files || !imageInput.files[0]) {
                showToast('Error', 'Please select an image to upload', 'danger');
                return;
            }
            
            // Find submit button and store original text if button exists
            const submitButton = form.querySelector('button[type="submit"]');
            let originalText = '';
            
            // Only try to access innerHTML if button exists
            if (submitButton) {
                originalText = submitButton.innerHTML;
                // Show loading state on button
                setLoadingState(true, submitButton, settings.loadingText);
            }
            
            // Create overlay on the preview image
            const previewOverlay = createPreviewOverlay(preview);
            
            // Create form data for submission
            const formData = new FormData(this);
            
            // Send request
            axios.post(endpoint, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Image upload response:', response.data);
                
                // Show success message
                showToast('Success', response.data.message || 'Image uploaded successfully', 'success');
                
                // Update image sources if needed
                if (response.data.data && response.data.data.profile_url) {
                    const oldSrc = preview.getAttribute('data-original-src') || preview.src;
                    const newSrc = response.data.data.profile_url;
                    
                    // Update all images with the same source
                    const images = document.querySelectorAll(`img[src="${oldSrc}"]`);
                    images.forEach(img => {
                        img.src = newSrc;
                    });
                }
                
                // Close modal if requested
                if (settings.closeModal) {
                    const modalId = settings.modalId || form.closest('.modal')?.id;
                    if (modalId) {
                        const modalElement = bootstrap.Modal.getInstance(document.getElementById(modalId));
                        if (modalElement) {
                            modalElement.hide();
                        }
                    }
                }
                
                // Execute success callback if provided
                if (typeof settings.onSuccess === 'function') {
                    settings.onSuccess(response.data);
                }
                
                // Reload page if requested
                if (settings.reloadPage) {
                    setTimeout(() => {
                        location.reload();
                    }, settings.reloadDelay);
                } else {
                    // Reset loading state if not reloading
                    if (submitButton) {
                        setLoadingState(false, submitButton, originalText);
                    }
                    removePreviewOverlay(preview);
                }
            })
            .catch(error => {
                console.error('Image upload error:', error);
                
                // Reset loading state
                if (submitButton) {
                    setLoadingState(false, submitButton, originalText);
                }
                removePreviewOverlay(preview);
                
                // Get error message
                const errorMessage = error.response && error.response.data && error.response.data.message 
                    ? error.response.data.message 
                    : 'Failed to upload image. Please try again.';
                
                // Show error message
                showToast('Error', errorMessage, 'danger');
                
                // Execute error callback if provided
                if (typeof settings.onError === 'function') {
                    settings.onError(error);
                }
            });
        });
    }
    
    /**
     * Set loading state for a button
     * @param {boolean} isLoading - Whether to show loading state
     * @param {HTMLElement} button - The button element
     * @param {string} loadingText - Text to display during loading
     */
    function setLoadingState(isLoading, button, loadingText) {
        if (!button) return;
        
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`;
        } else {
            button.disabled = false;
            button.innerHTML = loadingText; // Original text is passed as loadingText parameter
        }
    }
    
    /**
     * Create an overlay with spinner on the preview image
     * @param {HTMLElement} imageElement - The image element
     * @return {HTMLElement} The created overlay element
     */
    function createPreviewOverlay(imageElement) {
        if (!imageElement) return null;
        
        // Save the original src if not already saved
        if (!imageElement.getAttribute('data-original-src')) {
            imageElement.setAttribute('data-original-src', imageElement.src);
        }
        
        // Get dimensions from the image element
        const width = imageElement.offsetWidth;
        const height = imageElement.offsetHeight;
        
        // Create wrapper if not exists
        let wrapper = imageElement.parentElement;
        if (!wrapper.classList.contains('profile-image-wrapper')) {
            // Create new wrapper with position relative
            wrapper = document.createElement('div');
            wrapper.className = 'profile-image-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.width = `${width}px`;
            wrapper.style.height = `${height}px`;
            wrapper.style.display = 'inline-block';
            wrapper.style.borderRadius = '50%'; // For circular images
            
            // Replace image with wrapper containing image
            imageElement.parentElement.insertBefore(wrapper, imageElement);
            wrapper.appendChild(imageElement);
        }
        
        // Remove any existing overlay
        removePreviewOverlay(imageElement);
        
        // Create overlay element
        const overlay = document.createElement('div');
        overlay.className = 'image-upload-overlay';
        overlay.style.position = 'absolute';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        overlay.style.display = 'flex';
        overlay.style.justifyContent = 'center';
        overlay.style.alignItems = 'center';
        overlay.style.borderRadius = '50%'; // Match the rounded-circle class
        overlay.style.zIndex = '10'; // Ensure it's above the image
        
        // Create spinner
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-light';
        spinner.setAttribute('role', 'status');
        spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
        
        overlay.appendChild(spinner);
        wrapper.appendChild(overlay);
        
        return overlay;
    }
    
    /**
     * Remove the preview overlay
     * @param {HTMLElement} imageElement - The image element
     */
    function removePreviewOverlay(imageElement) {
        if (!imageElement) return;
        
        // Find wrapper
        const wrapper = imageElement.closest('.profile-image-wrapper') || imageElement.parentElement;
        
        // Remove overlay if exists
        const overlay = wrapper.querySelector('.image-upload-overlay');
        if (overlay) {
            wrapper.removeChild(overlay);
        }
    }
}