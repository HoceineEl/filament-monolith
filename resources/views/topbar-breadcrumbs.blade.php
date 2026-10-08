<nav
    aria-label="{{ __('monolith::customizer.breadcrumbs') }}"
    class="mn-topbar-breadcrumbs"
    x-data="{}"
    x-init="
        const breadcrumbs = document.querySelector('.fi-main .fi-header .fi-breadcrumbs')
        const heading = document.querySelector('.fi-main .fi-header-heading')

        if (breadcrumbs) {
            const clone = breadcrumbs.cloneNode(true)
            clone.removeAttribute('aria-label')
            $el.replaceChildren(clone)
        } else if (heading) {
            const title = document.createElement('span')
            title.className = 'mn-topbar-title'
            title.textContent = heading.textContent.trim()
            $el.replaceChildren(title)
        }
    "
></nav>
