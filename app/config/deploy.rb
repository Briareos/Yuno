set :stage_dir, 'app/config/deploy'
require 'capistrano/ext/multistage'
set :stages, %w(production testing development)

set :application, "Yuno"
#set :domain,      "s2-marketing.net"
#set :deploy_to,   "/home/s2market/yuno"
set :app_path,    "app"

set :use_sudo,    false
#set :user,        "s2market"
#set :server_user, "s2market"

set :repository,  "git@github.com:Briareos/Yuno.git"
set :scm,         :git
set :scm_passphrase, "metalfox"

set :model_manager, "doctrine"

#role :web,        domain                         # Your HTTP server, Apache/etc
#role :app,        domain                         # This may be the same as your `Web` server
#role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
#logger.level = Logger::MAX_LEVEL

set :shared_files,      ["app/config/parameters.yml", "web/app_dev.php"]
set :shared_children,   [app_path + "/logs", "vendor"]

# Mandatory for Symfony 2.1
set :use_composer, true

# In case of "SoftException in Application.cpp:XXX: Directory "/var/www/" is writeable by group" by WHM/cPanel
set :group_writable, false

# In case of some "ppy" errors
default_run_options[:pty] = true

