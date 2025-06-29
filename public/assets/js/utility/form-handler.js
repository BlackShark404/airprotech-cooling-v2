function handleFormSubmission(formId, actionUrl, refreshPage = false) {
    // Select the form element
    const form = document.getElementById(formId);

    // Check if form exists
    if (!form) {
        console.error(`Form with ID '${formId}' not found`);
        return;
    }

    // Create a function for processing the form submission
    const processFormSubmission = function (event) {
        if (event) {
            event.preventDefault();  // Prevent the default form submission
        }

        // Create an object to store form data
        const data = {};

        // Get all form elements
        const formElements = form.elements;

        // Loop through form elements and collect their values
        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i];

            // Skip buttons and elements without a name
            if (element.name && element.type !== 'button' && element.type !== 'submit') {
                // Handle select elements properly
                if (element.type === 'select-one' || element.type === 'select-multiple') {
                    if (element.value) {
                        data[element.name] = element.value;
                    }
                } else {
                    data[element.name] = element.value;
                }
            }
        }

        // Log the data to be sent (for debugging)
        console.log('Sending data:', data);

        // Make a POST request to the backend PHP script with custom headers
        axios.post(actionUrl, data, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('Response:', response.data);

                // Show success toast
                showToast('Success', response.data.message || 'Operation completed successfully', 'success');

                // ✅ Hide the modal
                const modalElement = bootstrap.Modal.getInstance(form.closest('.modal'));
                if (modalElement) {
                    modalElement.hide();
                }

                // ✅ Reset the form
                form.reset();

                // Optionally refresh the page after a slight delay
                if (refreshPage) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500); // Delay before refresh (adjustable)
                }

                // Optionally, display the updated session value (if needed)
                if (response.data.success && response.data.data && response.data.data.status) {
                    console.log("Updated session status:", response.data.data.status);
                    // Update the UI or perform any other actions based on the updated session value
                }

                // Check if response contains a redirect URL
                if (response.data.success && response.data.data && response.data.data.redirect_url) {
                    // Redirect to the specified URL after a short delay to allow toast to be seen
                    setTimeout(() => {
                        window.location.href = response.data.data.redirect_url;
                    }, 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Display error message if available
                const errorMessage = error.response && error.response.data && error.response.data.message
                    ? error.response.data.message
                    : 'An error occurred. Please try again.';

                // Show error toast
                showToast('Error', errorMessage, 'danger');
            });
    };

    // Check if this is being called from a submit event handler
    // If the caller is passing an event directly, process it immediately
    if (arguments.length > 3 && arguments[3] instanceof Event) {
        return processFormSubmission(arguments[3]);
    }

    // Otherwise, set up the event listener for future submissions
    form.addEventListener('submit', processFormSubmission);
}