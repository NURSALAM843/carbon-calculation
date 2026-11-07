# Product Requirements Document (PRD)

## Multi-Ecosystem Carbon Accounting System: Forest, Agroforestry, Wetland & Oil Palm Growth Mutation Engine

---

### 1. Ringkasan Eksekutif (Executive Summary)

Perhitungan cadangan karbon (_Carbon Stock_) dan laju penyerapan karbon (_Carbon Sequestration_) pada lanskap tropis mencakup berbagai tipe ekosistem hutan dan tutupan lahan:

1. **Hutan Alam Tropis (Tropical Natural Forest)**: Hutan Primer (_Primary Forest_), Hutan Sekunder (_Secondary Forest_), dan Areal Bernilai Konservasi Tinggi (_High Conservation Value / HCV_).
2. **Hutan Tanaman Industri (HTI / Timber Plantations)**: Tegakan monokultur kayu budidaya seperti _Acacia mangium_, _Eucalyptus pellita_, _Pinus merkusii_, dan Sengon (_Falcataria moluccana_).
3. **Hutan Rawa Gambut & Mangrove (Wetland & Coastal Forests)**: Ekosistem lahan basah dengan cadangan karbon tanah (_Soil Organic Carbon_) dan sedimen yang sangat dalam.
4. **Agroforestri & Tanaman Perkebunan Berkayu**: Karet (_Hevea brasiliensis_), Kakao, dan Kopi ternaungi.
5. **Perkebunan Kelapa Sawit (_Elaeis guineensis_)**: Monokultur monokotil tahunan produktif yang memiliki karakteristik pertumbuhan unik.

#### Rasional Ilmiah Spesifik: Mengapa Kelapa Sawit Memiliki Modul Perhitungan Khusus?

Pohon hutan biasa (dikotil/gimnosperma pada hutan alam, HTI, dan mangrove) memiliki **kambium vaskular sejati**, di mana pertambahan biomassa terjadi melalui penebalan sekunder tahunan (_annual secondary growth_) yang dapat diprediksi secara kontinyu menggunakan diameter batang setinggi dada (DBH), tinggi pohon ($H$), dan massa jenis kayu (_wood specific gravity_ $\rho$) via model alometrik global (seperti Chave et al. 2005/2014, Brown 1997, SNI 7724).

Sebaliknya, **kelapa sawit adalah monokotil (Arecaceae) yang TIDAK memiliki kambium vaskular**. Kelapa sawit tidak mengalami penebalan diameter batang sekunder setelah tajuk awal terbentuk. Pertumbuhan biomassa sawit terjadi secara diskret melalui **mutasi fase pertumbuhan fenologis (phenological growth stage mutations)**:

- **Fase TBM 1–3 (Tanaman Belum Menghasilkan, Umur 0–3 tahun)**: Pertumbuhan didominasi oleh perkembangan perakaran adventif dan ekspansi tajuk/pelepah (hingga 30–40 pelepah per tahun). Batang tegak belum meninggi secara nyata.
- **Fase TM Muda (Tanaman Menghasilkan Muda, Umur 4–8 tahun)**: Terjadi pemanjangan batang (_stem elongation_) yang sangat pesat ($40 - 75\text{ cm/tahun}$) dan ekspansi produksi Tandan Buah Segar (TBS) secara eksponensial. Rasio akar-tajuk (_root-to-shoot ratio_) bermutasi dari tinggi ($R \approx 0.205$) menjadi lebih rendah.
- **Fase TM Prima / Dewasa (Umur 9–18 tahun)**: Laju pemanjangan batang melambat dan stabil ($30 - 45\text{ cm/tahun}$). Diameter batang konstan. Mulai terjadi siklus rutin pemangkasan pelepah (_pruning turnover_) yang menyumbang biomassa signifikan ke kolam serasah (_litter pool_).
- **Fase TM Tua / Senescence (Umur 19–25+ tahun)**: Laju pertumbuhan vegetatif menurun drastis, kanopi menipis, hingga fase _replanting_ di mana terjadi perombakan biomassa menyeluruh.

Oleh karena itu, sistem ini dirancang dengan **Polymorphic Carbon Accounting Engine** yang mampu menghitung berbagai tipe tutupan hutan, sekaligus menyediakan **Specialized Growth Mutation Engine** khusus untuk kelapa sawit.

---

### 2. Ruang Lingkup Produk (Product Scope)

#### In-Scope:

