<?php 
require __DIR__ . '/../layouts/management_header.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid py-0">
    <!-- Filters & Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4 p-4 bg-white rounded-4 shadow-sm border animate-fade-down">
        <div>
            <h3 class="m-0 fw-900 fs-5 text-dark">Reports Dashboard</h3>
            <p class="text-muted small mt-1 mb-0 fw-600">Audit and moderate tenant feedback for properties.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-end justify-content-md-end gap-2 header-actions w-100 w-md-auto">
            <!-- Property Filter -->
            <div class="select-wrapper">
                <select id="propertyFilter" class="form-select border shadow-sm rounded-pill px-4 fw-semibold premium-select" 
                        style="min-width: 200px; font-size: 0.85rem;" onchange="loadReportsData()">
                    <option value="">All Properties</option>
                    <?php foreach ($properties as $prop): ?>
                        <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button id="refreshStatsBtn" class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold transition-all text-primary action-btn d-inline-flex align-items-center justify-content-center gap-2 btn-sm hover-rotate" 
                    style="height: 40px;" onclick="loadReportsData()">
                <i class="fa-solid fa-sync-alt" id="refreshIcon" style="font-size: 0.9rem;"></i>
                <span id="refreshText">REFRESH DATA</span>
            </button>
        </div>
    </div>

    <!-- KPI Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-lift transition-all">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4">
                        <i class="fa-solid fa-sack-dollar fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary small fw-semibold mb-1">Total Revenue</h6>
                        <h3 class="fw-bold mb-0 text-dark" id="statRevenue">₱ 0.00</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-lift transition-all">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-4">
                        <i class="fa-solid fa-house-user fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary small fw-semibold mb-1">Total Rented</h6>
                        <h3 class="fw-bold mb-0 text-dark" id="statBookings">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-lift transition-all">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-4">
                        <i class="fa-solid fa-users fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary small fw-semibold mb-1">Active Tenants</h6>
                        <h3 class="fw-bold mb-0 text-dark" id="statTenants">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-lift transition-all">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-4">
                        <i class="fa-solid fa-user-tie fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary small fw-semibold mb-1">Active Owners</h6>
                        <h3 class="fw-bold mb-0 text-dark" id="statOwners">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Revenue Trend Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Revenue Trend (Last 6 Months)</h5>
                    <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">Income Analytics</div>
                </div>
                <div style="height: 350px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Status Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white text-center">
                <h5 class="fw-bold text-dark mb-4 text-start">Booking Statuses</h5>
                <div style="height: 250px;" class="mb-4">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
                <div id="bookingLegend" class="d-flex flex-wrap justify-content-center gap-3 mt-auto">
                    <!-- Legend dynamic -->
                </div>
            </div>
        </div>
    </div>

    <!-- Top Properties -->
    <div class="row g-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-4">Top Performing Boarding Houses</h5>
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-secondary fw-semibold small text-uppercase">Property Name</th>
                                <th class="text-secondary fw-semibold small text-uppercase text-center">Total Bookings</th>
                                <th class="text-secondary fw-semibold small text-uppercase text-end">Est. Revenue</th>
                                <th class="text-secondary fw-semibold small text-uppercase text-center">Performance</th>
                            </tr>
                        </thead>
                        <tbody id="topPropertiesTable">
                            <!-- Rows dynamic -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Grid for Top Properties -->
                <div id="topPropertiesGrid" class="d-md-none row g-3 mt-1">
                    <!-- Cards dynamic -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift { border: 1px solid rgba(226, 232, 240, 0.8) !important; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1) !important; }
.transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.action-btn { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.action-btn:hover { box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.hover-rotate i { transition: transform 0.5s ease; }
.hover-rotate:hover i { transform: rotate(180deg); }

/* Premium Selection Styling (SVG Arrows) */
.premium-select {
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E") !important;
    background-size: 12px !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    padding-right: 32px !important;
    border: 1px solid #cbd5e1 !important;
    background-color: #f1f5f9 !important; /* Not White */
    color: #334155 !important;
    transition: all 0.3s ease !important;
}
.premium-select:hover { border-color: #94a3b8 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; }

@keyframes fa-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.fa-spin { animation: fa-spin 1s infinite linear; }

@media (max-width: 576px) {
    .header-actions {
        width: 100% !important;
        justify-content: flex-end !important;
        gap: 10px !important;
    }
}
</style>

<script>
let revenueChart, bookingChart;

$(document).ready(function() {
    loadReportsData();
});

function loadReportsData() {
    const btn = $('#refreshStatsBtn');
    const icon = $('#refreshIcon');
    const text = $('#refreshText');
    const houseId = $('#propertyFilter').val();
    
    btn.addClass('disabled').css('opacity', '0.7');
    icon.addClass('fa-spin');
    text.text('SYNCING...');

    $.post('/tenant/?url=admin/get_reports_stats', {
        house_id: houseId,
        csrf_token: '<?= htmlspecialchars(Csrf::token()) ?>'
    }, function(res) {
        if (res.success) {
            updateKPIs(res.kpis);
            renderRevenueChart(res.revenueTrend);
            renderBookingChart(res.bookingDist);
            renderTopProperties(res.topProperties);
            
            setTimeout(() => {
                btn.removeClass('disabled').css('opacity', '1');
                icon.removeClass('fa-spin');
                text.text('REFRESH DATA');
            }, 600);
        }
    }, 'json');
}

function updateKPIs(data) {
    $('#statRevenue').text(`₱ ${data.revenue}`);
    $('#statBookings').text(data.bookings);
    $('#statTenants').text(data.tenants);
    $('#statOwners').text(data.owners);
}

function renderRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    if (revenueChart) revenueChart.destroy();
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.4)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.month),
            datasets: [{
                label: 'Monthly Revenue',
                data: data.map(d => d.amount),
                borderColor: '#2563eb',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#fff',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
}

function renderBookingChart(data) {
    const ctx = document.getElementById('bookingStatusChart').getContext('2d');
    if (bookingChart) bookingChart.destroy();

    const colors = {
        'pending': '#f59e0b',
        'approved': '#10b981',
        'confirmed': '#2563eb',
        'cancelled': '#ef4444',
        'rejected': '#94a3b8'
    };

    const statusLabels = data.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1));
    const statusValues = data.map(d => d.count);
    const bgColors = data.map(d => colors[d.status] || '#cbd5e1');

    bookingChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: bgColors,
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });

    // Update Legend
    let legendHtml = '';
    data.forEach((d, i) => {
        legendHtml += `
            <div class="d-flex align-items-center gap-2 small">
                <div style="width: 10px; height: 10px; border-radius: 50%; background: ${bgColors[i]}"></div>
                <span class="text-secondary">${statusLabels[i]}: <b class="text-dark">${d.count}</b></span>
            </div>
        `;
    });
    $('#bookingLegend').html(legendHtml);
}

function renderTopProperties(data) {
    let tableHtml = '';
    let gridHtml = '';
    
    if (!data || data.length === 0) {
        $('#topPropertiesTable').html('<tr><td colspan="4" class="text-center text-muted py-4">No property data available</td></tr>');
        $('#topPropertiesGrid').html('<div class="col-12 text-center py-4 text-muted"><i class="fa-solid fa-house-chimney-crack fa-2x mb-2 d-block opacity-50"></i><small>No property data available</small></div>');
        return;
    }

    data.forEach((p, index) => {
        const revenue = parseFloat(p.revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        const progressWidth = Math.min(100, p.booking_count * 10);
        
        let rankBadge = `<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-circle p-2 px-3 fw-bold shadow-sm">#${index + 1}</span>`;
        if (index === 0) rankBadge = `<span class="badge text-warning bg-warning bg-opacity-10 rounded-circle p-2 px-3 fw-bold shadow-sm"><i class="fa-solid fa-trophy"></i> 1</span>`;
        else if (index === 1) rankBadge = `<span class="badge text-secondary bg-secondary bg-opacity-10 rounded-circle p-2 px-3 fw-bold shadow-sm" style="color: #94a3b8 !important;"><i class="fa-solid fa-medal"></i> 2</span>`;
        else if (index === 2) rankBadge = `<span class="badge text-danger bg-danger bg-opacity-10 rounded-circle p-2 px-3 fw-bold shadow-sm" style="color: #b45309 !important;"><i class="fa-solid fa-award"></i> 3</span>`;

        tableHtml += `
            <tr>
                <td><div class="d-flex align-items-center gap-3">${rankBadge} <div class="fw-bold text-dark text-truncate" style="max-width: 250px;">${p.name}</div></div></td>
                <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">${p.booking_count}</span></td>
                <td class="text-end fw-semibold">₱ ${revenue}</td>
                <td>
                    <div class="progress rounded-pill shadow-sm border" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: ${progressWidth}%"></div>
                    </div>
                </td>
            </tr>
        `;

        gridHtml += `
            <div class="col-12">
                <div class="premium-stat-card p-4 shadow-sm border-0 border-start border-4 position-relative bg-white rounded-4 text-start" style="border-left-color: #2563eb !important;">
                    <div class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <span class="fw-800 opacity-50" style="font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase;">Rank Placement</span>
                        ${rankBadge}
                    </div>
                    <div class="mb-3 text-start">
                        <div class="fw-900 text-dark fs-6" style="word-wrap: break-word; white-space: normal;">
                            <i class="fa-solid fa-house-chimney text-primary opacity-50 me-2"></i>${p.name}
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3 p-3 rounded-4 bg-light bg-opacity-50 border">
                        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                            <span class="fw-800 opacity-50 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Bookings</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">${p.booking_count} Bookings</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span class="fw-800 opacity-50 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">Est. Revenue</span>
                            <div class="fw-900 text-dark fs-5 text-truncate" style="letter-spacing: -0.02em;">₱ ${revenue}</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold" style="font-size: 0.7rem; color: #64748b;">Performance Matrix</span>
                        <span class="fw-bold" style="font-size: 0.7rem; color: #10b981;">${progressWidth}%</span>
                    </div>
                    <div class="progress rounded-pill shadow-sm border" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: ${progressWidth}%"></div>
                    </div>
                </div>
            </div>
        `;
    });

    $('#topPropertiesTable').html(tableHtml);
    $('#topPropertiesGrid').html(gridHtml);
}
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
