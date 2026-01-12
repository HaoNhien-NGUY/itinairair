<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'git@github.com:HaoNhien-NGUY/itinairair.git');

add('shared_files', ['.env.local', '.env.prod.local']);
add('shared_dirs', [
    'public/uploads',
    'var/log',
    'var/sessions',
    'public/media',
    ]);
add('writable_dirs', [
    'var',
    'public/uploads',
    'public/media',
]);

// Hosts

host('itinairair.com')
    ->set('port', getenv('SSH_PORT') ?: 22)
    ->set('remote_user', 'ubuntu')
    ->set('deploy_path', '/var/www/itinairair')
    ->set('http_user', 'www-data');

desc('Upload assets');
task('deploy:assets:upload', function () {
    upload('public/assets/', '{{release_path}}/public/assets/');
});

desc('Stop workers');
task('deploy:stop-workers', function () {
    run('{{bin/console}} messenger:stop-workers');
});

// Hooks
after('deploy:vendors', 'deploy:dump-env');
after('deploy:vendors', 'deploy:assets:upload');
after('deploy:vendors', 'database:migrate');
after('deploy:failed', 'deploy:unlock');
after('deploy:success', 'deploy:stop-workers');
