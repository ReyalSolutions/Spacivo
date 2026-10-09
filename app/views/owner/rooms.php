<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<style>
/* Elite Room Management Design System */
:root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.3);
    --accent-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    --accent-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.room-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Glass View */
.house-glass-panel {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    padding: 30px;
    margin-bottom: 2rem;
}

.house-header-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

/* Room Card */
.room-card {
    border: none;
    border-radius: 20px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
    overflow: hidden;
    height: 100%;
}
.room-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
}

.room-card-header {
    padding: 18px 20px;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.room-name {
    font-size: 1.1rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0;
}

.room-price {
    font-size: 1.25rem;
    font-weight: 900;
    color: #3b82f6;
}

.room-body {
    padding: 20px;
}

.room-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 14px;
    margin-bottom: 10px;
}
.room-stat i {
    color: #64748b;
    font-size: 1.1rem;
}
.room-stat-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0;
}
.room-stat-label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.slots-pill {
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 0.75rem;
    font-weight: 800;
}
.slots-full { background: #fee2e2; color: #dc2626; }
.slots-available { background: #dcfce7; color: #16a34a; }

.action-btn {
    padding: 8px 16px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.8rem;
    transition: all 0.2s;
    border: none;
}
.btn-edit-room {
    background: #eff6ff;
    color: #3b82f6;
}
.btn-edit-room:hover {
    background: #3b82f6;
    color: white;
}
</style>

<div class="container-fluid px-4 py-5 room-container">
    <!-- Header Hero -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-8">
            <h1 class="display-5 fw-900 text-dark mb-2 letter-spacing--2">Rooms Management</h1>
            <p class="text-muted fs-5 mb-0 fw-500">
                Manage individual rooms, capacity, and pricing across your entire property portfolio.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-800 shadow d-inline-flex align-items-center gap-2" style="background: var(--accent-blue); border: none;" onclick="openAddRoomModal()">
                <i class="fa-solid fa-plus pt-1"></i>
                <span>ADD NEW ROOM</span>
            </button>
        </div>
    </div>

    <div id="roomsContainer">
        <div class="text-center py-5">
            <div class="ui-skeleton ui-skeleton-line text-primary" role="status"></div>
            <p class="mt-3 text-muted fw-bold">Loading your properties and rooms...</p>
        </div>
    </div>
</div>

<script>
let currentRoomAction = 'store_room';

$(document).ready(function() {
    // Fix modal backdrop z-index overlay issue
    $('#roomModal').appendTo('body');
    
    // Initial fetch of rooms via AJAX
    loadRooms();

    // Form Submit Handler
    $('#roomForm').on('submit', function(e) {
        e.preventDefault();
        
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.text();
        $submitBtn.text('Saving...').prop('disabled', true);

        // Temporarily enable select to serialize
        const $bhSelect = $('#boarding_house_id');
        const wasDisabled = $bhSelect.prop('disabled');
        $bhSelect.prop('disabled', false);
        
        const formData = $(this).serialize();
        $bhSelect.prop('disabled', wasDisabled);

        $.ajax({
            url: '/tenant/?url=owner/' + currentRoomAction,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(() => {
                        $('#roomModal').modal('hide');
                        loadRooms();
                    });
                } else {
                    if (data.message === 'LIMIT_REACHED') {
                        $('#roomModal').modal('hide');
                        $('#upgradePlanModal').modal('show');
                    } else {
                        Swal.fire('Error', data.message || 'Operation failed', 'error');
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'Network connection issue.', 'error');
            },
            complete: function() {
                $submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});

function loadRooms() {
    $.ajax({
        url: '/tenant/?url=owner/get_all_rooms_json',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                renderRooms(response.data);
            } else {
                $('#roomsContainer').html('<div class="alert alert-danger rounded-4 fw-bold">Failed to load rooms.</div>');
            }
        },
        error: function() {
            $('#roomsContainer').html('<div class="alert alert-danger rounded-4 fw-bold">Network error while fetching rooms.</div>');
        }
    });
}

function renderRooms(houses) {
    let html = '';
    let totalRooms = 0;

    $.each(houses, function(index, house) {
        if (!house.rooms || house.rooms.length === 0) return;
        totalRooms += house.rooms.length;

        const escapeHtml = (text) => $('<div>').text(text).html();

        html += `
        <div class="house-glass-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59,130,246,0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <div>
                        <h2 class="house-header-title">${escapeHtml(house.name)}</h2>
                        <div class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i>${escapeHtml(house.address)}</div>
                    </div>
                </div>
            </div>
            <div class="row g-4">`;

        $.each(house.rooms, function(i, room) {
            const isFull = parseInt(room.available_slots) <= 0;
            const slotPillClass = isFull ? 'slots-full' : 'slots-available';
            const slotText = isFull ? 'Full' : `${room.available_slots} Slots Left`;
            const formattedPrice = parseFloat(room.price).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            html += `
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="room-card">
                        <div class="room-card-header">
                            <h4 class="room-name">${escapeHtml(room.room_name)}</h4>
                            <span class="slots-pill ${slotPillClass}">${slotText}</span>
                        </div>
                        <div class="room-body">
                            <div class="mb-4">
                                <div class="small text-muted fw-bold text-uppercase mb-1">Monthly Rate</div>
                                <div class="room-price">₱${formattedPrice}</div>
                            </div>
                            
                            <div class="room-stat">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(148,163,184,0.1); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <p class="room-stat-value">${parseInt(room.capacity)} Persons</p>
                                    <span class="room-stat-label">Total Capacity</span>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button class="action-btn btn-edit-room w-100 d-flex justify-content-center align-items-center gap-2" onclick="openEditRoomModal(${room.id}, ${house.id})">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit Room Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });

        html += `
            </div>
        </div>`;
    });

    if (totalRooms === 0) {
        html = `
        <div class="text-center py-5">
            <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"><i class="fa-solid fa-bed"></i></div>
            <h3 class="fw-bold text-slate-800">No rooms available</h3>
            <p class="text-muted">You haven't added any rooms to your properties yet.</p>
            <button class="btn btn-primary rounded-pill px-4 py-2 mt-3" onclick="openAddRoomModal()" style="background: var(--accent-blue); border: none;">Add Your First Room</button>
        </div>`;
    }

    $('#roomsContainer').html(html);
}

function openAddRoomModal() {
    currentRoomAction = 'store_room';
    $('#roomForm')[0].reset();
    $('#room_id').val('');
    $('#roomModalLabel').text('Add New Room');
    $('#btnDeleteRoom').addClass('d-none');
    $('#boarding_house_id').prop('disabled', false);
    
    $('#roomModal').modal('show');
}

function openEditRoomModal(roomId, houseId) {
    currentRoomAction = 'update_room';
    
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '/tenant/?url=owner/get_room&id=' + roomId,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                Swal.close();
                const r = data.room;
                
                $('#room_id').val(r.id);
                $('#boarding_house_id').val(r.boarding_house_id).prop('disabled', true);
                $('#room_name').val(r.room_name);
                $('#price').val(r.price);
                $('#capacity').val(r.capacity);
                
                $('#roomModalLabel').text('Edit Room Details');
                $('#btnDeleteRoom').removeClass('d-none');
                
                $('#roomModal').modal('show');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Network connection issue.', 'error');
        }
    });
}

function deleteRoom() {
    const roomId = $('#room_id').val();
    if (!roomId) return;
    
    Swal.fire({
        title: 'Delete Room?',
        text: 'This action cannot be undone. Any associated features/amenities will be lost.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            $.ajax({
                url: '/tenant/?url=owner/delete_room',
                method: 'POST',
                data: {
                    room_id: roomId,
                    csrf_token: $('input[name="csrf_token"]').val()
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        Swal.fire({title: 'Deleted!', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false})
                        .then(() => {
                            $('#roomModal').modal('hide');
                            loadRooms();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Network issue.', 'error');
                }
            });
        }
    });
}
</script>

<!-- Room CRUD Modal -->
<div class="modal fade" id="roomModal" tabindex="-1" data-bs-backdrop="false" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background: var(--accent-blue); padding: 20px 24px; border: none;">
                <h5 class="modal-title fw-800 text-white" id="roomModalLabel">Add New Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <form id="roomForm">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                    <input type="hidden" name="room_id" id="room_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Belongs To Property</label>
                        <select class="form-select" name="boarding_house_id" id="boarding_house_id" required style="border-radius: 12px; border-color: #e2e8f0; padding: 10px 15px;">
                            <?php foreach ($houses as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Room Name / Identifier</label>
                        <input type="text" class="form-control" name="room_name" id="room_name" required placeholder="e.g. Room A101" style="border-radius: 12px; border-color: #e2e8f0; padding: 10px 15px;">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase">Monthly Price (₱)</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="price" required placeholder="0.00" style="border-radius: 12px; border-color: #e2e8f0; padding: 10px 15px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase">Total Capacity</label>
                            <input type="number" class="form-control" name="capacity" id="capacity" required min="1" value="1" style="border-radius: 12px; border-color: #e2e8f0; padding: 10px 15px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" id="btnDeleteRoom" class="btn btn-outline-danger fw-bold d-none" style="border-radius: 12px; padding: 10px 20px;" onclick="deleteRoom()">Delete Room</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius: 12px; padding: 10px 20px; background: var(--accent-blue); border: none; margin-left: auto;">Save Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// Include Upgrade & Payment Modals if limit reached
if (isset($limits)) {
    include __DIR__ . '/../components/upgrade_modal.php';
    include __DIR__ . '/../components/payment_modal.php';
}

require __DIR__ . '/../layouts/management_footer.php';
?>
