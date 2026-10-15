Lanjutkan migrasi `src_old` → `src` (Pure src Style).

Baca dulu:
1. docs/progress_migrasi.md — status lengkap progres
2. docs/milestone_lintas.md — arsitektur & style guide

Konteks: Migrasi transaksi src_old ke src sudah 85% selesai. Fokus lanjutan:
- Fase 5: sisa 8 view directory (expedisi-invoice-gabung, rent-pendingin-invoice, dll)
- 8 PDF template (invoice, kwitansi, muat ×3 transaksi)
- Test: php artisan test --filter=Transaksi (13/13 passed)
- Error terakhir: View not found di expedisi-invoice + rent-pendingin-invoice
-> WARNING !!!
Harap Kerjakan tahap per tahap ya, jangan langsung semua dikerjakan samapi selesai. CONTOH katakan ada 3 fungsi ekspedisi, rent pendingin, coolroom -> maka kerjakan satu dulu anggap expedisi kerjakan done, setelah itu programmer cek jika sudah oke, lanjut ke tahap selanjutnya anggap rent pendingin done programmer cek di browser done baru lanjut dan so on.
jangan lupa selesai coding catat progress ke path docs/progress_migrasi.md
-> upayakan untuk menambah attribut unik untuk memudahkan tracing debung programmer contoh data-debug="transaksiExpedisiCreateBtn" jadi enak klik kanan inspect copy di vscode tinggal search global maka langsung bisa pilih halaman exact
-> upayakan jangan merusak file atau kode asli src karna itu isinya akuntansi perusahaan !

Atau — lebih singkat:
"Lanjutkan project migrasi src_old→src. Baca docs/progress_migrasi.md untuk konteks. Fokus ke Fase 5 — 8 view + 8 PDF template yang belum selesai. Backend (migration, model, controller, routes, test) sudah 100% complete."