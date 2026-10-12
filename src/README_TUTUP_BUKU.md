# 📘 Panduan Tutup Tahun (Tutup Buku)

> Dokumen ini menjelaskan langkah-langkah **Tutup Buku / Tutup Tahun** pada aplikasi akuntansi.
> Lokasi di aplikasi: **Dashboard → Laporan → Tutup Buku**

---

## 🎯 Tujuan Tutup Tahun

Proses Tutup Tahun digunakan untuk:
- Menutup seluruh akun **Pendapatan** dan **Beban** ke akun **Ikhtisar Laba/Rugi**
- Memindahkan saldo akhir ke akun **Laba Ditahan (Ekuitas)**
- Menandai tahun buku sebagai **sudah ditutup**
- Mengarsipkan jurnal tahun tersebut agar laporan periode baru bersih

---

## ⚠️ Prasyarat (Wajib Dipenuhi)

Sebelum menjalankan Tutup Tahun, pastikan:

- [ ] **Periode penuh 1 Januari – 31 Desember** pada tahun yang sama
- [ ] **Semua jurnal sudah di-posting** (tidak ada jurnal berstatus *draft*)
- [ ] **Account Mapping** sudah lengkap (akun *Laba Ditahan*, *Ikhtisar Laba Rugi*, dll. sudah ditetapkan)
- [ ] **Neraca akhir tahun** sudah balance
- [ ] User memiliki hak akses:
  - `laporan.tutup_buku` → permission `post` (untuk generate/mark/archive/close)
  - `laporan.tutup_buku` → permission `delete` (untuk hapus/reset)
  - `laporan.tutup_buku` → permission `export` (untuk export Excel)

---

## 🚀 Langkah-langkah di Aplikasi

### Langkah 1 — Buka Halaman Tutup Buku
1. Login ke aplikasi
2. Masuk ke menu **Laporan** → **Tutup Buku**
3. Isi filter periode:
   - `Tanggal Awal` : **01-01-YYYY**
   - `Tanggal Akhir`: **31-12-YYYY**
4. Klik tombol **Tampilkan**

> ℹ️ Anda akan melihat ringkasan: Pendapatan, Beban, Laba/Rugi Bersih, dan Saldo Laba Ditahan.

---

### Langkah 2 — Cek Account Mapping
1. Klik tombol **Account Mapping** (tombol kuning, kanan atas)
2. Pastikan akun-akun berikut **sudah dipetakan**:
   - **Laba Ditahan** (*retained_earnings*)
   - **Ikhtisar Laba Rugi** (jika ada)
   - Akun penutup lainnya sesuai konfigurasi
3. Jika belum, simpan dulu, baru kembali ke halaman Tutup Buku

---

### Langkah 3 — Generate Jurnal Penutup
Klik tombol biru **"Generate Jurnal Penutup"**

- Sistem menjalankan: `php artisan app:generate-closing-journal`
- Jurnal otomatis dibuat dengan:
  - Nomor jurnal: `TJ-…`
  - Referensi: `AUTO-CLOSING`
  - Keterangan: mengandung `Jurnal Tutup`
- **Syarat**:
  - Periode 1 tahun penuh
  - Tidak ada jurnal draft di tahun tersebut
  - Belum ada jurnal penutup untuk tahun yang sama

✅ Jika berhasil, akan muncul notifikasi: *"Jurnal penutup otomatis berhasil dibuat dan diposting."*

---

### Langkah 4 — Tandai Tahun Tutup
Klik tombol **"Tandai Tahun Tutup"** (warna kuning/outline)

- Sistem menjalankan: `php artisan app:mark-year-closed --year=YYYY`
- Mencatat di tabel `year_closings` bahwa tahun tersebut sudah ditutup
- **Syarat**:
  - Jurnal penutup sudah dibuat
  - Jurnal penutup **balance** (Debit = Kredit)

✅ Jika berhasil, akan muncul notifikasi: *"Tahun YYYY berhasil ditandai sebagai tutup."*

