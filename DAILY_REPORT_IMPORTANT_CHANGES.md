# Farm Management – Master Roadmap (MD)

**App:** Tabela (તબેલો)  
**Daily Report:** `http://127.0.0.1:8000/daily-reports`

This is the **single roadmap** to follow for all improvements. Work in order from **Section 6 (Master implementation path)**. Sections 1–2 are daily-report UI priorities; Sections 9–10 cover the full farm system.

### Implementation status (updated 2026-06-17)

| Phase | Status |
|-------|--------|
| Phase 1 – Daily Report UI + feed stock | **Done** (create/update/destroy stock, display_label, JS validation) |
| Phase 2 – DB & buffalo integrity | **Done** (migrations, Buffalo update, quick fixes) |
| Phase 3 – Feed stock complete | **Mostly done** (stock-in, transactions, kharch link; min_stock column added) |
| Phase 4 – Milk + money sync | **Partial** (MilkStockService, DR expenses→Kharch, DR income→Income module) |
| Phase 5 – Dashboard widgets | **Done** (today sales, બાકી દૂધ, low feed, delivery, missing report) |
| Phase 6 – PDF/print | **Partial** (print button + CSS; PDF export not yet) |
| Phase 7–8 – Advanced/polish | **Planned** (roles, cost per buffalo, blade partials) |

---
| Section | Topic |
|---------|--------|
| 1–2 | Must do: પશુ dropdown + feed stock validation |
| 3–5 | Quick fixes & optional daily-report polish |
| **6** | **Master implementation path (follow this order)** |
| 7–8 | UX examples & Phase 1 file checklist |
| 9 | Farm-wide features (critical → medium → nice) |
| **10** | **Dashboard target + PDF/print for committee** |

---

## 1) MUST DO – પશુ dropdown: નંબર + નામ એકસાથે (e.g. `B0001-Radha`)

### Current problem
- In `resources/views/Daily_Report/create.blade.php`, buffalo dropdown shows **only tag number** (`B0001`).
- Name appears in a **separate readonly column** only after you select the animal (JavaScript fills `buffalo-name`).
- User wants **both visible inside the dropdown** before selecting, like: **`B0001-Radha`**.

### Where to change (all buffalo `<select>` in daily report form)
| Section | Field name |
|---------|------------|
| Milk | `buffalo_id[]` |
| Feed | `feed_buffalo_id[]` |
| Health | `health_buffalo_id[]` |
| Vaccination | `vaccination_buffalo_id[]` |
| Pregnancy | `pregnancy_buffalo_id[]` |

### What to implement

**Option A – Simple (recommended first)**  
Change every buffalo option label to:

```blade
<option value="{{ $buffalo->id }}">
    {{ $buffalo->tag_number }}{{ $buffalo->name ? '-' . $buffalo->name : '' }}
</option>
```

Example display: `B0001-Radha`, `B0002` (if no name).

**Option B – Remove extra name column (milk & pregnancy)**  
- Remove the separate **પશુ નામ** column from milk and pregnancy tables.
- Dropdown already shows full label → less columns, cleaner mobile view.

**Option C – Select2 / searchable dropdown (later)**  
- If you have many animals, use Select2 so user can type `Radha` or `B0001` and find quickly.
- Label format stays: `B0001-Radha`.

### Controller – no DB change needed
- Still save `buffalo_id` only.
- Display format is UI-only.

### Show / print report
- In `Daily_Report/show.blade.php`, show full label everywhere:
  - `{{ $milk->buffalo->tag_number }}{{ $milk->buffalo->name ? '-' . $milk->buffalo->name : '' }}`
- Same for health, feed, vaccination, pregnancy rows.

---

## 2) MUST DO – Feed dropdown: stock visible + proper validation

### Current problem
- Feed master (`feeds` table) has `volume` = current stock (shown on `/feeds` page).
- Daily report feed dropdown shows **only feed name** – user cannot see how much stock is left.
- No validation: user can enter more qty than stock → stock can go negative (not implemented yet).

### Goal
When user selects feed in daily report:
1. Dropdown text shows: **feed name + available stock + unit**  
   Example: `ઘાસ - 250.00 Kg (સ્ટોક)`
2. On qty input, validate: **morning_qty + evening_qty (per feed type) ≤ available stock**
3. On save, **minus from `feeds.volume`** (current stock). If not enough stock → **block save** with clear error.

### Feed dropdown label format

