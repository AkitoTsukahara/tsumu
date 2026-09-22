SAIL := ./vendor/bin/sail

.DEFAULT_GOAL := help

.PHONY: help setup up stop down restart shell root-shell user migrate test test-unit test-feature test-db test-browser pint build dev logs boost-update

help:
	@echo "Tsumu 開発コマンド"
	@echo "  make setup         初回セットアップ"
	@echo "  make up            Dockerを起動"
	@echo "  make stop          Dockerを停止"
	@echo "  make down          コンテナを削除"
	@echo "  make restart       Dockerを再起動"
	@echo "  make shell         コンテナへ接続"
	@echo "  make user          ログインユーザーを作成"
	@echo "  make test          全テストを実行"
	@echo "  make pint          PHPコードを整形"
	@echo "  make build         フロントをビルド"
	@echo "  make dev           フロントの開発サーバーを起動"
	@echo "  make logs          アプリのログを表示"
	@echo "  make boost-update  Laravel Boostの生成物を更新"

setup: up
	$(SAIL) composer install
	@if grep -q '^APP_KEY=$$' .env; then $(SAIL) artisan key:generate; fi
	$(SAIL) artisan migrate --force
	$(SAIL) npm ci
	$(SAIL) npx playwright install chromium
	$(SAIL) npm run build
	@echo "セットアップが完了しました。続けて make user を実行してください。"

up: vendor/bin/sail .env database/database.sqlite
	$(SAIL) up -d

vendor/bin/sail:
	docker run --rm \
		--user "$$(id -u):$$(id -g)" \
		--volume "$(CURDIR):/app" \
		--workdir /app \
		composer:2 composer install --ignore-platform-reqs --no-scripts

.env:
	cp .env.example .env

database/database.sqlite:
	touch database/database.sqlite

stop:
	$(SAIL) stop

down:
	$(SAIL) down

restart:
	$(SAIL) restart

shell root-shell user migrate test test-unit test-feature test-db test-browser pint build dev logs boost-update: | up

shell:
	$(SAIL) shell

root-shell:
	$(SAIL) root-shell

user:
	$(SAIL) artisan tsumu:user:create

migrate:
	$(SAIL) artisan migrate

test:
	$(SAIL) composer test

test-unit:
	$(SAIL) artisan test --testsuite=Unit

test-feature:
	$(SAIL) artisan test --testsuite=Feature

test-db:
	$(SAIL) artisan test --testsuite=DbIntegration

test-browser:
	$(SAIL) artisan test --testsuite=Browser

pint:
	$(SAIL) pint --dirty --format agent

build:
	$(SAIL) npm run build

dev:
	$(SAIL) npm run dev

logs:
	$(SAIL) logs -f laravel.test

boost-update:
	$(SAIL) artisan boost:update --no-interaction
