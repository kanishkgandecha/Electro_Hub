<?php
$page_title = "Profile - ElectroHUT";
session_start();
require_once 'functions.php';

if (!isAuthenticated()) {
    redirectWithMessage('login.php', 'Please login to view your profile.', 'warning');
}

$conn = connectDB();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<style>
    .profile-page {
        background: #0a192f;
        min-height: 100vh;
    }
    
    .profile-card {
        background: #112240;
        border: 1px solid #233554;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .avatar-placeholder {
        width: 120px;
        height: 120px;
        background: #64ffda;
        color: #0a192f;
        font-weight: 700;
        font-size: 2.5rem !important;
        transition: transform 0.3s;
    }
    
    .avatar-placeholder:hover {
        transform: rotate(15deg);
    }
    
    .info-table td {
        padding: 0.75rem;
        border-color: #233554 !important;
    }
    
    .info-table th {
        color: #64ffda !important;
        background: rgba(17, 34, 64, 0.5);
    }
    
    .order-table {
        border-color: #233554 !important;
    }
    
    .order-table th {
        background: #112240;
        color: #64ffda;
    }
    
    .order-table tr:hover {
        background: rgba(17, 34, 64, 0.3) !important;
    }
    
    .badge-status {
        padding: 0.5em 0.8em;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .form-control {
        background: #0a192f;
        border: 1px solid #233554;
        color: #fff;
    }
    
    .form-control:focus {
        background: #0a192f;
        border-color: #64ffda;
        box-shadow: 0 0 0 0.25rem rgba(100, 255, 218, 0.25);
        color: #fff;
    }
    
    .modal-content {
        background: #112240;
        border: 1px solid #233554;
    }
    
    @media (max-width: 768px) {
        .profile-card {
            margin-top: 1.5rem;
        }
        
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            font-size: 1.8rem !important;
        }
    }
</style>

<section class="profile-page py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="profile-card mb-4">
                    <div class="card-body text-center p-4">
                        <div class="profile-avatar mb-4">
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center mx-auto">
                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                            </div>
                        </div>
                        <h3 class="mb-2 text-white"><?= htmlspecialchars($user['username']) ?></h3>
                        <p class="text-muted mb-4"><?= htmlspecialchars($user['email']) ?></p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-accent btn-sm" id="edit-profile-btn">
                                <i class="bi bi-pencil-square me-2"></i>Edit Profile
                            </button>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="mb-0">Your Profile</h2>
                            <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
                        </div>
                        
                        <div id="profile-info">
                            <div class="mb-4">
                                <h5>Account Information</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm text-white">
                                        <tr>
                                            <th width="30%">Username:</th>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Address:</th>
                                            <td><?= htmlspecialchars($user['address'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>City:</th>
                                            <td><?= htmlspecialchars($user['city'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>State:</th>
                                            <td><?= htmlspecialchars($user['state'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Country:</th>
                                            <td><?= htmlspecialchars($user['country'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Postal Code:</th>
                                            <td><?= htmlspecialchars($user['postal_code'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Member since:</th>
                                            <td><?= date('F j, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div id="profile-edit-form" style="display: none;">
                            <form id="update-profile-form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="edit-username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="edit-username" 
                                               value="<?= htmlspecialchars($user['username']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="edit-email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="edit-phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-dob" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="edit-dob" 
                                               value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="edit-address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="edit-address" 
                                               value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="edit-city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="edit-city" 
                                               value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="edit-state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="edit-state" 
                                               value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="edit-postal" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="edit-postal" 
                                               value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-country" class="form-label">Country</label>
                                        <select class="form-select" id="edit-country">
                                            <option value="">Select Country</option>
                                            <option value="India" <?= ($user['country'] ?? '') === 'India' ? 'selected' : '' ?>>India</option>
                                            <option value="United States" <?= ($user['country'] ?? '') === 'United States' ? 'selected' : '' ?>>United States</option>
                                            <option value="United Kingdom" <?= ($user['country'] ?? '') === 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-gender" class="form-label">Gender</label>
                                        <select class="form-select" id="edit-gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                            <option value="prefer_not_to_say" <?= ($user['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="current-password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current-password">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="new-password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new-password">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm-new-password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm-new-password">
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-accent">Save Changes</button>
                                    <button type="button" class="btn btn-outline-secondary" id="cancel-edit-btn">Cancel</button>
                                </div>
                            </form>
                        </div>
                        
                        <h4 class="mt-5 mb-3">Order History</h4>
                        <?php if (empty($orders)): ?>
                            <div class="alert alert-info">No orders found.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover text-white">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?= $order['id'] ?></td>
                                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= 
                                                        $order['status'] === 'completed' ? 'success' : 
                                                        ($order['status'] === 'processing' ? 'warning' : 'secondary') 
                                                    ?>">
                                                        <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </td>
                                                <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-accent view-order-details" 
                                                            data-order-id="<?= $order['id'] ?>">
                                                        Details
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-darker text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Order #<span id="modal-order-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="order-details-content">
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const profileInfo = document.getElementById('profile-info');
    const profileEditForm = document.getElementById('profile-edit-form');
    
    editProfileBtn.addEventListener('click', function() {
        profileInfo.style.display = 'none';
        profileEditForm.style.display = 'block';
    });
    
    cancelEditBtn.addEventListener('click', function() {
        profileInfo.style.display = 'block';
        profileEditForm.style.display = 'none';
    });
    
    const updateProfileForm = document.getElementById('update-profile-form');
    updateProfileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: document.getElementById('edit-username').value,
            email: document.getElementById('edit-email').value,
            phone: document.getElementById('edit-phone').value,
            address: document.getElementById('edit-address').value,
            city: document.getElementById('edit-city').value,
            state: document.getElementById('edit-state').value,
            postal_code: document.getElementById('edit-postal').value,
            country: document.getElementById('edit-country').value,
            date_of_birth: document.getElementById('edit-dob').value,
            gender: document.getElementById('edit-gender').value,
            current_password: document.getElementById('current-password').value,
            new_password: document.getElementById('new-password').value,
            confirm_new_password: document.getElementById('confirm-new-password').value
        };
        
        if (formData.new_password && formData.new_password !== formData.confirm_new_password) {
            alert('New passwords do not match!');
            return;
        }
    
        fetch('update_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating your profile.');
        });
    });

    const orderDetailButtons = document.querySelectorAll('.view-order-details');
    const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    
    orderDetailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            document.getElementById('modal-order-id').textContent = orderId;
            
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('order-details-content').innerHTML = html;
                    orderDetailsModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('order-details-content').innerHTML = 
                        '<div class="alert alert-danger">Failed to load order details.</div>';
                    orderDetailsModal.show();
                });
        });
    });
});
</script>

<?php include 'footer.php'; ?>