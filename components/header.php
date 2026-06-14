<?php
/*
 * @Module:      Global Header / Navigation
 * @Author:      FE-01 (UI Shell Lead)
 * @Date:        2026-05-24
 * @Description: Glassmorphism navigation bar with role-aware links,
 *               responsive mobile menu, and micro-interaction animations.
 *               Uses Tailwind CSS utilities + custom glassmorphism classes.
 * @Ownership:   FE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

// Determine current user role and auth status
$isLoggedIn  = isset($_SESSION['user_id']);
$currentRole = $_SESSION['role'] ?? null;
$userName    = '';

if ($isLoggedIn) {
    if ($currentRole === 'hr') {
        $userName = $_SESSION['company_name'] ?? 'Perusahaan';
    } else {
        $userName = $_SESSION['full_name'] ?? 'Pelamar';
    }
}

// Determine active page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

/**
 * Helper: returns Tailwind classes for active nav link
 */
function navActive(string $page, string $current): string {
    return $page === $current
        ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-500/10 border-indigo-500/30'
        : 'text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 hover:bg-indigo-500/5 border-transparent';
}
?>

<!-- ═══════════════════════════════════════════════════════════════
     HEADER — Glassmorphism Navigation Bar
     ═══════════════════════════════════════════════════════════════ -->
