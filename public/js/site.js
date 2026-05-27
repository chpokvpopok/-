document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('.nav-toggle');
    const siteNav = document.querySelector('.site-nav');

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', () => {
            const isOpen = siteNav.classList.toggle('site-nav--open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (event) => {
            if (!siteNav.classList.contains('site-nav--open')) {
                return;
            }

            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }

            if (siteNav.contains(target) || navToggle.contains(target)) {
                return;
            }

            siteNav.classList.remove('site-nav--open');
            navToggle.setAttribute('aria-expanded', 'false');
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && siteNav.classList.contains('site-nav--open')) {
                siteNav.classList.remove('site-nav--open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetId = link.getAttribute('href');
            if (!targetId || targetId === '#') {
                return;
            }

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                event.preventDefault();
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.pushState(null, '', targetId);
                if (siteNav?.classList.contains('site-nav--open')) {
                    siteNav.classList.remove('site-nav--open');
                    navToggle?.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });

    document.querySelectorAll('.faq-question').forEach((button) => {
        button.addEventListener('click', () => {
            const item = button.closest('.faq-item');
            const answerId = button.getAttribute('aria-controls');
            const answer = answerId ? document.getElementById(answerId) : null;

            if (!item || !answer) {
                return;
            }

            const isOpen = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!isOpen));
            if (isOpen) {
                answer.hidden = true;
                item.classList.remove('faq-item--open');
            } else {
                answer.hidden = false;
                item.classList.add('faq-item--open');
            }
        });
    });
});
