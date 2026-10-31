<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'SIM MAN 3 Jember',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'SIM MAN 3 Jember',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-success elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-success',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => false,
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'Cari',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar Search
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Cari',
        ],

        // Dashboard Admin
        [
            'text' => 'Dashboard',
            'url'  => '/dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'can' => 'role-admin',
        ],

        // Dashboard Guru
        [
            'text' => 'Dashboard Guru',
            'route' => 'guru.dashboard',
            'icon' => 'fas fa-chalkboard-teacher',
            'can' => 'role-guru',
        ],

        [
            'text' => 'Jadwal Saya',
            'route' => 'guru.jadwal-saya.index',
            'icon' => 'fas fa-calendar-check',
            'can' => 'role-guru',
        ],

        [
            'text' => 'Data Siswa',
            'route' => 'guru.siswas.index',
            'icon' => 'fas fa-user-graduate',
            'can' => 'role-guru',
        ],

        [
            'text' => 'Rekap Jurnal Saya',
            'route' => 'guru.laporan.index',
            'icon' => 'fas fa-file-pdf',
            'can' => 'role-guru',
        ],

        ['header' => 'ABSENSI HARIAN', 'can' => 'role-absensi-modul'],

        [
            'text' => 'Input Absensi + Bukti',
            'route' => 'absensi-modul.input',
            'icon' => 'fas fa-clipboard-check',
            'can' => 'role-absensi-modul',
        ],

        [
            'text' => 'Tool Hari Efektif',
            'route' => 'absensi-modul.hari-efektif',
            'icon' => 'fas fa-user-clock',
            'can' => 'role-absensi-modul',
        ],

        [
            'text' => 'Menu Wali Kelas',
            'route' => 'absensi-modul.wali',
            'icon' => 'fas fa-chalkboard',
            'can' => 'role-wali-or-admin',
        ],

        [
            'text' => 'Laporan Absensi Harian',
            'route' => 'absensi-modul.laporan',
            'icon' => 'fas fa-chart-pie',
            'can' => 'role-absensi-modul',
        ],

        [
            'text' => 'Kelola Wali Kelas',
            'route' => 'absensi-modul.wali.manage',
            'icon' => 'fas fa-user-tag',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Akun Staff Absensi',
            'route' => 'absensi-modul.staff.manage',
            'icon' => 'fas fa-user-shield',
            'can' => 'role-admin',
        ],

        ['header' => 'MASTER DATA', 'can' => 'role-admin'],

        [
            'text' => 'Import Data',
            'route' => 'import-data.index',
            'icon' => 'fas fa-database',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Data Guru',
            'route' => 'gurus.index',
            'icon' => 'fas fa-user-tie',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Data Siswa',
            'route' => 'siswas.index',
            'icon' => 'fas fa-user-graduate',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Manajemen Akun Guru',
            'route' => 'guru-accounts.index',
            'icon' => 'fas fa-users-cog',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Rekap Beban Mengajar',
            'route' => 'guru-beban-mengajar.index',
            'icon' => 'fas fa-chart-bar',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Import Guru',
            'route' => 'import-guru.index',
            'icon' => 'fas fa-file-upload',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Jadwal',
            'route' => 'jadwals.index',
            'icon' => 'fas fa-calendar-alt',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Import Jadwal',
            'route' => 'import-jadwal.index',
            'icon' => 'fas fa-file-import',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Master Jam Pelajaran',
            'route' => 'jam-pelajaran.index',
            'icon' => 'fas fa-clock',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Master Pola Jadwal',
            'route' => 'pola-jadwal.index',
            'icon' => 'fas fa-layer-group',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Master Mata Pelajaran',
            'route' => 'mata-pelajaran.index',
            'icon' => 'fas fa-book-open',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Usulan Mata Pelajaran',
            'route' => 'mata-pelajaran-usulan.index',
            'icon' => 'fas fa-lightbulb',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Permintaan Perubahan Jadwal',
            'route' => 'jadwal-perbaikan.index',
            'icon' => 'fas fa-calendar-times',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Import Siswa',
            'route' => 'import-siswa.index',
            'icon' => 'fas fa-user-graduate',
            'can' => 'role-admin',
        ],

        ['header' => 'MONITORING', 'can' => 'role-kepala-or-admin'],

        [
            'text' => 'Monitoring Kepala',
            'route' => 'kepala.dashboard',
            'icon' => 'fas fa-eye',
            'can' => 'role-kepala-or-admin',
        ],

        [
            'text' => 'Laporan PDF',
            'route' => 'laporan.index',
            'icon' => 'fas fa-file-pdf',
            'can' => 'role-kepala-or-admin',
        ],

        [
            'text' => 'Rekap Absensi Siswa',
            'route' => 'laporan.absensi.index',
            'icon' => 'fas fa-clipboard-list',
            'can' => 'role-kepala-or-admin',
        ],

        [
            'text' => 'Data Siswa',
            'route' => 'kepala.siswas.index',
            'icon' => 'fas fa-user-graduate',
            'can' => 'role-kepala',
        ],

        ['header' => 'JURNAL', 'can' => 'role-admin'],

        [
            'text' => 'Jurnal KBM',
            'route' => 'jurnals.index',
            'icon' => 'fas fa-book',
            'can' => 'role-admin',
        ],

        [
            'text' => 'Absensi Siswa',
            'route' => 'absensi-siswa.index',
            'icon' => 'fas fa-user-check',
            'can' => 'role-admin',
        ],

        ['header' => 'LMS PEMBELAJARAN', 'can' => 'role-lms'],

        [
            'text' => 'Dashboard LMS',
            'route' => 'lms.dashboard',
            'icon' => 'fas fa-graduation-cap',
            'can' => 'role-lms',
        ],
        [
            'text' => 'Materi Pembelajaran',
            'route' => 'lms.materials.index',
            'icon' => 'fas fa-book-reader',
            'can' => 'role-lms',
        ],
        [
            'text' => 'Tugas',
            'route' => 'lms.assignments.index',
            'icon' => 'fas fa-tasks',
            'can' => 'role-lms',
        ],
        [
            'text' => 'Pengumpulan Tugas',
            'route' => 'lms.submissions.index',
            'icon' => 'fas fa-inbox',
            'can' => 'role-lms',
        ],
        [
            'text' => 'Kuis',
            'route' => 'lms.quizzes.index',
            'icon' => 'fas fa-question-circle',
            'can' => 'role-lms',
        ],
        [
            'text' => 'Pengumuman',
            'route' => 'lms.announcements.index',
            'icon' => 'fas fa-bullhorn',
            'can' => 'role-lms',
        ],
        [
            'text' => 'Kelas & Jadwal Saya',
            'route' => 'lms.my-classes.index',
            'icon' => 'fas fa-chalkboard',
            'can' => 'role-lms-guru-or-admin',
        ],
        [
            'text' => 'Link Berbagi Siswa',
            'route' => 'lms.share.index',
            'icon' => 'fas fa-link',
            'can' => 'role-lms-guru-or-admin',
        ],
        [
            'text' => 'Monitoring LMS',
            'route' => 'lms.monitoring.index',
            'icon' => 'fas fa-chart-line',
            'can' => 'role-lms-kamad-or-admin',
        ],
        [
            'text' => 'Pengaturan LMS',
            'route' => 'lms.settings.edit',
            'icon' => 'fas fa-cog',
            'can' => 'role-lms-admin',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'PaginationFix' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/pagination-fix.css',
                ],
            ],
        ],
        'NavbarSuccessContrast' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/navbar-success-contrast.css',
                ],
            ],
        ],
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
