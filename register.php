<?php
$page_title = "Register - ElectroHUT";
session_start();
require_once 'functions.php';

if (isAuthenticated()) {
    header("Location: index.php");
    exit();
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'country' => '',
    'postal_code' => '',
    'dob' => '',
    'gender' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formData = array_map('sanitizeInput', $_POST);
    $formData['password'] = $_POST['password'] ?? '';
    $formData['confirm_password'] = $_POST['confirm_password'] ?? '';

    // Validation for new fields
    if (empty($formData['first_name'])) $errors['first_name'] = "First name required";
    elseif (strlen($formData['first_name']) < 2) $errors['first_name'] = "Minimum 2 characters";
    
    if (empty($formData['last_name'])) $errors['last_name'] = "Last name required";
    elseif (strlen($formData['last_name']) < 2) $errors['last_name'] = "Minimum 2 characters";

    if (empty($formData['username'])) $errors['username'] = "Username required";
    elseif (strlen($formData['username']) < 3) $errors['username'] = "Minimum 3 characters";

    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid email required";

    if (strlen($formData['password']) < 8) $errors['password'] = "Minimum 8 characters";
    elseif ($formData['password'] !== $formData['confirm_password']) $errors['confirm_password'] = "Passwords mismatch";

    if (!empty($formData['phone']) && !preg_match('/^\+?[0-9]{10,15}$/', $formData['phone'])) $errors['phone'] = "Invalid format";

    if (empty($errors)) {
        try {
            $pdo = connectDB();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$formData['email'], $formData['username']]);
            
            if ($stmt->fetch()) {
                $errors['email'] = "Email/username exists";
            } else {
                // Updated SQL with first/last name
                $insertStmt = $pdo->prepare("INSERT INTO users 
                    (first_name, last_name, username, email, password, phone, address, 
                    city, state, country, postal_code, date_of_birth, gender) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                
                $insertStmt->execute([
                    $formData['first_name'],
                    $formData['last_name'],
                    $formData['username'],
                    $formData['email'],
                    password_hash($formData['password'], PASSWORD_DEFAULT),
                    $formData['phone'],
                    $formData['address'],
                    $formData['city'],
                    $formData['state'],
                    $formData['country'],
                    $formData['postal_code'],
                    $formData['dob'] ?: null,
                    $formData['gender'] ?: null
                ]);

                $_SESSION['registration_success'] = true;
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include 'header.php'; ?>

<style>
.auth-section {
    background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.reg-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.input-group-text {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #64ffda;
}

.form-control {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    transition: all 0.3s;
}

.form-control:focus {
    background: rgba(255, 255, 255, 0.08);
    border-color: #64ffda;
    box-shadow: 0 0 0 3px rgba(100, 255, 218, 0.1);
}

.password-strength .progress {
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
}

.btn-primary {
    background: #64ffda;
    border: none;
    color: #0a192f;
    padding: 12px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #52eec8;
    transform: translateY(-2px);
}

.invalid-feedback {
    color: #ff6b6b;
    font-size: 0.9em;
}

.toggle-password {
    border-left: none;
    background: rgba(255, 255, 255, 0.05);
    color: #64ffda;
}

.toggle-password:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="reg-card p-4 p-md-5">
                    <div class="text-center mb-5">
                
                        <h1 class="text-white mb-3">Join ElectroHUT</h1>
                        <p class="text-muted">Create your account to get started</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-dark text-white border-0 mb-4">
                        <?php foreach ($errors as $error): ?>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row g-4">
                        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                       name="first_name" placeholder="First Name" 
                       value="<?= htmlspecialchars($formData['first_name']) ?>" required>
            </div>
            <?php if (isset($errors['first_name'])): ?>
                <div class="invalid-feedback d-block">
                    <?= htmlspecialchars($errors['first_name']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                       name="last_name" placeholder="Last Name" 
                       value="<?= htmlspecialchars($formData['last_name']) ?>" required>
            </div>
            <?php if (isset($errors['last_name'])): ?>
                <div class="invalid-feedback d-block">
                    <?= htmlspecialchars($errors['last_name']) ?>
                </div>
            <?php endif; ?>
        </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?= htmlspecialchars($formData['username']) ?>"
                                           placeholder="Username" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($formData['email']) ?>"
                                           placeholder="Email" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" name="password" 
                                           placeholder="Password" required>
                                    <button class="btn toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-2">
                                    <div class="progress">
                                        <div id="password-strength-bar" class="progress-bar"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" name="confirm_password" 
                                           placeholder="Confirm Password" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?= htmlspecialchars($formData['phone']) ?>"
                                       placeholder="Phone (optional)">
                            </div>

                            <div class="col-md-6">
                                <input type="date" class="form-control" name="dob" 
                                       max="<?= date('Y-m-d') ?>"
                                       value="<?= htmlspecialchars($formData['dob']) ?>">
                            </div>

                            <div class="col-md-6">
                                <select class="form-select" name="gender">
                                    <option value="">Gender (optional)</option>
                                    <option value="male" <?= $formData['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $formData['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= $formData['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <textarea class="form-control" name="address" rows="2"
                                          placeholder="Address"><?= htmlspecialchars($formData['address']) ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control" name="city" 
                                       value="<?= htmlspecialchars($formData['city']) ?>"
                                       placeholder="City">
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control" name="state" 
                                       value="<?= htmlspecialchars($formData['state']) ?>"
                                       placeholder="State">
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control" name="postal_code" 
                                       value="<?= htmlspecialchars($formData['postal_code']) ?>"
                                       placeholder="Postal Code">
                            </div>

                            <div class="col-md-6">
                                <select class="form-select" name="country">
                                    <option value="">Country (optional)</option>
                                    <option value="India" <?= $formData['country'] === 'India' ? 'selected' : '' ?>>India</option>
                                    <option value="USA" <?= $formData['country'] === 'USA' ? 'selected' : '' ?>>USA</option>
                                    <option value="UK" <?= $formData['country'] === 'UK' ? 'selected' : '' ?>>UK</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label text-muted" for="terms">
                                        I agree to the <a href="#" class="text-white">Terms & Conditions</a>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Create Account</button>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <p class="text-muted">Already have an account? 
                                    <a href="login.php" class="text-white fw-bold">Login Here</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = (btn) => {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye-slash');
    };

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => togglePassword(btn));
    });

    document.getElementById('password').addEventListener('input', function() {
        const strength = Math.min(this.value.length / 2, 100);
        const bar = document.getElementById('password-strength-bar');
        bar.style.width = `${strength}%`;
        bar.style.backgroundColor = strength > 75 ? '#64ffda' : strength > 50 ? '#ffd700' : '#ff6b6b';
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});
</script>

<?php include 'footer.php'; ?>