```blade
@foreach($feeds as $feed)
<option value="{{ $feed->id }}"
        data-stock="{{ $feed->volume }}"
        data-unit="{{ $feed->unit ?? 'Kg' }}">
    {{ $feed->name }} — {{ number_format($feed->volume, 2) }} {{ $feed->unit ?? 'Kg' }} (સ્ટોક)
</option>
@endforeach
```

- Store **`feed_id`** in form (not name string) – fixes id vs name mismatch in controller.
- Use same format in **create** and **edit** rows.

### Show remaining stock when feed is selected (UX)

Add small text under qty field (JavaScript):

```text
ઉપલબ્ધ સ્ટોક: 250.00 Kg
```

On qty change:

```text
બાકી સ્ટોક (આ રો પછી): 230.00 Kg
```

If qty > stock → red message: `સ્ટોક કરતાં વધુ જથ્થો દાખલ કરી શકાતો નથી`

### Validation rules (server – `DailyReportController`)

**Before saving report:**

1. Group all feed rows by `feed_id` (sum morning + evening qty per feed across all buffaloes).
2. For each feed:
   - `required_qty` = total qty user entered for that feed today
   - `available` = `Feed::find($id)->volume`
   - If `required_qty > available` → return error:
     - `"{{ $feed->name }} માટે પૂરતો સ્ટોક નથી. ઉપલબ્ધ: X, જરૂરી: Y"`
3. Wrap in `DB::transaction()`:
   - Save daily report + `daily_report_feed` rows
   - `Feed::where('id', $id)->decrement('volume', $required_qty)`

**On edit report:**

1. Add back old consumed qty to `feeds.volume` (from old `daily_report_feed` rows).
2. Validate new totals again.
3. Save + deduct new qty.

**On delete report (recommended):**

- Restore consumed qty back to `feeds.volume`.

### DB alignment for feed section (required for above to work)

Migration `daily_report_feed` needs:

| Column | Type | Notes |
|--------|------|--------|
| `feed_id` | FK → `feeds` | preferred over `feed_name` only |
| `buffalo_id` | FK → `buffaloes`, nullable | per-animal feed |
| `feed_time` | enum: `morning`, `evening` | controller already uses this |
| `quantity` | decimal | |
| `unit` | string | copy from feed master |

Keep `feed_name` optional (denormalized for print) or drop after `feed_id` works.

### Client-side validation (optional but helpful)

On form submit, loop feed rows:

- Read `data-stock` from selected option
- Sum qty for same `feed_id` in all rows
- If sum > stock → `preventDefault()` + alert

Server validation is **mandatory**; JS is extra UX only.

### Feed stock is farm-level (not per buffalo)

- `feeds.volume` = total store stock for that feed type.
- Daily report records **which buffalo got how much**, but stock deducts from **one central number** per feed.
- This matches: “if we don’t have enough feed we can’t give” + “minus from current stock”.

---

## 3) MUST DO – Quick fixes before feed stock works

These block daily report from saving correctly today:

| # | Issue | Fix |
|---|--------|-----|
| 1 | Duplicate route in `routes/web.php` | Remove duplicate `Route::resource('daily-reports', ...)` |
| 2 | `Employee` import wrong in `DailyReportController` | `use App\Models\Employee;` |
| 3 | `daily_report_feed` migration missing columns | Add `buffalo_id`, `feed_id`, `feed_time` |
| 4 | Create form sends `feed->id`, edit sends `feed->name` | Always use `feed_id` |
| 5 | `show.blade.php` feed table uses `$preg` instead of `$feed` | Use `$feed->buffalo->tag_number` |

---

## 4) OPTIONAL – Form sections (make optional / collapsible)

You said other things can be optional. Suggested UX:

| Section | Default on create | Notes |
|---------|-------------------|--------|
| Basic info (date, shift, reporter) | **Required** | |
| Staff attendance | Optional | Empty row OK; don’t `required` on first row |
| Milk production | Optional | Only save rows with qty > 0 |
| Feed | Optional | Only save if feed + qty filled |
| Health | Collapsed, optional | Already hidden – keep |
| Vaccination | Collapsed, optional | Already hidden – keep |
| Pregnancy | Collapsed, optional | Already hidden – keep |
| Cleaning | Optional | Checkboxes only |
| Expense / Income | Optional | Skip empty title rows |
| Notes | Optional | |

**Controller change:**  
Remove `required` from staff/milk in blade; in `store()` only insert rows where data exists (you already do this for health/expense – extend same pattern).

**Duplicate report same date:**  
Optional rule: one report per `report_date` → validate `unique:report_date` or warn user.

