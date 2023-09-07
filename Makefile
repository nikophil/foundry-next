.PHONY: help
help:
	@fgrep -h "###" $(MAKEFILE_LIST) | fgrep -v fgrep | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: test
test: test-schema-no-dama test-migrate-no-dama test-schema-dama test-migrate-dama ### Run all tests

.PHONY: test-schema-no-dama
test-schema-no-dama: ### Run tests with reset: schema, dama: no
	DATABASE_RESET_MODE=schema vendor/bin/phpunit

.PHONY: test-schema-no-dama
test-migrate-no-dama: ### Run tests with reset: migrate, dama: no
	DATABASE_RESET_MODE=migrate vendor/bin/phpunit

.PHONY: test-schema-dama
test-schema-dama: ### Run tests with reset: schema, dama: yes
	DATABASE_RESET_MODE=schema vendor/bin/phpunit -c phpunit.dama.xml.dist

.PHONY: test-migrate-dama
test-migrate-dama: ### Run tests with reset: migrate, dama: yes
	DATABASE_RESET_MODE=migrate vendor/bin/phpunit -c phpunit.dama.xml.dist

generate-migrations: ### Generate migrations
	bin/console doctrine:database:create --if-not-exists
	bin/console doctrine:schema:drop --force --full-database
	bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
	bin/console doctrine:migrations:diff --allow-empty-diff
	bin/console doctrine:schema:validate
