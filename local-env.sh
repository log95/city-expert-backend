#!/bin/bash

function up() {
    docker-compose up -d

    copy_test_jwt_keys

    load_test_images_to_bucket

    sleep 5  # Give db some time to up

    docker exec city-backend composer install --no-plugins --no-scripts

    docker exec city-backend ./bin/console doctrine:migrations:migrate --no-interaction

    docker exec city-backend ./bin/console doctrine:fixtures:load --append

    printf "Done\n"
}

function copy_test_jwt_keys() {
    if [ ! -d "./config/jwt" ]
    then
        cp -r ./config/jwt_tests ./config/jwt
    fi
}

function load_test_images_to_bucket() {
    is_bucket_exists=$(docker exec -it city-file-storage sh -c "test -d /data/tests && echo 1" | tr -d '\r')

    if [ "$is_bucket_exists" != "1" ]
    then
      docker cp ./fixture_data/tests city-file-storage:/data/tests
    fi
}

case $1 in
  up)
    up
    ;;
  down)
    docker-compose down
    ;;
  # TODO: протестить
  run-tests)
    docker exec city-backend ./vendor/bin/codecept run
  *)
    printf "Unknown command\n"
    ;;
esac