---

### Langkah 5 — Arsipkan Jurnal
Klik tombol **"Arsipkan Jurnal"** (warna merah/outline)

- Sistem menjalankan: `php artisan app:archive-journal --year=YYYY`
- Memindahkan jurnal tahun tersebut ke arsip
- **Syarat**:
  - Tahun sudah ditandai tutup

✅ Jika berhasil, akan muncul notifikasi: *"Jurnal tahun YYYY berhasil diarsipkan."*

---

### Langkah 6 (Alternatif) — ⚡ Selesaikan Tutup Tahun (All-in-One)
Jika ingin **proses cepat**, gunakan tombol hitam **"Selesaikan Tutup Tahun"** di kanan.

Tombol ini akan menjalankan **4 proses sekaligus**:
1. ✅ Generate Jurnal Penutup (jika belum ada)
2. ✅ Tandai Tahun Tutup
3. ✅ Arsipkan Jurnal
4. ✅ Validasi Neraca akhir tahun balance

**Cara pakai:**
1. ☑️ Centang checkbox **"Saya yakin ingin menutup tahun ini"**
2. Klik tombol **"Selesaikan Tutup Tahun"**
3. Konfirmasi di popup

✅ Jika berhasil: *"Proses tutup tahun YYYY selesai. Jurnal penutup dibuat, tahun ditandai tutup, jurnal diarsipkan, dan Neraca akhir tahun balance."*

---

## 🧰 Tombol Pendukung

| Tombol | Fungsi | Kapan Dipakai |
|---|---|---|
| **Export Excel** | Ekspor laporan tutup buku ke `.xlsx` | Untuk dokumentasi |
| **Cetak** | Cetak laporan ke printer/PDF | Untuk dokumentasi |
| **Hapus Jurnal Penutup** | Hapus jurnal `TJ-…` / `AUTO-CLOSING` tahun tersebut | Koreksi sebelum tahun ditutup |
| **Reset Tutup Tahun** | Hapus record di tabel `year_closings` | Jika tahun sudah keliru ditandai tutup |

---

## ✅ Checklist Ringkas Tutup Tahun

```
[ 1] Semua jurnal tahun YYYY sudah di-posting
[ 2] Account Mapping lengkap (Laba Ditahan, dll)
[ 3] Buka Laporan → Tutup Buku, isi 1 Jan – 31 Des YYYY
[ 4] Generate Jurnal Penutup        → OK?
[ 5] Tandai Tahun Tutup             → OK?
[ 6] Arsipkan Jurnal                → OK?
[ 7] Cek Neraca akhir tahun balance → OK?
[ 8] Export Excel / Cetak           → OK?
[ 9] Mulai tahun baru (YYYY+1)      → 🚀
```

---

## ❓ Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| Tombol Generate disable | Ada jurnal draft / tahun sudah ditutup | Posting semua jurnal, atau cek status tutup tahun |
| *"Jurnal penutup tahun ini belum balance"* | Debit ≠ Kredit pada jurnal penutup | Hapus jurnal penutup → Generate ulang |
| *"Tahun sudah ditutup"* | Tahun sudah pernah ditutup | Gunakan **Reset Tutup Tahun** jika memang salah |
| *"Akun Laba Ditahan belum dipetakan"* | Account Mapping belum lengkap | Buka menu **Account Mapping**, tetapkan akun Laba Ditahan |
| Tombol cetak/export tidak ada | Tidak punya permission | Minta admin授予 permission `export` |

---

## 📞 Kontak

Jika menemui kendala yang tidak bisa diselesaikan, hubungi **administrator sistem** dengan menyertakan:
- Tahun yang akan ditutup
- Screenshot pesan error
- Daftar jurnal draft (jika ada)

---

**Dokumen ini dibuat otomatis dari hasil eksplorasi kode aplikasi.**
**Lokasi file:** `README_TUTUP_BUKU.md` (root project)
