<?php
/*
 * @Module:      Job Search & Filter Engine
 * @Author:      BE-05 (Search & Integration)
 * @Date:        2026-05-24
 * @Description: Dynamic job search with multiple filter criteria,
 *               sorting options, and pagination. All queries use
 *               PDO prepared statements with named placeholders.
 * @Ownership:   BE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/database.php';

// ══════════════════════════════════════════════════════════════
// MAIN SEARCH FUNCTION
// ══════════════════════════════════════════════════════════════

/**
 * Search and filter active job listings.
 *
 * Builds a dynamic WHERE clause from filter parameters using
 * PDO named placeholders (never concatenates user input).
 *
 * Supported filters:
 *   - keyword:           Searches title + description + company name
 *   - industry_category: Exact match on industry
 *   - location:          Partial match (LIKE) on location
 *   - job_type:          Exact match on job type
 *   - status:            Defaults to 'active'
 *
 * Sorting options:
 *   - newest (default): ORDER BY created_at DESC
 *   - deadline:         ORDER BY deadline ASC
 *   - relevance:        ORDER BY title match relevance (with keyword)
 *
 * @param  array $filters Key-value filter criteria
 * @param  int   $page    Current page (1-indexed)
 * @param  int   $perPage Items per page
 * @return array ['jobs' => array, 'total' => int, 'pages' => int]
 */
function searchJobs(array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array
{
    $where  = ['j.status = :status'];
    $params = [':status' => $filters['status'] ?? JOB_STATUS_ACTIVE];

    // ── Keyword search (title + description + company name) ──
    if (!empty($filters['keyword'])) {
        $keyword = trim($filters['keyword']);
        $where[] = '(j.title LIKE :kw_title OR j.description LIKE :kw_desc OR hp.company_name LIKE :kw_company)';
        $params[':kw_title']   = '%' . $keyword . '%';
        $params[':kw_desc']    = '%' . $keyword . '%';
        $params[':kw_company'] = '%' . $keyword . '%';
    }

    // ── Industry category filter ──
    if (!empty($filters['industry_category'])) {
        $where[] = 'j.industry_category = :industry';
        $params[':industry'] = $filters['industry_category'];
    }

    // ── Location filter (partial match) ──
    if (!empty($filters['location'])) {
        $where[] = 'j.location LIKE :location';
        $params[':location'] = '%' . trim($filters['location']) . '%';
    }

    // ── Job type filter ──
    if (!empty($filters['job_type'])) {
        $where[] = 'j.job_type = :job_type';
        $params[':job_type'] = $filters['job_type'];
    }

    $whereClause = implode(' AND ', $where);

    // ── Get total count ──
    $total = countJobs($filters, $whereClause, $params);

    // ── Determine sort order ──
    $sort = $filters['sort'] ?? 'newest';
    $orderBy = match ($sort) {
        'deadline'  => 'j.deadline ASC, j.created_at DESC',
        'relevance' => !empty($filters['keyword'])
            ? 'CASE WHEN j.title LIKE :sort_kw THEN 0 ELSE 1 END ASC, j.created_at DESC'
            : 'j.created_at DESC',
        default     => 'j.created_at DESC', // 'newest'
    };

    // Add sort parameter if relevance sorting with keyword
    if ($sort === 'relevance' && !empty($filters['keyword'])) {
        $params[':sort_kw'] = '%' . trim($filters['keyword']) . '%';
    }

    // ── Pagination ──
    $offset = max(0, ($page - 1) * $perPage);

    $sql = "SELECT j.*, hp.company_name, hp.industry AS company_industry,
                   hp.location AS company_location
            FROM jobs j
            JOIN hr_profiles hp ON j.hr_id = hp.user_id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset";

    $stmt = Database::getConnection()->prepare($sql);

    // Bind all named parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $jobs = $stmt->fetchAll();

    return [
        'jobs'  => $jobs,
        'total' => $total,
        'pages' => max(1, (int) ceil($total / $perPage)),
    ];
}

// ══════════════════════════════════════════════════════════════
// COUNT FUNCTION
// ══════════════════════════════════════════════════════════════

/**
 * Count total jobs matching filter criteria.
 *
 * Can be called independently or used internally by searchJobs().
 *
 * @param  array       $filters     Filter criteria (same as searchJobs)
 * @param  string|null $whereClause Pre-built WHERE clause (internal use)
 * @param  array|null  $params      Pre-built params (internal use)
 * @return int         Total matching jobs
 */
function countJobs(array $filters = [], ?string $whereClause = null, ?array $params = null): int
{
    // Build WHERE clause if not provided
    if ($whereClause === null || $params === null) {
        $where  = ['j.status = :status'];
        $params = [':status' => $filters['status'] ?? JOB_STATUS_ACTIVE];

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $where[] = '(j.title LIKE :kw_title OR j.description LIKE :kw_desc OR hp.company_name LIKE :kw_company)';
            $params[':kw_title']   = '%' . $keyword . '%';
            $params[':kw_desc']    = '%' . $keyword . '%';
            $params[':kw_company'] = '%' . $keyword . '%';
        }

        if (!empty($filters['industry_category'])) {
            $where[] = 'j.industry_category = :industry';
            $params[':industry'] = $filters['industry_category'];
        }

        if (!empty($filters['location'])) {
            $where[] = 'j.location LIKE :location';
            $params[':location'] = '%' . trim($filters['location']) . '%';
        }

        if (!empty($filters['job_type'])) {
            $where[] = 'j.job_type = :job_type';
            $params[':job_type'] = $filters['job_type'];
        }

        $whereClause = implode(' AND ', $where);
    }

    $sql = "SELECT COUNT(*) FROM jobs j
            JOIN hr_profiles hp ON j.hr_id = hp.user_id
            WHERE {$whereClause}";

    return (int) Database::query($sql, $params)->fetchColumn();
}

