1.ketika alat dan kategori di pinjam tidak bisa di hapus
2.status di pinjam tdk dapat di hapus
3.end point me / profile 
4.di riwayat peinjam ada keterangan denda dan sudah di kembalikan
5.registrasi


langkah langkah set up 

2. Clone project dari GitHub
Buka terminal WSL Ubuntu:
cd ~
git clone https://github.com/fadlijhonyo-alt/projek-peminjaman-alat.git


Masuk folder:
cd ~/projek-peminjaman-alat


Kalau project sudah pernah di-clone, cukup:
git pull


3. Membuat file .env
Masuk backend:
cd backend


Buat .env:
cp .env.example .env


Buka di VS Code:
code .env


Isi konfigurasi pentingnya:

APP_NAME="Peminjaman Alat"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql-server
DB_PORT=3306
DB_DATABASE=API_ujikom
DB_USERNAME=api_ujikom
DB_PASSWORD=api_ujikom



Ingat: DB_HOST=mysql-server karena nama service MySQL di Docker kita adalah mysql-server.

4. Konfigurasi docker-compose.yml
File ini berada di folder utama project, bukan di backend.

Berikut konfigurasi kita:

services:
  mysql-server:
    image: mysql:8.0
    container_name: MySQL-API
    environment:
      MYSQL_DATABASE: API_ujikom
      MYSQL_USER: api_ujikom
      MYSQL_PASSWORD: api_ujikom
      MYSQL_ROOT_PASSWORD: passwordroot
    ports:
      - "3306:3306"
    networks:
      - API-UJIKOM
    volumes:
      - db_data:/var/lib/mysql

  applaravel:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: laravel-api
    working_dir: /var/www
    ports:
      - "8000:8000"
    volumes:
      - ./backend:/var/www
    command: php artisan serve --host=0.0.0.0 --port=8000
    depends_on:
      - mysql-server
    networks:
      - API-UJIKOM

  phpMyadmin:
    image: phpmyadmin
    container_name: PhpMyadmin-API
    environment:
      PMA_HOST: mysql-server
    ports:
      - "8081:80"
    depends_on:
      - mysql-server
    networks:
      - API-UJIKOM

volumes:
  db_data:

networks:
  API-UJIKOM:
    driver: bridge



5. Menjalankan Docker
Pastikan Docker Desktop sudah aktif dan WSL Integration sudah dicentang.

Cek Docker:
docker ps


Build dan jalankan container:
docker compose up -d --build


Cek status:
docker compose ps -a


Targetnya ada tiga container:
Container	Fungsi
MySQL-API	Database
laravel-api	Backend Laravel
PhpMyadmin-API	Pengelola database

6. Install dependency Laravel
Ini langkah yang kemarin sempat bikin error vendor/autoload.php.

Jalankan dari folder utama:
docker compose run --rm applaravel composer install


Perintah ini meng-install seluruh dependency berdasarkan composer.json dan composer.lock.
7. Generate APP_KEY
docker compose up -d


Kemudian:
docker compose exec applaravel php artisan key:generate


8. Migration dan Seeder
Untuk membuat tabel database:
docker compose exec applaravel php artisan migrate --seed


Penjelasannya:
- migrate: membuat tabel berdasarkan migration.
- --seed: mengisi data awal dari seeder.
Catatan: Ini untuk database baru. Kalau database sudah berisi data, jangan sembarangan menjalankan seeder ulang.
9. Storage Link
Untuk menghubungkan penyimpanan gambar Laravel:
docker compose exec applaravel php artisan storage:link


10. Menjalankan project
Project Peminjaman Alat
Laravel

Backend aplikasi

Port 8000

http://localhost:8000

phpMyAdmin

Pengelolaan database

Port 8081

http://localhost:8081


11. Cheat sheet: semua perintah sekaligus
Ini bagian yang paling penting. Simpan untuk nanti kalau pindah device lagi.

# 1. Clone project
git clone https://github.com/fadlijhonyo-alt/projek-peminjaman-alat.git

# 2. Masuk folder
cd ~/projek-peminjaman-alat

# 3. Buat env
cd backend
cp .env.example .env
cd ..

# 4. Build Docker
docker compose up -d --build

# 5. Install dependency
docker compose run --rm applaravel composer install

# 6. Jalankan container
docker compose up -d

# 7. Generate key
docker compose exec applaravel php artisan key:generate

# 8. Migration + seeder
docker compose exec applaravel php artisan migrate --seed

# 9. Storage link
docker compose exec applaravel php artisan storage:link

# 10. Cek container
docker compose ps

//set up ubuntu

1. Install WSL dan Ubuntu
Kita menggunakan Ubuntu melalui WSL 2 agar bisa coding Laravel menggunakan terminal Linux di Windows.

Langkah 1 — Buka PowerShell
- Klik Start Windows.
- Cari PowerShell.
- Klik kanan → Run as Administrator.
Langkah 2 — Install WSL

Jalankan:
wsl --install


Perintah ini akan meng-install WSL dan Ubuntu sebagai distro default jika belum ada.

Langkah 3 — Restart komputer

Setelah restart, Ubuntu akan terbuka dan meminta:
- UNIX username
- UNIX password
Buat username dan password sendiri.

Catatan: saat mengetik password di terminal, karakter memang tidak terlihat. Itu normal.

Langkah 4 — Cek Ubuntu

Buka PowerShell:
wsl -l -v


Pastikan Ubuntu menggunakan WSL version 2.

Kalau belum:
wsl --set-version Ubuntu 2


2. Update Ubuntu
Buka aplikasi Ubuntu, lalu:
sudo apt update && sudo apt upgrade -y


Perintah:
- apt update: memperbarui daftar paket.
- apt upgrade: memperbarui paket yang terpasang.
- sudo: menjalankan perintah dengan hak administrator.
3. Install Git
Git digunakan untuk mengambil project dari GitHub dan melakukan commit/push.
sudo apt install git -y


Cek:
git --version


Atur identitas Git:
git config --global user.name "Nama Kamu"
git config --global user.email "email-github-kamu"


4. Install VS Code










Visual Studio Code

Editor kode untuk Laravel dan PHP
Download VS Code

Setelah install, buka VS Code.

Install extension:
- WSL
- PHP Intelephense
- Laravel Extension Pack
- Docker
5. Install Docker Desktop

Docker Desktop

Menjalankan MySQL, Laravel, dan phpMyAdmin dalam container
Download Docker Desktop

Setelah instalasi:
1. Buka Docker Desktop.
2. Masuk Settings.
3. Pilih General.
4. Aktifkan WSL 2 based engine.
5. Buka Resources → WSL Integration.
6. Aktifkan Ubuntu.
7. Klik Apply & Restart.
Cek dari Ubuntu:
docker --version
docker compose version
docker ps


Kalau docker ps tidak error, berarti integrasi sudah berjalan.