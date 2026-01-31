<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Deploy Version: Check-002 (Fix Null & Protocol)

$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Remove script directory from URI (for subdirectory hosting) - Case Insensitive Check
if ($requestUri && stripos($requestUri, $scriptName) === 0 && $scriptName !== '/') {
    $uri = substr($requestUri, strlen($scriptName));
} else {
    $uri = $requestUri ?? '/';
}

// Remove trailing slash
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}
if ($uri === '') {
    $uri = '/';
}

// 1. Protocol Detection (including Reverse Proxy)
$protocol = 'http://';
if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    $_SERVER['SERVER_PORT'] == 443 ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
) {
    $protocol = 'https://';
}

// 2. Host and App Root Detection
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$appRoot = ($scriptName === '/' || $scriptName === '.') ? '' : $scriptName;

// 3. Default Base URL
$baseUrl = $protocol . $host . $appRoot;

// 4. Define Constants with ENV Override
$envBaseUrl = getenv('BASE_URL');
if ($envBaseUrl !== false && $envBaseUrl !== '') {
    define('BASE_URL', rtrim($envBaseUrl, '/'));
} else {
    define('BASE_URL', rtrim($baseUrl, '/'));
}

$envAssetUrl = getenv('ASSET_URL');
if ($envAssetUrl !== false && $envAssetUrl !== '') {
    define('ASSET_URL', rtrim($envAssetUrl, '/'));
} else {
    // Default assets location relative to BASE_URL
    define('ASSET_URL', BASE_URL);
}

// Debug Mode (Remove in production if needed, or secure behind param)
if (isset($_GET['debug_env']) && $_GET['debug_env'] === 'true') {
    header('Content-Type: text/plain');
    echo "--- Debug Info ---\n";
    echo "Protocol: $protocol\n";
    echo "Host: $host\n";
    echo "Remote Addr: " . $_SERVER['REMOTE_ADDR'] . "\n";
    echo "X-Forwarded-Proto: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "\n";
    echo "ENV BASE_URL: " . ($envBaseUrl ?: 'Not Set') . "\n";
    echo "ENV ASSET_URL: " . ($envAssetUrl ?: 'Not Set') . "\n";
    echo "Computed BASE_URL: " . BASE_URL . "\n";
    echo "Computed ASSET_URL: " . ASSET_URL . "\n";
    exit;
}

