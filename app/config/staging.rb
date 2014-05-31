server 'vifeed.co', :app, :web, :primary => true
set :parameters_file, 'parameters_staging.yml'

after :deploy do
  run "sh -c 'cd #{latest_release} && test -f app/logs/prod.log || touch app/logs/prod.log && chown deploy:www-data app/logs/* && chmod 664 app/logs/*'"
end
