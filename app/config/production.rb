# role :app, "54.194.153.217", "54.194.182.184", :primary => true
role :app, "54.194.182.184", :primary => true

set :parameters_files,  parameters: 'parameters_prod.yml', grunt: 'grunt_prod.json'

after :deploy do
  run "sh -c 'cd #{latest_release} && test -f app/logs/prod.log || touch app/logs/prod.log && chown deploy:www-data app/logs/* && chmod 664 app/logs/*'"
end
