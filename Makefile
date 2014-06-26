laravel-initdb::
	rm -f app/database/local.sqlite && touch app/database/local.sqlite

prepare::
	npm install
	composer install


# actually it's nothing to build in this project. (no stylus, no livescript)
npm-build::
	npm run build

all:: update

init:: prepare laravel-initdb npm-build
	composer dump-autoload
	php artisan optimize
	php artisan migrate

update:: prepare npm-build
	composer dump-autoload -o
	php artisan optimize
	php artisan migrate


