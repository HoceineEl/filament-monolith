import { readFileSync } from 'node:fs'
import { appearance, expect, openCustomizer, openRowActions, test, visit } from './support.mjs'

const fontList = readFileSync(new URL('./fixtures/bunny-list.json', import.meta.url), 'utf8')

test('customizer changes persist across reloads', async ({ page }) => {
    await appearance(page)
    await visit(page, '/admin/orders')

    const html = page.locator('html')
    await expect(html).toHaveAttribute('data-mn-sidebar', 'inset')

    const sheet = await openCustomizer(page)
    await sheet.getByRole('radio', { name: 'Floating' }).click()
    await sheet.getByRole('radio', { name: 'Blue' }).click()

    await expect(html).toHaveAttribute('data-mn-sidebar', 'floating')
    await expect(html).toHaveAttribute('data-mn-accent', 'blue')

    await page.reload()

    await expect(html).toHaveAttribute('data-mn-sidebar', 'floating')
    await expect(html).toHaveAttribute('data-mn-accent', 'blue')
    expect(await page.evaluate(() => JSON.parse(localStorage.getItem('monolith:appearance')))).toMatchObject({ sidebar: 'floating', accent: 'blue' })
})

test('font picker searches the catalog and applies a font', async ({ page }) => {
    await page.route('https://fonts.bunny.net/list', (route) => route.fulfill({ contentType: 'application/json', body: fontList }))

    await appearance(page)
    await visit(page, '/admin/orders')

    const sheet = await openCustomizer(page)
    const search = sheet.getByRole('combobox')

    await search.fill('space')

    const results = sheet.locator('#mn-font-results [role="option"]')
    await expect(results).toHaveCount(2)
    await expect(results.first()).toContainText('Space Grotesk')

    await results.first().click()

    await expect.poll(() => page.evaluate(() => document.documentElement.style.getPropertyValue('--font-family'))).toContain('Space Grotesk')
    await expect(page.locator('html')).toHaveAttribute('data-mn-font', 'Space Grotesk')
})

test('floating sidebar sits on the right in RTL', async ({ page }) => {
    await appearance(page, { sidebar: 'floating' })
    await visit(page, '/admin/orders?locale=ar')

    await expect(page.locator('html')).toHaveAttribute('dir', 'rtl')

    const box = await page.locator('.fi-main-sidebar').boundingBox()

    expect(box.x).toBeGreaterThan(page.viewportSize().width / 2)
})

test('sticky row actions dropdown is not covered when the table overflows', async ({ page }) => {
    await page.setViewportSize({ width: 820, height: 800 })
    await appearance(page)
    await visit(page, '/admin/orders')

    await expect(page.locator('.fi-ta-content-ctn')).toHaveClass(/mn-overflowing/)

    const panel = await openRowActions(page, 'NW-10245')

    for (const item of await panel.locator('.fi-dropdown-list-item').all()) {
        const covered = await item.evaluate((element) => {
            const { x, y, width, height } = element.getBoundingClientRect()
            const hit = document.elementFromPoint(x + width / 2, y + height / 2)

            return ! element.contains(hit) && ! hit?.closest('.fi-dropdown-panel')
        })

        expect(covered).toBe(false)
    }
})

for (const path of ['/admin', '/admin/orders', '/admin/orders/create', '/admin/orders/1/edit', '/admin/products', '/admin/orders?locale=ar', '/admin?locale=ar']) {
    test(`no JavaScript errors on ${path}`, async ({ page }) => {
        await appearance(page)
        await visit(page, path)
        await expect(page.locator('.fi-main')).toBeVisible()
    })
}

test('collapsible filters share the toolbar row with search', async ({ page }) => {
    await appearance(page)
    await visit(page, '/admin/products?collapsible-filters=1')

    const trigger = page.locator('.fi-ta-filters-trigger-action-ctn .fi-icon-btn')
    const search = page.locator('.fi-ta-search-field input')
    const [triggerBox, searchBox] = [await trigger.boundingBox(), await search.boundingBox()]

    expect(Math.abs(triggerBox.y + triggerBox.height / 2 - (searchBox.y + searchBox.height / 2))).toBeLessThan(3)
    expect(triggerBox.x).toBeGreaterThan(searchBox.x)

    await trigger.click()
    await expect(page.locator('.fi-ta-filters-above-content-ctn.fi-open .fi-ta-filters')).toBeVisible()
})
