<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Admin - Shop Service</title>
    <?php if (!defined('BASE_URL')) {
        define('BASE_URL', '');
    } ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h3 class="text-center mb-4">Novo Administrador</h3>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= \BASE_URL ?>/auth/store_admin" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">Registrar</button>
                    <a href="<?= \BASE_URL ?>/login" class="btn btn-outline-secondary">Voltar ao Login</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>