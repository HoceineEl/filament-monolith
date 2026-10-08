@php
    $cspNonce = \Illuminate\Support\Facades\Vite::cspNonce();
@endphp

@foreach ($fallbackFonts as $fallbackFont)
    <link href="{{ \HoceineEl\Monolith\MonolithTheme::get()->getFontUrl($fallbackFont) }}" rel="stylesheet" />
@endforeach

<script @if (filled($cspNonce)) nonce="{{ $cspNonce }}" @endif>
    (() => {
        const defaults = @js($appearance);
        const customizable = @js($customizable);
        const serverStored = @js($stored);
        const endpoint = @js($endpoint);
        const charts = @js($charts);
        const fallbackFonts = @js($fallbackFonts);
        const fontUrl = @js($fontUrl);
        const searchHint = @js($searchHint ?? null);
        const key = 'monolith:appearance'
        const root = document.documentElement

        const readStored = (name, fallback) => {
            try {
                return JSON.parse(localStorage.getItem(name)) ?? fallback
            } catch (error) {
                return fallback
            }
        }

        let stored = Object.fromEntries(
            Object.entries(serverStored ?? readStored(key, {})).filter(([name]) => customizable.includes(name)),
        )

        const systemStack = "ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif"

        const fontSlug = (family) => family.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')

        const loadFont = (family, preview = false) => {
            if (! family || family === 'system' || family === defaults.font) {
                return
            }

            const slug = fontSlug(family)

            if (document.getElementById(`mn-font-${slug}`) || (preview && document.getElementById(`mn-font-${slug}-preview`))) {
                return
            }

            const link = document.createElement('link')
            link.id = preview ? `mn-font-${slug}-preview` : `mn-font-${slug}`
            link.rel = 'stylesheet'
            link.href = fontUrl
                .replaceAll('{slug}', slug)
                .replaceAll('{family}', encodeURIComponent(family))
                .replaceAll('{weights}', preview ? '500' : '400,500,600,700')
            document.head.appendChild(link)
        }

        const fontStack = (family) => [
            family === 'system' ? systemStack : `'${family.replace(/['"\\]/g, '')}'`,
            ...fallbackFonts.map((fallback) => `'${fallback}'`),
        ].join(', ')

        const apply = (appearance = { ...defaults, ...stored }) => {
            Object.entries(appearance).forEach(([name, value]) => root.setAttribute(`data-mn-${name}`, value))
            loadFont(appearance.font)

            if (appearance.font !== defaults.font || fallbackFonts.length) {
                root.style.setProperty('--font-family', fontStack(appearance.font))
            } else {
                root.style.removeProperty('--font-family')
            }

            return appearance
        }

        let saveTimer = null

        const save = (appearance) => {
            stored = Object.fromEntries(
                Object.entries(appearance).filter(([name, value]) => customizable.includes(name) && defaults[name] !== value),
            )

            if (endpoint) {
                clearTimeout(saveTimer)
                saveTimer = setTimeout(() => fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ appearance: stored }),
                }).catch(() => {}), 400)
            } else {
                try {
                    Object.keys(stored).length
                        ? localStorage.setItem(key, JSON.stringify(stored))
                        : localStorage.removeItem(key)
                } catch (error) {}
            }

            return apply({ ...defaults, ...stored })
        }

        const isMac = /Mac|iPhone|iPad/.test(navigator.userAgentData?.platform ?? navigator.platform ?? '')

        if (isMac) {
            root.setAttribute('data-mn-os', 'mac')
        }

        if (searchHint) {
            root.setAttribute('data-mn-search-hint', '')
            root.style.setProperty('--mn-search-hint', JSON.stringify(isMac ? searchHint.mac : searchHint.other))
        }

        window.Monolith = { defaults, read: () => ({ ...defaults, ...stored }), apply, save, loadFont }

        if (charts) {
            const alpha = (color, amount) => {
                if (typeof color !== 'string') {
                    return color
                }

                if (color.startsWith('rgb(')) {
                    return color.replace('rgb(', 'rgba(').replace(')', `, ${amount})`)
                }

                if (/^(oklch|oklab|lab|lch|hsl|hwb)\(/.test(color) && ! color.includes('/')) {
                    return color.replace(/\)$/, ` / ${amount})`)
                }

                return color
            }

            let probe = null

            const token = (name) => {
                if (! probe) {
                    probe = document.createElement('span')
                    probe.hidden = true
                    document.body.appendChild(probe)
                }

                probe.style.color = `var(${name})`

                return getComputedStyle(probe).color
            }

            const paint = (index) => () => token(`--mn-chart-${(index % 5) + 1}`)

            window.filamentChartJsGlobalPlugins = [
                ...(window.filamentChartJsGlobalPlugins ?? []),
                {
                    id: 'monolith',
                    beforeInit(chart) {
                        const { options, config } = chart
                        const type = config.type
                        const datasets = config.data?.datasets ?? []

                        options.layout ??= {}
                        options.layout.padding ??= { top: 4 }

                        options.plugins ??= {}
                        options.plugins.legend ??= {}
                        options.plugins.legend.labels ??= {}
                        options.plugins.legend.labels.usePointStyle ??= true
                        options.plugins.legend.labels.pointStyle ??= 'circle'
                        options.plugins.legend.labels.boxWidth ??= 7
                        options.plugins.legend.labels.boxHeight ??= 7
                        options.plugins.legend.labels.padding ??= 16

                        options.plugins.tooltip ??= {}
                        options.plugins.tooltip.padding ??= 10
                        options.plugins.tooltip.cornerRadius ??= 8
                        options.plugins.tooltip.borderWidth ??= 1
                        options.plugins.tooltip.boxPadding ??= 4
                        options.plugins.tooltip.usePointStyle ??= true
                        options.plugins.tooltip.titleFont ??= { weight: 600 }
                        options.plugins.tooltip.caretSize ??= 0
                        options.plugins.tooltip.displayColors ??= datasets.length > 1 || ['doughnut', 'pie', 'polarArea'].includes(type)

                        if (['line', 'bar'].includes(type)) {
                            options.interaction ??= { mode: 'index', intersect: false }

                            for (const axis of ['x', 'y']) {
                                options.scales[axis] ??= {}
                                options.scales[axis].ticks ??= {}
                                options.scales[axis].ticks.padding ??= 8
                                options.scales[axis].border ??= {}
                                options.scales[axis].border.dash ??= [3, 4]
                            }

                            options.scales.y.ticks.maxTicksLimit ??= 6
                        }

                        datasets.forEach((dataset, index) => {
                            if (type === 'line' || type === 'radar') {
                                dataset.borderColor ??= paint(index)
                                dataset.pointBackgroundColor ??= paint(index)
                            }

                            if (type === 'bar') {
                                dataset.backgroundColor ??= paint(index)
                            }
                        })

                        if (type === 'line') {
                            for (const dataset of datasets) {
                                dataset.tension ??= 0.4
                                dataset.borderWidth ??= 2
                                dataset.pointRadius ??= 0
                                dataset.pointHoverRadius ??= 4
                                dataset.pointHoverBorderWidth ??= 2
                                dataset.pointHoverBorderColor ??= token('--mn-card') || '#fff'

                                if (dataset.fill && typeof dataset.backgroundColor !== 'function') {
                                    dataset.backgroundColor = (context) => {
                                        const { ctx, chartArea } = context.chart
                                        const border = context.dataset.borderColor ?? context.chart.options.borderColor
                                        const color = typeof border === 'function' ? border(context) : border

                                        if (! chartArea || typeof color !== 'string') {
                                            return alpha(color, 0.12)
                                        }

                                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
                                        gradient.addColorStop(0, alpha(color, 0.28))
                                        gradient.addColorStop(1, alpha(color, 0))

                                        return gradient
                                    }
                                }
                            }
                        }

                        if (type === 'bar') {
                            for (const dataset of datasets) {
                                dataset.borderRadius ??= 6
                                dataset.borderWidth ??= 0
                                dataset.borderSkipped ??= false
                                dataset.maxBarThickness ??= 36
                            }

                            options.scales.x.grid ??= {}
                            options.scales.y.grid ??= {}
                        }

                        if (['doughnut', 'pie'].includes(type)) {
                            if (type === 'doughnut') {
                                options.cutout ??= '72%'
                            }

                            for (const dataset of datasets) {
                                dataset.borderWidth ??= 3
                                dataset.borderRadius ??= 4
                                dataset.hoverOffset ??= 4
                            }
                        }
                    },
                },
            ]
        }

        apply()

        root.setAttribute('data-mn-sb', readStored('isOpenDesktop', true) ? 'open' : 'collapsed')

        document.addEventListener('alpine:initialized', () => {
            requestAnimationFrame(() => requestAnimationFrame(() => root.setAttribute('data-mn-ready', '')))
        })

        const heightsKey = 'monolith:heights'
        let heights = readStored(heightsKey, {})

        const widgetKey = (element) => {
            const owner = element.closest('[wire\\:snapshot]')

            try {
                return owner ? `${location.pathname}::${JSON.parse(owner.getAttribute('wire:snapshot')).memo.name}` : null
            } catch (error) {
                return null
            }
        }

        const sizePlaceholder = (element) => {
            const key = widgetKey(element)

            if (key && heights[key]) {
                element.style.height = `${heights[key]}px`
            }
        }

        new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType !== 1) {
                        continue
                    }

                    if (node.classList.contains('fi-loading-section')) {
                        sizePlaceholder(node)
                    }

                    node.querySelectorAll?.('.fi-loading-section').forEach(sizePlaceholder)
                }
            }
        }).observe(root, { childList: true, subtree: true })

        const rememberHeight = (element) => {
            if (! element || element.classList.contains('fi-loading-section') || element.querySelector(':scope > .fi-loading-section')) {
                return
            }

            const key = widgetKey(element)
            const height = Math.round(element.getBoundingClientRect().height)

            if (! key || height < 24 || heights[key] === height) {
                return
            }

            heights = { ...readStored(heightsKey, {}), [key]: height }

            try {
                localStorage.setItem(heightsKey, JSON.stringify(heights))
            } catch (error) {}
        }

        window.addEventListener('pagehide', () => {
            document.querySelectorAll('[wire\\:snapshot]').forEach((element) => {
                if (element.matches('.fi-wi-widget, [class*="fi-wi-"]')) {
                    rememberHeight(element)
                }
            })
        })

        const markOverflowing = (element) => element.classList.toggle('mn-overflowing', element.scrollWidth > element.clientWidth + 1)

        const tableObserver = new ResizeObserver((entries) => entries.forEach(({ target }) => markOverflowing(target)))

        const hideEmptyBadges = () => document.querySelectorAll('.fi-icon-btn-badge-ctn').forEach((badge) => {
            badge.toggleAttribute('data-mn-empty', badge.textContent.trim() === '0')
        })

        const markOverflowingTables = () => {
            hideEmptyBadges()

            document.querySelectorAll('.fi-ta-content-ctn').forEach((element) => {
                tableObserver.observe(element)
                markOverflowing(element)
            })
        }

        let overflowFrame = null

        const scheduleOverflowCheck = () => {
            cancelAnimationFrame(overflowFrame)
            overflowFrame = requestAnimationFrame(markOverflowingTables)
        }

        document.addEventListener('DOMContentLoaded', scheduleOverflowCheck)
        window.addEventListener('resize', scheduleOverflowCheck)

        document.addEventListener('livewire:navigated', () => {
            apply()
            scheduleOverflowCheck()
        })

        document.addEventListener('livewire:init', () => {
            window.Livewire.hook('commit', ({ component, succeed }) => succeed(() => queueMicrotask(() => {
                scheduleOverflowCheck()

                if (component.el?.matches?.('.fi-wi-widget, [class*="fi-wi-"]') || component.el?.querySelector?.(':scope > .fi-wi-widget, :scope > [class*="fi-wi-"]')) {
                    requestAnimationFrame(() => rememberHeight(component.el))
                }
            })))
        })

        document.addEventListener('alpine:init', () => {
            window.Alpine.data('monolithCustomizer', ({ enumNames, switches, defaultMode, fonts, fontCases }) => ({
                open: false,
                copied: false,
                mode: defaultMode,
                appearance: window.Monolith.read(),
                fonts,
                fontQuery: '',
                fontCatalog: null,
                fontCatalogFailed: false,
                fontResults: [],
                fontActive: -1,
                arabicOnly: root.dir === 'rtl',

                show() {
                    this.appearance = window.Monolith.read()
                    this.mode = localStorage.getItem('theme') ?? defaultMode
                    this.open = true
                    this.fonts.forEach((family) => window.Monolith.loadFont(family, true))
                },

                moveRadio(event) {
                    const radio = event.target.closest('[role=radio]')
                    const group = radio?.closest('[role=radiogroup]')
                    const rtl = group && getComputedStyle(group).direction === 'rtl'
                    const step = { ArrowDown: 1, ArrowUp: -1, ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1 }[event.key]

                    if (! group || ! step) {
                        return
                    }

                    event.preventDefault()

                    const radios = [...group.querySelectorAll('[role=radio]')]
                    const next = radios[(radios.indexOf(radio) + step + radios.length) % radios.length]

                    next.focus()
                    next.click()
                },

                syncRadios() {
                    this.$root.querySelectorAll('[role=radiogroup]').forEach((group) => {
                        const radios = [...group.querySelectorAll('[role=radio]')]
                        const checked = radios.find((radio) => radio.getAttribute('aria-checked') === 'true') ?? radios[0]

                        radios.forEach((radio) => (radio.tabIndex = radio === checked ? 0 : -1))
                    })
                },

                set(name, value) {
                    this.appearance = window.Monolith.save({ ...this.appearance, [name]: value })
                },

                toggle(name) {
                    this.set(name, this.appearance[name] === 'on' ? 'off' : 'on')
                    scheduleOverflowCheck()
                },

                setMode(mode) {
                    this.mode = mode
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: mode }))
                },

                reset() {
                    this.appearance = window.Monolith.save({ ...window.Monolith.defaults })
                },

                isCustomFont() {
                    return ! this.fonts.includes(this.appearance.font)
                },

                async loadFontCatalog() {
                    if (this.fontCatalog) {
                        return
                    }

                    const cacheKey = 'monolith:font-catalog'
                    const cached = readStored(cacheKey, null)

                    if (cached?.at > Date.now() - 6048e5 && cached.fonts?.length) {
                        this.fontCatalog = cached.fonts
                    } else {
                        try {
                            const list = await (await fetch('https://fonts.bunny.net/list')).json()

                            this.fontCatalog = Object.values(list).map(({ familyName, category, variants }) => [familyName, category, 'arabic' in (variants ?? {})])

                            try {
                                localStorage.setItem(cacheKey, JSON.stringify({ at: Date.now(), fonts: this.fontCatalog }))
                            } catch (error) {}
                        } catch (error) {
                            this.fontCatalogFailed = true

                            return
                        }
                    }

                    this.searchFonts()
                },

                searchFonts() {
                    if (! this.fontCatalog) {
                        return
                    }

                    const query = this.fontQuery.trim().toLowerCase()

                    this.fontResults = this.fontCatalog
                        .filter(([family, , arabic]) => (! this.arabicOnly || arabic) && family.toLowerCase().includes(query))
                        .sort(([a], [b]) => (b.toLowerCase().startsWith(query) - a.toLowerCase().startsWith(query)) || a.localeCompare(b))
                        .slice(0, 40)

                    this.fontActive = -1
                    clearTimeout(this.previewTimer)
                    this.previewTimer = setTimeout(() => this.fontResults.slice(0, 10).forEach(([family]) => window.Monolith.loadFont(family, true)), 180)
                },

                previewFont(index) {
                    const result = this.fontResults[index]

                    if (result) {
                        window.Monolith.loadFont(result[0], true)
                    }
                },

                moveFont(step) {
                    if (! this.fontResults.length) {
                        return
                    }

                    this.fontActive = (this.fontActive + step + this.fontResults.length) % this.fontResults.length
                    this.previewFont(this.fontActive)
                    this.$nextTick(() => this.$refs.fontResults?.children[this.fontActive]?.scrollIntoView({ block: 'nearest' }))
                },

                pickFont(index = this.fontActive) {
                    const result = this.fontResults[index]

                    if (result) {
                        this.set('font', result[0])
                    }
                },

                snippet() {
                    const lines = Object.entries(this.appearance).filter(([name, value]) => enumNames[name]?.[1][value]).map(([name, value]) => {
                        const [enumName, cases] = enumNames[name]

                        return `    ->${name}(${enumName}::${cases[value]})`
                    })

                    const font = this.appearance.font

                    lines.push(`    ->font(${fontCases[font] ? `Font::${fontCases[font]}` : `'${font.replace(/'/g, "\\'")}'`})`)

                    const toggles = Object.entries(switches).map(([name, method]) => `    ->${method}(${this.appearance[name] === 'on' ? '' : 'false'})`)

                    return ['MonolithTheme::make()', ...lines, ...toggles].join('\n')
                },

                async copy() {
                    try {
                        await navigator.clipboard.writeText(this.snippet())
                        this.copied = true
                        setTimeout(() => (this.copied = false), 1600)
                    } catch (error) {}
                },
            }))
        })
    })()
</script>