- **Hierarki Multi-Tipe Ekosistem**: Manajemen organisasi yang menaungi Estate / Unit Konsesi, terbagi menjadi Blok Lahan (_Land Blocks_) dengan klasifikasi tipe ekosistem:
    - `natural_forest` (Hutan Primer / Sekunder / HCV)
    - `hti_plantation` (Hutan Tanaman Kayu Industri: Akasia, Eukaliptus)
    - `mangrove` (Hutan Bakau Pesisir)
    - `peat_swamp_forest` (Hutan Rawa Gambut)
    - `agroforestry` (Kebun Campuran / Karet)
    - `oil_palm` (Perkebunan Kelapa Sawit dengan pelacakan mutasi fase)
- **Engine Alometrik Polimorfik**:
    - _Forest Allometric Strategy_: Chave et al. (2014), Brown (1997), SNI 7724 berbasis DBH, Tinggi Pohon, dan Wood Density ($\rho$).
    - _Mangrove Allometric Strategy_: Komiyama et al. (2005), Kauffman & Donato (2012) dengan komponen akar tunjang (_stilt roots_) dan pneumatofor.
    - _HTI Timber Strategy_: Persamaan volume tegakan dan faktor bentuk (_form factor_).
    - _Oil Palm Growth Mutation Strategy_: Persamaan alometrik Khalid et al. (1999), model logistik umur Henson (2005), dinamika pelepah hidup & pangkasan, serta pergeseran rasio _root-to-shoot_ antar fase umur (TBM $\to$ TM).
- **5 Carbon Pools Standar IPCC**:
    - _Above Ground Biomass_ (AGB)
    - _Below Ground Biomass_ (BGB)
    - _Deadwood_ (Kayu Mati Berdiri & Roboh)
    - _Litter_ (Serasah Daun & Pelepah Pangkasan)
    - _Soil Organic Carbon_ (SOC - Tanah Mineral & Lahan Gambut dengan faktor kedalaman hingga $100\text{ cm}$)
- **Perhitungan Sekuestrasi Multi-Periode ($\Delta C$)**: Pelacakan pertumbuhan biomassa, mutasi fase tegakan, dan emisi/serapan bersih tahunan ($\text{t CO}_2\text{e}/\text{ha}/\text{tahun}$).
- **Auditabilitas & Snapshot**: Penyimpanan parameter, formula, dan data input mentah pada saat kalkulasi dijalankan untuk keperluan audit ISPO, RSPO, SVLK, dan SRN-PPI.
- **Visualisasi Geospasial (GIS)**: Peta tematik sebaran blok hutan dan kebun, gradasi stok karbon (_choropleth_), dan peta laju sekuestrasi.

---

### 3. Pengguna & Persona (User Personas & RBAC)

| Peran (Role)                                | Tanggung Jawab Utama                                                                                                                                                     | Akses & Wewenang                                                                    |
| :------------------------------------------ | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :---------------------------------------------------------------------------------- |
| **Super Admin**                             | Konfigurasi sistem, manajemen entitas multi-organisasi, otorisasi pengguna.                                                                                              | Akses penuh (_Full Control_), audit log, manajemen akun & peran.                    |
| **Sustainability / Carbon Project Manager** | Mengatur metodologi, menyusun skenario sertifikasi (VCS/Verra, Plan Vivo, ISPO, RSPO), menjalankan simulasi sekuestrasi.                                                 | Manajemen metodologi, eksekusi kalkulasi, analisis perbandingan periode, pelaporan. |
| **Forestry / Field Surveyor**               | Menginput data sensus pohon hutan (DBH, spesies, tinggi) atau sensus sawit (tinggi batang, pelepah, umur fase), mengunggah sertifikat lab tanah dan berkas bukti survei. | Create & Read data inventarisasi hutan dan kebun, upload berkas bukti laboratorium. |
| **Carbon Auditor / Approver**               | Memverifikasi keabsahan metodologi, snapshot parameter, bukti laboratorium, dan menyetujui/menolak hasil kalkulasi.                                                      | Review kalkulasi, tombol Approve/Reject dengan catatan wajib, audit trails.         |
| **Executive / Board Viewer**                | Memantau portofolio karbon korporasi di seluruh ekosistem (hutan alam, HTI, mangrove, sawit).                                                                            | Read-only dashboard eksekutif, peta GIS, dan ekspor ringkasan laporan.              |

---

