.PHONY: help
help:
	@echo "Available commands:"
	@echo "  make up         - Start all services"
	@echo "  make down       - Stop all services"
	@echo "  make restart    - Restart all services"
	@echo "  make logs       - Show logs"
	@echo "  make frontend   - Access frontend shell"
	@echo "  make backend    - Access backend shell"
	@echo "  make db         - Access database shell"
	@echo "  make install    - Install dependencies"
	@echo "  make test       - Run tests"
	@echo "  make lint       - Run linters"
	@echo "  make format     - Run formatters"

.PHONY: up
up:
	docker-compose up -d

.PHONY: down
down:
	docker-compose down

.PHONY: restart
restart: down up

.PHONY: logs
logs:
	docker-compose logs -f

.PHONY: frontend
frontend:
	docker-compose exec frontend sh

.PHONY: backend
backend:
	docker-compose exec backend sh

.PHONY: db
db:
	docker-compose exec database mysql -u blog_user -pblog_password blog_db

.PHONY: install
install:
	docker-compose exec frontend npm install
	docker-compose exec backend composer install

.PHONY: test
test:
	docker-compose exec frontend npm test -- --watchAll=false
	docker-compose exec backend php artisan test

.PHONY: lint
lint:
	docker-compose exec frontend npm run lint
	docker-compose exec backend ./vendor/bin/pint --test

.PHONY: format
format:
	docker-compose exec frontend npm run format
	docker-compose exec backend ./vendor/bin/pint