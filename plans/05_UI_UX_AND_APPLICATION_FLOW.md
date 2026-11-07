# Desain Antarmuka & Alur Aplikasi (UI/UX Design & Application Flow)

## Arsitektur Menu, Alur Pengguna Multi-Ekosistem, dan Komponen Antarmuka Pengguna

---

### 1. Struktur Navigasi Menu Aplikasi (Navigation Hierarchy)

Arsitektur navigasi mendukung manajemen multi-ekosistem hutan dan perkebunan, terintegrasi penuh dengan `AppSidebar.vue` dan Reka UI (shadcn-vue standard).

```
Dashboard
├── Overview                  (Ringkasan portofolio karbon lintas ekosistem, stok t CO2e, status audit)
├── Carbon Stock              (Komposisi 5 carbon pools, sebaran t CO2e/ha per ekosistem)
└── Carbon Sequestration      (Tren laju penyerapan bersih tahunan, net sink vs source)

Data Management
├── Organizations             (Profil korporasi & entitas konsesi)
├── Estates / Consessions     (Daftar kebun & konsesi hutan, luas HGU/PBPH, geolokasi)
├── Land Blocks               (Blok kebun & kompartemen hutan, tipe ekosistem, jenis tanah, poligon)
├── Crop & Vegetation Cycles  (Siklus tanam sawit, rotasi tebang HTI, atau zona konservasi hutan)
├── Forest Tree Census        (Inventarisasi pohon hutan: tag pohon, spesies, DBH, tinggi, wood density)
├── Palm Stands & Phenology   (Sensus sawit: populasi, tinggi batang, pelepah, fase mutasi TBM/TM)
├── Soil Data                 (Hasil lab tanah mineral & profil kematangan gambut / peat depth)
├── Field Measurements        (Sensus lapangan, metode sensus, luas plot sampel)
└── Evidence Vault            (Unggah berkas sertifikat lab & citra drone dengan verifikasi SHA-256)

Carbon Accounting
├── Calculation Runs          (Riwayat kalkulasi, wizard kalkulasi cerdas, detail & snapshot)
├── Carbon Pools              (Konfigurasi 5 pool: AGB, BGB, Deadwood, Litter, Soil)
├── Methodologies             (Katalog metodologi IPCC, Chave 2014, SNI 7724, ISPO, RSPO)
├── Parameters                (Katalog parameter: rasio CF, alometrik hutan & sawit terversi)
├── Scenarios & Simulation    (Simulasi dampak replanting sawit, pembalakan HTI, restorasi gambut)
└── Comparisons               (Komparasi periode t1 vs t2 untuk analisis laju penyerapan bersih)

GIS Spasial
├── Estate & Concession Map   (Peta batas konsesi kebun & batas hutan alam)
├── Block Compartment Map     (Peta sebaran seluruh poligon blok interaktif)
├── Carbon Stock Map          (Choropleth gradasi warna kepadatan stok t CO2e/ha lintas ekosistem)
└── Sequestration Map         (Gradasi hijau untuk net sink, oranye/merah untuk net source)

Reports & Compliance
├── Carbon Stock Report       (Laporan cadangan karbon siap cetak / ekspor Excel & PDF)
├── Sequestration Report      (Laporan laju penyerapan multi-periode untuk audit karbon)
├── Ecosystem Breakdown Report(Ringkasan performa per tipe tutupan: Hutan Alam, HTI, Mangrove, Sawit)
└── Audit & Verification      (Berkas audit berisikan snapshot parameter, formula, dan tanda tangan)

Administration
├── Users & Roles             (Manajemen staf surveyor hutan/kebun, manajer ESG, auditor)
├── Approvals Queue           (Kotak masuk verifikasi & persetujuan perhitungan)
├── Audit Logs                (Pencatatan kronologis aktivitas dan mutasi data sistem)
└── System Settings           (Preferensi satuan, faktor emisi default, profil sistem)
```

---

### 2. Alur Wizard Perhitungan Karbon Adaptif (Adaptive Calculation Wizard)

Wizard kalkulasi otomatis beradaptasi berdasarkan tipe ekosistem (`ecosystem_type`) dari blok lahan yang dipilih:

