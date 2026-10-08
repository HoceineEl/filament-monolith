// Baselines are per platform: darwin via `npm run test:e2e -- visual --update-snapshots=all`, linux via `bin/update-linux-snapshots.sh`.
// CI regenerates linux baselines and uploads them as the `linux-baselines` artifact when none are committed.
import { appearance, expect, modal, openCustomizer, openRowActions, test, visit } from './support.mjs'

const pages = { table: '/admin/orders', form: '/admin/orders/1/edit', dashboard: '/admin' }

const snap = (page, name, options = {}) => expect(page).toHaveScreenshot(`${name}.png`, options)

for (const sidebar of ['inset', 'floating', 'classic']) {
    for (const mode of ['light', 'dark']) {
        for (const [name, path] of Object.entries(pages)) {
            test(`${name} · ${sidebar} · ${mode}`, async ({ page }) => {
                await appearance(page, { mode, sidebar })
                await visit(page, path)
                await snap(page, `${name}-${sidebar}-${mode}`)
            })
        }
    }
}

for (const density of ['compact', 'comfortable']) {
    test(`table · density ${density}`, async ({ page }) => {
        await appearance(page, { density })
        await visit(page, pages.table)
        await snap(page, `table-density-${density}`)
    })
}

for (const [sidebar, mode] of [['inset', 'light'], ['floating', 'dark']]) {
    for (const [name, path] of Object.entries(pages)) {
        test(`rtl ${name} · ${sidebar} · ${mode}`, async ({ page }) => {
            await appearance(page, { mode, sidebar })
            await visit(page, `${path}?locale=ar`)
            await snap(page, `rtl-${name}-${sidebar}-${mode}`)
        })
    }
}

test('row actions dropdown open', async ({ page }) => {
    await appearance(page)
    await visit(page, pages.table)
    await openRowActions(page, 'NW-10240')
    await snap(page, 'state-row-actions')
})

test('confirmation modal open', async ({ page }) => {
    await appearance(page)
    await visit(page, pages.table)
    await (await openRowActions(page, 'NW-10240')).getByText('Mark as shipped').click()
    await expect(modal(page)).toBeVisible()
    await snap(page, 'state-modal')
})

test('slide-over open', async ({ page }) => {
    await appearance(page)
    await visit(page, '/admin/products')
    await page.locator('.fi-ta-row', { hasText: 'Aero Desk Lamp' }).getByRole('button', { name: 'Edit' }).click()
    await expect(modal(page)).toBeVisible()
    await expect(modal(page).locator('input').first()).toHaveValue('Aero Desk Lamp')
    await snap(page, 'state-slide-over')
})

test('customizer sheet open', async ({ page }) => {
    await appearance(page)
    await visit(page, pages.table)
    await openCustomizer(page)
    await snap(page, 'state-customizer')
})

test('collapsed sidebar', async ({ page }) => {
    await appearance(page, { sidebarOpen: false })
    await visit(page, pages.table)
    await snap(page, 'state-sidebar-collapsed')
})

test.describe('mobile', () => {
    test.use({ viewport: { width: 390, height: 844 }, hasTouch: true, isMobile: true })

    test('table', async ({ page }) => {
        await appearance(page)
        await visit(page, pages.table)
        await snap(page, 'mobile-table')
    })

    test('drawer open', async ({ page }) => {
        await appearance(page)
        await visit(page, pages.table)
        await page.locator('.fi-topbar-open-sidebar-btn').click()
        await expect(page.locator('.fi-main-sidebar.fi-sidebar-open')).toBeVisible()
        await snap(page, 'mobile-drawer')
    })
})
