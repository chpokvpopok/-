# QA-чеклист для защиты диплома

> План реализации: [IMPLEMENTATION-OPTIMUM.md](./IMPLEMENTATION-OPTIMUM.md)  
> Пошаговый гайд: [GUIDE-MENYON.md](./GUIDE-MENYON.md)

Перед защитой пройди все пункты. Сервер: `http://localhost:8080` (запуск — [README.md](../README.md)).

---

## 1. Таблица URL и ожидаемый результат

| URL | Метод | Ожидаемый результат |
| --- | ----- | ------------------- |
| `/` | GET | Лендинг: hero, направления, преимущества, кейсы, форма КП, FAQ; шапка и подвал |
| `/catalog` | GET | 3 карточки категорий из MySQL |
| `/catalog/1` | GET | Товары ETEO-D1 и ETEO-M3, кнопка «Настроить» |
| `/catalog/999` | GET | 404 с header/footer |
| `/product/1` | GET | Конфигуратор: опции, пересчёт цены, модалка заказа |
| `/product/999` | GET | 404 с header/footer |
| `/order/success/{id}` | GET | Страница «Заказ принят» с номером, суммой, контактами; header/footer |
| `/order/success/99999` | GET | 404 |
| `/test-404` | GET | 404 с layout |
| `/api/csrf-token` | GET | JSON `{ "token": "..." }` |
| `/api/product/1` | GET | JSON с товаром и опциями |
| `/api/order/create` | POST | 201 + `order_id` при валидных данных и CSRF |
| `/api/lead/create` | POST | 201 + `id` при валидных данных и CSRF *(пакет O5)* |

---

## 2. Сценарий демо для комиссии (2–3 мин)

1. **Главная** — «Корпоративный лендинг производителя диспетчерской мебели; референс UX — eteo.ru, акцент диплома — онлайн-конфигуратор и backend заказа».
2. **Каталог** → категория «Диспетчерская мебель» (`/catalog/1`).
3. **Товар ETEO-D1** — изменить 1–2 опции, показать пересчёт цены.
4. **Оформить заказ** — заполнить модалку (имя, телефон +7…, город, адрес), отправить.
5. **Страница успеха** — номер заказа, сумма, контакты.
6. *(Опционально, O5)* **Форма КП** на главной (`#lead-form`) — «второй канал заявок, таблица `leads`».

**Фраза для комиссии:** у типичной витрины нет конфигуратора с расчётом цены и записью заказа в БД через API с CSRF.

---

## 3. Проверка API через curl

### CSRF-токен

```bash
curl -s http://localhost:8080/api/csrf-token
```

Ожидание: `{"token":"..."}`.

### Создание заказа

```bash
TOKEN=$(curl -s http://localhost:8080/api/csrf-token | php -r 'echo json_decode(file_get_contents("php://stdin"))->token;')

curl -s -X POST http://localhost:8080/api/order/create \
  -H "Content-Type: application/json" \
  -d "{
    \"csrf_token\": \"$TOKEN\",
    \"items\": [{\"product_id\": 1, \"quantity\": 1, \"selected_options\": []}],
    \"delivery\": {
      \"name\": \"Тест Тестов\",
      \"phone\": \"+77001234567\",
      \"city\": \"Алматы\",
      \"address\": \"ул. Абая, 10\"
    }
  }"
```

Ожидание: HTTP 201, `"success": true`, `"order_id": N`.  
Проверка в БД:

```bash
mysql -u root furniture_platform -e "SELECT id, total_price, status FROM orders ORDER BY id DESC LIMIT 1"
```

Открыть в браузере: `http://localhost:8080/order/success/N`.

### Без CSRF (должен быть отказ)

```bash
curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost:8080/api/order/create \
  -H "Content-Type: application/json" \
  -d '{"items":[],"delivery":{}}'
```

Ожидание: код **403** или **422**.

### Заявка на КП (лид) — после пакета O5

```bash
TOKEN=$(curl -s http://localhost:8080/api/csrf-token | php -r 'echo json_decode(file_get_contents("php://stdin"))->token;')

curl -s -X POST http://localhost:8080/api/lead/create \
  -H "Content-Type: application/json" \
  -d "{
    \"csrf_token\": \"$TOKEN\",
    \"email\": \"test@example.com\",
    \"name\": \"Иван Тестов\",
    \"phone\": \"+79991234567\",
    \"organization\": \"ООО Тест\",
    \"comment\": \"Нужно КП на диспетчерский пульт\"
  }"
```

Ожидание: HTTP 201, `"success": true`, `"id": N`.  
Проверка:

```bash
mysql -u root furniture_platform -e "SELECT * FROM leads ORDER BY id DESC LIMIT 1"
```

---

## 4. Быстрые проверки UI

- [ ] Burger-меню на узком экране открывается и закрывается
- [ ] Якорь «Запросить КП» с hero скроллит к `#lead-form`
- [ ] FAQ-аккордеон раскрывается по клику
- [ ] На `/product/1` шапка совпадает с главной, конфигуратор работает
- [ ] Нет битых изображений на каталоге и товаре
- [ ] После заказа редирект/переход на `/order/success/{id}` с layout

---

## 5. Что сказать на вопросы

| Тема | Ответ |
| ---- | ----- |
| Архитектура | Front controller: `index.php` → контроллер → view через `render()` |
| Безопасность | PDO prepared statements, CSRF на POST API, `htmlspecialchars` в шаблонах |
| Заказ vs лид | `orders` + `order_items` — оформленный заказ; `leads` — заявка на КП |
| Цена | Пересчитывается в JS для UX, **повторно проверяется на сервере** при создании заказа |

---

## 6. Команды перед выходом на защиту

```bash
# Запуск (macOS / Linux / Git Bash)
./scripts/dev.sh

# Windows PowerShell
.\start-dev.ps1

# Миграции (если база пустая)
./sql/migrate.sh

# Последние заказы и лиды
mysql -u root furniture_platform -e "SELECT id, total_price, status, created_at FROM orders ORDER BY id DESC LIMIT 3"
mysql -u root furniture_platform -e "SELECT id, email, name, phone, created_at FROM leads ORDER BY id DESC LIMIT 3"
```
