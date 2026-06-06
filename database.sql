-- ============================================================
--  FinanceApp - Script de création de la base de données
--  Compatible MySQL 5.7+
-- ============================================================

CREATE DATABASE IF NOT EXISTS financeapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE financeapp;

-- -------------------------------------------------------
-- Table : users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    username    VARCHAR(60)   NOT NULL UNIQUE,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,          -- bcrypt hash
    role        ENUM('admin','user') NOT NULL DEFAULT 'user',
    status      ENUM('active','pending','disabled') NOT NULL DEFAULT 'pending',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table : categories
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT,                              -- NULL = default/global
    name        VARCHAR(80)  NOT NULL,
    icon        VARCHAR(10)  DEFAULT '💰',
    color       VARCHAR(20)  DEFAULT '#DBEAFE',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- -------------------------------------------------------
-- Table : budgets
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS budgets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    owner_id    INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    period      ENUM('monthly','weekly','custom') NOT NULL DEFAULT 'monthly',
    global_limit DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_from   DATE,
    date_to     DATE,
    is_shared   TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table : budget_categories  (plafond par catégorie)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS budget_categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    budget_id   INT NOT NULL,
    category_id INT NOT NULL,
    cat_limit   DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (budget_id)   REFERENCES budgets(id)    ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table : budget_members  (collaboration)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS budget_members (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    budget_id   INT NOT NULL,
    user_id     INT NOT NULL,
    role        ENUM('admin','member','readonly') NOT NULL DEFAULT 'member',
    joined_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bm (budget_id, user_id),
    FOREIGN KEY (budget_id) REFERENCES budgets(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table : transactions
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    budget_id   INT,
    category_id INT,
    type        ENUM('income','expense') NOT NULL,
    amount      DECIMAL(12,2) NOT NULL,
    description VARCHAR(255),
    tx_date     DATE NOT NULL,
    comment     TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (budget_id)   REFERENCES budgets(id)     ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE SET NULL
);

-- -------------------------------------------------------
-- Table : alerts  (historique des alertes générées)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS alerts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    budget_id   INT,
    type        ENUM('over','warning','info') NOT NULL DEFAULT 'info',
    message     VARCHAR(255),
    is_read     TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE
);

-- ============================================================
-- Données de démonstration
-- ============================================================

-- Utilisateurs  (mots de passe : Admin123! / User123!)
INSERT INTO users (name, username, email, password, role, status) VALUES
('Admin Système', 'admin',   'admin@financeapp.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHMSSJ', 'admin', 'active'),
('Emna Kharrat',  'emna_k',  'emna@financeapp.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHMSSJ', 'user',  'active'),
('Marie Martin',  'marie_m', 'marie@financeapp.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHMSSJ', 'user',  'active'),
('Pierre Dupont', 'pierre_d','pierre@financeapp.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHMSSJ', 'user',  'active'),
('Lucas Petit',   'lucas_p', 'lucas@financeapp.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHMSSJ', 'user',  'pending');

-- Catégories par défaut
INSERT INTO categories (user_id, name, icon, color) VALUES
(NULL, 'Alimentation', '🍔', '#FEF9C3'),
(NULL, 'Transport',    '🚗', '#DBEAFE'),
(NULL, 'Logement',     '🏠', '#EDE9FE'),
(NULL, 'Santé',        '💊', '#FEE2E2'),
(NULL, 'Loisirs',      '🎮', '#D1FAE5'),
(NULL, 'Études',       '📚', '#E0F2FE'),
(NULL, 'Salaire',      '💼', '#DCFCE7'),
(NULL, 'Épargne',      '🐖', '#FEF3C7');

-- Budget Emna (id=2)
INSERT INTO budgets (owner_id, name, period, global_limit, date_from, date_to, is_shared) VALUES
(2, 'Budget Juin 2025',      'monthly', 3000.00, '2025-06-01', '2025-06-30', 0),
(2, 'Colocation Groupe 9',   'monthly', 2000.00, '2025-06-01', '2025-06-30', 1);

-- Membres budget partagé
INSERT INTO budget_members (budget_id, user_id, role) VALUES
(2, 2, 'admin'), (2, 3, 'member'), (2, 4, 'member');

-- Plafonds par catégorie
INSERT INTO budget_categories (budget_id, category_id, cat_limit) VALUES
(1, 1, 500.00), (1, 2, 350.00), (1, 3, 900.00), (1, 4, 150.00), (1, 5, 200.00), (1, 6, 300.00);

-- Transactions
INSERT INTO transactions (user_id, budget_id, category_id, type, amount, description, tx_date) VALUES
(2, 1, 1, 'expense', 83.15, 'Supermarché Carrefour', '2025-06-14'),
(2, 1, 7, 'income',  1200.00,'Dépôt mensuel',         '2025-06-14'),
(2, 1, 2, 'expense', 45.00, 'Abonnement TER',         '2025-06-13'),
(3, 1, 5, 'expense', 15.49, 'Netflix',                '2025-06-12'),
(2, 1, 4, 'expense', 28.50, 'Ordonnance',             '2025-06-11'),
(2, 1, 3, 'expense', 750.00,'Loyer',                  '2025-06-10'),
(2, 1, 6, 'expense', 65.00, 'Manuels scolaires',      '2025-06-09'),
(4, 1, 2, 'expense', 55.00, 'Essence',                '2025-06-08'),
(3, 1, 1, 'expense', 42.00, 'Restaurant',             '2025-06-07'),
(2, 1, 5, 'expense', 24.00, 'Cinéma',                 '2025-06-06'),
(2, 1, 7, 'income',  2800.00,'Salaire',               '2025-06-01'),
(4, 1, 2, 'expense', 89.00, 'Train Paris',            '2025-05-28');
