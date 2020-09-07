# Slowloris
The classic slowloris DDOS attack rewritten in PHP 7

https://en.wikipedia.org/wiki/Slowloris_(computer_security)

## Docker
`docker run javabudd/slowloris:latest localhost 80`

## Docker-Compose
`docker-compose run slowloris localhost 80`

# Manual execution/testing

## Dependencies
`composer install`

## Execution
`php slowloris.php localhost 80`

## Testing
`composer test`

# License
The code is licensed under the MIT License.
