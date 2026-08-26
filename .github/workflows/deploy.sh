#!/bin/bash
# Server-side half of the deploy. Run by .github/workflows/deploy.yml as:
#   ssh <user>@<host> "APP_DIR=... DEPLOY_SHA=... bash -s" < this file
#
# Front-end assets are NOT built here - the workflow builds them on the GitHub
# runner and rsyncs public/build/ across before this script runs. That keeps
# Node off the production server and makes a broken build a red step in Actions
# instead of a half-updated site.
#
# Output uses ::group:: / ::error:: so each phase folds separately in the
# Actions log and failures surface as annotations on the run.

# -E so the ERR trap is inherited by functions and subshells.
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/home/smslccuk/datafuture}"
DEPLOY_SHA="${DEPLOY_SHA:-unknown}"

# preflight = run only the toolchain check and exit. The workflow calls this
# BEFORE uploading anything, so a missing php/composer/rsync leaves the server
# completely untouched instead of stranding it on new assets + old code.
DEPLOY_MODE="${DEPLOY_MODE:-deploy}"

# composer.lock requires PHP >= 8.2 (laravel/nightwatch, symfony 7, nette/utils).
# Never rely on the account's default `php` - cPanel often points it at an
# older build than the one the app needs.
PHP_BIN="${PHP_BIN:-/usr/local/bin/ea-php82}"
# Optional override. Left empty, the toolchain check probes the usual cPanel
# locations and $PATH - the path differs between servers.
COMPOSER_BIN="${COMPOSER_BIN:-}"

PHASE="startup"

# Explicit, explained failure. Disarms the ERR trap so the run reports once.
fail() {
    trap - ERR
    echo "::endgroup::"
    echo "::error title=Deploy failed during ${PHASE}::$1"
    exit 1
}

# Anything that dies unexpectedly still names the phase, the line and the
# command, so the Actions annotation alone usually identifies the problem.
on_err() {
    local rc=$1 line=$2 cmd=$3
    trap - ERR
    echo "::endgroup::"
    echo "::error title=Deploy failed during ${PHASE}::line ${line}: '${cmd}' exited ${rc}"
    exit "$rc"
}
trap 'on_err "$?" "$LINENO" "$BASH_COMMAND"' ERR

phase() {
    [ "$PHASE" = "startup" ] || echo "::endgroup::"
    PHASE="$1"
    echo "::group::$1"
}

# ---------------------------------------------------------------------------
phase "1/6 Toolchain check"

cd "$APP_DIR" || fail "APP_DIR '$APP_DIR' does not exist or is not readable. Check the DEPLOY_PATH repository variable."

# --- PHP --------------------------------------------------------------
# If the configured build is absent, fall back to the newest ea-php that
# actually satisfies composer.lock, rather than failing on a guessed path.
if [ ! -x "$PHP_BIN" ]; then
    echo "note: $PHP_BIN not present, searching for a PHP >= 8.2 build..."
    for candidate in $(ls -1r /usr/local/bin/ea-php8* 2>/dev/null); do
        if [ -x "$candidate" ] && "$candidate" -r 'exit(version_compare(PHP_VERSION,"8.2.0",">=")?0:1);' 2>/dev/null; then
            PHP_BIN="$candidate"; break
        fi
    done
fi
if [ ! -x "$PHP_BIN" ]; then
    echo "PHP builds present on this server:"
    ls -1 /usr/local/bin/ea-php* 2>/dev/null || echo "  (none)"
    fail "No PHP >= 8.2 found. Install one in WHM > EasyApache 4, or set PHP_BIN in deploy.yml."
fi

PHP_VERSION="$("$PHP_BIN" -r 'echo PHP_VERSION;')"
"$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'     || fail "composer.lock needs PHP >= 8.2 but $PHP_BIN is $PHP_VERSION."

# --- Transfer tools ---------------------------------------------------
command -v rsync >/dev/null 2>&1 || fail "rsync is not installed on the server; built assets cannot be uploaded."
command -v git   >/dev/null 2>&1 || fail "git is not installed on the server."

