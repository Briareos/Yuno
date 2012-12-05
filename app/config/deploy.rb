set :application, "Yuno"
set :domain,      "s2-marketing.net"
set :deploy_to,   "/home/s2market/yuno"
set :app_path,    "app"
default_run_options[:pty] = true

set :use_sudo,    false
set :user,        "s2market"
set :server_user, "apache"

set :repository,  "git://github.com/Briareos/Yuno.git"
set :scm,         :git
set :scm_verbose, true

set :model_manager, "doctrine"
set :deploy_via,  :remote_cache

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :keep_releases,  3

#logger.level = Logger::MAX_LEVEL

set :shared_files,      ["app/config/parameters.yml", "web/app_dev.php"]
set :shared_children,   [app_path + "/logs", "vendor"]
set :use_composer, true

task :upload_parameters do
  shared_path = deploy_to + "/shared"
  origin_file = "app/config/parameters.yml.dist"
  destination_file = shared_path + "/app/config/parameters.yml"

  try_sudo "mkdir -p #{File.dirname(destination_file)}"
  top.upload(origin_file, destination_file)
end

task :make_cache_writable do
  current_path = deploy_to + "/current"
  try_sudo "setfacl -R -m u:#{server_user}:rwx -m u:#{user}:rwx #{deploy_to}/current/app/cache"
  try_sudo "setfacl -dR -m u:#{server_user}:rwx -m u:#{user}:rwx #{deploy_to}/current/app/cache"
end

task :make_logs_writable do
  current_path = deploy_to + "/current"
  try_sudo "setfacl -R -m u:#{server_user}:rwx -m u:#{user}:rwx #{deploy_to}/current/app/logs"
  try_sudo "setfacl -dR -m u:#{server_user}:rwx -m u:#{user}:rwx #{deploy_to}/current/app/logs"
end


# after "deploy:setup", "upload_parameters"
# after "deploy", "make_cache_writable"
# after "deploy", "make_logs_writable"

# setfacl -R -m u:apache:rwx -m u:cooleryc:rwx current/app/cache shared/app/logs shared/web/uploads
# setfacl -dR -m u:apache:rwx -m u:cooleryc:rwx current/app/cache shared/app/logs shared/web/uploads