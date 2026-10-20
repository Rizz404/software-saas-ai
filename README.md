# Software SaaS AI

Studio produk SaaS multi-tenant dengan AI konsultan, katalog enam produk, paket Starter/Growth/Scale, narator, dan permintaan demo 60 menit. Bahasa Indonesia, Inggris, dan Jepang. Laravel 13, Blade, Alpine 3, Tailwind 4, dan Vite 8; SQLite lokal, PostgreSQL produksi.

## Produk dan permintaan demo

Flow CRM, Field Mobile, Copilot Workspace, Pulse Analytics, Access Vault, dan Desk Support adalah produk contoh. Setiap produk memiliki tiga paket dengan harga per workspace per bulan, jumlah kursi, fitur, dan periode trial contoh. Admin dapat mengelola produk dan paket melalui formulir. Seluruh harga, fitur, dan profil adalah demo yang perlu disahkan pemilik sebelum menjadi penawaran bisnis nyata.

AI SaaS Consultant membaca produk, paket, harga, knowledge, dan jadwal melalui tujuh tool yang dibatasi tenant. Pengunjung dapat membandingkan paket, memilih produk/paket, lalu mengajukan demo lewat wizard atau chat. Situs tidak melakukan penagihan, pembayaran, aktivasi langganan, atau pembuatan akun trial otomatis.

Demo berlangsung 60 menit untuk 1–6 peserta, Senin–Jumat 09.00–17.00 Asia/Jakarta, dengan jeda 12.00–13.00. Permintaan berstatus pending sampai dikonfirmasi admin. Slot ditahan dalam transaksi dengan pembaruan bersyarat; pembatalan melepaskan kapasitas satu kali. Permintaan menyimpan snapshot produk, seluruh paket, paket yang dipilih, mata uang, nama konsultan, durasi, dan biaya demo. Kontak publik: software-saas-ai@fts-tech.co.id.

## Development

PHP harus memenuhi composer.lock (setidaknya 8.4), Node >=22.12, dan Composer. Jalankan composer setup lalu php artisan db:seed --no-interaction. Setup menginstal dependency, menyiapkan .env/APP_KEY, migrasi, dan build tanpa testing. Tidak ada akun admin dengan password bawaan. Pada Windows sesi ini, konfigurasi PHP khusus proyek ada di .local/runtime.ps1 yang diabaikan Git; jalankan `. .\.local\runtime.ps1` dari PowerShell bila memerlukan binary tersebut. Konfigurasi global tidak diubah.

Pembuka berada di `/`, studio tenant di `/software-saas-ai`, katalog di `/software-saas-ai/products`, permintaan demo di `/software-saas-ai/consultation`, dan admin di `/admin`. Jalur `consultation` dipertahankan sebagai kontrak teknis penjadwalan demo; perusahaan, konsultan, knowledge, dan handover tetap memakai modul generik turunan Cafe AI.

## Produksi

Target: https://software-saas-ai.fts-tech.co.id/ . Repository: https://github.com/Rizz404/software-saas-ai, branch main. Plesk Laravel Toolkit mengelola repository, Composer, Node, build, migrasi, dan cache. Gunakan PHP 8.4, Node 24, PostgreSQL, memory_limit 256M, max_execution_time 95, dan ProxyTimeout 100 khusus vhost target. Jangan menonaktifkan validasi timestamp OPcache. Template nginx/supervisor warisan adalah referensi VPS, bukan konfigurasi Plesk.

`saas:open-slots` membuka horizon 60 hari secara idempoten tanpa mereset slot yang sudah ditahan. Jadwal harian pukul 01.00 Asia/Jakarta memakai pencegahan overlap; scheduler server harus diaktifkan melalui Toolkit. `TRUSTED_PROXIES` hanya mempercayai IP/CIDR yang diberikan dan diterapkan setelah konfigurasi dimuat.

Untuk tenant kosong: `php artisan db:seed --class=ProductionCompanySeeder --force --no-interaction`. Untuk data demo yang telah disetujui, jalankan CompanySeeder, ProductSeeder, ConsultantSeeder, ConsultantImageSeeder, ConsultationSlotSeeder, lalu CompanyKnowledgeItemSeeder. Jangan menjalankan UserSeeder/CompanyUserSeeder untuk produksi.

Buat admin dengan `php artisan saas:create-admin --email=EMAIL --name=NAME --company=software-saas-ai`. Password minimal 12 karakter dibaca dua kali secara rahasia atau melalui stdin, tidak melalui argumen, dan command menolak akun yang sudah ada. Kredensial hanya disimpan privat di server. Password sementara perlu diganti melalui prosedur administrasi aman.

LLM menggunakan LOCAL_LLM_* dan ASSISTANT_REASONING_EFFORT=none; TTS memakai OPENROUTER_* melalui config/services.php. Rahasia berada pada .env dan penyimpanan privat server, tidak di repository. MAIL_MAILER=log; mailbox tidak otomatis mengaktifkan SMTP aplikasi. Jangan kirim rahasia atau data sensitif ke chat; knowledge menjelaskan penyimpanan percakapan dan penggunaan provider AI eksternal.

Aset studio dan karakter baru di public/images/saas/{studio,consultant}.png dibuat dengan built-in image_gen. Ringkasan prompt ada di public/images/saas/prompts.json. Ilustrasi UI produk SVG dibuat khusus proyek.

## Status

Development dan build lokal/produksi selesai. Migrasi SQLite dan PostgreSQL, kompilasi view/config, serta format PHP berhasil. Subdomain memakai SSL Let's Encrypt dengan redirect HTTPS; /up dan aset build merespons 200. Akun admin dan konten demo dibuat, scheduler Toolkit aktif, dan mode Automatic disiapkan dengan satu webhook GitHub (ping 204). Rahasia admin, database, mailbox, integrasi, serta state deployment tersimpan privat di /root/deploy-secrets/software-saas-ai-*.json, dengan file 600. Tidak ada file runtime milik root pada storage dan bootstrap/cache. Peringatan nonfatal saat menautkan log vhost tidak ditangani dengan perubahan permission subscription bersama.

Metadata lockfile melaporkan dua advisory pada league/commonmark 2.10.1; patch terbatas menunggu persetujuan dependency. Tidak ada audit tersendiri yang dijalankan. Build memberi peringatan opsional fontaine untuk optimasi fallback font, tanpa menggagalkan build atau menambah dependency.

Testing tidak dijalankan karena belum diminta secara eksplisit. Tes PHP/JS, lint/audit tersendiri, QA browser, login, booking, dan provider LLM/TTS/SMTP nyata belum dijalankan. Workflow CI hanya dipicu manual melalui workflow_dispatch dan tidak menjadi gerbang deployment. Tidak ada klaim suite lulus.
