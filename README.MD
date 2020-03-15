# Bike Robbery
First set database name and port in .env.test then in the root folder of project 
run `php ./bin/console d:d:c` for creating the database and then run 
`./bin/console d:m:m` for running database migrations.

For running test first of all you should install [Symfony Local Web Server](https://symfony.com/doc/current/setup/symfony_server.html).
Then you should define `PROJECT_BASE_URL=http://127.0.0.1:8000` in .env.test and 
set it based on running ip address and port.

Before running the tests you should run `APP_ENV=test symfony server:start` at the root of project and then run `./bin/phpunit`

note: if you wanted to run commands in another environment you can add 
`--env=ENVIRONMENT_NAME` to console commands.

for loading fixture to test database you should run `php bin/console doctrine:fixtures:load --env=test`
for running migration command on test database you should run `./bin/console app:data-migration --env=test`


 