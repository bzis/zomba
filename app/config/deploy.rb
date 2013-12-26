require "flowdock"
require 'capistrano/ext/multistage'

set :stages,        %w(prod preprod)
set :default_stage, "preprod"
set :stage_dir,     "app/config"

set :application, "vifeed"
set :domain,      "#{application}.co"
set :deploy_to,   "/var/sites/#{domain}"
set :app_path,    "app"

set :repository,  "git@github.com:vifeed/#{application}.git"
set :scm,         :git
set :branch, "master"

default_run_options[:pty] = false

# Automatically set proper permissions
# http://capifony.org/cookbook/set-permissions.html
set :writable_dirs,       [app_path + "/cache", app_path + "/logs"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true
set :user, "deploy"

set :model_manager, "doctrine"
# Or: `propel`

set :use_sudo,    false
set :use_composer, true
set :composer_options,  "--verbose --prefer-dist --optimize-autoloader"


role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

deploy.start

after :deploy, "deploy:notify_flow"

namespace :deploy do
  task :start, :roles => :app, :except => { :no_release => true } do
  end
  task :stop, :roles => :app, :except => { :no_release => true } do
  end
  task :restart, :roles => :app, :except => { :no_release => true } do
    run "sudo service nginx restart"
    puts "--> Restarting nginx".green
    run "sudo service php5-fpm restart"
    puts "--> Restarting php5-fpm".green
    # run "sudo service varnish restart"
    # puts "--> Restarting varnish".green
    # run "sudo supervisorctl update"
    # puts "--> Updating supervisord commands".green
    # run "sudo supervisorctl start all"
    # puts "--> Starting supervisord commands".green
  end
  desc "Notify flow about deployment using email"
  task :notify_flow do
    # create a new Flow object with target flow's api token and sender information
    flow = Flowdock::Flow.new(:api_token => "_YOUR_API_TOKEN_HERE_",
      :source => "Capistrano deployment", :project => "My project",
      :from => {:name => "John Doe", :address => "john.doe@yourdomain.com"})

    # send message to the flow
    flow.send_message(:format => "html", :subject => "Application deployed #deploy",
      :content => "Application deployed successfully!", :tags => ["deploy", "frontend"])
  end
end