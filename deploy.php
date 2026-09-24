<?php

namespace Deployer;

require 'recipe/laravel.php';

// ─────────────────────────────────────────────
// Configuration générale
// ─────────────────────────────────────────────
set('application', 'Roi du Sahel');
set('repository', 'git@github.com:mouhamedsouleymane/roi-du-sahel.git'); // ← À MODIFIER
set('git_tty', true);
set('keep_releases', 5); // Nombre de releases conservées sur le serveur

// Fichiers/dossiers partagés entre les releases (persistance)
set('shared_files', [
    '.env',
]);
set('shared_dirs', [
    'storage',
]);

// Dossiers dont les permissions doivent être ajustées
set('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// ─────────────────────────────────────────────
// Serveur de production
// ─────────────────────────────────────────────
host('production')
    ->set('hostname', getenv('SSH_HOST'))
    ->set('remote_user', getenv('SSH_USER'))
    ->set('deploy_path', getenv('SSH_PATH'))
    ->set('branch', 'main')
    ->set('http_user', 'www-data'); // Utilisateur du serveur web

// ─────────────────────────────────────────────
// Tâches personnalisées
// ─────────────────────────────────────────────

// Compiler les assets Vite sur le serveur (ou utiliser les assets pré-compilés du CI)
// Option A : les assets sont envoyés depuis le CI (recommandé)
desc('Copier les assets Vite pré-compilés');
task('deploy:vite', function () {
    upload('public/build/', '{{release_path}}/public/build/');
})->once();

// Option B : compiler sur le serveur (si Node est disponible)
// task('deploy:npm', function () {
//     run('cd {{release_path}} && npm ci && npm run build');
// });

// Redémarrer les queues Laravel après chaque déploiement
desc('Redémarrer les workers de queue');
task('artisan:queue:restart', artisan('queue:restart'));

// Optimiser l'application Laravel pour la production
desc('Optimiser Laravel pour la production');
task('deploy:optimize', [
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
]);

// ─────────────────────────────────────────────
// Séquence de déploiement (zero-downtime)
// ─────────────────────────────────────────────
desc('Déployer Roi du Sahel en production');
task('deploy', [
    // 1. Vérifications initiales
    'deploy:info',
    'deploy:setup',
    'deploy:lock',

    // 2. Récupérer le code
    'deploy:release',
    'deploy:update_code',

    // 3. Installer les dépendances PHP
    'deploy:shared',
    'deploy:vendors',

    // 4. Copier les assets frontend (pré-compilés par le CI)
    'deploy:vite',

    // 5. Préparer Laravel
    'deploy:writable',
    'artisan:storage:link',
    'artisan:migrate',           // Migrations DB sans interruption

    // 6. Optimisation
    'deploy:optimize',

    // 7. Activer la nouvelle release (le moment zero-downtime)
    'deploy:publish',

    // 8. Post-déploiement
    'artisan:queue:restart',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success',
]);

// En cas d'échec : déverrouiller et conserver la release précédente
after('deploy:failed', 'deploy:unlock');
