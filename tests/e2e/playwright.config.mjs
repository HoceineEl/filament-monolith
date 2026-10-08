import { defineConfig } from '@playwright/test'
import { createServer } from 'node:net'
import { fileURLToPath } from 'node:url'

const root = fileURLToPath(new URL('../..', import.meta.url))

const freePort = () =>
    new Promise((resolve, reject) => {
        const server = createServer()

        server.once('error', reject)
        server.listen(0, '127.0.0.1', () => {
            const { port } = server.address()
            server.close(() => resolve(port))
        })
    })

process.env.MN_E2E_PORT ||= String(await freePort())

const baseURL = `http://127.0.0.1:${process.env.MN_E2E_PORT}`

export default defineConfig({
    testDir: '.',
    testMatch: '*.spec.mjs',
    outputDir: `${root}/test-results`,
    snapshotPathTemplate: '{testDir}/__screenshots__/{testFileName}/{arg}-{platform}{ext}',
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    timeout: 30_000,
    expect: {
        timeout: 7_000,
        toHaveScreenshot: { maxDiffPixelRatio: 0.01, animations: 'disabled', caret: 'hide', scale: 'css' },
    },
    reporter: process.env.CI ? [['list'], ['github']] : 'list',
    use: {
        baseURL,
        viewport: { width: 1280, height: 800 },
        colorScheme: 'light',
        locale: 'en-US',
        timezoneId: 'UTC',
        trace: 'retain-on-failure',
        launchOptions: { executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE || undefined },
    },
    webServer: {
        command: `php vendor/bin/testbench workbench:build --quiet && php vendor/bin/testbench serve --port=${process.env.MN_E2E_PORT} --no-reload`,
        cwd: root,
        url: `${baseURL}/admin/orders`,
        env: { PHP_CLI_SERVER_WORKERS: '4' },
        reuseExistingServer: false,
        timeout: 120_000,
        stdout: 'ignore',
        stderr: 'pipe',
    },
})
