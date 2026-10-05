# Tugas Mandiri Modul 6: Perancangan ERD E-Library Kampus

**Nama:** Muhammad Nur Ichsan Putra Adnan  
**NIM:** D121241105  
**Mata Kuliah:** Pemrograman Web  

---

## 1. Spesifikasi Entitas dan Atribut (Desain Logis)

Sistem E-Library ini dirancang dengan pendekatan *best practice* industri, menggunakan *Surrogate Key* (ID Auto Increment) sebagai Primary Key (PK) untuk stabilitas relasi, dan menjadikan atribut unik nyata (seperti NIM dan ISBN) sebagai Unique Key (UK).

*   **Mahasiswa**: Menyimpan data anggota perpustakaan.
    *   `id_mahasiswa` (PK)
    *   `nim` (UK)
    *   `nama`
    *   `email`
    *   `jurusan`
*   **Penerbit**: Menyimpan entitas publisher buku.
    *   `id_penerbit` (PK)
    *   `nama_penerbit`
    *   `alamat`
    *   `telepon`
*   **Buku**: Menyimpan katalog buku yang tersedia.
    *   `id_buku` (PK)
    *   `isbn` (UK)
    *   `judul`
    *   `tahun_terbit`
    *   `stok`
    *   `id_penerbit` (FK mengarah ke Penerbit)
*   **Transaksi Peminjaman**: Menyimpan riwayat dan status peminjaman (Tabel *Junction* dengan atribut tambahan).
    *   `id_transaksi` (PK)
    *   `id_mahasiswa` (FK mengarah ke Mahasiswa)
    *   `id_buku` (FK mengarah ke Buku)
    *   `tanggal_pinjam`
    *   `tanggal_tenggat`
    *   `tanggal_kembali`
    *   `status` (Dipinjam, Dikembalikan, Terlambat)
