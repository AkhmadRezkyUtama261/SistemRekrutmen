# Sistem Rekrutmen - RecruitPro Enterprise

Landing Page
<img width="1918" height="967" alt="Cuplikan layar 2026-06-14 233433" src="https://github.com/user-attachments/assets/f9c9759d-e641-4e3b-a0d4-7d274cfbc2f5" />
<img width="1918" height="905" alt="Cuplikan layar 2026-06-14 233445" src="https://github.com/user-attachments/assets/03869dd0-608c-4c3b-8bb5-d4145832ef9a" />
<img width="1917" height="911" alt="Cuplikan layar 2026-06-14 233459" src="https://github.com/user-attachments/assets/fec74d3b-25c9-4757-8674-3faf7d74f24d" />
<img width="1918" height="912" alt="Cuplikan layar 2026-06-14 233520" src="https://github.com/user-attachments/assets/46e6409b-ed63-4219-9557-a2872db3168f" />
<img width="1917" height="907" alt="Cuplikan layar 2026-06-14 233526" src="https://github.com/user-attachments/assets/ac92a90f-66a6-41a9-9c6e-1ba08136d810" />

Register
<img width="1918" height="900" alt="Cuplikan layar 2026-06-14 233804" src="https://github.com/user-attachments/assets/2d2d1086-bfef-4118-bde0-ae5ccd837a05" />
<img width="1918" height="912" alt="Cuplikan layar 2026-06-14 233827" src="https://github.com/user-attachments/assets/26b89e85-4b6d-475b-b5a1-81a070725ce9" />

Login
<img width="1918" height="897" alt="Cuplikan layar 2026-06-14 233913" src="https://github.com/user-attachments/assets/d96d895c-e765-481a-af93-84a98cd7fb2e" />
<img width="1918" height="897" alt="Cuplikan layar 2026-06-14 233929" src="https://github.com/user-attachments/assets/312dec75-1f70-44ef-aa2c-04666a7f41ac" />

Pelamar

<img width="1918" height="907" alt="Cuplikan layar 2026-06-14 234024" src="https://github.com/user-attachments/assets/21e6b6f7-79c1-4d04-8685-5dde578d6e7a" />
<img width="1918" height="902" alt="Cuplikan layar 2026-06-14 234032" src="https://github.com/user-attachments/assets/3e420267-a2b1-48bd-99c5-ff0955109b38" />
<img width="1918" height="908" alt="Cuplikan layar 2026-06-14 234043" src="https://github.com/user-attachments/assets/83cc31ce-e178-4c82-8db8-ab61ceda20fa" />
<img width="1918" height="905" alt="Cuplikan layar 2026-06-14 234049" src="https://github.com/user-attachments/assets/45a671a2-7ff8-40bf-99f5-5ca83556acb1" />

<img width="1918" height="900" alt="Cuplikan layar 2026-06-14 234507" src="https://github.com/user-attachments/assets/79277db4-c9ba-4546-923f-cbaed7afa2ae" />
<img width="1918" height="906" alt="Cuplikan layar 2026-06-14 234411" src="https://github.com/user-attachments/assets/d157ad0b-c97f-4de4-9e42-ad761e8f5414" />
<img width="1917" height="896" alt="Cuplikan layar 2026-06-14 234421" src="https://github.com/user-attachments/assets/306611e6-d439-4c2b-a1be-78cae0db6083" />

Profil Pelamar



Sistem rekrutmen berbasis web untuk memudahkan pelamar mencari pekerjaan dan memudahkan HRD dalam mengelola lowongan serta memonitor status pelamar. Proyek ini dibangun menggunakan **PHP Native (PDO)**, **MySQL**, dan **Tailwind CSS**.

## Prasyarat
- XAMPP / MAMP / WAMP (PHP 8.0+ disarankan)
- MySQL Server (sudah termasuk di XAMPP)
- Web Browser modern (Chrome, Firefox, Safari)

## Cara Instalasi & Menjalankan Aplikasi

1. **Clone Repository**
   Clone repository ini ke dalam folder `htdocs` (jika menggunakan XAMPP) atau `www` (jika menggunakan WAMP).
   ```bash
   git clone https://github.com/AkhmadRezkyUtama261/SistemRekrutmen.git recruitment-enterprise
   ```

2. **Jalankan Apache & MySQL**
   Buka XAMPP Control Panel, lalu klik **Start** pada modul `Apache` dan `MySQL`.

3. **Setup Database**
   - Buka browser dan akses **phpMyAdmin** (biasanya di `http://localhost/phpmyadmin`).
   - Buat database baru dengan nama `recruitpro_db`.
   - Buka tab **Import**, lalu pilih file `sql/schema.sql` dan klik *Go* untuk membuat tabel-tabel.
   - (Opsional) Untuk mengisi data dummy/contoh, pilih file `sql/seed.sql` dan klik *Go*.

4. **Konfigurasi Database**
   Secara default, aplikasi menggunakan *username* `root` dan *password* kosong `''`. Jika kredensial MySQL Anda berbeda, buka file `config/app.php` dan sesuaikan pada bagian berikut:
   ```php
   define('DB_HOST',    '127.0.0.1');
   define('DB_USER',    'root');
   define('DB_PASS',    ''); // Isi jika ada password
   define('DB_NAME',    'recruitpro_db');
   ```

5. **Akses Aplikasi**
   Buka web browser dan akses URL berikut:
   ```
   http://localhost/recruitment-enterprise
   ```

## Akun Demo (Jika sudah melakukan Import seed.sql)

Untuk mempermudah pengujian, Anda bisa masuk menggunakan salah satu akun berikut:

**Akun Pelamar:**
- Email: `budi.santoso@gmail.com`
- Password: `password123`

**Akun HRD / Perusahaan:**
- Email: `hr@tokopintar.id`
- Password: `password123`

## Struktur Folder Utama
- `assets/` - Berisi file statis (CSS, JS, Gambar)
- `auth/` - Modul autentikasi (Login, Register, Logout)
- `components/` - Bagian antarmuka yang bisa digunakan ulang (Header, Footer)
- `config/` - Konfigurasi sistem dan Database PDO
- `hr/` - Dashboard & modul khusus HRD
- `pelamar/` - Dashboard & modul pencarian lowongan untuk Pelamar
- `sql/` - File database (Schema & Seeder)
- `uploads/` - Tempat penyimpanan file unggahan (CV, Foto)

Semoga sukses untuk ujian/deploy aplikasinya! 🚀
