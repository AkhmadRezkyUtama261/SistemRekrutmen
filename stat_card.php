<?php
/*
 * @Module:      Stat Card Component
 * @Author:      FE-05
 * @Date:        2026-05-24
 * @Description: Bento-Grid stat card with glassmorphism styling.
 *               Accepts: $icon, $label, $value, $trend, $trendDirection, $color.
 * @Ownership:   FE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

$statIcon      = $icon ?? '';
$statLabel     = htmlspecialchars($label ?? '', ENT_QUOTES, 'UTF-8');
$statValue     = htmlspecialchars($value ?? '0', ENT_QUOTES, 'UTF-8');
$statTrend     = $trend ?? null;
$statDirection = $trendDirection ?? 'up';
$statColor     = $color ?? 'indigo';

$colorClasses = [
    'indigo'  => 'from-indigo-500/20 to-indigo-600/20 border-indigo-500/20 text-indigo-400',
    'emerald' => 'from-emerald-500/20 to-emerald-600/20 border-emerald-500/20 text-emerald-400',
    'amber'   => 'from-amber-500/20 to-amber-600/20 border-amber-500/20 text-amber-400',
    'rose'    => 'from-rose-500/20 to-rose-600/20 border-rose-500/20 text-rose-400',
    'sky'     => 'from-sky-500/20 to-sky-600/20 border-sky-500/20 text-sky-400',
];
$cc = $colorClasses[$statColor] ?? $colorClasses['indigo'];
?>

<div class="bg-slate-800/60 backdrop-blur-xl border border-white/[0.08] rounded-2xl p-6 shadow-lg hover-lift transition-all duration-300">
    <div class="flex items-start justify-between mb-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br <?= $cc ?> border flex items-center justify-center">
            <?= $statIcon ?>
        </div>
        <?php if ($statTrend !== null): ?>
        <div class="flex items-center gap-1 text-xs font-semibold <?= $statDirection === 'up' ? 'text-emerald-400' : 'text-rose-400' ?>">
            <?php if ($statDirection === 'up'): ?>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
            <?php else: ?>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25"/></svg>
            <?php endif; ?>
            <?= htmlspecialchars($statTrend, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="stat-value"><?= $statValue ?></div>
    <div class="stat-label mt-1"><?= $statLabel ?></div>
</div>
