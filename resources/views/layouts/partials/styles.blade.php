    <style>
        /* Optional: Styling untuk scrollbar agar tampilan lebih konsisten di berbagai browser */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; /* Warna track scrollbar */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8; /* gray-400 */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b; /* gray-600 */
        }
        /* Alpine.js Cloak: Sembunyikan elemen sebelum Alpine dimuat */
        [x-cloak] { display: none !important; }

        /* Basic dropdown styling for demonstration (can be enhanced with JS if needed) */
        .group:hover .group-hover-show {
            display: block; /* Menampilkan dropdown saat group di-hover */
        }
        /* Penting untuk Font Awesome: Memastikan ikon memiliki lebar tetap untuk proporsionalitas yang lebih baik dalam daftar */
        .fa-fw {
            width: 1.2857142857em; /* Standar Font Awesome untuk fixed-width icons */
            text-align: center;
        }
        /* Penyesuaian tambahan untuk ikon Font Awesome agar center vertikal dengan teks */
        .fa-fw-aligned {
            display: flex;
            align-items: center;
            justify-content: center; /* Untuk ikon yang sudah memiliki lebar tetap, ini membuatnya center */
            height: 1em; /* Tinggi ikon agar sejajar dengan tinggi baris teks */
        }
        /* Smoother sidebar hover with staggered labels */
        :root {
            --sidebar-collapsed: 72px;
            --sidebar-expanded: 260px;
            --ease: cubic-bezier(.18,.9,.32,1);
        }
        .sidebar {
            width: var(--sidebar-collapsed);
            overflow: visible;
            will-change: width;
            position: relative;
            background: #ffffff;
        }

        /* Prevent all transitions on page load to avoid "blink" */
        .sidebar:not(.ready-to-animate),
        .sidebar:not(.ready-to-animate) * {
            transition: none !important;
        }

        .sidebar.ready-to-animate {
            transition: width 360ms var(--ease);
        }
        .sidebar.expanded {
            width: var(--sidebar-expanded);
            overflow: visible;
        }

        /* Labels: use max-width + opacity + transform for smooth reveal */
        .sidebar .label {
            display: inline-block;
            max-width: 0;
            opacity: 0;
            transform: translateX(-10px);
            transition: max-width 300ms var(--ease), opacity 200ms ease, transform 300ms var(--ease);
            white-space: nowrap;
            overflow: hidden;
            vertical-align: middle;
            font-size: 0.95rem;
            color: inherit;
        }
        .sidebar.expanded .label {
            max-width: 300px;
            opacity: 1;
            transform: translateX(0);
        }

        @media (min-width: 1025px) {
            /* Tooltip-style labels for collapsed sidebar (Supabase-like) */
            .sidebar:not(.expanded) nav {
                overflow: visible !important;
            }

            .sidebar:not(.expanded) .nav-item:hover .label {
                position: absolute;
                left: 100%;
                margin-left: 12px;
                background: #1e293b; /* Slate 800 */
                color: white;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 0.75rem;
                font-weight: 600;
                max-width: none;
                opacity: 1;
                transform: translateX(0);
                visibility: visible;
                overflow: visible;
                z-index: 100;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                pointer-events: none;
            }

            /* Hide tooltip when clicked (Supabase-like) */
            .sidebar:not(.expanded) .nav-item.clicked .label,
            .sidebar:not(.expanded) .nav-item:active .label {
                opacity: 0 !important;
                visibility: hidden;
                transition: opacity 200ms ease, visibility 0s linear 200ms !important;
            }
            .sidebar:not(.expanded) .nav-item.clicked .label::before,
            .sidebar:not(.expanded) .nav-item:active .label::before {
                opacity: 0 !important;
                transition: opacity 200ms ease !important;
            }

            /* Tooltip Arrow */
            .sidebar:not(.expanded) .nav-item:hover .label::before {
                content: '';
                position: absolute;
                left: -4px;
                top: 50%;
                transform: translateY(-50%) rotate(45deg);
                width: 8px;
                height: 8px;
                background: #1e293b;
                z-index: -1;
            }
        }

        /* Sidebar Separator */
        .sidebar .sidebar-separator {
            height: 1px;
            background: #f1f5f9;
            margin: 1.25rem 0.75rem;
            transition: margin 300ms var(--ease);
        }
        .sidebar.expanded .sidebar-separator {
            margin: 1.25rem 1.25rem;
        }

        /* Nav Item Styling */
        .nav-item {
            margin: 2px 12px;
            padding: 10px 0 10px 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            transition: background-color 200ms ease, color 200ms ease, padding 360ms var(--ease);
            color: #64748b;
            position: relative;
            text-decoration: none;
        }
        .sidebar.expanded .nav-item {
            padding-left: 16px;
        }

        .nav-item:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .nav-item.active {
            background-color: #eff6ff;
            color: #2563eb;
            font-weight: 600;
        }

        .nav-item .icon {
            font-size: 1.1rem;
            line-height: 1;
            transition: margin 360ms var(--ease), color 200ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        .sidebar.expanded .nav-item .icon {
            margin-right: 14px;
        }

        /* Active indicator */
        .nav-item.active::after {
            content: '';
            position: absolute;
            left: -12px;
            top: 25%;
            height: 50%;
            width: 4px;
            background-color: #2563eb;
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 200ms ease;
        }
        .sidebar.expanded .nav-item.active::after {
            opacity: 1;
        }

        /* Brand section */
        .brand-area {
            height: 80px;
            display: flex;
            align-items: center;
            padding: 0 16px;
            overflow: visible;
            transition: padding 360ms var(--ease);
        }
        .sidebar.expanded .brand-area {
            padding: 0 24px;
        }
        /* Pagination theme similar to screenshot: minimal, white/gray with blue active pill */
        .pagination-custom { display: flex; gap: 6px; align-items: center; font-weight: 500; }
        .pagination-custom .page-item { list-style: none; }
        .pagination-custom .page-link{
            display:inline-flex; align-items:center; justify-content:center;
            min-width:34px; height:34px; padding:0 10px; border-radius:8px;
            color: #374151; background: #ffffff; border: 1px solid rgba(31,41,55,0.04); text-decoration:none;
        }
        .pagination-custom .page-link:hover{ background:#f8fafc }
        .pagination-custom .page-item.disabled .page-link{ color:#cbd5e1 }
        .pagination-custom .page-item.active .page-link{ background:#3b82f6; color:white; box-shadow: 0 4px 10px rgba(59,130,246,0.12) }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            :root {
                --sidebar-collapsed: 0px;
                --sidebar-expanded: 280px;
            }
            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                z-index: 50;
                transition: left 300ms var(--ease), box-shadow 300ms ease;
                width: var(--sidebar-expanded);
                height: 100vh;
                margin: 0 !important;
                border-radius: 0;
            }
            .sidebar.mobile-show {
                left: 0;
                box-shadow: 20px 0 50px rgba(0,0,0,0.15);
            }
            .sidebar .label {
                max-width: 300px;
                opacity: 1;
                transform: translateX(0);
            }
            .sidebar .nav-item {
                justify-content: flex-start;
                padding-left: 16px;
            }
            .sidebar .nav-item .icon {
                margin-right: 14px;
            }
            .sidebar .sidebar-separator {
                margin: 1.5rem 1.25rem;
            }
            .sidebar .brand-area {
                padding: 0 24px;
            }
            .main-content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .desktop-only { display: none !important; }
            .mobile-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                backdrop-filter: blur(4px);
                z-index: 40;
            }
            .mobile-overlay.show { display: block; }
        }

        @media (min-width: 1025px) {
            .mobile-only { display: none !important; }
        }

        /* Header Elements Refinement */
        .user-dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #475569;
            font-size: 0.875rem;
            transition: all 150ms ease;
            text-decoration: none;
            border-radius: 8px;
            margin: 0 4px;
        }
        .user-dropdown-item:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .user-dropdown-item i {
            width: 1.25rem;
            margin-right: 0.75rem;
            color: #94a3b8;
        }
    </style>
