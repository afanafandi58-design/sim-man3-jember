@php
    $menuItems = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['route' => 'ppdb.index', 'icon' => 'bi-person-plus', 'label' => 'PPDB'],
        ['route' => 'siswa.index', 'icon' => 'bi-people', 'label' => 'Data Siswa'],
        ['route' => 'guru.index', 'icon' => 'bi-person-badge', 'label' => 'Guru'],
        ['route' => 'akademik.index', 'icon' => 'bi-book', 'label' => 'Akademik'],
        ['route' => 'administrasi.index', 'icon' => 'bi-folder2-open', 'label' => 'Administrasi'],
        ['route' => 'pengaturan.index', 'icon' => 'bi-gear', 'label' => 'Pengaturan'],
    ];
@endphp

<aside class="man3-sidebar" id="man3Sidebar">
    <div class="sidebar-header">
        <h6>Menu Utama</h6>
    </div>

    <nav class="nav flex-column py-2">
        @foreach ($menuItems as $item)
            @php
                $isActive = request()->routeIs($item['route'])
                    || ($item['route'] !== 'dashboard' && request()->routeIs(str_replace('.index', '.*', $item['route'])));
            @endphp
            <a href="{{ route($item['route']) }}"
               @class(['nav-link', 'active' => $isActive])>
                <i class="bi {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
