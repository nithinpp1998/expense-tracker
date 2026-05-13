.PHONY: setup serve test fresh queue

PHP = php
NPM = npm

setup:
	$(NPM) install
	@$(PHP) -r "file_exists('.env') || copy('.env.example', '.env');"
	$(PHP) artisan key:generate --ansi
	$(PHP) artisan migrate --force
	$(PHP) artisan db:seed --force
	$(NPM) run build

serve:
	$(PHP) artisan serve

dev:
	$(NPM) run dev &
	$(PHP) artisan serve

test:
	$(PHP) artisan config:clear
	$(PHP) artisan test

fresh:
	$(PHP) artisan migrate:fresh --seed
