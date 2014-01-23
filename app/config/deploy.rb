require 'flowdock/capistrano'
# for Flowdock Gem notifications
set :flowdock_project_name, 'vifeed'
set :flowdock_deploy_tags, ['frontend']
set :flowdock_api_token, '99c0b1ff68ca7ff45786742d6430d6a7'

set :stages,        %w(prod staging)
set :default_stage, 'staging'
set :stage_dir,     'app/config'
require 'capistrano/ext/multistage'

set :application, 'vifeed'
set :domain,      "#{application}.co"
set :deploy_to,   "/var/sites/#{application}"
set :app_path,    'app'

set :repository,  "git@github.com:vifeed/#{application}.git"
set :scm,         :git
set :branch, 'master'

default_run_options[:pty] = false

# Automatically set proper permissions
# http://capifony.org/cookbook/set-permissions.html
set :writable_dirs,       [app_path + '/cache', app_path + '/logs']
set :webserver_user,      'www-data'
set :permission_method,   :acl
set :use_set_permissions, true
set :user, 'deploy'

set :model_manager, 'doctrine'
# Or: `propel`

set :use_sudo,    false
set :use_composer, true
set :composer_options,  '--verbose --prefer-dist --optimize-autoloader'

set :keep_releases,  3


set :dump_assetic_assets, true
set :assets_install,      true

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

deploy.start

namespace :deploy do
  task :start, :roles => :app, :except => { :no_release => true } do
  end
  task :stop, :roles => :app, :except => { :no_release => true } do
  end
  task :restart, :roles => :app, :except => { :no_release => true } do
    run 'sudo /etc/init.d/nginx restart'
    puts '--> Restarting nginx'.green
    run 'sudo /etc/init.d/php5-fpm restart'
    puts '--> Restarting php5-fpm'.green
    # run 'sudo service varnish restart'
    # puts '--> Restarting varnish'.green
    # run 'sudo supervisorctl update'
    # puts '--> Updating supervisord commands'.green
    # run 'sudo supervisorctl start all'
    # puts '--> Starting supervisord commands'.green
  end
end

before 'symfony:assetic:dump' do
    run "sh -c 'cd #{latest_release} && npm install'"
    run "sh -c 'cd #{latest_release} && bower install'"
    run "sh -c 'cd #{latest_release} && php app/console fos:js-routing:dump --env=prod'"
    run "sh -c 'cd #{latest_release} && grunt'"
end

after 'symfony:assetic:dump' do
    run "sh -c 'cd #{latest_release} && grunt s3'"
end

set :parameters_dir, 'app/config/parameters'
set :parameters_file, false

task :upload_parameters do
  origin_file = parameters_dir + '/' + parameters_file if parameters_dir && parameters_file
  if origin_file && File.exists?(origin_file)
    ext = File.extname(parameters_file)
    relative_path = 'app/config/parameters' + ext

    if shared_files && shared_files.include?(relative_path)
      destination_file = shared_path + '/' + relative_path
    else
      destination_file = latest_release + '/' + relative_path
    end
    try_sudo "mkdir -p #{File.dirname(destination_file)}"

    top.upload(origin_file, destination_file)
  end
end

before 'deploy:share_childs', 'upload_parameters'