# Rois du Sahel

Application web Laravel 13 pour le projet **Rois du Sahel**.

## Stack technique

- **PHP** 8.3
- **Laravel** 13
- **Tailwind CSS** 4 (via `@tailwindcss/vite`)
- **Vite** 8
- **Alpine.js** 3
- **MySQL** (production) / **SQLite** (tests)

## Prérequis

- PHP >= 8.3
- Composer
- Node.js >= 20
- MySQL (ou SQLite pour les tests)

## Installation

```bash
# 1. Installer les dépendances PHP
composer install

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Générer la clé d'application
php artisan key:generate

# 4. Configurer la base de données dans .env puis migrer
php artisan migrate

# 5. Installer les dépendances frontend et compiler
npm install
npm run build
```

## Développement

```bash
# Lancer le serveur de développement (serveur + queue + Vite)
composer run dev
```

Ou séparément :

```bash
php artisan serve
npm run dev
```

## Tests

```bash
# Lancer tous les tests
php artisan test --compact

# Lancer un fichier de tests spécifique
php artisan test --compact tests/Feature/ProfileTest.php

# Filtrer sur un test précis
php artisan test --compact --filter=testName
```

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/     → Contrôleurs (web + API)
│   └── Requests/        → Form Requests (validation)
├── Models/              → Modèles Eloquent
└── Providers/           → Service Providers
routes/
├── web.php              → Routes web
├── api.php              → Routes API (versionnées /api/v1)
└── auth.php             → Routes d'authentification
database/
├── migrations/          → Migrations de schéma
├── factories/           → Factories de test
└── seeders/             → Seeders de données
resources/
├── css/                 → Styles (Tailwind CSS 4)
├── js/                  → JavaScript (Alpine.js)
└── views/               → Vues Blade
tests/
├── Feature/             → Tests de fonctionnalité
└── Unit/                → Tests unitaires
```

## API

L'API est versionnée sous le préfixe `/api/v1`.

| Méthode | Endpoint         | Description                     |
| ------- | ---------------- | ------------------------------- |
| GET     | `/api/v1/health` | Vérification de l'état de l'API |

## Outils de développement

- **Laravel Pint** : `vendor/bin/pint` (formatage du code PHP)
- **Laravel Pail** : `php artisan pail` (logs en temps réel)
- **Laravel Boost** : outils MCP pour l'assistant IA

## Licence

Propriétaire — Tous droits réservés.
