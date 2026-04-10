![PHP](https://img.shields.io/badge/PHP-8.1-blue)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-orange)
![MVC](https://img.shields.io/badge/Architecture-MVC-green)

# StageLink 🚀

**Plateforme de gestion des stages pour le CESI**

<img width="1366" height="768" alt="image" src="https://github.com/user-attachments/assets/cf716565-6286-4672-9703-ce8a35cffffc" />

<img width="1366" height="768" alt="image" src="https://github.com/user-attachments/assets/62c5509a-1dc4-4211-bbbb-08bbab8b5316" />

<img width="1366" height="768" alt="image" src="https://github.com/user-attachments/assets/67e71793-c62f-4bee-a3ee-6bd76d706a03" />




StageLink est une application web développée en PHP 8.1+ qui facilite la mise en relation entre **étudiants**, **pilotes de promotion**, **administrateurs** et **entreprises** pour la recherche, la publication et le suivi des stages.

---

## ✨ Fonctionnalités principales

### Pour les Étudiants
- Consultation des offres de stage
- Postulation avec CV et lettre de motivation
- Gestion des candidatures (suivi des statuts)
- Wishlist d'offres
- Gestion du profil et des CV

### Pour les Pilotes
- Suivi des promotions dont ils sont responsables
- Visualisation de toutes les candidatures de leurs étudiants
- Accès aux détails des étudiants et candidatures

### Pour les Administrateurs
- Gestion complète des utilisateurs (Pilotes + Étudiants)
- CRUD des promotions (création, modification, suppression, détail avec liste paginée des étudiants)
- Gestion des pilotes et étudiants

### Pour les Entreprises
- Publication et gestion des offres de stage
- Notation des entreprises par les étudiants

---

## 🛠 Technologies utilisées

- **Langage** : PHP 8.1+
- **Architecture** : MVC personnalisé (sans framework lourd)
- **Base de données** : MariaDB / MySQL
- **Autoloading** : Composer (PSR-4)
- **Tests** : PHPUnit
- **Hébergement DB** : Railway.app
- **Frontend** : HTML, CSS, JavaScript (assets simples)

---

## 📁 Structure du projet

```bash
.
├── app/
│   ├── Controllers/          # Contrôleurs (Admin, Pilote, Candidature, etc.)
│   ├── Core/                 # BaseController, BaseModel, Router, Database
│   ├── Models/               # Modèles (CandidatureModel, PiloteModel, etc.)
│   └── Views/                # Vues organisées par rôle
├── public/                   # Point d'entrée (index.php) + assets (css, js, img)
├── config/                   # Configuration (database.php, routes.php)
├── sql/                      # Schéma et seed de la base de données
├── templates/                # Templates communs (header, footer, pagination)
├── tests/                    # Tests unitaires
├── vendor/                   # Dépendances Composer
├── composer.json
└── README.md

🚀 Installation et configuration locale
1. Cloner le projet
Bashgit clone https://github.com/PerfectKars/StageLink.git
cd StageLink
2. Installer les dépendances
Bashcomposer install
3. Configurer la base de données

Importe le schéma et les données de test :Bashmysql -u root -p stagelink < sql/stagelink_db.sql(ou via phpMyAdmin / Adminer)
Configure la connexion dans config/database.php (ou via variables d'environnement si implémenté).

Données de test par défaut :

Mot de passe pour tous les comptes : Password1!
Comptes admin : admin@stagelink.fr / direction@stagelink.fr
Comptes pilotes et étudiants : voir le fichier seed.sql

4. Lancer le projet
Place le dossier public/ comme racine du serveur web (recommandé avec Apache ou Nginx).
Exemple avec PHP built-in (pour développement) :
Bashcd public
php -S localhost:8000
Accède à l'application : http://localhost:8000

🌐 Base de données (Production)
La base de données est hébergée sur Railway.app.

🔑 Routes principales

/ → Accueil
/login → Connexion
/offres → Liste des offres
/admin/utilisateurs → Gestion des utilisateurs (Admin)
/admin/promotions → Gestion des promotions (Admin)
/pilote/candidatures → Candidatures des promotions (Pilote)
/mes-candidatures → Mes candidatures (Étudiant)

Toutes les routes sont définies dans config/routes.php.

🧪 Tests
Le projet inclut des tests unitaires avec PHPUnit.
Exécuter les tests :
Bashvendor/bin/phpunit
Fichier de configuration : phpunit.xml
