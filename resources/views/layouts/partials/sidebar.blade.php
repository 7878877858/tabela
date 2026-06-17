@php
    $tabCow = request('tab') === 'cow';
    $isDashboard = request()->routeIs('dashboard');
    $isAnimals = request()->routeIs('buffalo.*');
    $isBuffaloAll = request()->routeIs('buffalo.index', 'buffalo.show', 'buffalo.edit') && !$tabCow;
    $isBuffaloAdd = request()->routeIs('buffalo.create');
    $isCows = request()->routeIs('buffalo.*') && $tabCow;
    $isMilkGroup = request()->routeIs('milk.*', 'sale.*');
    $isMilkEntry = request()->routeIs('milk.index');
    $isMilkHistory = request()->routeIs('milk.history');
    $isMilkSales = request()->routeIs('sale.*');
    $isMilkTx = request()->routeIs('milk.transactions');
    $isFinance = request()->routeIs('kharch.*', 'income.*');
    $isExpense = request()->routeIs('kharch.*');
    $isIncome = request()->routeIs('income.*');
    $isReports = request()->routeIs('reports.monthly', 'reports.yearly');
    $isMonthly = request()->routeIs('reports.monthly');
    $isYearly = request()->routeIs('reports.yearly');
    $isStaff = request()->routeIs('employees.*');
    $isAssets = request()->routeIs('assets.*');
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
                   class="erp-sidebar__sublink {{ $isBuffaloAll ? 'is-active' : '' }}">
                    <span>{{ __('common.all_buffaloes') }}</span>
                </a>
                <a href="{{ route('buffalo.create') }}"
                   class="erp-sidebar__sublink {{ $isBuffaloAdd ? 'is-active' : '' }}">
                    <span>{{ __('common.add_buffalo') }}</span>
                </a>
                <a href="{{ route('buffalo.index', ['tab' => 'cow']) }}"
                   class="erp-sidebar__sublink {{ $isCows ? 'is-active' : '' }}">
                    <span>{{ __('common.cows') }}</span>
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
            </div>
        </div>

        <div class="erp-sidebar__group {{ $isFinance ? 'is-active' : '' }}" data-group="finance">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-cash-stack"></i>
                <span>{{ __('common.finance') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('kharch.index') }}"
                   class="erp-sidebar__sublink {{ $isExpense ? 'is-active' : '' }}">
                    <span>{{ __('common.expense') }}</span>
                </a>
                <a href="{{ route('income.index') }}"
                   class="erp-sidebar__sublink {{ $isIncome ? 'is-active' : '' }}">
                    <span>{{ __('common.income') }}</span>
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

        <div class="erp-sidebar__group {{ $isAssets ? 'is-active' : '' }}" data-group="assets">
            <button type="button" class="erp-sidebar__group-toggle" aria-expanded="false">
                <i class="bi bi-box-seam"></i>
                <span>{{ __('common.assets') }}</span>
                <i class="bi bi-chevron-down erp-sidebar__chevron"></i>
            </button>
            <div class="erp-sidebar__group-items">
                <a href="{{ route('assets.index') }}"
                   class="erp-sidebar__sublink {{ $isAssets ? 'is-active' : '' }}">
                    <span>{{ __('common.assets') }}</span>
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
