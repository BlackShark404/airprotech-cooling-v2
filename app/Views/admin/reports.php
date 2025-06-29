<?php
$title = 'Reports Dashboard - AirProtech';
$activeTab = 'reports';

// Ensure we have a year variable available throughout the page
$currentYear = isset($currentYear) ? $currentYear : date('Y');
$year = $currentYear; // Also define $year for compatibility

// Add any additional styles specific to this page
$additionalStyles = <<<HTML
<style>
    .filter-card {
        border-radius: 12px;
        background-color: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .filter-dropdown {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
        width: 100%;
    }
    .card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
        padding: 15px 20px;
        font-weight: 600;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .card-body {
        padding: 20px;
    }
    .stats-card {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }
    .stats-icon.blue {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .stats-icon.green {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    .stats-icon.orange {
        background-color: rgba(253, 126, 20, 0.1);
        color: #fd7e14;
    }
    .stats-icon.red {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    .stats-title {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 5px;
    }
    .stats-value {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .year-filter {
        width: 120px;
        margin-left: auto;
    }
</style>
HTML;

// Prepare data for JavaScript
$serviceStatusLabels = [];
$serviceStatusData = [];
foreach ($serviceRequestsByStatus as $status) {
    $serviceStatusLabels[] = ucfirst($status['status']);
    $serviceStatusData[] = (int)$status['count'];
}

$serviceTypeLabels = [];
$serviceTypeData = [];
foreach ($serviceRequestsByType as $type) {
    $serviceTypeLabels[] = $type['type_name'];
    $serviceTypeData[] = (int)$type['count'];
}

$monthlyServiceData = array_fill(1, 12, 0);
foreach ($serviceRequestsByMonth as $monthly) {
    $monthlyServiceData[(int)$monthly['month']] = (int)$monthly['count'];
}

$productStatusLabels = [];
$productStatusData = [];
foreach ($productBookingsByStatus as $status) {
    $productStatusLabels[] = ucfirst($status['status']);
    $productStatusData[] = (int)$status['count'];
}

$monthlyProductData = array_fill(1, 12, 0);
foreach ($productBookingsByMonth as $monthly) {
    $monthlyProductData[(int)$monthly['month']] = (int)$monthly['count'];
}

$topProductLabels = [];
$topProductData = [];
foreach ($topSellingProducts as $product) {
    $topProductLabels[] = $product['product_name'];
    $topProductData[] = (int)$product['total_quantity'];
}

$technicianLabels = [];
$technicianTotalData = [];
$technicianCompletedData = [];
foreach ($technicianPerformance as $tech) {
    $technicianLabels[] = $tech['technician_name'];
    $technicianTotalData[] = (int)$tech['total_assignments'];
    $technicianCompletedData[] = (int)$tech['completed_assignments'];
}

$monthlyRevenueData = array_fill(1, 12, 0);
foreach ($revenueByMonth as $monthly) {
    $monthlyRevenueData[(int)$monthly['month']] = (float)$monthly['total_revenue'];
}

// Count totals for summary cards
$totalServiceRequests = array_sum($serviceStatusData);
$totalProductBookings = array_sum($productStatusData);
$totalTechnicians = count($technicianLabels);

// Calculate total revenue excluding 'pending' and 'cancelled' statuses
$totalRevenue = 0;
foreach ($revenueByMonth as $monthly) {
    $totalRevenue += (float)$monthly['total_revenue'];
}

// Create JSON data for JavaScript
$chartData = [
    'serviceStatusLabels' => $serviceStatusLabels,
    'serviceStatusData' => $serviceStatusData,
    'serviceTypeLabels' => $serviceTypeLabels,
    'serviceTypeData' => $serviceTypeData,
    'monthlyServiceData' => array_values($monthlyServiceData),
    'productStatusLabels' => $productStatusLabels,
    'productStatusData' => $productStatusData,
    'monthlyProductData' => array_values($monthlyProductData),
    'topProductLabels' => $topProductLabels,
    'topProductData' => $topProductData,
    'technicianLabels' => $technicianLabels,
    'technicianTotalData' => $technicianTotalData,
    'technicianCompletedData' => $technicianCompletedData,
    'monthlyRevenueData' => array_values($monthlyRevenueData)
];

// Create the JSON string before using it
$chartDataJson = json_encode($chartData);

// Create the JavaScript code without using heredoc to avoid PHP variable interpretation issues
$additionalScripts = '<script>
    // Chart data from PHP
    const chartData = ' . $chartDataJson . ';
    
    // Utility function to get month names
    function getMonthName(monthNumber) {
        const months = ["January", "February", "March", "April", "May", "June", 
                       "July", "August", "September", "October", "November", "December"];
        return months[monthNumber - 1];
    }
    
    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom"
            }
        }
    };
    
    // Chart instances to update when year changes
    let monthlyServiceChart;
    let monthlyProductChart;
    let revenueChart;
    
    // Create charts when DOM is loaded
    document.addEventListener("DOMContentLoaded", function() {
        // Year filter change handler
        document.getElementById("yearFilter").addEventListener("change", function() {
            const year = this.value;
            updateChartsForYear(year);
        });
        
        // Service Request Status Chart
        const serviceStatusCtx = document.getElementById("serviceStatusChart").getContext("2d");
        const serviceStatusChart = new Chart(serviceStatusCtx, {
            type: "pie",
            data: {
                labels: chartData.serviceStatusLabels,
                datasets: [{
                    data: chartData.serviceStatusData,
                    backgroundColor: chartData.serviceStatusLabels.map(status => {
                        switch(status.toLowerCase()) {
                            case "pending": return "#ffc107"; // warning - yellow
                            case "in-progress": return "#0d6efd"; // primary - blue
                            case "confirmed": return "#0dcaf0"; // info - light blue
                            case "completed": return "#198754"; // success - green
                            case "cancelled": return "#dc3545"; // danger - red
                            default: return "#6c757d"; // secondary - gray
                        }
                    }),
                    borderWidth: 1
                }]
            },
            options: commonOptions
        });
        
        // Service Request Type Chart
        const serviceTypeCtx = document.getElementById("serviceTypeChart").getContext("2d");
        const serviceTypeChart = new Chart(serviceTypeCtx, {
            type: "bar",
            data: {
                labels: chartData.serviceTypeLabels,
                datasets: [{
                    label: "Number of Requests",
                    data: chartData.serviceTypeData,
                    backgroundColor: "#0d6efd",
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Monthly Service Requests Chart
        const monthlyServiceCtx = document.getElementById("monthlyServiceChart").getContext("2d");
        monthlyServiceChart = new Chart(monthlyServiceCtx, {
            type: "line",
            data: {
                labels: Array.from({length: 12}, (_, i) => getMonthName(i + 1)),
                datasets: [{
                    label: "Service Requests",
                    data: chartData.monthlyServiceData,
                    backgroundColor: "rgba(13, 110, 253, 0.2)",
                    borderColor: "#0d6efd",
                    borderWidth: 2,
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Product Booking Status Chart
        const productStatusCtx = document.getElementById("productStatusChart").getContext("2d");
        const productStatusChart = new Chart(productStatusCtx, {
            type: "doughnut",
            data: {
                labels: chartData.productStatusLabels,
                datasets: [{
                    data: chartData.productStatusData,
                    backgroundColor: chartData.productStatusLabels.map(status => {
                        switch(status.toLowerCase()) {
                            case "pending": return "#ffc107"; // warning - yellow
                            case "confirmed": return "#0dcaf0"; // info - light blue
                            case "in-progress": return "#0d6efd"; // primary - blue
                            case "completed": return "#198754"; // success - green
                            case "cancelled": return "#dc3545"; // danger - red
                            default: return "#6c757d"; // secondary - gray
                        }
                    }),
                    borderWidth: 1
                }]
            },
            options: commonOptions
        });
        
        // Monthly Product Bookings Chart
        const monthlyProductCtx = document.getElementById("monthlyProductChart").getContext("2d");
        monthlyProductChart = new Chart(monthlyProductCtx, {
            type: "line",
            data: {
                labels: Array.from({length: 12}, (_, i) => getMonthName(i + 1)),
                datasets: [{
                    label: "Product Bookings",
                    data: chartData.monthlyProductData,
                    backgroundColor: "rgba(25, 135, 84, 0.2)",
                    borderColor: "#198754",
                    borderWidth: 2,
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Top Selling Products Chart
        const topProductsCtx = document.getElementById("topProductsChart").getContext("2d");
        const topProductsChart = new Chart(topProductsCtx, {
            type: "bar",
            data: {
                labels: chartData.topProductLabels,
                datasets: [{
                    label: "Units Sold",
                    data: chartData.topProductData,
                    backgroundColor: "#fd7e14",
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                ...commonOptions,
                indexAxis: "y",
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Technician Performance Chart
        const technicianCtx = document.getElementById("technicianChart").getContext("2d");
        const technicianChart = new Chart(technicianCtx, {
            type: "bar",
            data: {
                labels: chartData.technicianLabels,
                datasets: [
                    {
                        label: "Total Assignments",
                        data: chartData.technicianTotalData,
                        backgroundColor: "#6c757d",
                        borderWidth: 0,
                        borderRadius: 5
                    },
                    {
                        label: "Completed Assignments",
                        data: chartData.technicianCompletedData,
                        backgroundColor: "#198754",
                        borderWidth: 0,
                        borderRadius: 5
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Monthly Revenue Chart
        const revenueCtx = document.getElementById("revenueChart").getContext("2d");
        revenueChart = new Chart(revenueCtx, {
            type: "bar",
            data: {
                labels: Array.from({length: 12}, (_, i) => getMonthName(i + 1)),
                datasets: [{
                    label: "Revenue (₱)",
                    data: chartData.monthlyRevenueData,
                    backgroundColor: "#0d6efd",
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
    
    // Function to update charts when year changes
    function updateChartsForYear(year) {
        // Show loading state
        document.querySelectorAll(".chart-container").forEach(container => {
            container.style.opacity = "0.5";
        });
        
        // Update year in chart headers
        document.querySelectorAll(".chart-year").forEach(el => {
            el.textContent = year;
        });
        
        // Fetch data for the selected year
        fetch("/api/admin/reports/" + year)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update monthly service requests chart
                    monthlyServiceChart.data.datasets[0].data = data.data.serviceRequestsByMonth;
                    monthlyServiceChart.update();
                    
                    // Update monthly product bookings chart
                    monthlyProductChart.data.datasets[0].data = data.data.productBookingsByMonth;
                    monthlyProductChart.update();
                    
                    // Update monthly revenue chart
                    revenueChart.data.datasets[0].data = data.data.revenueByMonth;
                    revenueChart.update();
                    
                    // Reset opacity
                    document.querySelectorAll(".chart-container").forEach(container => {
                        container.style.opacity = "1";
                    });
                } else {
                    console.error("Error fetching report data:", data.message);
                    alert("Error fetching report data: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error fetching report data:", error);
                alert("Error fetching report data. Please try again.");
                
                // Reset opacity
                document.querySelectorAll(".chart-container").forEach(container => {
                    container.style.opacity = "1";
                });
            });
    }
</script>';

// Start output buffering for the main content
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Reports Dashboard</h2>
        <div class="d-flex align-items-center">
            <select id="yearFilter" class="form-select filter-dropdown year-filter">
                <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                    <option value="<?= $i ?>" <?= $i == $currentYear ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="stats-title">Total Service Requests</div>
                <div class="stats-value"><?= $totalServiceRequests ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon green">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stats-title">Total Product Bookings</div>
                <div class="stats-value"><?= $totalProductBookings ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon orange">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stats-title">Active Technicians</div>
                <div class="stats-value"><?= $totalTechnicians ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon red">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stats-title">Total Revenue</div>
                <div class="stats-value">₱<?= number_format($totalRevenue, 2) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Service Request Charts -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    Service Request Status
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="serviceStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-md-6">
            <div class="card">
                <div class="card-header">
                    Service Requests by Month (<span class="chart-year"><?= $currentYear ?></span>)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyServiceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Service Requests by Type
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="serviceTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Booking Charts -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    Product Booking Status
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="productStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-md-6">
            <div class="card">
                <div class="card-header">
                    Product Bookings by Month (<span class="chart-year"><?= $currentYear ?></span>)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyProductChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    Top Selling Products
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    Technician Performance (Service & Product Assignments)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="technicianChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Monthly Revenue (<span class="chart-year"><?= $currentYear ?></span>)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the base template (this will output everything)
include __DIR__ . '/../includes/admin/base.php';
?>
