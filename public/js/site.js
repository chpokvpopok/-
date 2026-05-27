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

    document.querySelectorAll('.js-lead-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const messageEl = form.querySelector('.lead-form__message');
            const submitBtn = form.querySelector('[type="submit"]');

            const setMessage = (text, type = '') => {
                if (!messageEl) {
                    return;
                }
                messageEl.textContent = text;
                messageEl.className = 'lead-form__message';
                if (type) {
                    messageEl.classList.add(`lead-form__message--${type}`);
                }
            };

            const formData = new FormData(form);
            const phoneRaw = String(formData.get('phone') ?? '').trim();
            const phoneDigits = phoneRaw.replace(/\D/g, '');

            const payload = {
                name: String(formData.get('name') ?? '').trim(),
                email: String(formData.get('email') ?? '').trim(),
                phone: phoneRaw,
                organization: String(formData.get('organization') ?? '').trim(),
                comment: String(formData.get('comment') ?? '').trim(),
                source: String(formData.get('source') ?? 'home').trim() || 'home',
            };

            if (payload.name.length < 2) {
                setMessage('Укажите имя (минимум 2 символа).', 'error');
                return;
            }

            if (!payload.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.email)) {
                setMessage('Укажите корректный email.', 'error');
                return;
            }

            if (phoneDigits.length < 10) {
                setMessage('Укажите телефон (минимум 10 цифр).', 'error');
                return;
            }

            if (submitBtn instanceof HTMLButtonElement) {
                submitBtn.disabled = true;
            }
            setMessage('Отправка…');

            try {
                const csrfRes = await fetch('/api/csrf-token', { credentials: 'same-origin' });
                if (!csrfRes.ok) {
                    throw new Error('Не удалось получить CSRF-токен.');
                }

                const csrfData = await csrfRes.json();
                payload.csrf_token = csrfData.token;

                const response = await fetch('/api/lead/create', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.error || 'Не удалось отправить заявку.');
                }

                form.reset();
                setMessage('Заявка отправлена. Менеджер свяжется с вами.', 'success');
            } catch (error) {
                const text = error instanceof Error ? error.message : 'Ошибка отправки.';
                setMessage(text, 'error');
            } finally {
                if (submitBtn instanceof HTMLButtonElement) {
                    submitBtn.disabled = false;
                }
            }
        });
    });
});
