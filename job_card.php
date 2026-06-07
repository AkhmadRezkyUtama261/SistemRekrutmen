<?php
/*
 * @Module:      Job Card Component
 * @Author:      FE-05
 * @Date:        2026-05-24
 * @Description: Glassmorphism job card with hover lift animation.
 *               Accepts $job array with job data.
 * @Ownership:   FE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

if (!isset($job)) return;

$title     = htmlspecialchars($job['title'] ?? '', ENT_QUOTES, 'UTF-8');
$company   = htmlspecialchars($job['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
$location  = htmlspecialchars($job['location'] ?? '', ENT_QUOTES, 'UTF-8');
$jobType   = htmlspecialchars($job['job_type'] ?? '', ENT_QUOTES, 'UTF-8');
$salary    = htmlspecialchars($job['salary_range'] ?? 'Negotiable', ENT_QUOTES, 'UTF-8');
$deadline  = $job['deadline'] ?? null;
$jobId     = (int)($job['id'] ?? 0);
$industry  = $job['industry_category'] ?? '';
?>

<div class="group bg-slate-800/60 backdrop-blur-xl border border-white/[0.08] rounded-2xl p-6 shadow-lg hover-lift transition-all duration-300 hover:border-indigo-500/20">
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500/20 to-indigo-600/20 border border-indigo-500/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
            </svg>
        </div>
        <?php if ($jobType): ?>
        <span class="job-type-badge"><?= htmlspecialchars(JOB_TYPES[$jobType] ?? ucfirst($jobType), ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
    </div>

    <!-- Title & Company -->
    <h3 class="text-lg font-bold text-white mb-1 group-hover:text-indigo-300 transition-colors line-clamp-2"><?= $title ?></h3>
    <p class="text-sm text-slate-400 mb-4"><?= $company ?></p>

    <!-- Meta Info -->
    <div class="space-y-2 mb-5">
        <?php if ($location): ?>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            <span><?= $location ?></span>
        </div>
        <?php endif; ?>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-emerald-400 font-medium"><?= $salary ?></span>
        </div>
        <?php if ($deadline): ?>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <span>Deadline: <?= htmlspecialchars(formatDate($deadline), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Action -->
    <a href="<?= BASE_URL ?>/pelamar/jobs/detail.php?id=<?= $jobId ?>"
       class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition-colors group/link">
        Lihat Detail
        <svg class="w-4 h-4 transform group-hover/link:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
        </svg>
    </a>
</div>
