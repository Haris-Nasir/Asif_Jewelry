# Asif Jewelry — Implementation Phases

This project is being converted from a textile management system to a jewelry management system **phase by phase** (not in one go).

## Phase 1 — Foundation ✅ (current)

- User roles: `admin`, `worker`, `investor`
- Login / logout (Laravel Sanctum token auth)
- Role-based API protection and sidebar visibility
- Rebrand: Asif Jewelry
- Jewelry item types seeded (Gold + Silver categories)
- Expense categories seeded (Electricity, Rent, Food, Salary, Refinery, Other)
- New tables: `tbl_investors`, `tbl_metal_balances`, `tbl_stock_ledger`
- Dashboard shows gold/silver stock totals
- Investor portal placeholder

### Default login accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@asifjewelry.com | password |
| Worker | worker@asifjewelry.com | password |
| Investor | investor@asifjewelry.com | password |

### Setup after pull

```bash
cd /Users/macbookair/Documents/TextileManagementSystem
/opt/homebrew/opt/php@8.1/bin/php artisan migrate
/opt/homebrew/opt/php@8.1/bin/php artisan db:seed
NODE_OPTIONS=--openssl-legacy-provider npm run dev
/opt/homebrew/opt/php@8.1/bin/php artisan serve
```

---

## Phase 2 — Core jewelry operations (next)

- Purchase flow: gold/silver weight, rate per gram, stock increment
- Sales bill: deduct stock on sale, sold price, profit per bill
- Item-wise stock tracking
- Bill count on dashboard
- Rename/adapt purchase & sales UI labels fully

---

## Phase 3 — Investor module

- Investor deposits (₹), gold purchase at rate, sale at rate
- Profit calculation after shared expenses
- Selectable profit share %
- PDF reports: daily, monthly, quarterly

---

## Phase 4 — Laboratory module

- Lab jobs (gold + rupees)
- Base price, refinery cost, sold price, profit
- Linked to investors

---

## Phase 5 — Polish

- Fine-grained worker permissions
- Audit logs
- Production hardening
