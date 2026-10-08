import { test as base, expect } from '@playwright/test'
import { execFileSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'

const root = fileURLToPath(new URL('../..', import.meta.url))

export const resetDatabase = (attempts = 3) => {
    try {
        execFileSync('php', ['vendor/bin/testbench', 'migrate:fresh', '--force', '--quiet'], { cwd: root, stdio: 'pipe' })
    } catch (error) {
        if (attempts <= 1) {
            throw error
        }

        execFileSync('sleep', ['0.5'])
        resetDatabase(attempts - 1)
    }
}

const freeze = `
    *, *::before, *::after { transition: none !important; animation: none !important; caret-color: transparent !important; }
    .fi-loading-section { background: none !important; }
`

export const test = base.extend({
    page: async ({ page, baseURL }, use) => {
        resetDatabase()

        await page.route((url) => !url.href.startsWith(baseURL), (route) =>
            route.request().resourceType() === 'stylesheet' ? route.fulfill({ contentType: 'text/css', body: '' }) : route.abort(),
        )

        const errors = []
        page.on('pageerror', (error) => errors.push(error.message))

        await use(page)

        expect(errors, 'no JavaScript errors').toEqual([])
    },
})

export { expect }

export const appearance = (page, { mode = 'light', sidebarOpen = true, ...values } = {}) =>
    page.addInitScript(
        ([mode, sidebarOpen, values]) => {
            if (sessionStorage.getItem('mn-e2e-seeded')) {
                return
            }

            sessionStorage.setItem('mn-e2e-seeded', '1')
            localStorage.setItem('theme', mode)
            localStorage.setItem('isOpenDesktop', JSON.stringify(sidebarOpen))

            if (Object.keys(values).length) {
                localStorage.setItem('monolith:appearance', JSON.stringify(values))
            }
        },
        [mode, sidebarOpen, values],
    )

export const visit = async (page, path) => {
    await page.goto(path)
    await page.waitForLoadState('networkidle')
    await page.locator('html[data-mn-ready]').waitFor({ state: 'attached' })
    await page.addStyleTag({ content: freeze })
    await page.evaluate(() => document.fonts.ready)
    await page.mouse.move(0, 0)
}

export const row = (page, text) => page.locator('.fi-ta-row', { hasText: text })

export const modal = (page) => page.locator('.fi-modal-window:visible').last()

export const openRowActions = async (page, text) => {
    await row(page, text).locator('.fi-ta-actions .fi-dropdown-trigger').first().click()

    const panel = page.locator('.fi-dropdown-panel:visible').last()
    await expect(panel).toBeVisible()

    return panel
}

export const openCustomizer = async (page) => {
    await page.locator('.mn-customizer-trigger').click()

    const sheet = page.locator('.mn-sheet')
    await expect(sheet).toBeVisible()

    return sheet
}
