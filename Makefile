.DEFAULT_GOAL := help

help:
	@echo 'Usage:'
	@echo '  make [target]'
	@echo ''
	@echo 'Targets which run locally:'
	@echo '  lc-init                   Initialize the application, e.g.: re-create database and load database migrations.'
	@echo '  lc-init-full              Initialize the application, e.g.: re-create database, load database migrations and apply fixtures.'
	@echo '  lc-init-full-for-phpunit  Initialize the application, e.g.: re-create database, load database migrations and apply fixtures for PHPUnit.'
	@echo '  lc-init-full-for-dredd    Initialize the application, e.g.: re-create database, load database migrations and apply fixtures for Dredd.'
	@echo ''
	@echo 'Targets which run inside Docker:'
	@echo '  dc-init                   Configure and initialize the application, e.g.: start Docker containers, re-create database and load database migrations.'
	@echo '  dc-init-full              Configure and initialize the application, e.g.: start Docker containers, re-create database, load database migrations and apply fixtures.'
	@echo '  dc-init-full-for-phpunit  Configure and initialize the application, e.g.: start Docker containers, re-create database, load database migrations and apply fixtures for PHPUnit.'
	@echo '  dc-init-full-for-dredd    Configure and initialize the application, e.g.: start Docker containers, re-create database, load database migrations and apply fixtures for Dredd.'
	@echo '  dc-down                   Stop Docker containers.'

lc-init:
	@bin/delete_database
	@bin/create_database
	@bin/migrate

lc-init-full: lc-init
	@php bin/load_fixtures

lc-init-full-for-phpunit: lc-init
	@php bin/load_fixtures_for_phpunit

lc-init-full-for-dredd: lc-init
	@php bin/load_fixtures_for_dredd

dc-init:
	@docker_bin/docker_configure
	@docker_bin/docker_build
	@docker_bin/docker_up
	@docker_bin/docker_initialize
	@docker_bin/delete_database
	@docker_bin/create_database
	@docker_bin/migrate

dc-init-full: dc-init
	@docker_bin/load_fixtures

dc-init-full-for-phpunit: dc-init
	@docker_bin/load_fixtures_for_phpunit

dc-init-full-for-dredd: dc-init
	@docker_bin/load_fixtures_for_dredd

dc-down:
	@docker_bin/docker_down
