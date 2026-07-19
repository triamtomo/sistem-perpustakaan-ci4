# 📚 Sistem Manajemen Perpustakaan (CI4)

Aplikasi manajemen perpustakaan berbasis web yang dibangun menggunakan CodeIgniter 4, dengan fitur pencatatan buku, anggota, rak, kategori, dan proses peminjaman — dilengkapi integrasi QR Code untuk mempercepat proses transaksi.

## ✨ Fitur Utama
- **Manajemen Admin** — kontrol akses dan pengelolaan sistem
- **Manajemen Anggota** — pendataan anggota perpustakaan
- **Manajemen Rak & Kategori** — pengelompokan lokasi dan jenis buku
- **Manajemen Buku** — CRUD (Create, Read, Update, Delete) data buku secara lengkap
- **Peminjaman Buku** — pencatatan transaksi peminjaman dan pengembalian
- **QR Code Scanner** — mempercepat proses peminjaman buku melalui pemindaian kode QR

## 🛠️ Tech Stack
- **Backend:** PHP, CodeIgniter 4
- **Database:** MySQL
- **Fitur Tambahan:** Integrasi QR Code

## 🚀 Cara Menjalankan Project

1. Clone repository ini
```bash
   git clone https://github.com/triamtomo/sistem-perpustakaan-ci4.git
```
2. Install dependencies
```bash
   composer install
```
3. Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi database
4. Import struktur database ke MySQL
5. Jalankan server lokal
```bash
   php spark serve
```

## 📁 Struktur Project
Mengikuti struktur standar CodeIgniter 4 (folder `app` untuk logic aplikasi, `public` sebagai entry point web).

## 👤 Author
**Triam Tomo**
Mahasiswa S1 Informatika — Universitas Bina Sarana Informatika
