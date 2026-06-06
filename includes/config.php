<?php
// ============================================================
//  Configuration de la base de données
//  Modifiez ces valeurs selon votre environnement XAMPP/WAMP
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'financeapp');
define('DB_USER', 'root');
define('DB_PASS', '');          // Mot de passe MySQL (vide par défaut sur XAMPP)
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'FinanceApp');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/financeapp');

// Session lifetime (secondes)
define('SESSION_LIFETIME', 3600);

// Seuil d'alerte budget (%)
define('ALERT_THRESHOLD', 80);

/**
 * Retourne la connexion PDO (singleton)
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Connexion DB impossible : ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
