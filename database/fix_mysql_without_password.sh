#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${DB_NAME:-talentteno_db}"
DB_USER="${DB_USER:-talentteno_app}"
DB_PASS="${DB_PASS:-talentteno_local_password}"
RESET_SOCKET="${RESET_SOCKET:-/tmp/talentteno-mysql-reset.sock}"
RESET_PID_FILE="${RESET_PID_FILE:-/tmp/talentteno-mysql-reset.pid}"

cd "$(dirname "$0")/.."

cleanup_reset_server() {
    if [[ -f "$RESET_PID_FILE" ]]; then
        sudo kill "$(cat "$RESET_PID_FILE")" >/dev/null 2>&1 || true
        sudo rm -f "$RESET_PID_FILE" "$RESET_SOCKET" >/dev/null 2>&1 || true
    fi
}

echo "Stopping system MySQL..."
sudo systemctl stop mysql

trap cleanup_reset_server EXIT

echo "Starting temporary MySQL without grant checks..."
sudo mysqld_safe \
    --skip-grant-tables \
    --skip-networking \
    --socket="$RESET_SOCKET" \
    --pid-file="$RESET_PID_FILE" \
    >/tmp/talentteno-mysql-reset.log 2>&1 &

for _ in {1..30}; do
    if mysqladmin --protocol=socket --socket="$RESET_SOCKET" --user=root ping >/dev/null 2>&1; then
        break
    fi
    sleep 1
done

if ! mysqladmin --protocol=socket --socket="$RESET_SOCKET" --user=root ping >/dev/null 2>&1; then
    echo "Temporary MySQL did not start. Log:"
    sed -n '1,160p' /tmp/talentteno-mysql-reset.log || true
    exit 1
fi

echo "Creating project database and app user..."
mysql --protocol=socket --socket="$RESET_SOCKET" --user=root <<SQL
FLUSH PRIVILEGES;
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

echo "Stopping temporary MySQL..."
cleanup_reset_server
trap - EXIT

echo "Starting system MySQL..."
sudo systemctl start mysql

echo "Importing project schema and seed data..."
mysql --force --host=127.0.0.1 --port=3306 --user="$DB_USER" --password="$DB_PASS" "$DB_NAME" < database/database_setup.sql

echo
echo "Done. PHP and Node should use:"
echo "DB_HOST=127.0.0.1"
echo "DB_PORT=3306"
echo "DB_USER=$DB_USER"
echo "DB_PASS=$DB_PASS"
echo "DB_NAME=$DB_NAME"
