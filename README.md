# lab9_php_modular

 Aplikasi **Manajemen Barang** berbasis **PHP & MySQL** dengan autentikasi dan pembagian akses:
 - **Admin**: kelola data barang (CRUD)
 - **User**: akses halaman shop + cart + checkout

 Halaman utama `index.php` **hanya untuk admin**. Jika login sebagai user, akan diarahkan ke `user/shop.php`.

 ## Fitur

 - **Autentikasi**
   - Login (`modules/auth/login.php`)
   - Register akun user (`modules/auth/register.php`)
   - Logout (`modules/auth/logout.php`)
   - Password hashing (`password_hash` / `password_verify`)

 - **Admin: Manajemen Data Barang**
   - List barang
   - Tambah barang (`tambah.php`)
   - Ubah barang (`ubah.php`)
   - Hapus barang (`hapus.php`)
   - Upload gambar barang (opsional, disimpan di folder `gambar/`)

 - **User: Shop & Cart**
   - Shop (`user/shop.php`)
   - Cart (`user/cart.php`)
   - Checkout (`user/checkout.php`)

 ## Akun Demo

 - **Admin (default)**
   - Username: `admin`
   - Password: `admin123`

 Akun admin default ini otomatis dipastikan ada oleh `config/database.php`.

 ## Prasyarat

 - PHP (disarankan via **XAMPP**)
 - MySQL / MariaDB
 - Web server Apache/Nginx (umumnya Apache via XAMPP)

 ## Struktur Folder (ringkas)

 ```text
 lab9_php_modular/
 ├── admin/               # Endpoint admin sederhana
 ├── assets/              # CSS/JS
 ├── config/
 │   └── database.php     # Koneksi & setup database auth (auto-create + default admin)
 ├── gambar/              # Penyimpanan file gambar upload
 ├── modules/
 │   └── auth/            # Login / Register / Logout
 ├── user/                # Halaman shop/cart/checkout untuk role user
 ├── views/               # Header/Footer (template)
 ├── index.php            # Dashboard admin (daftar barang)
 ├── tambah.php           # CRUD barang (admin)
 ├── ubah.php
 ├── hapus.php
 ├── koneksi.php          # Koneksi database barang (default: latihan1)
 └── README.md
 ```

 ## Konfigurasi Database

 Project ini memakai **2 database**:
 - **Auth**: `lab9_php_modular` (dibuat otomatis oleh `config/database.php`)
 - **Barang**: default `latihan1` (diatur di `koneksi.php`)

 ### 1) Database Auth (otomatis)

 Saat halaman login/register diakses, file `config/database.php` akan:
 - membuat DB `lab9_php_modular` bila belum ada
 - membuat tabel `users` bila belum ada
 - memastikan akun admin default tersedia (`admin/admin123`)

 Jika ingin membuat ulang/menambah user secara manual, kamu bisa memakai `create_user.php` (opsional).

 ### 2) Database Barang (manual)

 Buat database dan tabel barang sesuai nama DB di `koneksi.php` (default: `latihan1`).

 ```sql
 CREATE DATABASE IF NOT EXISTS latihan1;
 USE latihan1;

 CREATE TABLE data_barang (
   id_barang INT(10) NOT NULL AUTO_INCREMENT,
   nama VARCHAR(100) NOT NULL,
   kategori ENUM('Elektronik','Pakaian','Makanan','Minuman','Lainnya') NOT NULL,
   harga_beli DECIMAL(10,0) NOT NULL,
   harga_jual DECIMAL(10,0) NOT NULL,
   stok INT(4) NOT NULL,
   gambar VARCHAR(100) DEFAULT NULL,
   PRIMARY KEY (id_barang)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 ```

 ## Cara Menjalankan

 - Jalankan **Apache** dan **MySQL** di XAMPP.
 - Pindahkan folder project ke `htdocs`:
   - `xampp/htdocs/lab9_php_modular`
 - Pastikan database barang (mis. `latihan1`) dan tabel `data_barang` sudah dibuat.
 - Buka:
   - `http://localhost/lab9_php_modular/`
 - Login:
   - Admin: `admin / admin123`
   - User: buat via halaman register

 ## Identitas

 - **Nama**  : Dwi Okta Ramadhani
 - **NIM**   : 312410056
 - **Kelas** : TI.24.A1
 - **Mata Kuliah** : Bahasa Pemrograman Web 1

## Alur Autentikasi Singkat

- Akses `http://localhost/lab9_php_modular/`  
  → jika belum login, redirect ke halaman login.  
- Login sukses  
  → jika role **admin** redirect ke `index.php` (Dashboard/Daftar Barang).  
  → jika role **user** redirect ke `user/shop.php`.  
- Klik **Logout** di navbar  
  → session dihapus, kembali ke halaman login.