---

## 5) OPTIONAL – Other improvements (do later)

### 5.1 Milk section
- Auto-load lactating buffaloes only in milk dropdown.
- Pre-fill today’s milk from `milk_entries` if report date = today.
- Auto-calculate `total_milk` on report header from sum of rows.

### 5.2 Index page (`daily-reports` list)
- Show real stats (not hardcoded `0` for animals/staff).
- Show `report_number` and `reporter` from DB (not hardcoded `Admin`).
- Add delete button + pagination.
- Filter by date range.

### 5.3 Buffalo master sync
- Pregnancy form saves dates on buffalo (`heat_date`, `ai_date`, etc.) but `store()` only saves `DailyReportPregnancy` – buffalo fields not updated.
- Optional: on pregnancy row save, `Buffalo::update(...)` those dates.

### 5.4 Meetings module
- Fix `employee` vs `Employee` import (same as daily report).
- `update()` does not sync participants.

### 5.5 Translations
- Sidebar still says `New Feeds` / `Daily Reports` in English – move to `common.php` (gu/hi/en).

### 5.6 Advanced stock (future)
- `feed_stock_transactions` table for purchase/consume history.
- “Add stock” screen (purchase entry) separate from daily report.
- Low-stock alert when `volume < minimum_threshold`.

### 5.7 Code quality
- Split `create.blade.php` (~2400 lines) into partials: `_staff.blade.php`, `_milk.blade.php`, `_feed.blade.php`.
- Use Form Request classes for validation instead of inline in controller.
- Use route model binding: `edit(DailyReport $dailyReport)` instead of `$id`.

---

## 6) Master implementation path (follow in this order)

Use this as the **official build sequence** after the baseline code pushed to Git.

### Phase 1 – Daily Report UI + feed stock (current priority)
- [x] Buffalo dropdown → `display_label` in all sections (milk, feed, health, vaccination, pregnancy)
- [x] Feed dropdown → show stock in label + `data-stock` attribute
- [x] Fix migration + `feed_id` in controller/views
- [x] Server validation: cannot give more than available stock
- [x] On save: minus from `feeds.volume` via `FeedStockService`
- [x] JS validation banner on feed stock
- [x] Edit/delete report: restore old stock + re-validate/deduct new qty

### Phase 2 – Database & buffalo data integrity
- [x] DB migrations: `buffaloes` breeding/birth columns
- [x] DB migrations: `daily_reports` extra columns
- [x] DB migrations: `daily_report_feed` → `feed_id`, `buffalo_id`, `feed_time`
- [x] Fix `BuffaloController@update()` to save all breeding/birth fields
- [x] Fix `Employee` import, duplicate route, `show.blade.php` feed buffalo bug

### Phase 3 – Feed stock complete (purchase + consume)
- [x] Feed **purchase / stock in** screen
- [x] Link feed purchase to **Kharch** (`category = feed`) when amount entered
- [x] `feed_stock_transactions` table (audit trail)
- [x] `min_stock` on feeds column (dashboard low-stock widget)

### Phase 4 – Single milk path + money sync
- [ ] Decide **one** milk entry path (banner on `/milk` → use Daily Report)
- [x] Link daily report **expenses** → main `expenses` table
- [x] Link daily report **income** → `incomes` module
- [x] Dashboard profit uses Kharch + MilkSale + Income

### Phase 5 – Dashboard (farm owner view)
- [x] **Today milk**
- [x] **Today sales**
- [x] **Remaining milk** — produced − sold
- [x] **Low feed stock**
- [x] **Delivery this week**
- [x] **Today's daily report missing?**
- [x] **Pending salary**
- [x] **Heat reminders** count + `heatAnimals` on daily report
- [x] Active / lactating / pregnant counts

### Phase 6 – PDF / print for committee
- [x] **Print** button on daily report show page
- [ ] **Export PDF** (DomPDF not installed yet)
- [ ] **Monthly committee pack** PDF
- [ ] Committee **read-only** role routes

### Phase 7 – Grow the farm system (medium priority)
- [ ] **Cost per buffalo** — feed + medicine + allocated labour vs milk income
- [ ] **Calf → new buffalo** — button on birth record; copy calf tag, pre-fill fields
- [ ] **Mother–calf link** — `mother_buffalo_id` on `buffaloes`
- [ ] **Medicine stock** — same as feed (`medicines` table, volume, purchase in, daily report deduct)
- [ ] **Tasks tied to animals** — e.g. “B0001 – hoof trim” with `buffalo_id` on tasks
- [ ] **User roles** — Manager (full), Milker (milk+feed only), Committee (read-only)
- [ ] **Sold/dead history** — `sold_date`, `sale_price`, `buyer`; never hard-delete buffalo
- [ ] Vaccination schedule with next due date
- [ ] Audit log for stock/milk changes

