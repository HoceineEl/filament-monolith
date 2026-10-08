import AxeBuilder from '@axe-core/playwright'
import { appearance, expect, openCustomizer, test, visit } from './support.mjs'

const audit = (page, include = null) => {
    const builder = new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'])

    return (include ? builder.include(include) : builder).analyze()
}

const summarize = ({ violations }) => violations.map(({ id, nodes }) => `${id}: ${nodes.map((node) => node.target.join(' ')).slice(0, 3).join(', ')}`)

for (const mode of ['light', 'dark']) {
    for (const [name, path] of [['dashboard', '/admin'], ['orders', '/admin/orders'], ['order form', '/admin/orders/create'], ['orders rtl', '/admin/orders?locale=ar']]) {
        test(`${name} has no WCAG AA violations in ${mode} mode`, async ({ page }) => {
            await appearance(page, { mode })
            await visit(page, path)

            expect(summarize(await audit(page))).toEqual([])
        })
    }

    test(`customizer and row actions have no WCAG AA violations in ${mode} mode`, async ({ page }) => {
        await appearance(page, { mode })
        await visit(page, '/admin/orders')

        await page.locator('.fi-ta-row').first().locator('.fi-ta-actions .fi-dropdown-trigger').first().click()
        await expect(page.locator('.fi-dropdown-panel:visible').last()).toBeVisible()
        expect(summarize(await audit(page, '.fi-dropdown-panel'))).toEqual([])
        await page.keyboard.press('Escape')

        await openCustomizer(page)
        expect(summarize(await audit(page, '.mn-sheet'))).toEqual([])
    })
}

test('customizer radio groups are keyboard operable', async ({ page }) => {
    await appearance(page)
    await visit(page, '/admin/orders')

    await page.locator('.mn-customizer-trigger').focus()
    await page.keyboard.press('Enter')
    await expect(page.locator('.mn-sheet')).toBeVisible()

    await page.keyboard.press('Tab')
    await page.keyboard.press('Tab')
    await expect(page.locator(':focus')).toHaveText(/Inset/)

    await page.keyboard.press('ArrowRight')
    await expect(page.locator('html')).toHaveAttribute('data-mn-sidebar', 'classic')
    await expect(page.locator(':focus')).toHaveText(/Classic/)

    await page.keyboard.press('Tab')
    await expect(page.locator(':focus')).toHaveAttribute('role', 'radio')
    await expect(page.locator(':focus')).toHaveAttribute('aria-checked', 'true')

    await page.keyboard.press('Escape')
    await expect(page.locator('.mn-customizer-trigger')).toBeFocused()
})

test('every focusable control on the orders page shows a focus indicator', async ({ page }) => {
    await appearance(page)
    await visit(page, '/admin/orders')

    const missing = []

    for (let index = 0; index < 30; index++) {
        await page.keyboard.press('Tab')

        const result = await page.evaluate(() => {
            const element = document.activeElement
            const style = getComputedStyle(element)
            const ring = (style.outlineStyle !== 'none' && parseFloat(style.outlineWidth) > 0) || style.boxShadow !== 'none'

            return ring ? null : `${element.className}`.split(' ').slice(0, 2).join('.')
        })

        if (result) {
            missing.push(result)
        }
    }

    expect(missing).toEqual([])
})
