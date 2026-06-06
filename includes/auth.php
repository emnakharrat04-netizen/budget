<?php
require_once __DIR__ . '/config.php';

// Démarrer la session si elle n'existe pas
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/* --------------------------------------------------------
   Fonctions d'authentification
   -------------------------------------------------------- */

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, name, username, email, role, status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function loginUser(string $email, string $password): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }
    if ($user['status'] === 'pending') {
        return ['success' => false, 'message' => 'Votre compte est en attente de validation par un administrateur.'];
    }
    if ($user['status'] === 'disabled') {
        return ['success' => false, 'message' => 'Votre compte a été désactivé. Contactez un administrateur.'];
    }
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    session_regenerate_id(true);

    return ['success' => true, 'role' => $user['role']];
}

function registerUser(string $name, string $username, string $email, string $password): array {
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.'];
    }
    $pdo = getDB();
    // Vérification unicité
    $chk = $pdo->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $chk->execute([$email, $username]);
    if ($chk->fetch()) {
        return ['success' => false, 'message' => 'Cet email ou nom d\'utilisateur est déjà utilisé.'];
    }
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $ins  = $pdo->prepare("INSERT INTO users (name,username,email,password,role,status) VALUES (?,?,?,?,'user','pending')");
    $ins->execute([$name, $username, $email, $hash]);
    return ['success' => true, 'message' => 'Compte créé. En attente de validation par un administrateur.'];
}

function logoutUser(): void {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

/* --------------------------------------------------------
   Helpers divers
   -------------------------------------------------------- */
function sanitize(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function formatMoney(float $amount): string {
    return number_format($amount, 2, ',', ' ') . ' €';
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getBudgetStatus(float $spent, float $limit): string {
    if ($limit <= 0) return 'ok';
    $pct = ($spent / $limit) * 100;
    if ($pct >= 100) return 'over';
    if ($pct >= ALERT_THRESHOLD) return 'warning';
    return 'ok';
}
