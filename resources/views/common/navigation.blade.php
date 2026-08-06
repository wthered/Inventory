<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <ul class="sidebar-menu">
        <!-- Dashboard -->
        <li>
            <a href="{{ route('dashboard') }}" class="{{ Route::is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>

        <li class="menu-header">Inventory Core</li>

        <!-- Products -->
        <li>
            <a href="{{ route('inventory.products.index') }}"
               class="{{ Route::is('inventory.products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i> Products
            </a>
        </li>

        {{-- Έλεγχος άδειας για Categories --}}
        @can('category.view')
            <li>
                <a href="{{ route('inventory.categories.index') }}"
                   class="{{ Route::is('inventory.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Categories
                </a>
            </li>
        @endcan

        {{-- Έλεγχος άδειας για Brands --}}
        @can('brand.view')
            <li>
                <a href="{{ route('inventory.brands.index') }}"
                   class="{{ Route::is('inventory.brands.*') ? 'active' : '' }}">
                    <i class="fas fa-trademark"></i> Brands
                </a>
            </li>
        @endcan

        <!-- Warehouses -->
        <li>
            <a href="{{ route('inventory.warehouses.index') }}"
               class="{{ Route::is('inventory.warehouses.*') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i> Warehouses
            </a>
        </li>

        <!-- Stock Transfers -->
        <li>
            <a href="{{ route('inventory.transfers.index') }}"
               class="{{ Route::is('inventory.transfers.*') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt"></i> Stock Transfers
            </a>
        </li>

        <!-- Stock Adjustments -->
        <li>
            <a href="{{ route('inventory.adjustments.index') }}"
               class="{{ Route::is('inventory.adjustments.*') ? 'active' : '' }}">
                <i class="fas fa-sliders-h"></i> Stock Adjustments
            </a>
        </li>

        <!-- Stock Returns -->
        <li>
            <a href="{{ route('inventory.returns.index') }}"
               class="{{ Route::is('inventory.returns.*') ? 'active' : '' }}">
                <i class="fas fa-undo-alt"></i> Stock Returns
            </a>
        </li>

        <li class="menu-header">Commercial</li>

        <!-- Purchases & Suppliers -->
        <li>
            <a href="{{ route('inventory.purchases.index') }}"
               class="{{ Route::is('inventory.purchases.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i> Purchase Orders
            </a>
        </li>

        <!-- Sales Orders -->
        <li>
            <a href="{{ route('inventory.sales.index') }}"
               class="{{ Route::is('sales.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-bag"></i> Sales Orders
            </a>
        </li>

        <li>
            <a href="{{ route('inventory.suppliers.index') }}"
               class="{{ Route::is('inventory.suppliers.*') ? 'active' : '' }}">
                <i class="fas fa-truck"></i> Suppliers
            </a>
        </li>

        <!-- Customers & Invoices -->
        <li>
            <a href="{{ route('inventory.customers.index') }}"
               class="{{ Route::is('inventory.customers.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Customers
            </a>
        </li>
        <li>
            <a href="{{ route('inventory.invoices.index') }}"
               class="{{ Route::is('inventory.invoices.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> Invoices
            </a>
        </li>

        <!-- Human Resources Section -->
        <li class="menu-header">Human Resources</li>

        <!-- Employees -->
        <li>
            <a href="{{ route('hr.employees.index') }}"
               class="{{ Route::is('hr.employees.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i> Employees
            </a>
        </li>

        {{-- Optional: Departments & Positions --}}
        @can('department.view')
            <li>
                <a href="{{ route('hr.departments.index') }}"
                   class="{{ Route::is('hr.departments.*') ? 'active' : '' }}">
                    <i class="fas fa-sitemap"></i> Departments
                </a>
            </li>
        @endcan

        {{-- Optional: Attendance & Leaves --}}
        <li>
            <a href="{{ route('hr.attendances.index') }}"
               class="{{ Route::is('hr.attendances.*') ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i> Attendance
            </a>
        </li>
        <li>
            <a href="{{ route('hr.leave-requests.index') }}"
               class="{{ Route::is('hr.leave-requests.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-minus"></i> Leave Requests
            </a>
        </li>

        <li class="menu-header">System</li>

        <!-- Reports -->
        <li>
            <a href="{{ route('inventory.reports.index') }}"
               class="{{ Route::is('inventory.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>

        <!-- Settings -->
        <li>
            <a href="{{ route('profile.edit') }}" class="{{ Route::is('profile.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>
