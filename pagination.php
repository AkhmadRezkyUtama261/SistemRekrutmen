<?php
/*
 * @Module:      Pagination Component
 * @Author:      FE-05
 * @Date:        2026-05-24
 * @Description: Glassmorphism pagination with previous/next and page numbers.
 * @Ownership:   FE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

if (!isset($currentPage) || !isset($totalPages) || !isset($baseUrl)) return;
if ($totalPages <= 1) return;

$range = 2;
$start = max(1, $currentPage - $range);
$end   = min($totalPages, $currentPage + $range);
?>

<nav class="flex items-center justify-center gap-2 mt-8" aria-label="Pagination">
    <!-- Previous -->
    <?php if ($currentPage > 1): ?>
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>&page=<?= $currentPage - 1 ?>"
       class="px-3 py-2 rounded-xl text-sm font-medium text-slate-400 bg-slate-800/60 border border-white/[0.08] hover:bg-white/5 hover:text-white transition-all duration-200">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
    </a>
    <?php else: ?>
    <span class="px-3 py-2 rounded-xl text-sm font-medium text-slate-600 bg-slate-800/30 border border-white/[0.04] cursor-not-allowed">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
    </span>
    <?php endif; ?>

    <!-- First page -->
    <?php if ($start > 1): ?>
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>&page=1"
       class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 bg-slate-800/60 border border-white/[0.08] hover:bg-white/5 hover:text-white transition-all duration-200">1</a>
    <?php if ($start > 2): ?>
    <span class="px-2 text-slate-600">...</span>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Page Numbers -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
    <?php if ($i === $currentPage): ?>
    <span class="px-3.5 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-500 shadow-[0_4px_14px_rgba(99,102,241,0.35)]"><?= $i ?></span>
    <?php else: ?>
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>&page=<?= $i ?>"
       class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 bg-slate-800/60 border border-white/[0.08] hover:bg-white/5 hover:text-white transition-all duration-200"><?= $i ?></a>
    <?php endif; ?>
    <?php endfor; ?>

    <!-- Last page -->
    <?php if ($end < $totalPages): ?>
    <?php if ($end < $totalPages - 1): ?>
    <span class="px-2 text-slate-600">...</span>
    <?php endif; ?>
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>&page=<?= $totalPages ?>"
       class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 bg-slate-800/60 border border-white/[0.08] hover:bg-white/5 hover:text-white transition-all duration-200"><?= $totalPages ?></a>
    <?php endif; ?>

    <!-- Next -->
    <?php if ($currentPage < $totalPages): ?>
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>&page=<?= $currentPage + 1 ?>"
       class="px-3 py-2 rounded-xl text-sm font-medium text-slate-400 bg-slate-800/60 border border-white/[0.08] hover:bg-white/5 hover:text-white transition-all duration-200">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
    </a>
    <?php else: ?>
    <span class="px-3 py-2 rounded-xl text-sm font-medium text-slate-600 bg-slate-800/30 border border-white/[0.04] cursor-not-allowed">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
    </span>
    <?php endif; ?>
</nav>