### Phase 8 – Polish (when core is stable)
- [ ] Optional/collapsible daily report sections
- [ ] Daily report index: real stats, pagination, delete, date filter
- [ ] Split large blade files into partials
- [ ] Translations (gu/hi/en) for sidebar labels
- [ ] Backup export (Excel), weekly DB backup notes

---

## 7) Example – what user sees in daily report (target UX)

**પશુ dropdown (milk):**
```
[ પશુ પસંદ કરો ▼ ]
  B0001-Radha
  B0002-Gauri
  B0003
```

**ચારો dropdown (morning):**
```
[ ચારો પસંદ કરો ▼ ]
  ઘાસ — 250.00 Kg (સ્ટોક)
  લીલો ચારો — 80.50 Kg (સ્ટોક)
  ખલ — 500.00 Kg (સ્ટોક)
```

**After selecting ઘાસ and typing 15 Kg:**
```
જથ્થો: [ 15 ]
ઉપલબ્ધ સ્ટોક: 250.00 Kg | બાકી: 235.00 Kg
```

**On save with 300 Kg total for ઘાસ:**
```
❌ ઘાસ માટે પૂરતો સ્ટોક નથી. ઉપલબ્ધ: 250.00 Kg, જરૂરી: 300.00 Kg
```

---

## 8) Files to touch (Phase 1 checklist)

- [ ] `resources/views/Daily_Report/create.blade.php` – buffalo + feed dropdowns, JS stock hint
- [ ] `resources/views/Daily_Report/show.blade.php` – display `tag-name`, fix feed buffalo column
- [ ] `app/Http/Controllers/DailyReportController.php` – validation, stock deduct/restore, `Employee` import
- [ ] `database/migrations/..._create_daily_report_feeds_table.php` – add columns OR new alter migration
- [ ] `app/Models/DailyReportFeed.php` – `feed_id`, `buffalo` relationship
- [ ] `app/Models/Feed.php` – optional helper: `hasStock($qty)`, `consume($qty)`
- [ ] `routes/web.php` – remove duplicate route

---

## 9) Farm-wide improvements (beyond Daily Report page)

If you run a real તબેલો / dairy farm, these are other gaps found in the **whole project** — not only daily report.

### 9.1 CRITICAL – data will not save correctly today

| Issue | Where | Why it matters for farm |
|-------|--------|-------------------------|
| **Buffalo pregnancy/birth fields missing in DB** | Form has `heat_date`, `ai_date`, `birth_date`, etc. but `buffaloes` migration has no these columns | Breeding & calf records lost on save |
| **Buffalo `update()` ignores breeding fields** | `BuffaloController@update` only saves basic fields; create form shows pregnancy block but edit won’t save it | After first save, heat/AI/delivery dates can’t be updated |
| **Daily report columns missing in DB** | `report_number`, `reporter`, `clean_cowshed*`, etc. used in code but not in `daily_reports` migration | Report save may fail or fields stay empty |
| **Milk entered in 2 places** | `/milk` module + Daily Report both write `milk_entries` | Double entry, mismatch, staff confusion |
| **Daily report money not in main accounts** | `DailyReportExpense` / `DailyReportIncome` separate from `expenses` / `milk_sales` | Dashboard profit wrong; committee sees different numbers |

**Fix first:** run alter migrations for `buffaloes` + `daily_reports`; fix `BuffaloController@update`; decide **one place** for daily milk entry.

---

### 9.2 HIGH – daily farm operations you will need

#### A) Milk balance (ઉત્પાદન vs વેચાણ)
- Today: you record **milk produced** (`milk_entries`) and **milk sold** (`milk_sales`) separately.
- Missing: **બાકી દૂધ / milk stock** = produced − sold − wastage.
- Farm need: dashboard card “આજનું બાકી દૂધ” so you know what is left before next sale.
- Optional field on daily report: `wastage_liters` or `farm_use_liters`.

#### B) Feed purchase (સ્ટોક ઉમેરવું)
- `feeds.volume` is stock, but there is no **“ખરીદી / stock in”** screen.
- Farm need: “Add 500 Kg ઘાસ purchased today” → increases stock (opposite of daily report consume).
- Link purchase to **Kharch** (`category = feed`) for cost tracking.

