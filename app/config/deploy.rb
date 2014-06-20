require 'capistrano/flowdock'

# for Flowdock Gem notifications
set :flowdock_project_name, 'vifeed'
set :flowdock_deploy_tags, ['frontend']
set :flowdock_api_token, '99c0b1ff68ca7ff45786742d6430d6a7'

set :stages,        %w(production staging)
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
set :interactive_mode, false

default_run_options[:pty] = false

# Automatically set proper permissions
# http://capifony.org/cookbook/set-permissions.html
set :writable_dirs,       [app_path + '/cache', app_path + '/logs']
set :shared_children,     []
# set :shared_files,        [app_path + '/config/supervisor/deamons.conf']
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
  task :start, roles: :app, except: { no_release: true } do
  end
  task :stop, roles: :app, except: { no_release: true } do
  end
  task :restart, roles: :app do
    run 'sudo /etc/init.d/nginx restart'
    puts '--> Restarting nginx'.green
    run 'sudo /etc/init.d/php5-fpm restart'
    puts '--> Restarting php5-fpm'.green
    run 'sudo service varnish restart'
    puts '--> Restarting varnish'.green
    run 'sudo supervisorctl update'
    puts '--> Updating supervisord commands'.green
    run 'sudo supervisorctl start all'
    puts '--> Starting supervisord commands'.green
  end
end

namespace :frontend do
  task :before_assetic_dump, role: :app do
    npm_install
    bower_install
    fos_js_routing_dump
    grunt.default
  end
  task :npm_install, role: :app do
    run "sh -c 'cd #{latest_release} && npm cache clear && npm install'"
    puts '--> bower install'.green
  end
  task :bower_install, role: :app do
    run "sh -c 'cd #{latest_release} && bower cache clean && bower install'"
    puts '--> npm install'.green
  end
  task :fos_js_routing_dump, role: :app do
    run "sh -c 'cd #{latest_release} && php app/console fos:js-routing:dump --env=prod'"
    puts '--> fos js routing dump'.green
  end
  namespace :grunt do
    task :default, role: :app do
      run "sh -c 'cd #{latest_release} && grunt --verbose'"
      puts '--> grunt default'.green
    end
    task :after_assetic_dump, role: :app do
      run "sh -c 'cd #{latest_release} && grunt after_assetic_dump'"
      puts '--> grunt after_assetic_dump'.green
    end
  end
end
after "deploy:update", "deploy:cleanup"

before 'symfony:cache:warmup', 'symfony:doctrine:migrations:migrate'

before 'symfony:assetic:dump', 'frontend:before_assetic_dump'

after 'symfony:assetic:dump', 'frontend:grunt:after_assetic_dump'

set :parameters_dir, 'app/config'
set :parameters_files, []

task :upload_parameters do
  parameters_files.each { |destination_file_name, origin|
    origin_file = parameters_dir + '/' + origin if parameters_dir && origin
    if origin_file && File.exist?(origin_file)
      ext = File.extname(origin)
      relative_path = 'app/config/' + destination_file_name.to_s + ext

      if shared_files && shared_files.include?(relative_path)
        destination_file = shared_path + '/' + relative_path
      else
        destination_file = latest_release + '/' + relative_path
      end
      try_sudo "mkdir -p #{File.dirname(destination_file)}"
      try_sudo "ls -l #{latest_release}/app/config"

      top.upload(origin_file, destination_file)
    end
  }
end

before 'deploy:share_childs', 'upload_parameters'
