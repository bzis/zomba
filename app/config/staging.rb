server 'vifeed.co', :app, :web, :primary => true
set :parameters_files,  { parameters: 'parameters_stage.yml', grunt:' grunt_stage.json'}

after :deploy do
  run "sh -c 'cd #{latest_release} && test -f app/logs/prod.log || touch app/logs/prod.log && chown deploy:www-data app/logs/* && chmod 664 app/logs/*'"
end
