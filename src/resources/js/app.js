import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();



// import './bootstrap';
// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// // Komponen utama halaman harga customer
// Alpine.data('priceCusPage', (kodecus) => ({
//     kodecus: String(kodecus ?? '').trim(),
//     toastMessage: '',
//     toastType: 'success',
//     updatingAll: false,
//     updateAllUrl: '',

//     init() {
//         const successMessage = this.$el.dataset.successMessage;

//         this.updateAllUrl = this.$el.dataset.updateAllUrl;

//         if (successMessage) {
//             this.showToast(successMessage, 'success');
//         }
//     },

//     showToast(message, type = 'success') {
//         this.toastMessage = message;
//         this.toastType = type;

//         setTimeout(() => {
//             this.toastMessage = '';
//         }, 5000);
//     },

//     async updateAll() {
//         if (!confirm(
//             'Update semua harga customer ini? Harga baru yang belum ada akan ditambahkan.'
//         )) {
//             return;
//         }

//         this.updatingAll = true;
//         this.toastMessage = '';

//         try {
//             const url = this.updateAllUrl.replace(
//                 '__KODECUS__',
//                 encodeURIComponent(this.kodecus)
//             );

//             const response = await fetch(url, {
//                 method: 'POST',
//                 headers: {
//                     'X-CSRF-TOKEN':
//                         document.querySelector('meta[name="csrf-token"]')?.content || '',
//                     'Content-Type': 'application/json',
//                     'Accept': 'application/json',
//                 },
//             });

//             const data = await response.json();

//             if (!response.ok || !data.success) {
//                 throw new Error(data.message || 'Gagal update harga.');
//             }

//             this.showToast(data.message, 'success');

//             setTimeout(() => {
//                 window.location.reload();
//             }, 1500);
//         } catch (error) {
//             this.showToast(
//                 'Terjadi kesalahan: ' + error.message,
//                 'error'
//             );
//         } finally {
//             this.updatingAll = false;
//         }
//     },
// }));

// // Komponen untuk setiap baris harga
// Alpine.data('priceRow', (config) => ({
//     id: config.id,
//     kode: config.kode,
//     originalPrice: Number(config.originalPrice) || 0,
//     editedPrice: Number(config.originalPrice) || 0,
//     saving: false,

//     async savePrice() {
//         const harga = parseFloat(this.editedPrice);

//         if (
//             this.editedPrice === '' ||
//             this.editedPrice === null ||
//             !Number.isFinite(harga) ||
//             harga < 0
//         ) {
//             this.$dispatch('price-saved', {
//                 message: 'Harga harus berupa angka nol atau lebih!',
//                 type: 'error',
//             });

//             this.editedPrice = this.originalPrice;
//             return;
//         }

//         this.saving = true;

//         try {
//             const root = document.querySelector(
//                 '[data-debug="transaksiPriceCustomerDetail"]'
//             );

//             const response = await fetch(root.dataset.updateRowUrl, {
//                 method: 'POST',
//                 headers: {
//                     'X-CSRF-TOKEN':
//                         document.querySelector('meta[name="csrf-token"]')?.content || '',
//                     'Content-Type': 'application/json',
//                     'Accept': 'application/json',
//                 },
//                 body: JSON.stringify({
//                     id: this.id,
//                     harga: harga,
//                 }),
//             });

//             const data = await response.json();

//             if (!response.ok || !data.success) {
//                 throw new Error(data.message || 'Gagal update harga.');
//             }

//             this.originalPrice = harga;

//             this.$dispatch('price-saved', {
//                 message: data.message,
//                 type: 'success',
//             });
//         } catch (error) {
//             this.editedPrice = this.originalPrice;

//             this.$dispatch('price-saved', {
//                 message: 'Terjadi kesalahan: ' + error.message,
//                 type: 'error',
//             });
//         } finally {
//             this.saving = false;
//         }
//     },
// }));

// // Jalankan Alpine setelah semua komponen didaftarkan
// Alpine.start();
// console.log('priceCusPage:', typeof Alpine.data('priceCusPage'));
// console.log('priceRow:', typeof Alpine.data('priceRow'));
