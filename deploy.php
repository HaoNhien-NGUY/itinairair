<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'git@github.com:HaoNhien-NGUY/itinairair.git');

add('shared_files', ['.env.local', '.env.prod.local']);
add('shared_dirs', [
    'public/uploads',
    'var/log',
    'var/sessions'
    ]);
add('writable_dirs', [
    'var',
    'public/uploads',
]);

// Hosts

host('itinairair.com')
    ->set('port', getenv('SSH_PORT') ?: 22)
    ->set('remote_user', 'ubuntu')
    ->set('deploy_path', '/var/www/itinairair')
    ->set('http_user', 'www-data');

desc('Build assets');
task('deploy:assets:build', function () {
    cd('{{release_path}}');
    run('{{bin/php}} bin/console tailwind:build --minify');
    run('{{bin/php}} bin/console asset-map:compile');
});


// Hooks
after('deploy:vendors', 'deploy:dump-env');
after('deploy:vendors', 'deploy:assets:build');
after('deploy:vendors', 'database:migrate');
after('deploy:failed', 'deploy:unlock');