```
[Langkah 1: Pilih Blok Lahan] ──► Sistem mendeteksi tipe ekosistem:
                                  - Hutan Alam / HTI / Mangrove (Pohon Berkayu) ATAU
                                  - Kelapa Sawit (Mutasi Fase Pertumbuhan)
         │
         ▼
[Langkah 2: Periode & Tipe]   ──► Rentang tanggal (period_start s/d period_end)
                                  & Mode: Stok Karbon Tunggal vs Sekuestrasi Multi-Periode
         │
         ▼
[Langkah 3: Pilih Metodologi] ──► Menampilkan metodologi yang sesuai dengan ekosistem:
                                  - Hutan Alam -> Chave et al. 2014 / SNI 7724
                                  - HTI -> Volume & BEF
                                  - Mangrove -> Komiyama / Kauffman
                                  - Sawit -> Khalid et al. / Henson Logistic
         │
         ▼
[Langkah 4: Validasi Input]   ──► Form adaptif:
                                  - Jika Hutan: Cek plot sensus, distribusi DBH, spesies, ρ
                                  - Jika Sawit: Cek tinggi batang, fase TBM/TM, pelepah, umur
                                  - Cek Data Uji Tanah (Bulk Density, SOC%, Kedalaman)
         │
         ▼
[Langkah 5: Pratinjau Cepat]  ──► Engine menghitung instan & menampilkan visualisasi:
                                  - Rincian 5 Kolam Karbon (AGB, BGB, Deadwood, Litter, SOC)
                                  - Total Cadangan Karbon (t C) & Total CO2e (t CO2e)
                                  - Jika Sekuestrasi: Delta ΔC dan Laju Tahunan (t CO2e/ha/tahun)
         │
         ▼
[Langkah 6: Konfirmasi]       ──► Menyimpan Calculation Run + 3 Immutable Snapshots
                                  Status: Draft atau Ajukan ke Antrean Persetujuan
```

---

### 3. Alur Verifikasi & Persetujuan (Approval & Audit Workflow)

```
┌──────────────────┐
│   DRAFT (User)   │ ──► Surveyor/User menginput atau merevisi data sensus
└────────┬─────────┘
         │ Klik "Submit for Approval"
         ▼
┌──────────────────────┐
│  PENDING APPROVAL    │ ──► Data terkunci sementara (Freeze editing)
│  (Auditor / ESG Mgr) │     Muncul di antrean Approvals Queue
└────────┬─────────────┘
         │
         ├───► [REJECT / REVISION] ──► Mengisi catatan alasan penolakan wajib
         │                            Status kembali ke "Draft" / "Rejected"
         ▼
┌──────────────────┐
│     APPROVED     │ ──► Record TERKUNCI PERMANEN (Read-only selamanya)
│ (Auditor signed) │     Hasil tampil di Peta GIS & Dashboard Eksekutif
└──────────────────┘     Bisa dijadikan baseline periode t1 untuk kalkulasi serapan
```

---

### 4. Spesifikasi Tampilan Kunci

#### 4.1 Dashboard Komparasi Ekosistem

- **Ecosystem Selector Tab**: Memfilter metrik berdasarkan tipe ekosistem (Semua, Kelapa Sawit, Hutan Alam, HTI, Mangrove).
- **5 Carbon Pools Breakdown**: Menampilkan grafik proporsi pool (AGB, BGB, Deadwood, Litter, SOC).
- **Growth Stage Matrix**: Khusus kelapa sawit, menampilkan sebaran blok berdasarkan fase mutasi (TBM 1-3, TM Muda, TM Prima, TM Tua) dan kontribusi stok karbon tiap fase.

#### 4.2 Peta GIS Spasial Multi-Layer

- **Polygon Overlay**: Menampilkan warna batas blok berdasarkan tipe ekosistem:
    - Hijau zamrud: Hutan Alam Konservasi (HCV)
    - Hijau muda: Kelapa Sawit Produktif
    - Biru toska: Mangrove / Lahan Basah
    - Cokelat kehijauan: HTI Kayu
- **Choropleth Mode**: Menampilkan gradasi kepadatan stok karbon ($\text{t CO}_2\text{e}/\text{ha}$) atau laju penyerapan tahunan.
- **Interactive Drawer**: Klik pada blok membuka panel samping yang menampilkan riwayat sensus pohon/sawit, profil tanah, dan status kalkulasi terakhir.
