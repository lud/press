all:
	composer install --prefer-dist
	npm install --loglevel info
	bower install
	php artisan publish:assets --bench=lud/press
	gulp

