# Website Booking Lapangan Futsal

Repository ini berisi proyek website untuk sistem pemesanan lapangan futsal secara online. Terdapat dua bagian utama dalam proyek ini, yaitu website untuk pengguna dan dashboard admin.
Bagian pertama adalah folder `akhir/`, yang merupakan website utama yang digunakan oleh pengguna. Di dalam website ini, pengguna dapat melihat daftar tempat atau vendor lapangan futsal, melihat detail dari masing-masing lapangan, mengisi data pemesanan, dan melakukan pembayaran. Website ini dibuat menggunakan struktur dan teknologi web standar (seperti HTML, CSS, dan PHP biasa) tanpa menggunakan framework tertentu.
Bagian kedua adalah folder `booking_lapangan_admin/`, yang merupakan dashboard admin untuk mengelola data pada sistem booking. Berbeda dengan folder user, website admin ini dibangun menggunakan framework Laravel. Di dalam dashboard ini, admin dapat mengelola data lapangan, memantau pemesanan dari pengguna, serta memverifikasi pembayaran yang masuk.

# Pembayaran
Pada sistem pembayaran Website Booking Lapangan Futsal, sistem pembayaran dari website ini menggunakan sistem pembayaran berbasis pihak ketiga yaitu Tripay. Tripay adalah pihak ketiga yang membantu pembayaran dengan menyediakan QRIS untuk pelanggan sebagai salah satu bentuk pembayaran yang dapat dibayar menggunkan M-Banking atau E-Wallet.

# Akses
Website pengguna dapat diakses melalui `http://localhost/akhir/`, dan dashboard admin melalui `http://localhost/booking_lapangan_admin/public/` (sesuai struktur Laravel).