<header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <!-- Glass backdrop layer -->
    <nav class="mx-auto max-w-[1400px] mt-4 mx-4 rounded-2xl
                bg-slate-900/60 backdrop-blur-xl
                border border-white/[0.08]
                shadow-[0_8px_32px_rgba(0,0,0,0.25),inset_0_1px_0_rgba(255,255,255,0.05)]
                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
         id="glass-navbar">

        <div class="flex items-center justify-between px-6 py-3">

            <!-- ── Brand Logo ── -->
            <a href="<?= BASE_URL ?>" class="flex items-center gap-3 group" id="brand-logo">
                <!-- Logo Mark -->
                <div class="relative">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600
                                flex items-center justify-center
                                shadow-[0_4px_14px_rgba(99,102,241,0.4)]
                                group-hover:shadow-[0_6px_20px_rgba(99,102,241,0.5)]
                                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <!-- Glow pulse (subtle) -->
                    <div class="absolute inset-0 rounded-xl bg-indigo-500/20 blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </div>
                <!-- Logo Text -->
                <div class="hidden sm:block">
                    <span class="text-lg font-bold text-white tracking-tight">Recruit<span class="text-indigo-400">Pro</span></span>
                    <span class="block text-[10px] font-semibold text-slate-500 uppercase tracking-[0.15em] -mt-1">Enterprise</span>
                </div>
            </a>

            <!-- ── Desktop Navigation Links ── -->
            <div class="hidden lg:flex items-center gap-1" id="desktop-nav">

                <?php if (!$isLoggedIn): ?>
                    <!-- PUBLIC NAV -->
                    <a href="<?= BASE_URL ?>"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('index', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-home">
                        Beranda
                    </a>
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('browse', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-jobs">
                        Lowongan
                    </a>

                <?php elseif ($currentRole === ROLE_HR): ?>
                    <!-- HR NAV -->
                    <a href="<?= BASE_URL ?>/hr/dashboard.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('dashboard', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-hr-dashboard">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            Dashboard
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/hr/jobs/list.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('list', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-hr-jobs">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            Lowongan
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/hr/applicants/list.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('list', $currentPage) && $currentDir === 'applicants' ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-500/10 border-indigo-500/30' : 'text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 hover:bg-indigo-500/5 border-transparent' ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-hr-applicants">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                            Pelamar
                        </span>
                    </a>

                <?php elseif ($currentRole === ROLE_PELAMAR): ?>
                    <!-- PELAMAR NAV -->
                    <a href="<?= BASE_URL ?>/pelamar/dashboard.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('dashboard', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-pelamar-dashboard">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            Dashboard
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('browse', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-browse-jobs">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                            Cari Lowongan
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/pelamar/applications/list.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('list', $currentPage) && $currentDir === 'applications' ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-500/10 border-indigo-500/30' : 'text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 hover:bg-indigo-500/5 border-transparent' ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-my-applications">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                            </svg>
                            Lamaran Saya
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/pelamar/perusahaan.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('perusahaan', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-perusahaan">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                            Perusahaan
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/pelamar/bantuan.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium border
                              <?= navActive('bantuan', $currentPage) ?>
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-bantuan">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                            </svg>
                            Bantuan
                        </span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- ── Right Side: Auth Actions ── -->
            <div class="flex items-center gap-3" id="header-actions">

                <?php if ($isLoggedIn): ?>
                    <!-- Theme Toggle Button -->
                    <button onclick="toggleTheme()"
                            class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200"
                            title="Toggle Dark/Light Mode">
                        <!-- Sun Icon (Hidden in Dark Mode) -->
                        <svg class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 21v-2.25m-6.364-.386l1.591-1.591M3 12h2.25m.386-6.364l1.591 1.591M16.892 16.892A7.5 7.5 0 1112 4.5a7.5 7.5 0 014.892 12.392z" />
                        </svg>
                        <!-- Moon Icon (Hidden in Light Mode) -->
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <!-- User Avatar & Dropdown -->
                    <div class="relative" id="user-dropdown-container">
                        <button onclick="toggleUserDropdown()"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl
                                       hover:bg-white/5 transition-all duration-200
                                       ease-[cubic-bezier(0.4,0,0.2,1)] cursor-pointer"
                                id="user-dropdown-trigger">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500
                                        flex items-center justify-center text-white text-xs font-bold
                                        shadow-[0_2px_8px_rgba(99,102,241,0.3)]">
                                <?= strtoupper(substr(htmlspecialchars($userName), 0, 1)) ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-semibold text-white leading-tight"><?= htmlspecialchars($userName) ?></p>
                                <p class="text-[11px] text-slate-400 leading-tight capitalize"><?= htmlspecialchars($currentRole) ?></p>
                            </div>
                            <!-- Chevron -->
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" id="dropdown-chevron"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full mt-2 w-56
                                    bg-slate-800/90 backdrop-blur-xl
                                    border border-white/[0.08] rounded-xl
                                    shadow-[0_20px_40px_rgba(0,0,0,0.4)]
                                    opacity-0 invisible translate-y-2
                                    transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]
                                    overflow-hidden"
                             id="user-dropdown-menu">
                            <div class="p-2">
                                <?php if ($currentRole === ROLE_HR): ?>
                                    <a href="<?= BASE_URL ?>/hr/profile.php"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                                              text-sm text-slate-300 hover:text-white hover:bg-white/5
                                              transition-all duration-150"
                                       id="dropdown-profile">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                        Profil Perusahaan
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/pelamar/profile/edit.php"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                                              text-sm text-slate-300 hover:text-white hover:bg-white/5
                                              transition-all duration-150"
                                       id="dropdown-profile">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                        Edit Profil
                                    </a>
                                    <a href="<?= BASE_URL ?>/pelamar/profile/cetak_cv.php"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                                              text-sm text-slate-300 hover:text-white hover:bg-white/5
                                              transition-all duration-150"
                                       id="dropdown-cetak-cv">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0v2.796c0 1.171.84 2.15 2.002 2.215a45.023 45.023 0 018.496 0c1.162.065 2.002-.916 2.002-2.088v-2.922zM12 7.5v-3m0 0v-1.5m0 1.5h1.5m-1.5 0H10.5"/>
                                        </svg>
                                        Cetak CV
                                    </a>
                                    <a href="<?= BASE_URL ?>/pelamar/jobs/saved.php"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                                              text-sm text-slate-300 hover:text-white hover:bg-white/5
                                              transition-all duration-150"
                                       id="dropdown-saved-jobs">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                                        </svg>
                                        Lowongan Tersimpan
                                    </a>
                                <?php endif; ?>
                            </div>
                            <!-- Divider -->
                            <div class="border-t border-white/[0.06] mx-2"></div>
                            <div class="p-2">
                                <a href="<?= BASE_URL ?>/auth/logout.php"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                                          text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/10
                                          transition-all duration-150"
                                   id="dropdown-logout">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                    </svg>
                                    Keluar
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Guest Actions -->
                    <a href="<?= BASE_URL ?>/auth/login.php"
                       class="px-4 py-2 rounded-xl text-sm font-medium
                              text-slate-300 hover:text-white
                              border border-transparent hover:border-white/10
                              hover:bg-white/5
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-login">
                        Masuk
                    </a>
                    <a href="<?= BASE_URL ?>/auth/register.php"
                       class="px-5 py-2.5 rounded-xl text-sm font-semibold
                              text-white bg-gradient-to-r from-indigo-500 to-indigo-600
                              shadow-[0_4px_14px_rgba(99,102,241,0.35)]
                              hover:shadow-[0_6px_20px_rgba(99,102,241,0.5)]
                              hover:-translate-y-0.5
                              active:translate-y-0 active:scale-[0.97]
                              transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
                       id="nav-register">
                        Daftar Sekarang
                    </a>
                <?php endif; ?>

                <!-- ── Mobile Menu Toggle ── -->
                <button onclick="toggleMobileMenu()"
                        class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white
                               hover:bg-white/5 transition-all duration-200
                               ease-[cubic-bezier(0.4,0,0.2,1)]"
                        id="mobile-menu-toggle"
                        aria-label="Toggle navigation menu">
                    <svg class="w-6 h-6" id="menu-icon-open" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <svg class="w-6 h-6 hidden" id="menu-icon-close" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             MOBILE MENU (Slide-down panel)
             ═══════════════════════════════════════════════════════ -->
        <div class="lg:hidden overflow-hidden max-h-0 transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
             id="mobile-menu">
            <div class="px-4 pb-4 pt-2 border-t border-white/[0.06]">
                <div class="flex flex-col gap-1">
                    <?php if (!$isLoggedIn): ?>
                        <a href="<?= BASE_URL ?>" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Beranda</a>
                        <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Lowongan</a>
                        <div class="border-t border-white/[0.06] my-2"></div>
                        <a href="<?= BASE_URL ?>/auth/login.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Masuk</a>
                        <a href="<?= BASE_URL ?>/auth/register.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-indigo-600 text-center transition-all duration-200">Daftar Sekarang</a>
                    <?php elseif ($currentRole === ROLE_HR): ?>
                        <a href="<?= BASE_URL ?>/hr/dashboard.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Dashboard</a>
                        <a href="<?= BASE_URL ?>/hr/jobs/list.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Lowongan</a>
                        <a href="<?= BASE_URL ?>/hr/applicants/list.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Pelamar</a>
                        <div class="border-t border-white/[0.06] my-2"></div>
                        <a href="<?= BASE_URL ?>/hr/profile.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Profil Perusahaan</a>
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-rose-400 hover:bg-rose-500/10 transition-all duration-200">Keluar</a>
                    <?php elseif ($currentRole === ROLE_PELAMAR): ?>
                        <a href="<?= BASE_URL ?>/pelamar/dashboard.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Dashboard</a>
                        <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Cari Lowongan</a>
                        <a href="<?= BASE_URL ?>/pelamar/applications/list.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Lamaran Saya</a>
                        <a href="<?= BASE_URL ?>/pelamar/perusahaan.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Perusahaan</a>
                        <a href="<?= BASE_URL ?>/pelamar/bantuan.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Bantuan</a>
                        <a href="<?= BASE_URL ?>/pelamar/jobs/saved.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Lowongan Tersimpan</a>
                        <a href="<?= BASE_URL ?>/pelamar/profile/cetak_cv.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Cetak CV</a>
                        <div class="border-t border-white/[0.06] my-2"></div>
                        <a href="<?= BASE_URL ?>/pelamar/profile/edit.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200">Edit Profil</a>
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-medium text-rose-400 hover:bg-rose-500/10 transition-all duration-200">Keluar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- ── Header Spacing (push content below fixed nav) ── -->
<div class="h-24" id="header-spacer"></div>

<!-- ═══════════════════════════════════════════════════════════════
     HEADER JAVASCRIPT — Dropdown, Mobile Menu, Scroll Effect
     ═══════════════════════════════════════════════════════════════ -->
<script>
/**
 * Toggle user dropdown menu with smooth animation
 */
function toggleUserDropdown() {
    const menu = document.getElementById('user-dropdown-menu');
    const chevron = document.getElementById('dropdown-chevron');

    if (menu.classList.contains('invisible')) {
        menu.classList.remove('invisible', 'opacity-0', 'translate-y-2');
        menu.classList.add('visible', 'opacity-100', 'translate-y-0');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        menu.classList.add('invisible', 'opacity-0', 'translate-y-2');
        menu.classList.remove('visible', 'opacity-100', 'translate-y-0');
        chevron.style.transform = 'rotate(0deg)';
    }
}

/**
 * Toggle mobile navigation menu
 */
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.getElementById('menu-icon-open');
    const iconClose = document.getElementById('menu-icon-close');

    if (menu.style.maxHeight && menu.style.maxHeight !== '0px') {
        menu.style.maxHeight = '0px';
        iconOpen.classList.remove('hidden');
        iconClose.classList.add('hidden');
    } else {
        menu.style.maxHeight = menu.scrollHeight + 'px';
        iconOpen.classList.add('hidden');
        iconClose.classList.remove('hidden');
    }
}

/**
 * Close dropdown when clicking outside
 */
document.addEventListener('click', function(e) {
    const container = document.getElementById('user-dropdown-container');
    const menu = document.getElementById('user-dropdown-menu');

    if (container && menu && !container.contains(e.target)) {
        menu.classList.add('invisible', 'opacity-0', 'translate-y-2');
        menu.classList.remove('visible', 'opacity-100', 'translate-y-0');
        const chevron = document.getElementById('dropdown-chevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
});

/**
 * Glassmorphism scroll effect — increase opacity on scroll
 */
let lastScroll = 0;
const navbar = document.getElementById('glass-navbar');

window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 50) {
        navbar.classList.add('bg-slate-900/80');
        navbar.classList.remove('bg-slate-900/60');
        navbar.style.boxShadow = '0 8px 32px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05)';
    } else {
        navbar.classList.remove('bg-slate-900/80');
        navbar.classList.add('bg-slate-900/60');
        navbar.style.boxShadow = '0 8px 32px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.05)';
    }

    lastScroll = currentScroll;
}, { passive: true });
</script>