$routes = [
    '/' => ['controller' => 'App\Controllers\LandingController', 'action' => 'index'], // Landing Page
    '/login' => ['controller' => 'App\Controllers\AuthController', 'action' => 'login'],
    '/auth/authenticate' => ['controller' => 'App\Controllers\AuthController', 'action' => 'authenticate'],
    '/logout' => ['controller' => 'App\Controllers\AuthController', 'action' => 'logout'],
    '/admin/register' => ['controller' => 'App\Controllers\AuthController', 'action' => 'register_admin'],
    '/auth/store_admin' => ['controller' => 'App\Controllers\AuthController', 'action' => 'store_admin'],
    // Main Dashboard
    '/dashboard' => ['controller' => 'App\Controllers\DashboardController', 'action' => 'index'],
    // Ativos Routes
    '/ativos' => ['controller' => 'App\Controllers\AtivoController', 'action' => 'index'],
    '/ativos/criar' => ['controller' => 'App\Controllers\AtivoController', 'action' => 'create'],
    '/ativos/store' => ['controller' => 'App\Controllers\AtivoController', 'action' => 'store'],
    '/ativos/delete' => ['controller' => 'App\Controllers\AtivoController', 'action' => 'delete'],
    '/ativos/update' => ['controller' => 'App\Controllers\AtivoController', 'action' => 'update'], // Using POST for update
// Asset Edit Route handled by wildcard below or specific route if using query param?
// The router uses exact match usually, but I have wildcards like /ativo/view/.
// Let's add wildcard for edit below or use query param?
// My wildcard logic is at the bottom. I will add one for edit there.
// OS Routes
    '/os' => ['controller' => 'App\Controllers\OSController', 'action' => 'index'],
    '/os/criar' => ['controller' => 'App\Controllers\OSController', 'action' => 'create'],
    '/os/store' => ['controller' => 'App\Controllers\OSController', 'action' => 'store'],
    '/os/update-status' => ['controller' => 'App\Controllers\OSController', 'action' => 'update_status'],
    '/os/update' => ['controller' => 'App\Controllers\OSController', 'action' => 'update'],
    // Orcamento Routes
    '/orcamentos' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'index'],
    '/orcamentos/criar' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'create'],
    '/orcamentos/store' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'store'],
    '/orcamentos/update' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'update'],
    '/orcamentos/nova-versao' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'create_simple'],
    '/orcamentos/store_simple' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'store_simple'],
    // V2 Hierarchical Routes
    '/orcamentos/criar-v2' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'create_v2'],
    '/orcamentos/create_v2' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'create_v2'],
    '/orcamentos/store_v2' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'store_v2'],

    // Import Module Routes
    '/orcamentos/import' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'import'],
    '/orcamentos/import/upload' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'uploadImport'],
    '/orcamentos/import/process' => ['controller' => 'App\Controllers\OrcamentoController', 'action' => 'processImport'],
    // Valve Routes
    '/valves' => ['controller' => 'App\Controllers\ValveController', 'action' => 'index'],
    '/valves/create' => ['controller' => 'App\Controllers\ValveController', 'action' => 'create'],
    '/valves/store' => ['controller' => 'App\Controllers\ValveController', 'action' => 'store'],
    '/valves/update' => ['controller' => 'App\Controllers\ValveController', 'action' => 'update'],
    '/valves/delete' => ['controller' => 'App\Controllers\ValveController', 'action' => 'delete'],
    // Financeiro Routes
    '/financeiro' => ['controller' => 'App\Controllers\FinanceiroController', 'action' => 'index'],
    '/financeiro/store' => ['controller' => 'App\Controllers\FinanceiroController', 'action' => 'store'],
    '/financeiro/tags/store' => ['controller' => 'App\Controllers\FinanceiroController', 'action' => 'store_tag'],
    '/financeiro/tags/delete' => ['controller' => 'App\Controllers\FinanceiroController', 'action' => 'delete_tag'],
    '/financeiro/status/toggle' => ['controller' => 'App\Controllers\FinanceiroController', 'action' => 'toggle_status'],
    // Gallery Routes
    '/galeria' => ['controller' => 'App\Controllers\GaleriaController', 'action' => 'index'],
    '/galeria/upload' => ['controller' => 'App\Controllers\GaleriaController', 'action' => 'upload'],
    // User Management Route
    '/usuarios' => ['controller' => 'App\Controllers\UserController', 'action' => 'index'],
    '/usuarios/criar' => ['controller' => 'App\Controllers\UserController', 'action' => 'create'],
    '/usuarios/store' => ['controller' => 'App\Controllers\UserController', 'action' => 'store'],
    '/usuarios/update' => ['controller' => 'App\Controllers\UserController', 'action' => 'update'],
    // User Edit/Delete handled via wildcards or specific routes
// '/usuarios/delete' => ['controller' => 'App\Controllers\UserController', 'action' => 'delete'], // Usually needs ID
// Client Management Route
    '/clientes' => ['controller' => 'App\Controllers\ClienteController', 'action' => 'index'],
    '/clientes/criar' => ['controller' => 'App\Controllers\ClienteController', 'action' => 'create'],
    '/clientes/store' => ['controller' => 'App\Controllers\ClienteController', 'action' => 'store'],
    // Wildcard route for PDF below
// Wildcard route for viewing an asset (handled manually below)

    // Config Routes
    '/configuracoes' => ['controller' => 'App\Controllers\ConfigController', 'action' => 'index'],
    '/configuracoes/update' => ['controller' => 'App\Controllers\ConfigController', 'action' => 'update'],

    // Kanban Routes
    '/kanban' => ['controller' => 'App\Controllers\KanbanController', 'action' => 'index'],
    '/kanban/move' => ['controller' => 'App\Controllers\KanbanController', 'action' => 'move'],

    // Photo API Routes
    '/photos/update-details' => ['controller' => 'App\Controllers\PhotoController', 'action' => 'updateDetails'],
    '/photos/save-image' => ['controller' => 'App\Controllers\PhotoController', 'action' => 'saveImage'],
];

