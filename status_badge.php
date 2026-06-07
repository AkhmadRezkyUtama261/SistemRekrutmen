<?php
/*
 * @Module:      Status Badge Component
 * @Author:      FE-05
 * @Date:        2026-05-24
 * @Description: Status badge with color-coded styling per status.
 * @Ownership:   FE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

/**
 * Render a status badge.
 * @param string $status The application status
 * @return string HTML badge
 */
function renderStatusBadge(string $status): string {
    $config = [
        'applied'      => ['class' => 'badge-sky',     'label' => 'Dilamar',      'dot' => 'bg-sky-400'],
        'under_review' => ['class' => 'badge-amber',   'label' => 'Dalam Review', 'dot' => 'bg-amber-400'],
        'interview'    => ['class' => 'badge-indigo',   'label' => 'Wawancara',    'dot' => 'bg-indigo-400'],
        'accepted'     => ['class' => 'badge-emerald',  'label' => 'Diterima',     'dot' => 'bg-emerald-400'],
        'rejected'     => ['class' => 'badge-rose',     'label' => 'Ditolak',      'dot' => 'bg-rose-400'],
    ];

    $cfg = $config[$status] ?? ['class' => 'badge-sky', 'label' => ucfirst($status), 'dot' => 'bg-slate-400'];

    return '<span class="badge ' . $cfg['class'] . '">'
        . '<span class="w-1.5 h-1.5 rounded-full ' . $cfg['dot'] . '"></span>'
        . htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8')
        . '</span>';
}

/**
 * Render a job status badge.
 */
function renderJobStatusBadge(string $status): string {
    $config = [
        'active' => ['class' => 'badge-emerald', 'label' => 'Aktif'],
        'closed' => ['class' => 'badge-rose',    'label' => 'Ditutup'],
        'draft'  => ['class' => 'badge-amber',   'label' => 'Draft'],
    ];

    $cfg = $config[$status] ?? ['class' => 'badge-sky', 'label' => ucfirst($status)];

    return '<span class="badge ' . $cfg['class'] . '">'
        . htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8')
        . '</span>';
}
