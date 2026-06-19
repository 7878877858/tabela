@php
    $isDashboard = request()->routeIs('dashboard');
    $isAnimals = request()->routeIs('buffalo.*');
    $isAnimalList = request()->routeIs('buffalo.index', 'buffalo.show', 'buffalo.edit');
    $isBuffaloAdd = request()->routeIs('buffalo.create');
    $isAnimalTxn = request()->routeIs('animal-transactions.*', 'reports.animal-sales', 'reports.animal-purchases');
    $isAnimalSaleReport = request()->routeIs('reports.animal-sales');
    $isAnimalTxnHistory = request()->routeIs('animal-transactions.*');
    $isFeeds = request()->routeIs('feeds.*');
    $isFeedsList = request()->routeIs('feeds.index', 'feeds.show', 'feeds.edit', 'feeds.history');
    $isFeedsAdd = request()->routeIs('feeds.create');
    $isMilkGroup = request()->routeIs('milk.*', 'sale.*', 'milk-distribution.*', 'milk-customers.*', 'dairy-collections.*');
    $isMilkEntry = request()->routeIs('milk.index');
    $isMilkHistory = request()->routeIs('milk.history');
    $isMilkSales = request()->routeIs('sale.*');
    $isMilkTx = request()->routeIs('milk.transactions');
    $isMilkDistribution = request()->routeIs('milk-distribution.*', 'milk-customers.*');
    $isDairyCollection = request()->routeIs('dairy-collections.*');
    $isFinance = request()->routeIs('expenses.*', 'kharch.*', 'income.*', 'reports.daily-expenses', 'reports.feed-purchases', 'reports.utility-bills', 'reports.insurance', 'reports.loans', 'reports.asset-purchases', 'reports.financial-summary');
    $isExpense = request()->routeIs('expenses.*', 'kharch.*');
    $isExpenseHub = request()->routeIs('expenses.index', 'kharch.index');
    $isDailyExpense = request()->routeIs('expenses.daily', 'reports.daily-expenses');
    $isUtilityBills = request()->routeIs('expenses.utility-bills.*', 'reports.utility-bills');
    $isInsurance = request()->routeIs('expenses.insurance.*', 'reports.insurance');
    $isLoans = request()->routeIs('expenses.loans.*', 'reports.loans');
    $isOtherExpense = request()->routeIs('expenses.other.*');
    $isIncome = request()->routeIs('income.*');
    $isIncomeHub = request()->routeIs('income.index');
    $isManureSales = request()->routeIs('income.manure-sales.*');
    $isOtherIncome = request()->routeIs('income.other.*');
    $isReports = request()->routeIs('reports.monthly', 'reports.yearly', 'reports.animal-investment', 'reports.birth-history', 'reports.milk-distribution', 'reports.dairy-collection', 'reports.milk-reconciliation', 'reports.manure-sales', 'reports.income-summary', 'reports.daily-expenses', 'reports.feed-purchases', 'reports.utility-bills', 'reports.insurance', 'reports.loans', 'reports.animal-purchases', 'reports.asset-purchases', 'reports.financial-summary');
    $isMonthly = request()->routeIs('reports.monthly');
    $isYearly = request()->routeIs('reports.yearly');
    $isAnimalInvestment = request()->routeIs('reports.animal-investment');
    $isBirthHistory = request()->routeIs('reports.birth-history');
    $isStaff = request()->routeIs('employees.*');
    $isAssets = request()->routeIs('assets.*');
    $isAssetsList = request()->routeIs('assets.index', 'assets.show', 'assets.edit');
    $isAssetsCreate = request()->routeIs('assets.create');
    $isAssetReports = request()->routeIs('reports.assets');
    $isTasks = request()->routeIs('tasks.*');
    $isMeetings = request()->routeIs('meetings.*');
    $isDailyReport = request()->routeIs('daily-reports.*');
    $isSettings = request()->routeIs('settings.*');
@endphp