</aside>


<!-- Header -->
<header class="navbar">
    <div class="navbar-left">
        <button id="sidebar-toggle" class="sidebar-toggle" title="Toggle Sidebar">
            &#9776;
        </button>
        <span class="logo">{{ config('app.name', 'Inventory') }} Dashboard</span>
    </div>

    <div class="navbar-right">

        <!-- Language Selector -->
        <div class="nav-item language-selector">
            <button class="nav-icon-btn" id="lang-toggle">
                @if(app()->getLocale() == 'el')
                    <span class="flag-icon">🇬🇷</span>
                    <span class="lang-text">Ελληνικά</span>
                @elseif(app()->getLocale() == 'fr')
                    <span class="flag-icon">🇫🇷</span>
                    <span class="lang-text">Français</span>
                @else
                    <span class="flag-icon">🇬🇧</span>
                    <span class="lang-text">English</span>
                @endif
            </button>
            <div class="lang-dropdown">
                <a href="{{ route('lang.switch', 'el') }}">Ελληνικά</a>
                <a href="{{ route('lang.switch', 'en') }}">English</a>
                <a href="{{ route('lang.switch', 'fr') }}">Français</a>
            </div>
        </div>

        <button id="theme-toggle" class="nav-icon-btn">
            <i class="fas fa-moon"></i> <!-- Ή fa-sun ανάλογα το theme -->
        </button>

        <!-- Notifications Bell -->
        <div class="nav-item notifications-item">
            <button class="nav-icon-btn" id="notifications-toggle">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <div class="nav-dropdown notifications-dropdown">
                <div class="dropdown-header">Notifications</div>
                <div class="dropdown-scroll">
                    <!-- Μη αναγνωσμένο Notification -->
                    <div class="notif-item unread">
                        <div class="notif-content">
                            <p>Low stock alert: <strong>Product A</strong></p>
                            <small>2 mins ago</small>
                        </div>
                    </div>
                    <!-- Αναγνωσμένα Notifications -->
                    <div class="notif-item">
                        <div class="notif-content">
                            <p>New supplier invoice created.</p>
                            <small>1 hour ago</small>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-content">
                            <p>Warehouse B reached 90% capacity.</p>
                            <small>5 hours ago</small>
                        </div>
                    </div>
                </div>
                <a href="#" class="dropdown-footer">View All</a>
            </div>
        </div>

        <div class="user-info" id="user-profile-toggle">
            <!-- Εδώ χρησιμοποιούμε το firstName και lastName από το DTO -->
            <span class="user-name">{{ $user->firstName }} {{ $user->lastName }}</span>

            <div class="user-avatar-container">
                <!-- Το avatar έρχεται έτοιμο από το DTO (είτε από τη βάση είτε το robohash fallback) -->
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="user-avatar">

                <div class="user-dropdown-menu">
                    <!-- Μεταφρασμένα links -->
                    <a href="{{ route('profile.show') }}" data-icon="👤">Προφίλ</a>
                    <a href="{{ route('profile.settings.index') }}" data-icon="🔧">Ρυθμίσεις</a>
                    <a href="#" data-icon="🛍️">Οι Παραγγελίες μου</a>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('auth.sign.out') }}">
                        @csrf
                        <button type="submit" data-icon="✖" class="dropdown-logout-btn">Αποσύνδεση</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