# --- Composer ---------------------------------------------------------
# This project keeps composer inside the app directory, so look there first,
# then fall back to the usual cPanel locations and $PATH. Set COMPOSER_BIN in
# deploy.yml to skip the search entirely.
COMPOSER_RUN=""
composer_candidates=(
    ${COMPOSER_BIN:-}
    "$APP_DIR/composer.phar"
    "$APP_DIR/composer"
    "$HOME/composer.phar"
    "$HOME/bin/composer"
    /opt/cpanel/composer/bin/composer
    /usr/local/bin/composer
    /usr/bin/composer
    "$(command -v composer 2>/dev/null || true)"
)

for candidate in "${composer_candidates[@]}"; do
    [ -n "$candidate" ] && [ -f "$candidate" ] || continue
    # Prefer running the phar under our chosen PHP, so composer cannot pick up
    # the account default (older) interpreter and fail the platform check.
    if "$PHP_BIN" "$candidate" --version >/dev/null 2>&1; then
        COMPOSER_RUN="$PHP_BIN $candidate"; break
    fi
    # Otherwise it is a shell wrapper - run it as-is.
    if [ -x "$candidate" ] && "$candidate" --version >/dev/null 2>&1; then
        COMPOSER_RUN="$candidate"; break
    fi
done

if [ -z "$COMPOSER_RUN" ]; then
    echo "Searched:"; printf '  %s
' "${composer_candidates[@]}"
    echo "command -v composer -> $(command -v composer 2>/dev/null || echo 'not on PATH')"
    fail "Composer not found. Install it, or set COMPOSER_BIN in deploy.yml to its full path."
fi

echo "php      : $PHP_VERSION ($PHP_BIN)"
echo "composer : $($COMPOSER_RUN --version 2>/dev/null | head -1)  [$COMPOSER_RUN]"
echo "app dir  : $APP_DIR"
echo "target   : $DEPLOY_SHA"

echo "rsync    : $(command -v rsync)"

if [ "$DEPLOY_MODE" = "preflight" ]; then
    echo "preflight OK - server is ready, nothing has been changed."
    echo "::endgroup::"
    trap - ERR
    exit 0
fi

# Assets are rsynced in before this runs; if they are absent something went
# wrong upstream and the site would 500 on every Vite asset.
if [ ! -f public/build/manifest.json ] && [ ! -f public/build/.vite/manifest.json ]; then
    fail "No Vite manifest in $APP_DIR/public/build - the asset upload did not land."
fi
echo "assets   : manifest present ($(find public/build -type f | wc -l) files)"

# ---------------------------------------------------------------------------
phase "2/6 Pull latest code"

echo "was at: $(git rev-parse --short HEAD) $(git log -1 --format=%s)"
git pull origin main
echo "now at: $(git rev-parse --short HEAD) $(git log -1 --format=%s)"

if [ "$DEPLOY_SHA" != "unknown" ] && [ "$(git rev-parse HEAD)" != "$DEPLOY_SHA" ]; then
    echo "::warning title=Commit mismatch::Server is on $(git rev-parse HEAD) but the workflow deployed $DEPLOY_SHA. Someone may have pushed during the run."
fi

# ---------------------------------------------------------------------------
phase "3/6 Composer dependencies"

$COMPOSER_RUN install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# ---------------------------------------------------------------------------
phase "4/6 Database migrations"

"$PHP_BIN" artisan migrate --force

# ---------------------------------------------------------------------------
phase "5/6 Rebuild caches"
# Clear first, then rebuild. The old script ran optimize:clear last, which threw
# away the config/route/view caches it had just built.

"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

# ---------------------------------------------------------------------------
phase "6/6 Restart queue workers"

"$PHP_BIN" artisan queue:restart

# ---------------------------------------------------------------------------
echo "::endgroup::"
PHASE="done"
trap - ERR
echo "::notice title=Deployed::$(git rev-parse --short HEAD) on PHP $PHP_VERSION"
