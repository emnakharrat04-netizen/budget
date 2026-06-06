# FinanceApp

## Description

FinanceApp est une application web de gestion financière personnelle et collaborative développée en PHP/MySQL.

L'application permet aux utilisateurs de suivre leurs revenus et dépenses, gérer des budgets, collaborer sur des budgets partagés et visualiser leur situation financière à travers des statistiques et des graphiques interactifs.

---

## Fonctionnalités principales

### Authentification

* Connexion utilisateur
* Inscription avec validation administrateur
* Gestion des rôles (Administrateur / Utilisateur)
* Modification du profil
* Changement de mot de passe

### Gestion des transactions

* Ajout de revenus et dépenses
* Modification des transactions
* Suppression des transactions
* Recherche et filtrage avancés
* Association à des catégories et budgets

### Gestion des catégories

* Création de catégories personnalisées
* Icônes et couleurs personnalisables
* Modification et suppression

### Gestion des budgets

* Création de budgets mensuels ou personnalisés
* Limites globales et par catégorie
* Barres de progression
* Alertes de dépassement

### Collaboration

* Budgets partagés
* Ajout de membres
* Gestion des rôles des collaborateurs
* Suivi des contributions

### Statistiques

* Répartition des dépenses par catégorie
* Évolution mensuelle des dépenses
* Comparaison revenus / dépenses
* Taux d'épargne

### Administration

* Validation des comptes utilisateurs
* Désactivation des comptes
* Suppression des utilisateurs
* Consultation globale des budgets et transactions

---

## Technologies utilisées

### Front-end

* HTML5
* CSS3
* JavaScript
* Tabler Icons

### Back-end

* PHP 8+

### Base de données

* MySQL 5.7+

---

## Structure du projet

FinanceApp/
│
├── css/
│   └── style.css
│
├── includes/
│   ├── auth.php
│   ├── config.php
│   ├── header.php
│   └── footer.php
│
├── pages/
│   ├── dashboard.php
│   ├── transactions.php
│   ├── budgets.php
│   ├── categories.php
│   ├── collab.php
│   ├── alerts.php
│   ├── stats.php
│   ├── profile.php
│   └── admin.php
│
├── database.sql
├── index.php
└── README.md
```

---

## Installation

### 1. Cloner le projet

```bash
git clone <repository_url>
```

### 2. Importer la base de données

Créer une base MySQL puis importer :

```bash
database.sql
```

### 3. Configurer la connexion

Modifier les paramètres dans :

```php
includes/config.php
```

### 4. Lancer le projet

Placer le projet dans le serveur local :

* XAMPP
* WAMP
* MAMP
* Laragon

Puis accéder à :

```text
http://localhost/FinanceApp
```

---

## Comptes de démonstration

### Administrateur

Email :
admin@financeapp.com
Pw: Admin123!


### Utilisateur

Email :
emna@financeapp.com
Pw: User123!
Les mots de passe peuvent être modifiés directement dans la base de données si nécessaire.

---

## Auteur

Projet réalisé par :

**Emna Kharrat**

Dans le cadre du projet de développement web et gestion de bases de données.

---

## Version

Version : 1.0
