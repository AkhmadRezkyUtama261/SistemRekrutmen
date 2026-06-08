-- ═══════════════════════════════════════════════════════════════
-- RecruitPro Enterprise — Seed Data
-- @Author:      BE-01 (Database Core & Security)
-- @Date:        2026-05-24
-- @Description: Sample data matching the PHP application schema precisely.
--               Password for all accounts: password123
--               Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ═══════════════════════════════════════════════════════════════

USE recruitpro_db;

-- ──────────────────────────────────────────────────────────────
-- HR ACCOUNTS (3)
-- ──────────────────────────────────────────────────────────────
INSERT INTO users (email, password_hash, role, created_at) VALUES
('hr@tokopintar.id',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', '2026-01-15 08:00:00'),
('hr@banknusantara.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', '2026-02-01 09:00:00'),
('hr@medikasehat.id',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', '2026-02-20 10:00:00');

INSERT INTO hr_profiles (user_id, company_name, industry, location, phone, website, company_description) VALUES
(1, 'PT Toko Pintar Indonesia', 'technology', 'Jakarta Selatan', '021-5551234', 'https://tokopintar.id',
 'Perusahaan e-commerce terkemuka di Indonesia yang menyediakan platform belanja online terlengkap dengan jutaan produk dari berbagai kategori.'),
(2, 'Bank Nusantara', 'finance', 'Jakarta Pusat', '021-5555678', 'https://banknusantara.id',
 'Bank swasta nasional terbesar dengan layanan perbankan digital inovatif, melayani lebih dari 20 juta nasabah di seluruh Indonesia.'),
(3, 'RS Medika Sehat', 'healthcare', 'Surabaya', '031-5559012', 'https://medikasehat.id',
 'Jaringan rumah sakit modern dengan fasilitas lengkap dan tenaga medis profesional, tersebar di 15 kota besar di Indonesia.');

-- ──────────────────────────────────────────────────────────────
-- PELAMAR ACCOUNTS (5)
-- ──────────────────────────────────────────────────────────────
INSERT INTO users (email, password_hash, role, created_at) VALUES
('budi.santoso@gmail.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pelamar', '2026-03-01 10:00:00'),
('siti.rahayu@gmail.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pelamar', '2026-03-05 11:00:00'),
('ahmad.wijaya@gmail.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pelamar', '2026-03-10 09:00:00'),
('dewi.lestari@gmail.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pelamar', '2026-03-15 14:00:00'),
('rizky.pratama@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pelamar', '2026-03-20 16:00:00');

INSERT INTO pelamar_profiles (user_id, full_name, phone, date_of_birth, address, education_level, skills) VALUES
(4, 'Budi Santoso',    '081234567890', '1998-05-15', 'Jl. Sudirman No. 123, Jakarta Selatan', 'S1', 'PHP, JavaScript, MySQL, Laravel, React'),
(5, 'Siti Rahayu',     '081298765432', '1999-08-22', 'Jl. Gatot Subroto No. 45, Jakarta Pusat', 'S1', 'Python, Data Analysis, Machine Learning, SQL, Tableau'),
(6, 'Ahmad Wijaya',    '081345678901', '1997-12-03', 'Jl. Diponegoro No. 67, Bandung', 'S2', 'Financial Analysis, Risk Management, Excel, SAP'),
(7, 'Dewi Lestari',    '081456789012', '2000-03-18', 'Jl. Ahmad Yani No. 89, Surabaya', 'S1', 'Nursing, Patient Care, Medical Records, First Aid'),
(8, 'Rizky Pratama',   '081567890123', '1996-11-07', 'Jl. Merdeka No. 12, Yogyakarta', 'S1', 'UI/UX Design, Figma, Adobe XD, HTML, CSS');

-- ──────────────────────────────────────────────────────────────
-- JOBS (10)
-- ──────────────────────────────────────────────────────────────
INSERT INTO jobs (hr_profile_id, title, description, requirements, location, job_type, industry_category, salary_range, deadline, status, created_at) VALUES
(1, 'Senior Backend Developer',
 'Kami mencari Senior Backend Developer yang berpengalaman untuk bergabung dengan tim engineering kami. Anda akan bertanggung jawab dalam merancang dan mengembangkan arsitektur backend yang scalable untuk platform e-commerce kami yang melayani jutaan pengguna.',
 '- Minimal 5 tahun pengalaman backend development\n- Menguasai PHP/Laravel atau Node.js\n- Pengalaman dengan microservices architecture\n- Familiar dengan Docker dan Kubernetes\n- Kemampuan problem-solving yang kuat',
 'Jakarta Selatan', 'full_time', 'technology', 'Rp 25.000.000 - Rp 40.000.000', '2026-07-31', 'active', '2026-04-01 09:00:00'),

(1, 'Data Analyst',
 'Posisi Data Analyst untuk menganalisis data bisnis, membuat dashboard reporting, dan memberikan insight strategis bagi management. Bekerja langsung dengan tim product dan marketing.',
 '- S1 Statistika, Matematika, atau bidang terkait\n- Menguasai SQL dan Python\n- Pengalaman dengan Tableau/Power BI\n- Kemampuan analisis dan presentasi\n- Teliti dan detail-oriented',
 'Jakarta Selatan', 'full_time', 'technology', 'Rp 15.000.000 - Rp 22.000.000', '2026-08-15', 'active', '2026-04-05 10:00:00'),

(1, 'UI/UX Design Intern',
 'Program internship 6 bulan untuk mahasiswa desain yang ingin belajar langsung tentang product design di perusahaan teknologi. Mentoring langsung dari Senior Designer.',
 '- Mahasiswa aktif semester 6+\n- Portfolio desain (Figma/Adobe XD)\n- Pemahaman dasar UX research\n- Kreatif dan mau belajar',
 'Jakarta Selatan', 'internship', 'technology', 'Rp 3.000.000 - Rp 5.000.000', '2026-06-30', 'active', '2026-04-10 11:00:00'),

(2, 'Relationship Manager',
 'Bank Nusantara membuka posisi Relationship Manager untuk mengelola portofolio nasabah premium. Anda akan menjadi konsultan keuangan pribadi bagi nasabah-nasabah terpilih.',
 '- S1 Ekonomi/Keuangan/Perbankan\n- Minimal 3 tahun pengalaman di perbankan\n- Sertifikasi WAPERD\n- Komunikasi dan negosiasi yang baik\n- Target-oriented',
 'Jakarta Pusat', 'full_time', 'finance', 'Rp 18.000.000 - Rp 30.000.000', '2026-07-15', 'active', '2026-04-03 09:00:00'),

(2, 'IT Security Analyst',
 'Bergabunglah dengan tim keamanan IT kami untuk melindungi sistem perbankan dari ancaman cyber. Tanggung jawab meliputi monitoring, incident response, dan security audit.',
 '- S1 Teknik Informatika/Cyber Security\n- Sertifikasi CEH/CISSP lebih disukai\n- Pengalaman dengan SIEM tools\n- Pemahaman ISO 27001\n- On-call readiness',
 'Jakarta Pusat', 'full_time', 'finance', 'Rp 20.000.000 - Rp 35.000.000', '2026-08-01', 'active', '2026-04-08 14:00:00'),

(2, 'Customer Service Officer',
 'Melayani nasabah dengan ramah dan profesional melalui berbagai channel komunikasi. Posisi ini cocok untuk fresh graduate yang ingin berkarir di dunia perbankan.',
 '- S1 segala jurusan\n- Fresh graduate diperbolehkan\n- Kemampuan komunikasi yang baik\n- Bahasa Inggris aktif\n- Bersedia kerja shift',
 'Jakarta Pusat', 'full_time', 'finance', 'Rp 7.000.000 - Rp 10.000.000', '2026-07-01', 'active', '2026-04-12 10:00:00'),

(3, 'Dokter Umum',
 'RS Medika Sehat membuka lowongan untuk Dokter Umum yang akan bertugas di unit gawat darurat dan poliklinik umum. Fasilitas lengkap dan lingkungan kerja profesional.',
 '- Lulusan Kedokteran dengan STR aktif\n- Sertifikasi ACLS/ATLS\n- Pengalaman minimal 2 tahun\n- Bersedia kerja shift\n- Empati dan dedikasi tinggi',
 'Surabaya', 'full_time', 'healthcare', 'Rp 20.000.000 - Rp 35.000.000', '2026-08-30', 'active', '2026-04-15 08:00:00'),

(3, 'Perawat ICU',
 'Dibutuhkan Perawat ICU berpengalaman untuk unit perawatan intensif. Bertanggung jawab dalam monitoring pasien kritis dan koordinasi dengan tim medis.',
 '- S1 Keperawatan + Ners\n- STR aktif\n- Pengalaman ICU minimal 2 tahun\n- Sertifikasi BLS\n- Teliti dan tenang di bawah tekanan',
 'Surabaya', 'contract', 'healthcare', 'Rp 10.000.000 - Rp 15.000.000', '2026-07-31', 'active', '2026-04-18 09:00:00'),

(1, 'DevOps Engineer',
 'Posisi DevOps Engineer untuk mengelola infrastruktur cloud, CI/CD pipeline, dan memastikan ketersediaan sistem 24/7.',
 '- Pengalaman dengan AWS/GCP\n- Menguasai Docker, Kubernetes, Terraform\n- Scripting (Bash, Python)\n- Pengalaman dengan monitoring tools\n- SLA management',
 'Jakarta Selatan', 'full_time', 'technology', 'Rp 22.000.000 - Rp 38.000.000', '2026-09-01', 'active', '2026-04-20 10:00:00'),

(3, 'Apoteker',
 'RS Medika Sehat membutuhkan Apoteker untuk instalasi farmasi rumah sakit. Bertanggung jawab dalam pengelolaan obat dan konsultasi farmasi.',
 '- Profesi Apoteker dengan STRA aktif\n- Pengalaman di farmasi RS minimal 1 tahun\n- Menguasai sistem informasi farmasi\n- Teliti dan bertanggung jawab',
 'Surabaya', 'full_time', 'healthcare', 'Rp 12.000.000 - Rp 18.000.000', '2026-08-15', 'active', '2026-04-22 11:00:00');

-- ──────────────────────────────────────────────────────────────
-- APPLICATIONS (15)
-- ──────────────────────────────────────────────────────────────
INSERT INTO applications (job_id, pelamar_profile_id, cover_letter, current_status, applied_at) VALUES
(1, 1, 'Saya sangat tertarik dengan posisi Senior Backend Developer di PT Toko Pintar Indonesia. Dengan pengalaman 5 tahun di PHP/Laravel dan pengalaman membangun sistem microservices, saya yakin dapat memberikan kontribusi signifikan bagi tim engineering Anda.', 'interview', '2026-04-05 14:30:00'),
(2, 2, 'Sebagai lulusan ITB jurusan Data Science, saya memiliki keahlian kuat di Python dan SQL. Saya telah menyelesaikan beberapa proyek analisis data selama kuliah dan magang.', 'under_review', '2026-04-10 09:15:00'),
(3, 5, 'Saya mahasiswa desain semester 7 di Unpad dengan portfolio UI/UX yang cukup lengkap. Program internship ini sangat cocok untuk pengembangan karir saya.', 'accepted', '2026-04-15 11:00:00'),
(4, 3, 'Dengan pengalaman 4 tahun di perbankan sebagai analyst, saya siap mengambil tantangan sebagai Relationship Manager. Saya memiliki sertifikasi WAPERD dan track record baik.', 'interview', '2026-04-08 10:00:00'),
(5, 1, 'Saya memiliki pengalaman di bidang keamanan IT dan tertarik dengan posisi IT Security Analyst di Bank Nusantara.', 'applied', '2026-04-12 16:00:00'),
(6, 2, 'Fresh graduate ITB yang tertarik berkarir di perbankan. Saya memiliki kemampuan komunikasi yang baik dan bahasa Inggris aktif.', 'rejected', '2026-04-15 08:30:00'),
(7, 4, 'Sebagai Dokter Umum dengan STR aktif dan pengalaman 3 tahun di IGD, saya tertarik bergabung dengan RS Medika Sehat.', 'under_review', '2026-04-20 09:00:00'),
(8, 4, 'Saya juga memiliki pengalaman di unit ICU dan tertarik dengan posisi Perawat ICU jika posisi Dokter Umum tidak tersedia.', 'applied', '2026-04-22 10:30:00'),
(9, 1, 'Pengalaman DevOps saya meliputi AWS, Docker, dan Kubernetes. Saya siap bergabung dengan tim infrastructure Toko Pintar.', 'under_review', '2026-04-25 13:00:00'),
(1, 5, 'Meskipun background saya desain, saya juga memiliki kemampuan frontend yang kuat dan tertarik belajar backend development.', 'rejected', '2026-04-06 15:00:00'),
(2, 3, 'Latar belakang keuangan saya dari UGM sangat relevan dengan posisi Data Analyst untuk menganalisis data bisnis perbankan.', 'applied', '2026-04-11 14:00:00'),
(4, 2, 'Saya tertarik dengan posisi Relationship Manager karena kombinasi kemampuan analitis dan interpersonal saya.', 'applied', '2026-04-09 11:30:00'),
(9, 3, 'Pengalaman saya dengan infrastructure dan cloud platforms dapat mendukung posisi DevOps Engineer ini.', 'applied', '2026-04-26 09:00:00'),
(10, 4, 'Sebagai tenaga kesehatan, saya tertarik dengan posisi Apoteker meskipun latar belakang saya keperawatan.', 'rejected', '2026-04-23 14:00:00'),
(1, 2, 'Pengalaman Python dan data engineering saya relevan dengan backend development modern.', 'applied', '2026-04-07 10:00:00');

-- ──────────────────────────────────────────────────────────────
-- STATUS HISTORY
-- ──────────────────────────────────────────────────────────────
INSERT INTO status_history (application_id, status, notes, changed_by_user_id, changed_at) VALUES
(1, 'applied', 'Lamaran dikirim oleh pelamar.', 4, '2026-04-05 14:30:00'),
(1, 'under_review', 'Profil menarik, perlu review lebih lanjut.', 1, '2026-04-07 09:00:00'),
(1, 'interview', 'Dijadwalkan interview teknis tanggal 20 April.', 1, '2026-04-15 10:00:00'),
(2, 'applied', 'Lamaran dikirim oleh pelamar.', 5, '2026-04-10 09:15:00'),
(2, 'under_review', 'Latar belakang ITB, perlu assessment.', 1, '2026-04-12 14:00:00'),
(3, 'applied', 'Lamaran dikirim oleh pelamar.', 8, '2026-04-15 11:00:00'),
(3, 'under_review', 'Portfolio bagus, jadwalkan interview.', 1, '2026-04-17 09:00:00'),
(3, 'interview', 'Interview dengan design lead.', 1, '2026-04-19 10:00:00'),
(3, 'accepted', 'Kandidat diterima, mulai 1 Juni 2026.', 1, '2026-04-25 11:00:00'),
(4, 'applied', 'Lamaran dikirim oleh pelamar.', 6, '2026-04-08 10:00:00'),
(4, 'under_review', 'Pengalaman perbankan relevan.', 2, '2026-04-10 09:00:00'),
(4, 'interview', 'Interview dengan Branch Manager.', 2, '2026-04-18 14:00:00'),
(6, 'applied', 'Lamaran dikirim oleh pelamar.', 5, '2026-04-15 08:30:00'),
(6, 'under_review', 'Review CV.', 2, '2026-04-16 10:00:00'),
(6, 'rejected', 'Belum memenuhi kualifikasi pengalaman.', 2, '2026-04-20 11:00:00'),
(10, 'applied', 'Lamaran dikirim oleh pelamar.', 8, '2026-04-06 15:00:00'),
(10, 'rejected', 'Background tidak sesuai dengan kebutuhan posisi.', 1, '2026-04-08 10:00:00'),
(14, 'applied', 'Lamaran dikirim oleh pelamar.', 7, '2026-04-23 14:00:00'),
(14, 'rejected', 'Latar belakang tidak sesuai posisi apoteker.', 3, '2026-04-24 09:00:00');
