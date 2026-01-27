<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopService</title>
    <!-- Ensure BASE_URL is defined if not already -->
    <?php if (!defined('BASE_URL')) {
        define('BASE_URL', '');
    } ?>
    <?php if (!defined('ASSET_URL')) {
        define('ASSET_URL', '');
    } ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body,
        html {
            height: 100%;
            background-color: #f8f9fa;
        }

        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .card {
            width: 100%;
            max-width: 400px;
            border: none;
            shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container login-container">
        <div class="card p-4 shadow-sm">
            <div class="text-center mb-4">
                <img src="<?= \ASSET_URL ?>/assets/img/logo.jpg" alt="Shop Service"
                    style="height: 60px; margin-bottom: 10px;">
            </div>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <form action="<?= \BASE_URL ?>/auth/authenticate" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
                <script>
                    document.getElementById('togglePassword').addEventListener('click', function (e) {
                        const passwordInput = document.getElementById('password');
                        const icon = this.querySelector('i');
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    });
                </script>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>