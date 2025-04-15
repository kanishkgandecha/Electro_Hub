<?php
$page_title = "Login - ElectroHUT";
session_start();
require_once 'functions.php';

if (isAuthenticated()) {
    header("Location: index.php");
    exit();
}

$email = $password = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email)) {
        $errors['email'] = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email.";
    }
    if (empty($password)) {
        $errors['password'] = "Please enter your password.";
    }

    if (empty($errors)) {
        try {
            $conn = connectDB();
            $query = "SELECT id, username, password FROM users WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    header("Location: index.php");
                    exit();
                } else {
                    $errors['password'] = "Invalid password.";
                }
            } else {
                $errors['email'] = "Email not found.";
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}
?>

<style>
.auth-section {
    background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.login-card {
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

.form-check-input:checked {
    background-color: #64ffda;
    border-color: #64ffda;
}
</style>

<?php include 'header.php'; ?>
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="login-card p-4 p-md-5">
                    <div class="text-center mb-5">
                        <h1 class="text-white mb-3">Welcome Back</h1>
                        <p class="text-muted">Sign in to your account</p>
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
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       name="password" placeholder="Password" required>
                                <button class="btn toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($errors['password']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="text-decoration-none" style="color: #64ffda;">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-4">Sign In <i class="bi bi-arrow-right-short ms-2"></i></button>

                        <div class="text-center text-muted">
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="fw-bold" style="color: #64ffda;">Sign up</a>
                            </p>
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


    document.querySelector('form').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });

    Array.from(document.forms[0].elements).forEach(element => {
        element.addEventListener('input', () => {
            if (element.checkValidity()) {
                element.classList.remove('is-invalid');
            } else {
                element.classList.add('is-invalid');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>