// ══════════════════════════════════════════════════════════════
// FILTER OPTIONS
// ══════════════════════════════════════════════════════════════

/**
 * Get distinct values for filter dropdown menus.
 *
 * Queries the database for unique locations, industries, and
 * job types from currently active jobs.
 *
 * @return array ['locations' => [], 'industries' => [], 'job_types' => []]
 */
function getFilterOptions(): array
{
    // Get distinct locations from active jobs
    $locationsSql = "SELECT DISTINCT j.location
                     FROM jobs j
                     WHERE j.status = :status AND j.location IS NOT NULL AND j.location != ''
                     ORDER BY j.location ASC";
    $locations = Database::query($locationsSql, [':status' => JOB_STATUS_ACTIVE])->fetchAll(PDO::FETCH_COLUMN);

    // Get distinct industry categories from active jobs
    $industriesSql = "SELECT DISTINCT j.industry_category
                      FROM jobs j
                      WHERE j.status = :status AND j.industry_category IS NOT NULL AND j.industry_category != ''
                      ORDER BY j.industry_category ASC";
    $industries = Database::query($industriesSql, [':status' => JOB_STATUS_ACTIVE])->fetchAll(PDO::FETCH_COLUMN);

    // Get distinct job types from active jobs
    $jobTypesSql = "SELECT DISTINCT j.job_type
                    FROM jobs j
                    WHERE j.status = :status AND j.job_type IS NOT NULL AND j.job_type != ''
                    ORDER BY j.job_type ASC";
    $jobTypes = Database::query($jobTypesSql, [':status' => JOB_STATUS_ACTIVE])->fetchAll(PDO::FETCH_COLUMN);

    return [
        'locations'  => $locations,
        'industries' => $industries,
        'job_types'  => $jobTypes,
    ];
}

// ══════════════════════════════════════════════════════════════
// SINGLE JOB & RELATED JOBS
// ══════════════════════════════════════════════════════════════

/**
 * Get a single job with full company details.
 *
 * @param  int        $jobId Job ID
 * @return array|null Job data with company info, or null if not found
 */
function getJobDetail(int $jobId): ?array
{
    $sql = "SELECT j.*, hp.company_name, hp.industry AS company_industry,
                   hp.location AS company_location, hp.phone AS company_phone,
                   hp.website AS company_website, hp.company_description,
                   u.email AS hr_email
            FROM jobs j
            JOIN hr_profiles hp ON j.hr_id = hp.user_id
            JOIN users u ON j.hr_id = u.id
            WHERE j.id = :id";

    $job = Database::query($sql, [':id' => $jobId])->fetch();
    return $job ?: null;
}

/**
 * Get related jobs in the same industry (excluding current job).
 *
 * @param  int    $jobId    Current job ID to exclude
 * @param  string $industry Industry category to match
 * @param  int    $limit    Maximum results (default 3)
 * @return array  Related job listings
 */
function getRelatedJobs(int $jobId, string $industry, int $limit = 3): array
{
    $sql = "SELECT j.*, hp.company_name
            FROM jobs j
            JOIN hr_profiles hp ON j.hr_id = hp.user_id
            WHERE j.id != :id AND j.industry_category = :industry AND j.status = :status
            ORDER BY j.created_at DESC
            LIMIT :limit";

    $stmt = Database::getConnection()->prepare($sql);
    $stmt->bindValue(':id', $jobId, PDO::PARAM_INT);
    $stmt->bindValue(':industry', $industry);
    $stmt->bindValue(':status', JOB_STATUS_ACTIVE);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Get latest active job listings.
 *
 * @param  int   $limit Maximum results (default 6)
 * @return array Latest job listings with company names
 */
function getLatestJobs(int $limit = 6): array
{
    $sql = "SELECT j.*, hp.company_name
            FROM jobs j
            JOIN hr_profiles hp ON j.hr_id = hp.user_id
            WHERE j.status = :status
            ORDER BY j.created_at DESC
            LIMIT :limit";

    $stmt = Database::getConnection()->prepare($sql);
    $stmt->bindValue(':status', JOB_STATUS_ACTIVE);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
