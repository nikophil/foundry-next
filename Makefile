MYSQL_URL="mysql://root:1234@localhost:3307/foundry_test"
POSTGRES_URL="postgresql://zenstruck:zenstruck@localhost:5433/zenstruck_foundry?serverVersion=15"

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

generate-migrations: ### Generate/replace migrations
	rm -rf tests/Fixture/Migrations
	DATABASE_URL=${MYSQL_URL} bin/console doctrine:database:create --if-not-exists
	DATABASE_URL=${MYSQL_URL} bin/console doctrine:schema:drop --force --full-database
	DATABASE_URL=${MYSQL_URL} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
	DATABASE_URL=${MYSQL_URL} bin/console doctrine:migrations:diff --allow-empty-diff
	DATABASE_URL=${MYSQL_URL} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
	DATABASE_URL=${MYSQL_URL} bin/console doctrine:schema:validate
	DATABASE_URL=${POSTGRES_URL} bin/console doctrine:database:create --if-not-exists
	DATABASE_URL=${POSTGRES_URL} bin/console doctrine:schema:drop --force --full-database
	DATABASE_URL=${POSTGRES_URL} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
	DATABASE_URL=${POSTGRES_URL} bin/console doctrine:migrations:diff --allow-empty-diff
	DATABASE_URL=${POSTGRES_URL} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
	DATABASE_URL=${POSTGRES_URL} bin/console doctrine:schema:validate
