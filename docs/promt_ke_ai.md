Saya punya milestone merge project di /home/dode/project/lintas_anugrah_new/docs/milestone_lintas.md.

Sekarang project teman sudah ada di /home/dode/project/lintas_anugrah/ (src-nya ada di sini ini). Project saya lama (src_old) udah pindah ke /home/dode/project/lintas_anugrah_new/.

Tolong:

1. Baca milestone_lintas.md untuk konteks penuh
2. Bandingkan project teman (/home/dode/project/lintas_anugrah/) dengan project saya yang lama (/home/dode/project/lintas_anugrah_new/) — fokus ke migration, models, controllers, routes, views, config
3. Update Bab 1 fact-check table dengan perbandingan yang akurat (jangan asumsi, cek benar-benar ada atau tidak)
4. Update risk matrix di Bab 9 dengan temuan baru
5. Tandai milestone mana yang bisa langsung di-skip karena project teman sudah punya
6. Identifikasi conflict migration timestamp — migration yang sama timestamp tapi isi beda
7. Berikan rekomendasi urutan eksekusi yang realistis (bukan sekadar teori)
8. Jika ada schema conflict (uppercase vs lowercase), beri opsi migration data + query SQL untuk transform
9. Cek apakah RentPendinginInvoiceController benar-benar dipakai di view rentPendingin.blade.php
10. Cek apakah API Ledger endpoint di proyek teman (jika ada) kompatibel dengan yang di project saya

Report dalam format markdown dengan checkbox yang bisa langsung dipakai sebagai to-do.
🔄 Alternatif: Prompt Singkat untuk Analisa Cepat
Saya punya 2 project Laravel:
- /home/dode/project/lintas_anugrah_new/ (project saya — transaksi lengkap, sudah jadi)
- /home/dode/project/lintas_anugrah/ (project teman — ledger, mau digabung)

Tolong bandingkan keduanya dan jawab:
1. Migration mana yang konflik (sama timestamp, beda isi)?
2. Schema tabel mana yang bertolak belakang (uppercase vs lowercase)?
3. Controller/views mana yang sudah ada di project teman?
4. Dependencies apa yang belum ada di project teman?
5. Berapa estimate effort untuk merge (hari kerja)?
6. Rekomendasi urutan 5 langkah pertama?

Lihat milestone reference: docs/milestone_lintas.md