<?php
require __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

$pdo->beginTransaction();
try {
    // Check if HR user exists
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'hr' LIMIT 1");
    $hrUserId = $stmt->fetchColumn();

    if (!$hrUserId) {
        // Create an HR user
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (email, password_hash, role) VALUES ('hr_seeder@tokopintar.id', '$hash', 'hr')");
        $hrUserId = $pdo->lastInsertId();
    }

    // Check HR profile
    $stmt = $pdo->query("SELECT id FROM hr_profiles WHERE user_id = $hrUserId LIMIT 1");
    $hrProfileId = $stmt->fetchColumn();

    if (!$hrProfileId) {
        $pdo->exec("INSERT INTO hr_profiles (user_id, company_name, industry, location) VALUES ($hrUserId, 'TokoPintar Nusantara', 'Teknologi & IT', 'Jakarta Selatan')");
        $hrProfileId = $pdo->lastInsertId();
    }

    // Insert 20 dummy jobs
    $jobs = [
        ['title' => 'Senior Frontend Developer', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 15.000.000 - Rp 25.000.000', 'location' => 'Jakarta Selatan'],
        ['title' => 'UI/UX Designer', 'industry' => 'Design & Creative', 'type' => 'full_time', 'salary' => 'Rp 10.000.000 - Rp 18.000.000', 'location' => 'Bandung'],
        ['title' => 'Backend Engineer (Node.js)', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 14.000.000 - Rp 24.000.000', 'location' => 'Remote'],
        ['title' => 'Digital Marketing Specialist', 'industry' => 'Marketing', 'type' => 'contract', 'salary' => 'Rp 8.000.000 - Rp 12.000.000', 'location' => 'Surabaya'],
        ['title' => 'Product Manager', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 20.000.000 - Rp 35.000.000', 'location' => 'Jakarta Pusat'],
        ['title' => 'Data Analyst', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 12.000.000 - Rp 18.000.000', 'location' => 'Jakarta Selatan'],
        ['title' => 'Financial Controller', 'industry' => 'Finance', 'type' => 'full_time', 'salary' => 'Rp 18.000.000 - Rp 28.000.000', 'location' => 'Jakarta Barat'],
        ['title' => 'Graphic Designer', 'industry' => 'Design & Creative', 'type' => 'freelance', 'salary' => 'Rp 5.000.000 - Rp 10.000.000', 'location' => 'Remote'],
        ['title' => 'Social Media Manager', 'industry' => 'Marketing', 'type' => 'full_time', 'salary' => 'Rp 7.000.000 - Rp 11.000.000', 'location' => 'Yogyakarta'],
        ['title' => 'DevOps Engineer', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 16.000.000 - Rp 26.000.000', 'location' => 'Remote'],
        ['title' => 'Content Writer', 'industry' => 'Design & Creative', 'type' => 'part_time', 'salary' => 'Rp 4.000.000 - Rp 6.000.000', 'location' => 'Remote'],
        ['title' => 'Cyber Security Analyst', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 15.000.000 - Rp 25.000.000', 'location' => 'Jakarta Pusat'],
        ['title' => 'HR Business Partner', 'industry' => 'Human Resources', 'type' => 'full_time', 'salary' => 'Rp 12.000.000 - Rp 20.000.000', 'location' => 'Jakarta Selatan'],
        ['title' => 'Accountant', 'industry' => 'Finance', 'type' => 'full_time', 'salary' => 'Rp 8.000.000 - Rp 14.000.000', 'location' => 'Surabaya'],
        ['title' => 'SEO Specialist', 'industry' => 'Marketing', 'type' => 'full_time', 'salary' => 'Rp 9.000.000 - Rp 15.000.000', 'location' => 'Bali'],
        ['title' => 'Fullstack Developer (Laravel & Vue)', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 12.000.000 - Rp 20.000.000', 'location' => 'Remote'],
        ['title' => 'Mobile App Developer (Flutter)', 'industry' => 'IT & Software', 'type' => 'full_time', 'salary' => 'Rp 14.000.000 - Rp 22.000.000', 'location' => 'Bandung'],
        ['title' => 'Art Director', 'industry' => 'Design & Creative', 'type' => 'full_time', 'salary' => 'Rp 15.000.000 - Rp 25.000.000', 'location' => 'Jakarta Selatan'],
        ['title' => 'Brand Strategist', 'industry' => 'Marketing', 'type' => 'full_time', 'salary' => 'Rp 12.000.000 - Rp 18.000.000', 'location' => 'Jakarta Pusat'],
        ['title' => 'Investment Analyst', 'industry' => 'Finance', 'type' => 'full_time', 'salary' => 'Rp 14.000.000 - Rp 22.000.000', 'location' => 'Jakarta Selatan'],
    ];

    $stmt = $pdo->prepare("INSERT INTO jobs (hr_profile_id, title, description, requirements, location, job_type, industry_category, salary_range, deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    foreach ($jobs as $job) {
        $desc = "Kami mencari seorang " . $job['title'] . " yang berbakat untuk bergabung dengan tim kami. Anda akan bertanggung jawab untuk memimpin inisiatif di bidang " . $job['industry'] . " dan memberikan dampak nyata.";
        $req = "- Minimal 2 tahun pengalaman di bidang terkait.\n- Menguasai tools dan framework terkini.\n- Kemampuan komunikasi yang baik.\n- Mampu bekerja secara tim maupun individu.";
        $deadline = date('Y-m-d', strtotime('+30 days'));
        
        $stmt->execute([
            $hrProfileId,
            $job['title'],
            $desc,
            $req,
            $job['location'],
            $job['type'],
            $job['industry'],
            $job['salary'],
            $deadline
        ]);
    }

    $pdo->commit();
    echo "Successfully seeded " . count($jobs) . " jobs.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
