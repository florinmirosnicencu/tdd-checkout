# Laravel artisan

## sample command

Generate key
```
docker-compose run php php src/artisan key:generate
```
Make model
```
docker-compose run php php src/artisan make:model Concert
```
Refresh and seed
```
docker-compose run php php src/artisan migrate:refresh --seed