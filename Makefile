COMMIT := $(shell git rev-parse --short=8 HEAD)
ZIP_FILENAME := $(or $(ZIP_FILENAME),"simple-event-sheets.zip")
BUILD_DIR := $(or $(BUILD_DIR),"build")
VENDOR_AUTOLOAD := vendor/autoload.php
NODE_MODULES := node_modules/.package-lock.json

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build:  ## Build
	git archive --format=zip --output=${ZIP_FILENAME} $(COMMIT)
	mkdir ${BUILD_DIR} && mv ${ZIP_FILENAME} ${BUILD_DIR}/

.PHONY: clean
clean:  ## clean
	rm -rf build dist

$(VENDOR_AUTOLOAD):
	composer install --prefer-dist --no-progress

$(NODE_MODULES):
	npm install

.PHONY: composer
composer: $(VENDOR_AUTOLOAD) ## Runs composer install

.PHONY: npm
npm: $(NODE_MODULES) ## Runs npm install

.PHONY: lint
lint: composer npm ## Lint
	vendor/squizlabs/php_codesniffer/bin/phpcs
	npm run lint
	npm run prettier

.PHONY: fmt
fmt: composer ## PHP Fmt
	vendor/squizlabs/php_codesniffer/bin/phpcbf

.PHONY: docs
docs:  ## Generate Docs
	docker run --rm -v $(shell pwd):/data phpdoc/phpdoc:3 --ignore=vendor/ -d . -t docs/

.PHONY: dev
dev:  ## Docker up
	docker-compose up

.PHONY: mysql
mysql:  ## Runs mysql cli in mysql container
	docker exec -it $(BASENAME)-db-1 mariadb -u root -psomewordpress wordpress

.PHONY: bash
bash:  ## Runs bash shell in wordpress container
	docker exec -it -w /var/www/html $(BASENAME)-wordpress-1 bash