// Dispatch
if (array_key_exists($uri, $routes)) {
    $controllerName = $routes[$uri]['controller'];
    $action = $routes[$uri]['action'];

    // Check if class exists
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            http_response_code(500);
            echo "Action not found: $action";
        }
    } else {
        http_response_code(500);
        echo "Controller not found: $controllerName";
    }
} elseif (preg_match('#^/os/pdf/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for OS PDF
    require_once __DIR__ . '/../app/Controllers/OSController.php'; // Ensure loaded
    $controller = new \App\Controllers\OSController();
    $controller->gerarPdf($matches[1]);
} elseif (preg_match('#^/os/edit/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for OS Edit
    require_once __DIR__ . '/../app/Controllers/OSController.php';
    $controller = new \App\Controllers\OSController();
    $controller->edit($matches[1]);
} elseif (preg_match('#^/orcamentos/edit/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Orcamento Edit
    require_once __DIR__ . '/../app/Controllers/OrcamentoController.php';
    $controller = new \App\Controllers\OrcamentoController();
    $controller->edit($matches[1]);
} elseif (preg_match('#^/orcamentos/duplicar/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Orcamento Duplicate
    require_once __DIR__ . '/../app/Controllers/OrcamentoController.php';
    $controller = new \App\Controllers\OrcamentoController();
    $controller->duplicar($matches[1]);
} elseif (preg_match('#^/orcamentos/aprovar/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Orcamento Approval
    require_once __DIR__ . '/../app/Controllers/OrcamentoController.php';
    $controller = new \App\Controllers\OrcamentoController();
    $controller->aprovar($matches[1]);
} elseif (preg_match('#^/orcamentos/detalhes/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Orcamento Details
    require_once __DIR__ . '/../app/Controllers/OrcamentoController.php';
    $controller = new \App\Controllers\OrcamentoController();
    $controller->show($matches[1]);
} elseif (preg_match('#^/orcamentos/pdf/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Budget PDF
    require_once __DIR__ . '/../app/Controllers/OrcamentoController.php';
    $controller = new \App\Controllers\OrcamentoController();
    $controller->gerarPdf($matches[1]);
} elseif (preg_match('#^/ativo/view/([a-f0-9\-]+)$#', $uri, $matches)) {
    // Dynamic Route for Viewing Asset
    require_once __DIR__ . '/../app/Controllers/AtivoController.php'; // Ensure loaded
    $controller = new \App\Controllers\AtivoController();
    $controller->view($matches[1]);
} elseif (preg_match('#^/ativos/detalhes/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Internal Asset Details
    require_once __DIR__ . '/../app/Controllers/AtivoController.php';
    $controller = new \App\Controllers\AtivoController();
    $controller->details($matches[1]);
} elseif (preg_match('#^/ativos/edit/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Editing Asset
    require_once __DIR__ . '/../app/Controllers/AtivoController.php';
    $controller = new \App\Controllers\AtivoController();
    $controller->edit($matches[1]);
} elseif (preg_match('#^/valves/edit/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Editing Valve Model
    require_once __DIR__ . '/../app/Controllers/ValveController.php';
    $controller = new \App\Controllers\ValveController();
    $controller->edit($matches[1]);
} elseif (preg_match('#^/cliente/view/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Viewing Client Profile
    require_once __DIR__ . '/../app/Controllers/ClienteController.php';
    $controller = new \App\Controllers\ClienteController();
    $controller->view($matches[1]);
} elseif (preg_match('#^/usuarios/edit/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for User Edit
    require_once __DIR__ . '/../app/Controllers/UserController.php';
    $controller = new \App\Controllers\UserController();
    $controller->edit($matches[1]);
} elseif (preg_match('#^/usuarios/delete/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for User Delete
    require_once __DIR__ . '/../app/Controllers/UserController.php';
    $controller = new \App\Controllers\UserController();
    $controller->delete($matches[1]);
} elseif (preg_match('#^/galeria/show/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Gallery Show
    require_once __DIR__ . '/../app/Controllers/GaleriaController.php';
    $controller = new \App\Controllers\GaleriaController();
    $controller->show($matches[1]);
} elseif (preg_match('#^/galeria/delete/(\d+)$#', $uri, $matches)) {
    // Dynamic Route for Photo Delete
    require_once __DIR__ . '/../app/Controllers/GaleriaController.php';
    $controller = new \App\Controllers\GaleriaController();
    $controller->delete($matches[1]);
} else {
    http_response_code(404);
    echo "404 Not Found";
}