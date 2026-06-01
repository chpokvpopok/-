/**
 * configurator.js
 *
 * Модуль конфигуратора мебельного заказа.
 *
 * Отвечает за:
 *  - Динамический пересчёт цены при изменении опций (без перезагрузки)
 *  - Управление галереей изображений
 *  - Открытие/закрытие модального окна заказа
 *  - Клиентскую валидацию формы перед отправкой
 *  - AJAX-отправку заказа через fetch API в формате JSON
 *  - Отображение статусов загрузки и ошибок
 *
 * Архитектура: IIFE-модуль (Immediately Invoked Function Expression).
 * Все переменные и функции изолированы внутри модуля —
 * глобальное пространство имён не загрязняется.
 *
 * type="module" в HTML-теге <script> обеспечивает:
 *  - Автоматический строгий режим ('use strict')
 *  - Изоляцию области видимости
 *  - Выполнение после построения DOM (аналог DOMContentLoaded)
 */

const Configurator = (() => {

    // ==================================================================
    // КОНСТАНТЫ И СОСТОЯНИЕ
    // ==================================================================

    /**
     * Центральный объект состояния конфигуратора.
     * Все изменения идут через функции-мутаторы, а не напрямую —
     * это упрощает отладку и исключает рассинхронизацию UI и данных.
     */
    const state = {
        basePrice:       0,       // базовая цена товара (читается из data-атрибута)
        currentPrice:    0,       // текущая расчётная цена с опциями
        productId:       0,       // ID товара (читается из data-атрибута)
        selectedOptions: new Set(), // Set хранит ID выбранных опций без дублей
        isSubmitting:    false,   // флаг блокировки повторной отправки
    };

    // ==================================================================
    // УТИЛИТЫ
    // ==================================================================

    /**
     * Форматирует число в строку вида "850 000 ₸".
     * Пробел как разделитель тысяч — стандарт для казахстанских цен.
     *
     * @param {number} amount
     * @returns {string}
     */
    const formatPrice = (amount) => {
        return new Intl.NumberFormat('ru-KZ', {
            style:    'currency',
            currency: 'KZT',
            // Убираем десятичные знаки — цены целые
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    };

    /**
     * Минимальная клиентская санитизация строки.
     * Не является заменой серверной валидации — только
     * дополнительный рубеж перед отправкой.
     *
     * @param {string} str
     * @returns {string}
     */
    const sanitize = (str) => String(str).trim().slice(0, 1000);

    /**
     * Показывает или скрывает элемент через атрибут hidden.
     * Предпочтительнее display:none/block — не требует знания
     * исходного типа display и работает с CSS-анимациями.
     *
     * @param {HTMLElement} el
     * @param {boolean} visible
     */
    const setVisible = (el, visible) => {
        if (!el) return;
        if (visible) {
            el.removeAttribute('hidden');
        } else {
            el.setAttribute('hidden', '');
        }
    };

    // ==================================================================
    // ИНИЦИАЛИЗАЦИЯ
    // ==================================================================

    /**
     * Точка входа модуля. Вызывается один раз после загрузки DOM.
     * Находит корневой элемент конфигуратора, читает начальные данные,
     * навешивает все обработчики событий.
     */
    const init = () => {
        const configuratorEl = document.getElementById('product-configurator');
        if (!configuratorEl) return; // страница без конфигуратора — выходим

        // Читаем начальные данные из data-атрибутов HTML-элемента.
        // Это безопасно: данные пришли с сервера (PHP), а не от пользователя.
        state.productId  = parseInt(configuratorEl.dataset.productId,  10) || 0;
        state.basePrice  = parseFloat(configuratorEl.dataset.basePrice) || 0;
        state.currentPrice = state.basePrice;
        state.selectedOptions.clear();

        configuratorEl.querySelectorAll('input[data-option-id]:checked').forEach((input) => {
            const id = parseInt(input.dataset.optionId, 10);
            if (id) {
                state.selectedOptions.add(id);
            }
        });

        recalculatePrice();
        renderPrice();

        // Навешиваем слушатели
        bindConfiguratorEvents(configuratorEl);
        bindGalleryEvents();
        bindModalEvents();
    };

    // ==================================================================
    // ЛОГИКА КОНФИГУРАТОРА (ПЕРЕСЧЁТ ЦЕНЫ)
    // ==================================================================

    /**
     * Навешивает обработчики на все input'ы конфигуратора.
     * Используем делегирование событий: один слушатель на родителе
     * вместо множества слушателей на каждом input'е.
     * Это эффективнее по памяти и работает с динамически добавленными элементами.
     *
     * @param {HTMLElement} root  Корневой элемент конфигуратора
     */
    const bindConfiguratorEvents = (root) => {
        root.addEventListener('change', (event) => {
            const input = event.target;

            // Обрабатываем только radio и checkbox с data-option-id
            if (
                (input.type === 'radio' || input.type === 'checkbox') &&
                input.dataset.optionId
            ) {
                handleOptionChange(input);
            }
        });
    };

    /**
     * Обрабатывает изменение опции конфигуратора:
     * обновляет Set выбранных опций и пересчитывает цену.
     *
     * @param {HTMLInputElement} input  Изменившийся input
     */
    const handleOptionChange = (input) => {
        const optionId       = parseInt(input.dataset.optionId, 10);
        const priceModifier  = parseFloat(input.dataset.priceModifier) || 0;

        if (input.type === 'radio') {
            removeOptionsByName(input.name);
            state.selectedOptions.add(optionId);
        } else if (input.type === 'checkbox') {
            // Для checkbox: просто переключаем наличие в Set
            if (input.checked) {
                state.selectedOptions.add(optionId);
            } else {
                state.selectedOptions.delete(optionId);
            }
        }

        recalculatePrice();
        renderPrice();
    };

    /**
     * Удаляет из Set все опции, принадлежащие radio-группе с данным name.
     * Нужно при переключении radio: старое значение группы уходит.
     *
     * @param {string} groupName  Значение атрибута name у radio-группы
     */
    const removeOptionsByName = (groupName) => {
        // Находим все radio с данным name и удаляем их optionId из Set
        document.querySelectorAll(`input[type="radio"][name="${groupName}"]`)
            .forEach((radio) => {
                const id = parseInt(radio.dataset.optionId, 10);
                if (id) state.selectedOptions.delete(id);
            });
    };

    /**
     * Пересчитывает итоговую цену:
     * basePrice + сумма price_modifier всех активных опций.
     *
     * Важно: price_modifier берём из DOM (data-атрибуты), а не из
     * отдельного хранилища — это допустимо для отображения клиенту,
     * но сервер ВСЕГДА пересчитывает цену самостоятельно по БД.
     */
    const recalculatePrice = () => {
        let total = state.basePrice;

        // Проходим по всем выбранным опциям и суммируем модификаторы
        state.selectedOptions.forEach((optionId) => {
            const input = document.querySelector(
                `[data-option-id="${optionId}"]`
            );
            if (input) {
                total += parseFloat(input.dataset.priceModifier) || 0;
            }
        });

        state.currentPrice = total;
    };

    /**
     * Обновляет отображение цены во всех местах страницы.
     * Запускает CSS-анимацию "пульсация" для привлечения внимания.
     */
    const renderPrice = () => {
        const formatted = formatPrice(state.currentPrice);

        // Обновляем оба элемента с ценой: в конфигураторе и в модальном окне
        ['price-display', 'modal-price-display'].forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;

            el.textContent = formatted;

            // Микро-анимация: добавляем класс, затем убираем
            el.classList.add('is-updating');
            // requestAnimationFrame гарантирует, что браузер применит
            // класс до его удаления (иначе анимация не сработает)
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    el.classList.remove('is-updating');
                });
            });
        });
    };

    // ==================================================================
    // ГАЛЕРЕЯ ИЗОБРАЖЕНИЙ
    // ==================================================================

    /**
     * Обработчик клика по миниатюрам галереи.
     * Меняет основное изображение с плавным переходом.
     */
    const bindGalleryEvents = () => {
        const thumbnails = document.querySelectorAll('.gallery__thumb');
        const mainImage  = document.getElementById('product-main-image');

        if (!mainImage || !thumbnails.length) return;

        thumbnails.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const newSrc = thumb.dataset.src;
                if (!newSrc || mainImage.src.endsWith(newSrc)) return;

                // Плавная смена: сначала затухание
                mainImage.classList.add('is-loading');

                // Создаём временный Image для предзагрузки
                const preloader = new Image();
                preloader.onload = () => {
                    mainImage.src = newSrc;
                    mainImage.classList.remove('is-loading');
                };
                preloader.onerror = () => {
                    mainImage.classList.remove('is-loading');
                };
                preloader.src = newSrc;

                // Обновляем активный класс на миниатюрах
                thumbnails.forEach((t) => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
    };

    // ==================================================================
    // МОДАЛЬНОЕ ОКНО
    // ==================================================================

    /**
     * Навешивает обработчики открытия/закрытия модального окна заказа.
     */
    const bindModalEvents = () => {
        const modal      = document.getElementById('order-modal');
        const btnOrder   = document.getElementById('btn-order');
        const btnClose   = document.getElementById('modal-close');
        const btnCancel  = document.getElementById('btn-cancel-order');
        const btnSubmit  = document.getElementById('btn-submit-order');

        if (!modal) return;

        // Открытие
        btnOrder?.addEventListener('click', () => openModal(modal));

        // Закрытие — три способа: кнопка X, кнопка Отмена, клик по оверлею
        btnClose?.addEventListener('click',  () => closeModal(modal));
        btnCancel?.addEventListener('click', () => closeModal(modal));

        modal.addEventListener('click', (e) => {
            // Закрываем только при клике на оверлей, не на само модальное окно
            if (e.target === modal) closeModal(modal);
        });

        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
                closeModal(modal);
            }
        });

        // Отправка заказа
        btnSubmit?.addEventListener('click', () => handleSubmitOrder());
    };

    /**
     * Открывает модальное окно.
     * Обновляет цену в модалке и перемещает фокус внутрь (accessibility).
     *
     * @param {HTMLElement} modal
     */
    const openModal = (modal) => {
        renderPrice(); // синхронизируем цену на случай изменений
        setVisible(modal, true);
        document.body.style.overflow = 'hidden'; // блокируем скролл фона

        // Перемещаем фокус на первый интерактивный элемент внутри модалки
        const firstInput = modal.querySelector('input, button');
        firstInput?.focus();
    };

    /**
     * Закрывает модальное окно и сбрасывает состояние формы.
     *
     * @param {HTMLElement} modal
     */
    const closeModal = (modal) => {
        setVisible(modal, false);
        document.body.style.overflow = '';
        clearFormErrors();
        setVisible(document.getElementById('submit-status'), false);
        setVisible(document.getElementById('submit-error'),  false);

        // Возвращаем фокус на кнопку открытия (accessibility)
        document.getElementById('btn-order')?.focus();
    };

    // ==================================================================
    // ВАЛИДАЦИЯ ФОРМЫ (КЛИЕНТСКАЯ)
    // ==================================================================

    /**
     * Валидирует поля формы заказа.
     * Возвращает объект с данными формы или null при ошибке.
     *
     * Клиентская валидация — это удобство для пользователя (быстрая обратная связь).
     * Сервер ВСЕГДА валидирует данные независимо от клиента.
     *
     * @returns {{ name, phone, city, address, comment }|null}
     */
    const validateForm = () => {
        clearFormErrors();
        let isValid = true;

        const getValue = (id) => sanitize(
            document.getElementById(id)?.value ?? ''
        );

        const name    = getValue('input-name');
        const phone   = getValue('input-phone');
        const city    = getValue('input-city');
        const address = getValue('input-address');
        const comment = getValue('input-comment');
        const agreed  = document.getElementById('input-agree')?.checked;

        // Правила валидации: [значение, ID поля ошибки, сообщение]
        const rules = [
            [
                name.length >= 2 && name.length <= 150,
                'error-name',
                'Введите имя (от 2 до 150 символов).'
            ],
            [
                /^(\+7|8|\+77)\d{9,10}$/.test(phone.replace(/[\s\-()]/g, '')),
                'error-phone',
                'Введите корректный номер телефона (+7XXXXXXXXXX).'
            ],
            [
                city.length >= 2,
                'error-city',
                'Введите название города.'
            ],
            [
                address.length >= 5,
                'error-address',
                'Введите адрес доставки (минимум 5 символов).'
            ],
            [
                agreed === true,
                'error-agree',
                'Необходимо принять условия оферты.'
            ],
        ];

        rules.forEach(([condition, errorId, message]) => {
            if (!condition) {
                showFieldError(errorId, message);
                isValid = false;
            }
        });

        if (!isValid) return null;

        return { name, phone, city, address, comment };
    };

    /**
     * Показывает сообщение об ошибке под конкретным полем.
     *
     * @param {string} errorElId  ID элемента .form-error
     * @param {string} message    Текст ошибки
     */
    const showFieldError = (errorElId, message) => {
        const errorEl = document.getElementById(errorElId);
        if (!errorEl) return;

        errorEl.textContent = message;

        // Подсвечиваем связанный input красной рамкой
        const inputId = errorElId.replace('error-', 'input-');
        document.getElementById(inputId)?.classList.add('is-invalid');
    };

    /**
     * Сбрасывает все ошибки валидации в форме.
     */
    const clearFormErrors = () => {
        document.querySelectorAll('.form-error').forEach((el) => {
            el.textContent = '';
        });
        document.querySelectorAll('.form-control.is-invalid').forEach((el) => {
            el.classList.remove('is-invalid');
        });
    };

    // ==================================================================
    // ОТПРАВКА ЗАКАЗА (AJAX через fetch API)
    // ==================================================================

    /**
     * Обрабатывает нажатие кнопки «Подтвердить заказ»:
     * 1. Валидирует форму
     * 2. Собирает данные конфигуратора
     * 3. Получает CSRF-токен с сервера
     * 4. Отправляет POST-запрос в формате JSON
     * 5. Обрабатывает ответ сервера
     */
    const handleSubmitOrder = async () => {
        // Защита от двойного клика / повторной отправки
        if (state.isSubmitting) return;

        // Валидируем форму
        const delivery = validateForm();
        if (!delivery) return; // форма содержит ошибки — останавливаемся

        // Блокируем повторную отправку
        state.isSubmitting = true;
        const btnSubmit = document.getElementById('btn-submit-order');
        if (btnSubmit) btnSubmit.disabled = true;

        // Показываем индикатор загрузки
        setVisible(document.getElementById('submit-status'), true);
        setVisible(document.getElementById('submit-error'),  false);

        try {
            // --- Шаг 1: Получаем CSRF-токен от сервера ---
            // Сервер генерирует токен, сохраняет в сессию, возвращает нам.
            // Мы включаем его в тело запроса — сервер сверяет с сессией.
            const csrfToken = await fetchCsrfToken();

            // --- Шаг 2: Собираем данные конфигуратора ---
            const selectedOptionIds = Array.from(state.selectedOptions);

            const payload = {
                csrf_token: csrfToken,
                items: [
                    {
                        product_id:       state.productId,
                        quantity:         1,
                        selected_options: selectedOptionIds,
                    }
                ],
                delivery: delivery,
            };

            // --- Шаг 3: Отправляем POST-запрос ---
            const response = await fetch('/api/order/create', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // X-Requested-With — стандартный заголовок AJAX-запросов,
                    // позволяет серверу отличить их от обычных запросов
                    'X-Requested-With': 'XMLHttpRequest',
                },
                // credentials: 'same-origin' — отправляем сессионные cookie
                // (нужно для CSRF-валидации на сервере)
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            // --- Шаг 4: Обрабатываем ответ ---
            const data = await response.json();

            if (response.ok && data.success) {
                // Успех: перенаправляем на страницу подтверждения
                handleOrderSuccess(data);
            } else {
                // Сервер вернул ошибку (422, 400 и т.д.)
                handleOrderError(data.error || 'Произошла ошибка. Попробуйте ещё раз.');
            }

        } catch (networkError) {
            // Сетевая ошибка (нет интернета, сервер недоступен)
            console.error('[Configurator] Network error:', networkError);
            handleOrderError(
                'Не удалось отправить заказ. Проверьте интернет-соединение и попробуйте снова.'
            );
        } finally {
            // Всегда снимаем блокировку после завершения
            state.isSubmitting = false;
            if (btnSubmit) btnSubmit.disabled = false;
            setVisible(document.getElementById('submit-status'), false);
        }
    };

    /**
     * Запрашивает у сервера свежий CSRF-токен.
     * Сервер генерирует токен, сохраняет в $_SESSION['csrf_token']
     * и возвращает нам в JSON.
     *
     * @returns {Promise<string>}  CSRF-токен
     * @throws {Error}             Если запрос не удался
     */
    const fetchCsrfToken = async () => {
        const response = await fetch('/api/csrf-token', {
            method:      'GET',
            credentials: 'same-origin', // отправляем cookie сессии
        });

        if (!response.ok) {
            throw new Error('Не удалось получить токен безопасности.');
        }

        const data = await response.json();

        if (!data.token) {
            throw new Error('Сервер вернул пустой токен безопасности.');
        }

        return data.token;
    };

    /**
     * Обрабатывает успешный ответ сервера.
     * Перенаправляет на страницу подтверждения заказа.
     *
     * @param {{ order_id: number, message: string }} data
     */
    const handleOrderSuccess = (data) => {
        // Перенаправление на страницу успеха с ID заказа
        window.location.href = `/order/success/${data.order_id}`;
    };

    /**
     * Отображает сообщение об ошибке под кнопкой отправки.
     *
     * @param {string} message  Текст ошибки от сервера
     */
    const handleOrderError = (message) => {
        const errorEl = document.getElementById('submit-error');
        if (!errorEl) return;

        errorEl.textContent = message;
        setVisible(errorEl, true);

        // Прокручиваем к сообщению об ошибке
        errorEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    // ==================================================================
    // ПУБЛИЧНЫЙ ИНТЕРФЕЙС МОДУЛЯ
    // ==================================================================
    // Возвращаем только то, что должно быть доступно снаружи.
    // Все внутренние функции остаются приватными.
    return { init };

})(); // конец IIFE


// ==================================================================
// ТОЧКА ЗАПУСКА
// Так как скрипт загружается с атрибутом defer type="module",
// DOM уже построен к моменту выполнения — DOMContentLoaded не нужен.
// ==================================================================
Configurator.init();
