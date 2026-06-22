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

## Phase 2 — Core jewelry operations ✅

- Purchase flow records gold/silver weight (grams), rate per gram, item type
- Stock ledger + metal balances update automatically on purchase
- Direct sales bill deducts stock and calculates cost / profit
- Sales bill (challan) flow records weight; invoice-from-sales-bill deducts stock and records profit
- Stock Ledger UI with filters (metal type, transaction type)
- List views show weight, sold amount, and profit where applicable
- Dashboard shows purchases, sales bills, profit, gold/silver stock

---

## Phase 3 — Investor module ✅

- Investor deposits (₹), withdrawals, gold buy/sell at rate
- `tbl_investor_transactions` for all investor capital movements
- Profit calculation: gross sales profit − shared expenses × profit share %
- Admin: manage investors, set profit share %, record transactions
- Investor portal: period summary, holdings, transaction list
- PDF reports: daily, monthly, quarterly (financial year supported)

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
