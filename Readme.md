# Ticketing API
Une API REST de gestion de tickets développée avec **Symfony 7** afin de présenter une architecture backend et une bonne couverture de tests fonctionnels.

#### ***⚠️ Ce projet est fourni à des fins de démonstration et de portfolio.***

## 🚀 Fonctionnalités

### Authentification

* Authentification JWT
* Routes protégées
* Gestion des rôles (en cours)

### Tickets

* Liste des tickets
* Création d'un ticket
* Consultation d'un ticket
* Modification d'un ticket
* Suppression d'un ticket
* Pagination
* Filtres

### Commentaires

* Création d'un commentaire sur un ticket
* Consultation d'un commentaire
* Modification d'un commentaire
* Suppression d'un commentaire
* Liste des commentaires d'un ticket
* Liste des commentaires d'un utilisateur

### Utilisateurs

* Liste des utilisateurs
* Consultation d'un utilisateur
* Liste des tickets d'un utilisateur
* Liste des commentaires d'un utilisateur

---

# 🛠 Stack technique

## Backend

* PHP 8.3
* Symfony 7
* Doctrine ORM
* MariaDB / MySQL
* JWT Authentication (LexikJWTAuthenticationBundle)

## Qualité

* PHPUnit
* Symfony Validator
* DTO
* ObjectMapper
* Serializer Groups

## Environnement

* DDEV
* Composer

---

# 📦 Installation
>Le projet utilise DDEV en version `v1.25.3`. https://docs.ddev.com/en/stable/users/install/ddev-installation/

## Cloner le projet

```bash
git clone <repository-url>
cd ticketing-api
```

## Démarrer DDEV

```bash
ddev start
```

## Installer les dépendances

```bash
ddev composer install
```

# ⚙️ Configuration
Les variables d'environnement sont déjà présentes par défaut.


# 🔐 Génération des clés JWT

```bash
ddev console lexik:jwt:generate-keypair --env=dev
ddev console lexik:jwt:generate-keypair --env=test
```
> Les clés générées sont stockées dans le dossier `config/jwt`.

# 🗄 Base de données

Initialisez les bases de données et les fixtures grâce à une commande DDEV personnalisée située dans : `.ddev/commands/web`

```bash
ddev setup-db dev 
ddev setup-db test
```
---

# ▶️ Lancer les tests

Exécuter toute la suite :

```bash
ddev phpunit --testdox
```

Exécuter un test précis :

```bash
ddev phpunit --filter TicketListByUserTest::testShouldReturnAllTicketsForGivenUser --testdox
```
---

# 📖 Documentation API

> Si une documentation OpenAPI / Swagger est disponible, elle est accessible depuis son endpoint dédié.

---

# 🏗 Architecture

Le projet suit une architecture orientée API avec notamment :

* Controllers
* DTO
* Symfony Validator
* Doctrine ORM
* Repositories
* Serializer Groups
* Object Mapping
* Tests fonctionnels

---

# 📂 Structure du projet

```
src/
├── Controller/
├── Dto/
├── Entity/
├── Enum/
├── Repository/
├── Validator/
├── Security/
└── ...
```

---

# 🧪 Tests

Le projet est accompagné de tests fonctionnels couvrant notamment :

* CRUD Tickets
* CRUD Commentaires
* Pagination
* Filtres
* Validation
* Gestion des erreurs
---

# 🧹 Qualité du code

Le projet utilise plusieurs outils afin de garantir la qualité et la cohérence du code.

## PHPStan

**Analyser l'ensemble du projet :**

```bash
ddev phpstan analyse
```

**Analyser un répertoire spécifique :**

```bash
ddev phpstan analyse src/
ddev phpstan analyse tests/
```

## ECS (Easy Coding Standard)

Vérifie le respect des conventions de codage.

**Vérifier le style du code :**

```bash
ddev ecs
```

**Corriger automatiquement les problèmes détectés :**

```bash
ddev ecs --fix
```


# 📌 Roadmap

* [x] Authentification JWT
* [x] CRUD Tickets
* [x] CRUD Commentaires
* [x] Pagination
* [x] Filtres
* [x] Tests fonctionnels
* [x] Permissions via Voters
* [ ] Migration de Symfony 7 vers Symfony 8
* [ ] Qualités
    * [x] Phpstan
    * [X] Ecs
    * [ ] Rector
* [x] GitHub Actions (CI)
    - [x] Exécuter les tests dans la CI
    - [x] Exécuter PHPStan dans la CI
    - [x] Exécuter ECS dans la CI
* [ ] Documentation OpenAPI
