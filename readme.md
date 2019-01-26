## U-test
For running project create `.env` file(just copy `.env.example`) and run  `docker-compose up -d`.
Then run `docker exec -ti u-test_server_1 /bin/bash`.
Now you can use artisan-commands, composer, etc...

For example, you can find api-routes by using `php artisan route:list` from docker container.

For running tests run `docker exec -ti u-test_server_1 "vendor/bin/phpunit"` or just run `vendor/bin/phpunit` if you're inside the docker container.
