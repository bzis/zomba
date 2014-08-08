# role :app, "54.194.153.217", "54.194.182.184", :primary => true
role :app, "54.194.153.217", :primary => true

set :parameters_files,  parameters: 'parameters_stage.yml', grunt: 'grunt_stage.json'
