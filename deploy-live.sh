#!/usr/bin/env bash
set -euo pipefail

EXPECTED_ROOT="/home/athenas/Downloads/talentteno."
EXPECTED_REMOTE="https://github.com/Bharath-Thiravium/talenttino-website.git"

repo_root="$(git rev-parse --show-toplevel)"
branch="$(git branch --show-current)"
remote="$(git remote get-url origin)"

if [[ "$repo_root" != "$EXPECTED_ROOT" ]]; then
  echo "Wrong repository root: $repo_root" >&2
  exit 1
fi

if [[ "$branch" != "main" ]]; then
  echo "Wrong branch: $branch" >&2
  exit 1
fi

if [[ "$remote" != "$EXPECTED_REMOTE" ]]; then
  echo "Wrong origin remote: $remote" >&2
  exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Working tree is not clean. Commit or discard local changes before deployment." >&2
  exit 1
fi

git fetch origin
local_head="$(git rev-parse HEAD)"
remote_head="$(git rev-parse origin/main)"

if [[ "$local_head" != "$remote_head" ]]; then
  echo "Local HEAD does not match origin/main." >&2
  echo "Local:  $local_head" >&2
  echo "Remote: $remote_head" >&2
  exit 1
fi

if [[ -z "${LIVE_ROOT:-}" ]]; then
  echo "LIVE_ROOT is required. Example: LIVE_ROOT=/home/ACCOUNT/public_html/ergon ./deploy-live.sh" >&2
  exit 1
fi

case "$LIVE_ROOT" in
  /|/home|/var|/var/www|/usr|/etc|"")
    echo "Refusing dangerous LIVE_ROOT: $LIVE_ROOT" >&2
    exit 1
    ;;
esac

if [[ ! -d "$LIVE_ROOT" ]]; then
  echo "LIVE_ROOT does not exist: $LIVE_ROOT" >&2
  exit 1
fi

if [[ ! -f "$LIVE_ROOT/frontend/includes/site-data.php" && ! -f "$LIVE_ROOT/index.php" ]]; then
  echo "LIVE_ROOT does not contain the expected Talenttino project marker." >&2
  exit 1
fi

timestamp="$(date +%Y%m%d-%H%M%S)"
backup_root="$(dirname "$LIVE_ROOT")/talenttino-backup-$timestamp"
commit_short="$(git rev-parse --short HEAD)"

echo "Creating backup: $backup_root"
rsync -a \
  --exclude ".git" \
  --exclude "node_modules" \
  --exclude "database/runtime" \
  --exclude "tmp" \
  --exclude "cache" \
  "$LIVE_ROOT"/ "$backup_root"/

echo "Deploying commit: $commit_short"
rsync -a \
  --exclude ".git" \
  --exclude ".env" \
  --exclude "node_modules" \
  --exclude "database/runtime" \
  --exclude "frontend/uploads" \
  --exclude "backend/includes/db.php" \
  --exclude "talentteno-old-backup" \
  --exclude "talentteno" \
  --exclude "tmp" \
  --exclude "cache" \
  "$repo_root"/ "$LIVE_ROOT"/

echo "Deployed commit: $commit_short"
echo "View Source marker to verify: TT_DEPLOY_VERSION: $commit_short"