<aside class="erp-sidebar" id="sidebar">
    <div class="erp-sidebar__brand">
        <div class="erp-sidebar__logo" aria-hidden="true">🐃</div>
        <div class="erp-sidebar__brand-text">
            <h1 class="erp-sidebar__brand-title">{{ $farmName }}</h1>
            <p class="erp-sidebar__brand-sub">{{ __('common.management') }}</p>
        </div>
    </div>

    <nav class="erp-sidebar__nav" id="sidebarNav">
        <a href="{{ route('dashboard') }}"
           class="erp-sidebar__link {{ $isDashboard ? 'is-active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>{{ __('common.dashboard') }}</span>
        </a>

        <div class="erp-sidebar__group {{ $isAnimals ? 'is-active' : '' }}" data-group="animals">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-bounding-box"></i>
                <span>{{ __('common.animals') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('buffalo.index') }}"
                   class="erp-sidebar__sublink {{ $isAnimalList ? 'is-active' : '' }}">
                    <span>{{ __('common.all_buffaloes') }}</span>
                </a>
                <a href="{{ route('buffalo.create') }}"
                   class="erp-sidebar__sublink {{ $isBuffaloAdd ? 'is-active' : '' }}">
                    <span>{{ __('common.add_buffalo') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isAnimalTxn ? 'is-active' : '' }}" data-group="animal-txn">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-arrow-left-right"></i>
                <span>🐃 {{ __('farm.animal_transactions') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('reports.animal-sales') }}"
                   class="erp-sidebar__sublink {{ $isAnimalSaleReport ? 'is-active' : '' }}">
                    <span>📊 {{ __('income.animal_sale_report') }}</span>
                </a>
                <a href="{{ route('animal-transactions.index') }}"
                   class="erp-sidebar__sublink {{ $isAnimalTxnHistory ? 'is-active' : '' }}">
                    <span>{{ __('farm.transaction_history') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isFeeds ? 'is-active' : '' }}" data-group="feeds">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-basket2"></i>
                <span>{{ __('common.feeds') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('feeds.index') }}"
                   class="erp-sidebar__sublink {{ $isFeedsList ? 'is-active' : '' }}">
                    <span>{{ __('common.feeds') }}</span>
                </a>
                <a href="{{ route('feeds.create') }}"
                   class="erp-sidebar__sublink {{ $isFeedsAdd ? 'is-active' : '' }}">
                    <span>{{ __('common.add_feed') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isMilkGroup ? 'is-active' : '' }}" data-group="milk">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-cup"></i>
                <span>{{ __('common.milk') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('milk.index') }}"
                   class="erp-sidebar__sublink {{ $isMilkEntry ? 'is-active' : '' }}">
                    <span>{{ __('common.milk_entry') }}</span>
                </a>
                <a href="{{ route('milk.history') }}"
                   class="erp-sidebar__sublink {{ $isMilkHistory ? 'is-active' : '' }}">
                    <span>{{ __('common.milk_history') }}</span>
                </a>
                <a href="{{ route('sale.index') }}"
                   class="erp-sidebar__sublink {{ $isMilkSales ? 'is-active' : '' }}">
                    <span>{{ __('common.milk_sales') }}</span>
                </a>
                <a href="{{ route('milk.transactions') }}"
                   class="erp-sidebar__sublink {{ $isMilkTx ? 'is-active' : '' }}">
                    <span>{{ __('common.milk_transactions') }}</span>
                </a>
                <a href="{{ route('milk-distribution.index') }}"
                   class="erp-sidebar__sublink {{ $isMilkDistribution ? 'is-active' : '' }}">
                    <span>🥛 {{ __('milk_flow.milk_distribution') }}</span>
                </a>
                <a href="{{ route('dairy-collections.index') }}"
                   class="erp-sidebar__sublink {{ $isDairyCollection ? 'is-active' : '' }}">
                    <span>🏭 {{ __('milk_flow.dairy_collection') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isExpense ? 'is-active' : '' }}" data-group="expenses">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-cash-stack"></i>
                <span>📊 {{ __('farm.expenses_hub') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('expenses.index') }}"
                   class="erp-sidebar__sublink {{ $isExpenseHub ? 'is-active' : '' }}">
                    <span>{{ __('farm.expenses_hub') }}</span>
                </a>
                <a href="{{ route('expenses.daily') }}"
                   class="erp-sidebar__sublink {{ $isDailyExpense ? 'is-active' : '' }}">
                    <span>{{ __('farm.daily_expenses') }}</span>
                </a>
                <a href="{{ route('expenses.utility-bills.index') }}"
                   class="erp-sidebar__sublink {{ $isUtilityBills ? 'is-active' : '' }}">
                    <span>{{ __('farm.utility_bills') }}</span>
                </a>
                <a href="{{ route('expenses.insurance.index') }}"
                   class="erp-sidebar__sublink {{ $isInsurance ? 'is-active' : '' }}">
                    <span>{{ __('farm.insurance') }}</span>
                </a>
                <a href="{{ route('expenses.loans.index') }}"
                   class="erp-sidebar__sublink {{ $isLoans ? 'is-active' : '' }}">
                    <span>{{ __('farm.loans') }}</span>
                </a>
                <a href="{{ route('expenses.other.index') }}"
                   class="erp-sidebar__sublink {{ $isOtherExpense ? 'is-active' : '' }}">
                    <span>{{ __('farm.other_expenses') }}</span>
                </a>
                <a href="{{ route('reports.financial-summary') }}"
                   class="erp-sidebar__sublink {{ request()->routeIs('reports.financial-summary') ? 'is-active' : '' }}">
                    <span>📈 {{ __('farm.report_financial_summary') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isIncome ? 'is-active' : '' }}" data-group="income">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-graph-up-arrow"></i>
                <span>{{ __('income.income') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('income.index') }}"
                   class="erp-sidebar__sublink {{ $isIncomeHub ? 'is-active' : '' }}">
                    <span>📈 {{ __('income.income_hub') }}</span>
                </a>
                <a href="{{ route('income.manure-sales.index') }}"
                   class="erp-sidebar__sublink {{ $isManureSales ? 'is-active' : '' }}">
                    <span>💩 {{ __('income.manure_sale') }}</span>
                </a>
                <a href="{{ route('income.other.index') }}"
                   class="erp-sidebar__sublink {{ $isOtherIncome ? 'is-active' : '' }}">
                    <span>📦 {{ __('income.other_income') }}</span>
                </a>
                <a href="{{ route('reports.income-summary') }}"
                   class="erp-sidebar__sublink {{ request()->routeIs('reports.income-summary', 'reports.animal-sales', 'reports.manure-sales') ? 'is-active' : '' }}">
                    <span>📊 {{ __('income.summary_report') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isReports ? 'is-active' : '' }}" data-group="reports">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-bar-chart"></i>
                <span>{{ __('common.reports') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('reports.monthly') }}"
                   class="erp-sidebar__sublink {{ $isMonthly ? 'is-active' : '' }}">
                    <span>{{ __('common.monthly_report') }}</span>
                </a>
                <a href="{{ route('reports.yearly') }}"
                   class="erp-sidebar__sublink {{ $isYearly ? 'is-active' : '' }}">
                    <span>{{ __('common.yearly_report') }}</span>
                </a>
                <a href="{{ route('reports.animal-investment') }}"
                   class="erp-sidebar__sublink {{ $isAnimalInvestment ? 'is-active' : '' }}">
                    <span>{{ __('common.animal_investment_report') }}</span>
                </a>
                <a href="{{ route('reports.birth-history') }}"
                   class="erp-sidebar__sublink {{ $isBirthHistory ? 'is-active' : '' }}">
                    <span>{{ __('common.birth_history_report') }}</span>
                </a>
                <a href="{{ route('reports.milk-reconciliation') }}"
                   class="erp-sidebar__sublink {{ request()->routeIs('reports.milk-*', 'reports.dairy-collection') ? 'is-active' : '' }}">
                    <span>🥛 {{ __('milk_flow.report_reconciliation') }}</span>
                </a>
                <a href="{{ route('reports.financial-summary') }}"
                   class="erp-sidebar__sublink {{ request()->routeIs('reports.financial-summary') ? 'is-active' : '' }}">
                    <span>📈 {{ __('farm.report_financial_summary') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isStaff ? 'is-active' : '' }}" data-group="staff">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-people"></i>
                <span>{{ __('common.staff') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('employees.index') }}"
                   class="erp-sidebar__sublink {{ $isStaff ? 'is-active' : '' }}">
                    <span>{{ __('common.employees') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isAssets || $isAssetReports ? 'is-active' : '' }}" data-group="assets">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-box-seam"></i>
                <span>{{ __('common.assets') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('assets.index') }}"
                   class="erp-sidebar__sublink {{ $isAssetsList ? 'is-active' : '' }}">
                    <span>{{ __('common.assets') }}</span>
                </a>
                <a href="{{ route('assets.create') }}"
                   class="erp-sidebar__sublink {{ $isAssetsCreate ? 'is-active' : '' }}">
                    <span>{{ __('asset.add_asset') }}</span>
                </a>
                <a href="{{ route('reports.assets') }}"
                   class="erp-sidebar__sublink {{ $isAssetReports ? 'is-active' : '' }}">
                    <span>{{ __('asset.reports') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isTasks ? 'is-active' : '' }}" data-group="tasks">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-list-check"></i>
                <span>{{ __('common.tasks') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('tasks.index') }}"
                   class="erp-sidebar__sublink {{ $isTasks ? 'is-active' : '' }}">
                    <span>{{ __('common.tasks') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isMeetings ? 'is-active' : '' }}" data-group="meetings">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-calendar-event"></i>
                <span>{{ __('common.meetings') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('meetings.index') }}"
                   class="erp-sidebar__sublink {{ $isMeetings ? 'is-active' : '' }}">
                    <span>{{ __('common.meetings') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isDailyReport ? 'is-active' : '' }}" data-group="daily-report">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-journal-text"></i>
                <span>{{ __('common.daily_report') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('daily-reports.index') }}"
                   class="erp-sidebar__sublink {{ $isDailyReport ? 'is-active' : '' }}">
                    <span>{{ __('common.daily_report') }}</span>
                </a>
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isSettings ? 'is-active' : '' }}" data-group="system">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-gear"></i>
                <span>{{ __('common.system') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('settings.index') }}"
                   class="erp-sidebar__sublink {{ $isSettings ? 'is-active' : '' }}">
                    <span>{{ __('common.settings') }}</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="erp-sidebar__footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="erp-sidebar__logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>{{ __('common.logout') }}</span>
            </button>
        </form>
    </div>
</aside>
