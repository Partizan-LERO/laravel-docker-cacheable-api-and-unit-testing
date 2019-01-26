## U-test
How to run:
1. Create `.env` file(just copy `.env.example`)
2. `docker-compose up -d`.
3. `docker exec -ti u-test_server_1 /bin/bash`.
4. `composer install`.
5. `php artisan migrate`.

For running tests make sure you have testing environment. 
Then run `docker exec -ti u-test_server_1 "vendor/bin/phpunit"` or just run `vendor/bin/phpunit` if you're inside the docker container.
