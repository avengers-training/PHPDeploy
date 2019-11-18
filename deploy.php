<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'deploy_project');

// Project repository
set('repository', 'git@github.com:avengers-training/PHPDeploy.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Default branch
set('branch', 'develop');

// Shared files/dirs between deploys 
add('shared_files', [
	'.env',
]);
add('shared_dirs', [
	'storage',
	'bootstrap/cache',
]);

// Writable dirs by web server 
add('writable_dirs', [
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

// Hosts

host('10.0.4.16')
	->user('deploy')
	->stage('thequy')
    ->set('deploy_path', '~/{{application}}')
    ->forwardAgent(false);    
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('npm:install', function () {
    run('cd {{release_path}} && npm install');
});

task('npm:run_dev', function () {
    run('cd {{release_path}} && npm run dev');
});

task('reload:php-fpm', function () {
    run('sudo /etc/init.d/php7.3-fpm reload');
});

task('deploy', [
	'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'npm:install',
    'npm:run_dev',
    'deploy:writable',
    'deploy:vendors',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:cache:clear',
    'artisan:config:cache',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);
// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');

after('cleanup', 'reload:php-fpm');
