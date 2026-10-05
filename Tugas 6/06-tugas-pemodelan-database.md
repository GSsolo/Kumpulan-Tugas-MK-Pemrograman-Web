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

---

## 2. Simulasi Normalisasi (UNF hingga 3NF)

### Unnormalized Form (UNF)
Data mentah berupa format laporan campuran yang memuat *repeating groups* (satu mahasiswa meminjam banyak buku sekaligus dalam satu baris data).

| NIM | Nama | Jurusan | Buku_Dipinjam (ISBN, Judul, Penerbit, Alamat_Penerbit, Tgl_Pinjam, Tgl_Kembali) |
|---|---|---|---|
| 101 | Budi | Teknik | { (978-A, Basis Data, TechPress, Jkt, 01-10, 07-10), (978-B, Web Dev, CodePub, Bdg, 01-10, 08-10) } |

### First Normal Form (1NF)
Menghilangkan *repeating groups*. Setiap sel hanya berisi satu nilai atomik (terjadi redudansi data mahasiswa dan penerbit).

| NIM | Nama | Jurusan | ISBN | Judul | Penerbit | Alamat_Penerbit | Tgl_Pinjam | Tgl_Kembali |
|---|---|---|---|---|---|---|---|---|
| 101 | Budi | Teknik | 978-A | Basis Data | TechPress | Jkt | 01-10-2026 | 07-10-2026 |
| 101 | Budi | Teknik | 978-B | Web Dev | CodePub | Bdg | 01-10-2026 | 08-10-2026 |

### Second Normal Form (2NF)
Menghilangkan *partial dependency*. Atribut non-kunci harus bergantung sepenuhnya pada Primary Key. Kita memisahkan master entitas dan transaksi.

**Tabel Mahasiswa:** (NIM, Nama, Jurusan)
**Tabel Buku:** (ISBN, Judul, Penerbit, Alamat_Penerbit)
**Tabel Transaksi:** (NIM, ISBN, Tgl_Pinjam, Tgl_Kembali)

### Third Normal Form (3NF)
Menghilangkan *transitive dependency*. Atribut `Alamat_Penerbit` bergantung pada `Penerbit`, bukan langsung pada `ISBN`. Oleh karena itu, Penerbit dipisah menjadi entitas mandiri.

**Tabel Mahasiswa:** (NIM, Nama, Jurusan)
**Tabel Penerbit:** (ID_Penerbit, Nama_Penerbit, Alamat_Penerbit)
**Tabel Buku:** (ISBN, Judul, ID_Penerbit)
**Tabel Transaksi:** (ID_Transaksi, NIM, ISBN, Tgl_Pinjam, Tgl_Kembali)

*(Catatan: Desain final di bagian 3 telah mengadopsi 3NF dengan penambahan Surrogate Key `id_...` untuk optimalisasi basis data nyata).*

---

## 3. Rancangan Tabel Akhir (Skema 3NF)

### Tabel `mahasiswa`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id_mahasiswa` | INT | Primary Key, Auto Increment |
| `nim` | VARCHAR(15) | Unique Key |
| `nama` | VARCHAR(100) | Not Null |
| `email` | VARCHAR(100) | Not Null, Unique |
| `jurusan` | VARCHAR(50) | Not Null |

### Tabel `penerbit`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id_penerbit` | INT | Primary Key, Auto Increment |
| `nama_penerbit` | VARCHAR(100) | Not Null |
| `alamat` | TEXT | Nullable |
| `telepon` | VARCHAR(20) | Nullable |

### Tabel `buku`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id_buku` | INT | Primary Key, Auto Increment |
| `isbn` | VARCHAR(20) | Unique Key |
| `judul` | VARCHAR(200) | Not Null |
| `tahun_terbit` | YEAR | Not Null |
| `stok` | INT | Default 0 |
| `id_penerbit` | INT | Foreign Key (penerbit.id_penerbit) |

### Tabel `transaksi_peminjaman`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id_transaksi` | INT | Primary Key, Auto Increment |
| `id_mahasiswa` | INT | Foreign Key (mahasiswa.id_mahasiswa) |
| `id_buku` | INT | Foreign Key (buku.id_buku) |
| `tanggal_pinjam` | DATE | Not Null |
| `tanggal_tenggat` | DATE | Not Null |
| `tanggal_kembali` | DATE | Nullable |
| `status` | ENUM | ('Dipinjam', 'Dikembalikan', 'Terlambat') |

---