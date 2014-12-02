all:
	composer install
	npm install
	bower install
	php artisan publish:assets --bench=lud/press
	gulp

