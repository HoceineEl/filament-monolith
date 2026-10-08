#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
version="$(node -p "require('$root/node_modules/@playwright/test/package.json').version")"

docker run --rm \
    -v "$root:/src:ro" \
    -v "$root/tests/e2e/__screenshots__:/out" \
    "mcr.microsoft.com/playwright:v$version-noble" \
    bash -c '
        set -e
        apt-get update -qq >/dev/null
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq software-properties-common >/dev/null
        add-apt-repository -y ppa:ondrej/php >/dev/null
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq php8.4-cli php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-intl php8.4-curl php8.4-zip >/dev/null
        mkdir /work && cd /src && tar --exclude=./test-results --exclude='./vendor/orchestra/testbench-core/laravel/storage/framework/views/*.php' -cf - . | tar -xf - -C /work
        cd /work
        CI=1 npx playwright test -c tests/e2e/playwright.config.mjs visual --update-snapshots=all --retries=0 "$@"
        cp tests/e2e/__screenshots__/visual.spec.mjs/*-linux.png /out/visual.spec.mjs/
    ' -- "$@"
