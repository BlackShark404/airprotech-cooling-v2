document.addEventListener("DOMContentLoaded", function () {
  // Initialize DataTablesManager with improved configuration
  const userTableManager = new DataTablesManager("usersTable", {
    ajaxUrl: "/api/users",
    responsive: true,
    dom: '<"row align-items-center mb-3"<"col-md-6 d-flex align-items-center"<"table-title me-3"><"table-length"l>><"col-md-6 d-flex justify-content-end"f>>rt<"row align-items-center"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',
    autoWidth: false, // Disable auto width calculation

    // Add page length options
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    pageLength: 10, // Default number of records per page

    // Disable automatic adding of action column by setting callbacks to null initially
    // We'll handle these separately after initialization
    viewRowCallback: null,
    editRowCallback: null,

    //This is for add  customer
    columns: [
      { data: "id", title: "ID" },
      {
        // Combined name column
        data: null,
        title: "Name",
        render: function (data, type, row) {
          return `<div class="d-flex align-items-center">
                    <div class="me-2 rounded-circle" style="width: 40px; height: 40px; overflow: hidden;">
                      <img src="${row.profile_url || '/assets/images/avatar/default-avatar.png'}" alt="${row.first_name}" class="img-fluid rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <p class="mb-0 fw-medium text-dark">${row.first_name} ${row.last_name}</p>
                  </div>`;
        },
      },
      { data: "email", title: "Email" },
      {
        data: "role",
        title: "Role",
        render: function (data, type, row) {
          let badgeClass = " ";
          if (data === "admin") {
            badgeClass = "badge-admin";
          } else if (data === "technician") {
            badgeClass = "badge-technician";
          } else if (data === "customer") {
            badgeClass = "badge-customer";
          }

          return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)
            }</span>`;
        },
      },
      {
        data: "status",
        title: "Status",
        render: function (data, type, row) {
          const isActive = data === "active";
          const indicatorClass = isActive ? "status-active" : "status-inactive";
          const badgeClass = isActive ? "badge-active" : "badge-inactive";

          return `<span class="badge ${badgeClass}">
                          <span class="status-indicator ${indicatorClass}"></span>
                          ${data.charAt(0).toUpperCase() + data.slice(1)}
                      </span>`;
        },
      },
      {
        data: "registered",
        title: "Registered",
        render: function (data, type, row) {
          return `<span class="text-nowrap"><i class="bi bi-calendar3 me-1 text-muted"></i>${data}</span>`;
        },
      },
      {
        data: "last_login",
        title: "Last Login",
        render: function (data, type, row) {
          if (!data) {
            return '<span class="text-muted">Never</span>';
          }
          return `<span class="text-nowrap"><i class="bi bi-clock me-1 text-muted"></i>${data}</span>`;
        },
      },
      {
        // Action column with icons
        data: null,
        title: "Actions",
        orderable: false,
        className: "text-center",
        render: function (data, type, row) {
          return `<div class="d-flex">
                    <div class="action-icon action-icon-view view-btn me-1" data-id="${row.id}">
                      <i class="bi bi-eye"></i>
                    </div>
                    <div class="action-icon action-icon-edit edit-btn" data-id="${row.id}">
                      <i class="bi bi-pencil"></i>
                    </div>
                  </div>`;
        }
      }
    ],

    // After data loaded callback to update user count
    afterDataLoadedCallback: function (data) {
      updateUserCount();
    },

    // Remove all buttons
    buttons: [],
  });

  // Add table title
  $(".table-title").html('<h5 class="mb-0">User List</h5>');

  // Add search icon to the search input and improve styling
  $(".dataTables_filter input").addClass("form-control");
  $(".dataTables_filter").addClass("position-relative");
  $(".dataTables_filter label").html(`
    <div class="input-group float-end" style="width: 250px;">
      <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
      <input type="search" class="form-control border-start-0" placeholder="Search users..." aria-controls="${userTableManager.tableId}">
    </div>
  `);

  // Style the length menu
  $(".table-length select").addClass("form-select");
  $(".table-length").addClass("d-flex align-items-center");
  $(".table-length label").html(`
    <div class="d-flex align-items-center">
      <span class="me-2 text-muted small">Show</span>
      <select name="usersTable_length" aria-controls="usersTable" class="form-select form-select-sm">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="-1">All</option>
      </select>
      <span class="ms-2 text-muted small">entries</span>
    </div>
  `);

  // Restore length menu functionality
  $(".table-length select").on('change', function () {
    userTableManager.dataTable.page.len($(this).val()).draw();
  });

  // Apply Bootstrap styling to pagination
  function enhancePagination() {
    // Step 1: Add Bootstrap pagination container classes
    $(".dataTables_paginate").addClass("pagination-container");

    // Step 2: Convert DataTables pagination to Bootstrap pagination
    if ($(".pagination-container ul.pagination").length === 0) {
      // Create a Bootstrap pagination container if it doesn't exist
      $(".pagination-container").wrapInner("<ul class='pagination'></ul>");
    }

    // Step 3: Style all paginate buttons as Bootstrap page items/links
    $(".dataTables_paginate .paginate_button").each(function () {
      $(this).addClass("page-item");

      // If this button doesn't have a page-link yet, wrap its content in one
      if ($(this).children(".page-link").length === 0) {
        const buttonContent = $(this).html();
        $(this).html(`<a class="page-link" href="#">${buttonContent}</a>`);
      }

      // Handle disabled state
      if ($(this).hasClass("disabled")) {
        $(this).addClass("disabled");
      }

      // Handle active state
      if ($(this).hasClass("current")) {
        $(this).addClass("active");
      }
    });

    // Step 4: Special handling for previous/next buttons
    $(".paginate_button.previous, .paginate_button.next").addClass("page-item");

    // Step 5: Apply proper styling to ellipsis
    $(".ellipsis").addClass("page-item disabled").html('<a class="page-link" href="#">...</a>');
  }

  // Apply pagination styling on initial load
  enhancePagination();

  // Re-apply when table is redrawn (for sorting, filtering, pagination changes)
  $("#usersTable").on("draw.dt", function () {
    enhancePagination();
  });

  // Restore search functionality after customizing the search input
  $(".dataTables_filter input").on('keyup', function (e) {
    userTableManager.dataTable.search(this.value).draw();
  });

  // Manually attach event handlers for view and edit buttons
  $("#usersTable").on("click", ".view-btn", function () {
    const id = $(this).data("id");
    const rowData = userTableManager.data.find(row => row.id == id);
    if (rowData) {
      handleViewUser(rowData, userTableManager);
    }
  });

  $("#usersTable").on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    const rowData = userTableManager.data.find(row => row.id == id);
    if (rowData) {
      setupEditUserModal(rowData, userTableManager);
    }
  });

  // View user handler function
  function handleViewUser(rowData, tableManager) {
    // Set user profile image
    $("#userProfileImage").attr("src", rowData.profile_url || "/assets/images/default-avatar.png");

    // Populate the view modal with user data
    $("#viewUserId").text(rowData.id);
    $("#viewUserName").text(rowData.first_name + " " + rowData.last_name);
    $("#viewUserEmail").text(rowData.email);

    // Set role with badge
    let roleBadgeClass = "bg-success";
    if (rowData.role === "admin") {
      roleBadgeClass = "bg-danger";
    } else if (rowData.role === "technician") {
      roleBadgeClass = "bg-primary";
    }

    $("#viewUserRole").html(
      `<span class="badge ${roleBadgeClass} rounded-pill">${rowData.role.charAt(0).toUpperCase() + rowData.role.slice(1)
      }</span>`
    );

    // Set status with badge
    const statusBadgeClass =
      rowData.status === "active" ? "bg-success" : "bg-danger";
    $("#viewUserStatus").html(
      `<span class="badge ${statusBadgeClass} rounded-pill">${rowData.status.charAt(0).toUpperCase() + rowData.status.slice(1)
      }</span>`
    );

    $("#viewUserRegistered").text(rowData.registered);
    $("#viewUserLastLogin").text(rowData.last_login || "Never");

    // Show the modal
    const viewModal = new bootstrap.Modal(
      document.getElementById("viewUserModal")
    );
    viewModal.show();

    // Setup edit button in view modal
    $("#viewUserEditBtn")
      .off("click")
      .on("click", function () {
        // Hide view modal
        viewModal.hide();

        // Setup and show edit modal
        setupEditUserModal(rowData, tableManager);
      });
  }

  // Function to update user count
  function updateUserCount() {
    const table = $("#usersTable").DataTable();
    const filteredData = table.rows({ search: "applied" }).data();
    $("#userCount").text(`User List (${filteredData.length})`);
  }

  // Auto-apply filters when selection changes
  $("#roleFilter, #statusFilter").on("change", function () {
    applyTableFilters();
  });

  // Apply filters to table
  function applyTableFilters() {
    const roleFilter = $("#roleFilter").val();
    const statusFilter = $("#statusFilter").val();

    // Apply filters
    const filters = {};
    if (roleFilter) filters.role = roleFilter;
    if (statusFilter) filters.status = statusFilter;

    userTableManager.applyFilters(filters);

    // Update user count
    updateUserCount();
  }

  // Reset filters
  $("#resetFilters").on("click", function () {
    // Reset filter selects
    $("#roleFilter").val("");
    $("#statusFilter").val("");
    $("#searchInput").val("");

    // Clear filters
    userTableManager.applyFilters({});

    // Clear search
    const table = $("#usersTable").DataTable();
    table.search("").draw();

    // Update user count
    updateUserCount();
  });

  // Search input keyup event
  $("#searchInput").on("keyup", function (e) {
    if (e.key === "Enter") {
      applyTableFilters();
    }
  });

  // Handle add user form submission
  $("#saveUserBtn").on("click", function () {
    // Validate form
    const firstName = $("#first_name").val();
    const lastName = $("#last_name").val();
    const email = $("#email").val();
    const password = $("#password").val();
    const confirmPassword = $("#confirm_password").val();
    const roleId = parseInt($("#role_id").val()); // Ensure roleId is an integer
    const isActive = parseInt($("#is_active").val()); // Ensure isActive is an integer

    // Simple validation
    if (!firstName || !lastName || !email || !password || !roleId) {
      userTableManager.showErrorToast(
        "Validation Error",
        "Please fill all required fields"
      );
      return;
    }

    // Check passwords match
    if (password !== confirmPassword) {
      userTableManager.showErrorToast(
        "Validation Error",
        "Passwords do not match"
      );
      return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      userTableManager.showErrorToast(
        "Validation Error",
        "Please enter a valid email address"
      );
      return;
    }

    const formData = {
      first_name: firstName,
      last_name: lastName,
      email: email,
      password: password,
      role_id: roleId, // Now correctly as a number
      is_active: isActive, // Now correctly as a number
    };

    // Submit form via AJAX
    $.ajax({
      url: "/api/users",
      method: "POST",
      data: JSON.stringify(formData),
      contentType: "application/json",
      success: function (response) {
        if (response.success) {
          // Properly close the modal and clean up
          const addModalEl = document.getElementById("addUserModal");

          // First manually remove the modal backdrop and reset body styles
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');

          // Try to get Bootstrap modal instance and hide it
          try {
            const bsModal = bootstrap.Modal.getInstance(addModalEl);
            if (bsModal) {
              bsModal.hide();
            }
          } catch (error) {
            console.log("Modal already closed or instance not found");
          }

          // Reset form
          $("#addUserForm")[0].reset();

          // Refresh table
          userTableManager.dataTable.ajax.reload();

          // Show success message
          userTableManager.showSuccessToast("User Added", response.message);
        } else {
          userTableManager.showErrorToast("Error", response.message);
        }
      },
      error: function (xhr) {
        const response = xhr.responseJSON || { message: "Server error" };
        userTableManager.showErrorToast("Error", response.message);
      },
    });
  });

  // Handle edit user form submission
  $("#updateUserBtn").on("click", function () {
    // Get form data
    const userId = $("#edit_user_id").val();
    const firstName = $("#edit_first_name").val();
    const lastName = $("#edit_last_name").val();
    const email = $("#edit_email").val();
    const password = $("#edit_password").val();
    // Get roleId from the disabled select (we'll keep the original value)
    const roleId = parseInt($("#edit_role_id").val());
    // Convert isActive to boolean instead of integer
    const isActive = $("#edit_is_active").val() === "true";

    // Simple validation
    if (!firstName || !lastName || !email) {
      userTableManager.showErrorToast(
        "Validation Error",
        "Please fill all required fields"
      );
      return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      userTableManager.showErrorToast(
        "Validation Error",
        "Please enter a valid email address"
      );
      return;
    }

    // Prepare form data
    const formData = {
      first_name: firstName,
      last_name: lastName,
      email: email,
      role_id: roleId, // Include role_id but it won't change since field is disabled
      is_active: isActive
    };

    // Add password only if provided
    if (password) {
      formData.password = password;
    }

    // Submit form via AJAX
    $.ajax({
      url: `/api/users/${userId}`,
      method: "PUT",
      data: JSON.stringify(formData),
      contentType: "application/json",
      success: function (response) {
        if (response.success) {
          // Properly close the modal and clean up
          const editModalEl = document.getElementById("editUserModal");

          // First manually remove the modal backdrop and reset body styles
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');

          // Try to get Bootstrap modal instance and hide it
          try {
            const bsModal = bootstrap.Modal.getInstance(editModalEl);
            if (bsModal) {
              bsModal.hide();
            }
          } catch (error) {
            console.log("Modal already closed or instance not found");
          }

          // Refresh table
          userTableManager.dataTable.ajax.reload();

          // Show success message
          userTableManager.showSuccessToast("User Updated", response.message);
        } else {
          userTableManager.showErrorToast("Error", response.message);
        }
      },
      error: function (xhr) {
        const response = xhr.responseJSON || { message: "Server error" };
        userTableManager.showErrorToast("Error", response.message);
      },
    });
  });

  // Function to setup edit user modal 
  function setupEditUserModal(rowData, tableManager) {
    // Set form values
    $("#edit_user_id").val(rowData.id);
    $("#edit_first_name").val(rowData.first_name);
    $("#edit_last_name").val(rowData.last_name);
    $("#edit_email").val(rowData.email);

    // FIXED: Properly map role_id based on the actual database values
    // Make sure these match your actual database values in USER_ROLE table
    let roleId;
    if (rowData.role === "admin") {
      roleId = "3"; // ID for admin in your database
    } else if (rowData.role === "technician") {
      roleId = "2"; // ID for technician in your database
    } else if (rowData.role === "customer") {
      roleId = "1"; // ID for customer in your database
    } else {
      roleId = "1"; // Default to customer
    }

    console.log("Setting role dropdown for: " + rowData.role + " with value: " + roleId);

    // Set the dropdown value (even though it's disabled, we want to display the correct role)
    $("#edit_role_id").val(roleId);

    // Set status using true/false strings
    $("#edit_is_active").val(rowData.status === "active" ? "true" : "false");

    // Clear password field (for security)
    $("#edit_password").val("");

    // Show the edit modal
    const editModal = new bootstrap.Modal(
      document.getElementById("editUserModal")
    );
    editModal.show();

    // Diagnostic check after modal is shown
    setTimeout(function () {
      console.log("After modal shown, role value is: " + $("#edit_role_id").val());
      console.log("Selected option text: " + $("#edit_role_id option:selected").text());
    }, 100);
  }

  // Toggle password visibility
  $("#togglePassword").on("click", function () {
    const passwordField = $("#password");
    const type =
      passwordField.attr("type") === "password" ? "text" : "password";
    passwordField.attr("type", type);
    $(this).find("i").toggleClass("bi-eye bi-eye-slash");
  });

  $("#toggleConfirmPassword").on("click", function () {
    const passwordField = $("#confirm_password");
    const type =
      passwordField.attr("type") === "password" ? "text" : "password";
    passwordField.attr("type", type);
    $(this).find("i").toggleClass("bi-eye bi-eye-slash");
  });

  $("#toggleEditPassword").on("click", function () {
    const passwordField = $("#edit_password");
    const type =
      passwordField.attr("type") === "password" ? "text" : "password";
    passwordField.attr("type", type);
    $(this).find("i").toggleClass("bi-eye bi-eye-slash");
  });

  // Add CSS for icon buttons
  $('<style>')
    .prop('type', 'text/css')
    .html(`
      .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 4px;
      }
      .btn-icon i {
        font-size: 14px;
      }
    `)
    .appendTo('head');
});