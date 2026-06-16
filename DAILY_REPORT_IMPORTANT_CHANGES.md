# Daily Report – Improvement Plan (MD)

**Page:** `http://127.0.0.1:8000/daily-reports`

This document lists what to change. **Section 1–2 are required (your main request).** Section 3+ are optional improvements you can do later.

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

## 6) Implementation order (recommended)

### Phase 1 – Your priority (do first)
1. Buffalo dropdown → `B0001-Radha` in all sections.
2. Feed dropdown → show stock in label + `data-stock` attribute.
3. Fix migration + `feed_id` in controller/views.
4. Server validation + deduct `feeds.volume` on save.
5. JS hint: remaining stock while typing qty.

### Phase 2 – Stability
6. Fix Employee import, duplicate route, show.blade.php bug.
7. Edit/delete report stock restore logic.

### Phase 3 – Optional polish
8. Optional sections + collapse defaults.
9. Index page improvements.
10. Partials, translations, transaction history.

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

### 9.3 MEDIUM – run farm better over time

| Feature | Benefit |
|---------|---------|
| **Per-buffalo cost vs milk** | Feed + medicine + allocated labour → profit per animal |
| **Sold / dead animal history** | Sale price, buyer, reason; don’t delete — mark `sold` with date |
| **Calf → new buffalo** | On birth, button “Create buffalo from calf” (copy tag, set mother link) |
| **Mother–calf link** | `mother_buffalo_id` for lineage |
| **Medicine stock** | Same pattern as feed (volume + daily report deduct) |
| **Low stock alerts** | Dashboard: feeds where `volume < minimum` (add `min_stock` on feeds) |
| **Tasks linked to animals** | “B0001 – hoof trim” due today (you have Tasks module) |
| **Monthly committee pack** | Auto PDF: milk total, expenses, daily report summary |
| **Roles** | Manager (full), Milker (milk+feed only), Committee (read-only reports) |
| **Audit log** | Who changed milk/feed/stock and when |

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

Add cards/widgets not present today:

| Widget | Source |
|--------|--------|
| આજનું દૂધ | `milk_entries` today |
| આજનું વેચાણ | `milk_sales` today |
| બાકી દૂધ | produced − sold |
| Low feed stock | `feeds` where volume low |
| Delivery this week | `buffaloes.expected_delivery_date` |
| Pending salary | already exists ✓ |
| Last daily report | link if today missing |
| Active / lactating / pregnant count | buffalo counts ✓ (partial) |

---

### 9.6 Data & safety (real farm)

- **Backup:** weekly DB backup (manual or scheduled `mysqldump`)
- **No hard delete:** buffalo `sold`/`dead` keep history; soft delete on reports
- **Unique tag number** ✓ already
- **One report per date** (optional rule) to avoid duplicate daily reports
- **Gujarati labels everywhere** — sidebar still has English “New Feeds”, “Daily Reports”

---

### 9.7 Suggested priority for whole farm (after Phase 1 daily report)

| Order | Task |
|-------|------|
| 1 | DB migrations: buffalo breeding fields + daily_reports extra columns |
| 2 | Fix buffalo create/update to save all breeding fields |
| 3 | Feed stock: purchase in + consume in daily report (your MD Phase 1) |
| 4 | Single milk entry path (daily report OR milk page) |
| 5 | Dashboard: milk balance + delivery reminders + low feed |
| 6 | Link daily report expenses to main `expenses` table (or merge) |
| 7 | PDF print daily report for committee |
| 8 | Roles + backup |

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

*Last updated: પશુ dropdown + feed stock validation (Phase 1) + farm-wide improvement list (Section 9).*