#### C) Breeding & delivery reminders (હીટ / AI / પ્રસૂતિ)
- You collect heat, AI, expected delivery on buffalo — but **no alerts**.
- Farm need on dashboard:
  - Animals due for heat (e.g. 18–21 days after last heat)
  - AI follow-up / pregnancy check due
  - **Delivery in next 7 days** (from `expected_delivery_date`)
- Daily report section “હીટ માં પશુ” shows `0` because `heatAnimals` is never passed from controller.

#### D) Vaccination schedule
- Vaccination only inside daily report — no **next due date** per animal.
- Farm need: master list (FMD, HS, Brucella, etc.) + due in 30 days + last given date.

#### E) One daily workflow (staff friendly)
Suggested single flow for milkman each morning/evening:

```
1. Open Daily Report (or Milk page — pick ONE)
2. Enter milk + feed (stock auto-check)
3. Mark staff present
4. Optional: health note
5. Save → auto-updates milk history + feed stock
```

Avoid forcing staff to also open `/milk` separately.

#### F) Print / PDF / WhatsApp share
- Committee members often want **paper or PDF** of daily report.
- Add: Print button (CSS `@media print` exists partially) + **Export PDF** on show page.
- Optional: summary image for WhatsApp.

---

### 9.3 MEDIUM – grow the farm system (Phase 7 detail)

| # | Feature | What to build | Benefit |
|---|---------|---------------|---------|
| 1 | **Cost per buffalo** | Sum feed qty × cost, `medicine_cost` from health, optional labour share ÷ lactating days; compare to milk liters × sale rate | Know which animal is profitable |
| 2 | **Calf → new buffalo** | On buffalo birth section or daily report: button **“નવું પશુ બનાવો”** → pre-fill `tag_number` from `calf_tag_number`, link mother | No double typing; clean records |
| 3 | **Mother–calf link** | Add `mother_buffalo_id` nullable FK on `buffaloes`; show on buffalo show page | Lineage / breeding history |
| 4 | **Medicine stock** | Table `medicines` (name, volume, unit, min_stock); purchase in; deduct from daily report health `medicine_cost` qty if tracked by unit | Same control as feed |
| 5 | **Low feed alert** | `min_stock` on `feeds`; dashboard widget + red badge on feeds index | Never run out unexpectedly |
| 6 | **Tasks tied to animals** | Add `buffalo_id` to `tasks`; title auto: `{tag}-{name} – {task}`; filter “today’s animal tasks” on dashboard | Field work tracking |
| 7 | **User roles** | `role` on users or Spatie permission: `manager`, `milker`, `committee`; middleware on routes | Security + simpler UI per role |
| 8 | **Sold/dead history** | On status `sold`/`dead`: capture `sold_date`, `sale_price`, `buyer_name`, `reason`; hide from active lists, keep in reports | Audit + no data loss |
| 9 | **Audit log** | `activity_log` or simple table: user, action, model, old/new values | Trust for committee |

---

### 9.4 NICE TO HAVE – later

- SMS/WhatsApp reminder for heat/delivery (Twilio / local gateway)
- Photo per buffalo
- Weight tracking graph
- Weather / season notes on daily report
- Multi-shed / group buffaloes by shed
- Backup export (Excel of milk, expenses, animals)
- Offline mobile entry (PWA)

---

### 9.5 Dashboard – what a farm owner should see (target)

**Phase 5** — add cards/widgets not present today:

| Priority | Widget (Gujarati) | Calculation / source | Status |
|----------|-------------------|----------------------|--------|
| High | આજનું દૂધ | `SUM(milk_entries.total_liters)` where `entry_date = today` | Partial (month only today) |
| High | આજનું વેચાણ | `SUM(milk_sales.total_amount)` + liters where `sale_date = today` | Missing |
| High | બાકી દૂધ | today produced − today sold − wastage | **Missing** |
| High | Low feed stock | `feeds` where `volume < min_stock` | **Missing** |
| High | Delivery this week | `expected_delivery_date` between today and +7 days | **Missing** |
| High | આજનો અહેવાલ બાકી? | No `daily_reports` row for today → link to create | **Missing** |
| Medium | Pending salary | `Employee::pendingMonths()` | ✓ Exists |
| Medium | Heat / AI reminders | Buffalo heat cycle + AI dates | **Missing** |
| Low | Active / lactating / pregnant | Buffalo counts by status | ✓ Partial |
| Low | Top milk buffaloes this month | Already on dashboard | ✓ Exists |

