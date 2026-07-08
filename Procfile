web: (test -f storage/app/database.sqlite || touch storage/app/database.sqlite) && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
