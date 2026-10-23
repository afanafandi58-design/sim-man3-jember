<nav class="navbar man3-navbar navbar-expand-lg">
    <div class="container-fluid px-3 px-lg-4">
        <button class="btn btn-sidebar-toggle d-lg-none me-2" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo-man3-jember.svg') }}"
                 alt="Logo MAN 3 Jember"
                 class="logo-img">
            <div>
                <div class="brand-title">Sistem Informasi MAN 3 Jember</div>
                <div class="brand-subtitle">Madrasah Aliyah Negeri 3 Jember</div>
            </div>
        </a>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <button class="btn btn-link text-white p-0 position-relative" type="button" aria-label="Notifikasi">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.6rem;">
                    3
                </span>
            </button>

            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="text-white d-none d-md-inline fw-medium" style="font-size: 0.875rem;">Administrator</span>
                    <i class="bi bi-chevron-down text-white d-none d-md-inline" style="font-size: 0.7rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="{{ route('pengaturan.index') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