### 4. Kebutuhan Fungsional (Functional Requirements)

#### 4.1 Manajemen Ekosistem & Blok Lahan

- **FR-01 (Klasifikasi Ekosistem Blok)**: Setiap blok lahan wajib menentukan tipe ekosistem (`ecosystem_type`), jenis tanah (`soil_type`), luas area ($ha$), dan poligon batas spasial (GeoJSON).
- **FR-02 (Data Inventarisasi Hutan Biasa / Pohon Dikotil)**: Untuk blok berjenis hutan alam, HTI, dan mangrove, sistem mencatat data sensus tegakan: spesies pohon (_botanical species_), diameter batang setinggi dada (DBH dalam $cm$), tinggi pohon total ($m$), dan berat jenis kayu ($\rho$ dalam $\text{g/cm}^3$).
- **FR-03 (Data Mutasi Pertumbuhan Kelapa Sawit)**: Untuk blok kelapa sawit, sistem mencatat siklus tanam, varietas bibit, fase pertumbuhan (TBM 1, TBM 2, TBM 3, TM Muda, TM Prima, TM Tua), tinggi batang ($m$), rata-rata jumlah pelepah per pohon, dan rata-rata berat biomassa pelepah pangkasan.
- **FR-04 (Pengukuran Tanah Mineral & Gambut)**: Pencatatan horizon tanah, kedalaman sampel, _bulk density_, persentase C-organik, dan untuk lahan gambut mencatat tingkat kematangan gambut (fibrik, hemik, saprik).
- **FR-05 (Vault Berkas Bukti / Evidence Files)**: Unggah dokumen pendukung survei, citra drone, berita acara sensus, dan hasil lab terverifikasi hash kriptografi SHA-256.

#### 4.2 Engine Perhitungan Karbon Polimorfik

- **FR-06 (Strategi Alometrik Terpisah)**:
    - _Hutan Alam_: $AGB = 0.0673 \times (\rho \times D^2 \times H)^{0.976}$ (Chave et al. 2014).
    - _HTI_: Persamaan alometrik spesifik spesies kayu budidaya.
    - _Mangrove_: Kombinasi biomassa batang + akar tunjang/nafas (Komiyama et al. 2005).
    - _Kelapa Sawit_: $AGB_{\text{palm}} = a + b \times H$ (Khalid et al. 1999) atau fungsi umur logistik Henson (2005), ditambah biomassa tajuk pelepah dan pergeseran rasio _root-to-shoot_ sesuai fase umur.
- **FR-07 (Agregasi 5 Kolam Karbon IPCC)**: Menghitung cadangan karbon per hektar ($t\ C/ha$) dan total blok ($t\ C$ dan $t\ \text{CO}_2\text{e}$) untuk AGB, BGB, Deadwood, Litter, dan Soil.
- **FR-08 (Perhitungan Sekuestrasi Berbasis Delta Multi-Periode)**: Menghitung laju serapan tahunan ($\text{t CO}_2\text{e}/\text{ha}/\text{tahun}$) dengan membandingkan dua periode pengukuran yang telah disetujui ($t_1 \to t_2$).
- **FR-09 (Katalog Metodologi & Parameter Terversi)**: Mendukung multi-versi metodologi (IPCC 2006/2019, SNI 7724, ISPO, RSPO, Verra VM0007). Parameter dapat dikonfigurasi tanpa perubahan kode sumber.
- **FR-10 (Audit Snapshots Permanen)**: Setiap eksekusi kalkulasi menyimpan salinan persis (_immutable snapshot_) dari data input lapangan, formula yang digunakan, dan nilai parameter aktif.

#### 4.3 Alur Persetujuan & Peta GIS

- **FR-11 (Alur Verifikasi Persetujuan)**: Status kalkulasi: _Draft_ $\to$ _Pending Approval_ $\to$ _Approved_ / _Rejected_. Hasil kalkulasi yang disetujui berstatus terkunci permanen (_read-only_).
- **FR-12 (Peta GIS Interaktif)**: Visualisasi poligon batas blok dengan layer tematik choropleth stok karbon ($t\ \text{CO}_2\text{e}/ha$) dan status serapan bersih (_net sink_ vs _net source_).
- **FR-13 (Pelaporan Audit)**: Ekspor dokumen resmi PDF dan spreadsheet Excel yang mencantumkan rincian pool, snapshot parameter, dan riwayat verifikasi auditor.
