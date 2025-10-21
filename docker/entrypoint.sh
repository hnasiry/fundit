#!/bin/sh
set -eu

APP_DIR="/var/www/html"
APP_USER="${APP_USER:-www-data}"
APP_GROUP="${APP_GROUP:-www-data}"

run_as_app() {
    if [ "$(id -u)" = '0' ] && command -v gosu >/dev/null 2>&1; then
        gosu "${APP_USER}:${APP_GROUP}" "$@"
    else
        "$@"
    fi
}

if [ "$(id -u)" = '0' ]; then
    install -d -m 775 -o "${APP_USER}" -g "${APP_GROUP}" \
        "${APP_DIR}/storage" \
        "${APP_DIR}/storage/framework" \
        "${APP_DIR}/storage/framework/sessions" \
        "${APP_DIR}/storage/framework/views" \
        "${APP_DIR}/storage/framework/cache" \
        "${APP_DIR}/storage/logs" \
        "${APP_DIR}/bootstrap/cache"

    chown -R "${APP_USER}:${APP_GROUP}" "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
    chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

    if [ ! -f "${APP_DIR}/storage/logs/laravel.log" ]; then
        touch "${APP_DIR}/storage/logs/laravel.log"
    fi

    chown "${APP_USER}:${APP_GROUP}" "${APP_DIR}/storage/logs/laravel.log"
    chmod 664 "${APP_DIR}/storage/logs/laravel.log"
fi

cd "${APP_DIR}"

if [ "$#" -eq 0 ]; then
    set -- php-fpm
fi

if [ ! -f artisan ]; then
    if [ "$(id -u)" = '0' ] && command -v gosu >/dev/null 2>&1; then
        exec gosu "${APP_USER}:${APP_GROUP}" "$@"
    fi

    exec "$@"
fi

command="$1"

if [ "$command" = "php-fpm" ] || [ "$command" = "php-fpm8.4" ]; then
    umask 002

    if [ -f composer.json ] && [ ! -d vendor ]; then
        run_as_app composer install --no-interaction --prefer-dist
    fi

    if [ ! -f .env ] && [ -f .env.example ]; then
        run_as_app cp .env.example .env
    fi

    if [ -f .env ]; then
        if ! grep -q '^APP_KEY=' .env 2>/dev/null || [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2-)" ]; then
            run_as_app php artisan key:generate --force --ansi
        fi
    else
        echo "Skipping APP_KEY generation because no .env file is present." >&2
    fi

    if [ -f .env ]; then
        if [ "${DB_CONNECTION:-}" = "mysql" ]; then
            echo "Waiting for database connection..."
            until run_as_app php -r '
                $host = getenv("DB_HOST") ?: "mysql";
                $port = getenv("DB_PORT") ?: 3306;
                $db   = getenv("DB_DATABASE") ?: "";
                $user = getenv("DB_USERNAME") ?: "";
                $pass = getenv("DB_PASSWORD") ?: "";

                $dsn = $db !== "" ?
                    sprintf("mysql:host=%s;port=%s;dbname=%s", $host, $port, $db) :
                    sprintf("mysql:host=%s;port=%s", $host, $port);

                try {
                    new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 1, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    exit(0);
                } catch (PDOException $e) {
                    exit(1);
                }
            ';
            do
                echo "  Database is unavailable - sleeping"
                sleep 1
            done
        fi

        echo "Running database migrations..."
        run_as_app php artisan migrate --force --no-interaction

        echo "Clearing caches..."
        run_as_app php artisan optimize:clear
    else
        echo "Skipping database migrations and cache clearing because no .env file is present." >&2
    fi

    exec "$@"
fi

if [ "$(id -u)" = '0' ] && command -v gosu >/dev/null 2>&1; then
    exec gosu "${APP_USER}:${APP_GROUP}" "$@"
fi

exec "$@"