---

### 9.6 Data & safety (real farm)

- **Backup:** weekly DB backup (manual or scheduled `mysqldump`)
- **No hard delete:** buffalo `sold`/`dead` keep history; soft delete on reports
- **Unique tag number** ✓ already
- **One report per date** (optional rule) to avoid duplicate daily reports
- **Gujarati labels everywhere** — sidebar still has English “New Feeds”, “Daily Reports”

---

### 9.7 Quick reference – order after daily report Phase 1

> Full step-by-step list is in **Section 6**. Summary:

| Step | Phase | Task |
|------|-------|------|
| 1 | 2 | DB migrations (buffalo + daily_reports + daily_report_feed) |
| 2 | 2 | Fix buffalo save/update for breeding fields |
| 3 | 1–3 | Feed stock: consume with validation + purchase in |
| 4 | 4 | Single milk entry path |
| 5 | 5 | Dashboard: milk balance, low feed, delivery, missing report link |
| 6 | 4 | Link daily report money → Kharch / milk_sales |
| 7 | 6 | PDF + print for committee |
| 8 | 7 | Roles, cost per buffalo, calf link, medicine stock, tasks |

---

### 9.8 Quick answers – “do we need this for our farm?”

| Question | Recommendation |
|----------|----------------|
| Separate Milk page + Daily Report? | **No long term** — use one; other can read-only sync |
| Feed stock per buffalo? | **No** — stock is godown total; per-buffalo is usage only |
| Committee users in app? | **Yes** — read-only report view + PDF |
| Medicine stock like feed? | **Yes** if you buy medicine in bulk |
| Meeting module? | Optional — only if committee meets often |
| Assets module? | Yes for tractor, chaff cutter, milk machine value |

---

## 10) Dashboard & committee PDF/print (detailed spec)

### 10.1 Dashboard layout (after Phase 5)

```
┌─────────────────────────────────────────────────────────────┐
│  આજનું દૂધ    │  આજનું વેચાણ   │  બાકી દૂધ    │  Pending salary │
├─────────────────────────────────────────────────────────────┤
│  Low feed (2)  │  Delivery this week (1)  │  Report missing? │
├─────────────────────────────────────────────────────────────┤
│  Last 7 days milk chart (exists)  │  Top buffaloes (exists) │
└─────────────────────────────────────────────────────────────┘
```

**“આજનો અહેવાલ બાકી?” logic:**
```php
$todayReport = DailyReport::whereDate('report_date', today())->first();
if (!$todayReport) {
    // show alert + link route('daily-reports.create')
}
```

**Remaining milk logic:**
```php
$todayProduced = MilkEntry::whereDate('entry_date', today())->sum('total_liters');
$todaySold     = MilkSale::whereDate('sale_date', today())->sum('liters_sold');
$remaining     = $todayProduced - $todaySold; // minus wastage when field added
```

### 10.2 PDF / print for committee (Phase 6)

| Item | Implementation |
|------|----------------|
| Print button | On `Daily_Report/show.blade.php` — `window.print()` + print CSS hide sidebar |
| PDF export | Route `daily-reports/{id}/pdf` — DomPDF render same show view |
| Committee access | Role `committee`: only `index`, `show`, `pdf` routes |
| Monthly pack | Route `reports/committee-pack?month=&year=` — aggregate milk, kharch, daily reports |
| WhatsApp | Optional: generate PDF, store in `storage`, share download link |

**Print checklist:**
- [ ] Hide app sidebar and topbar when printing
- [ ] Page breaks between major sections
- [ ] Gujarati font embedded in PDF (Hind Vadodara or Noto Sans Gujarati)
- [ ] Header: farm name from `settings`, date, report number, reporter
- [ ] Footer: signature lines for manager + committee

### 10.3 Files for Phase 5–6

- [ ] `app/Http/Controllers/DashboardController.php` — new widgets
- [ ] `resources/views/dashboard/index.blade.php` — new cards
- [ ] `resources/views/Daily_Report/show.blade.php` — print + PDF button
- [ ] `app/Http/Controllers/DailyReportController.php` — `pdf()` method
- [ ] `routes/web.php` — pdf route + role middleware
- [ ] `database/migrations/` — `users.role` or permissions table

---

*Last updated: Master roadmap Sections 6, 9, 10 — medium farm features, dashboard targets, PDF/print for committee.*
