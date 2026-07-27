#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${DB_NAME:-talentteno_db}"
DB_USER="${DB_USER:-talentteno_app}"
DB_PASS="${DB_PASS:-talentteno_local_password}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
MYSQL_BIN="${MYSQL_BIN:-mysql}"

if [[ -x /opt/lampp/bin/mysql ]]; then
    MYSQL_BIN="${MYSQL_BIN:-/opt/lampp/bin/mysql}"
    MYSQL_BIN="/opt/lampp/bin/mysql"
fi

cd "$(dirname "$0")/.."

setup_sql=$(mktemp)
trap 'rm -f "$setup_sql"' EXIT

cat > "$setup_sql" <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if "$MYSQL_BIN" --host="$DB_HOST" --port="$DB_PORT" --user=root --password= < "$setup_sql"; then
    :
elif ! sudo mysql < "$setup_sql"; then
    echo
    echo "sudo mysql could not log in. Enter a MySQL admin account that can create databases/users."
    read -r -p "MySQL admin username [root]: " MYSQL_ADMIN_USER
    MYSQL_ADMIN_USER="${MYSQL_ADMIN_USER:-root}"
    read -r -s -p "MySQL admin password: " MYSQL_ADMIN_PASS
    echo

    MYSQL_PWD="${MYSQL_ADMIN_PASS}" "$MYSQL_BIN" --user="${MYSQL_ADMIN_USER}" < "$setup_sql"
fi

"$MYSQL_BIN" --force --host="${DB_HOST}" --port="$DB_PORT" --user="${DB_USER}" --password="${DB_PASS}" "${DB_NAME}" < database/database_setup.sql

echo "Local MySQL database '${DB_NAME}' is ready for user '${DB_USER}'."
