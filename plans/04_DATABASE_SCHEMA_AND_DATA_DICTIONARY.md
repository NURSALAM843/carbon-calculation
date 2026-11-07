# Kamus Data & Skema Database (Database Schema & Data Dictionary)

## Relasi Tabel Lengkap Multi-Ekosistem Berdasarkan Cetak Biru ERD (`plans/erd.png`)

---

### 1. Diagram Relasi Entitas Multi-Ekosistem (Entity-Relationship Overview)

Skema database dirancang untuk mendukung integritas referensial ketat, auditabilitas penuh, dan mengakomodasi polimorfisme pengukuran antara **hutan biasa (dikotil berkayu)** dan **kelapa sawit (monokotil dengan mutasi fase)**. Seluruh tabel operasional menggunakan **UUIDv4** sebagai Primary Key.

```
                      [methodologies]
                             │ 1
                             ▼ N
                 [methodology_versions]
                             │ 1
                             ▼ N
                 [calculation_parameters]
                             │ 1
                             ▼ N
                    [parameter_versions]
                             │
 [organizations]             │
        │ 1                  │
        ▼ N                  │
    [estates]                │
        │ 1                  │
        ▼ N                  │
  [land_blocks] ─────────────┼─────────────────────────┐
    │ 1          │ 1         │                         │
    ▼ N          ▼ N         │                         │
[crop_cycles] [field_measurements]                     ▼
    │ 1          │ 1         │                        1│ N
    ▼ N          ├──► [palm_measurements] (1:N)  [evidence_files]
[palm_stands]    └──► [tree_measurements] (1:N)        ▲
                             │                         │
                             ▼ N                       │
                     [calculation_runs] ───────────────┘
                             │ 1
                             ├──► [calculation_inputs] (1:N)
                             ├──► [calculation_results] (1:1) ──► [carbon_pool_results] (1:N)
                             ├──► [calculation_comparisons] (1:N)
                             ├──► [approvals] (1:N)
                             └──► [audit_logs] (Polymorphic/FK)
```

---

### 2. Kamus Data Rinci (Detailed Data Dictionary)

#### 2.1 Domain Organisasi & Geospasial

##### 1. `organizations`

Menyimpan data induk korporasi atau badan usaha pengelola lahan/konsesi.

| Nama Kolom                  | Tipe Data     | Nullable | Deskripsi & Aturan                                      |
| :-------------------------- | :------------ | :------- | :------------------------------------------------------ |
| `id`                        | `uuid`        | No       | Primary Key (UUIDv4).                                   |
| `code`                      | `string(50)`  | No       | Kode unik organisasi (contoh: `CORP-01`). Unique index. |
| `name`                      | `string(255)` | No       | Nama resmi korporasi/perusahaan.                        |
| `description`               | `text`        | Yes      | Keterangan profil perusahaan.                           |
| `created_at` / `updated_at` | `timestamp`   | No       | Waktu pembuatan dan pembaruan record.                   |

##### 2. `estates`

Unit kebun / unit konsesi hutan di bawah organisasi yang menaungi hamparan blok lahan.

| Nama Kolom        | Tipe Data       | Nullable | Deskripsi & Aturan                                                  |
| :---------------- | :-------------- | :------- | :------------------------------------------------------------------ |
| `id`              | `uuid`          | No       | Primary Key (UUIDv4).                                               |
| `organization_id` | `uuid`          | No       | Foreign Key merujuk ke `organizations.id` (onDelete: cascade).      |
| `code`            | `string(50)`    | No       | Kode unik estate/konsesi (contoh: `EST-RIAU-01`, `KONSESI-HTI-02`). |
| `name`            | `string(255)`   | No       | Nama estate/konsesi.                                                |
| `total_area_ha`   | `decimal(12,4)` | No       | Total luas terdaftar dalam hektar ($ha$).                           |
| `province`        | `string(100)`   | No       | Provinsi lokasi (contoh: Riau, Kalteng, Sumut, Papua).              |
| `regency`         | `string(100)`   | No       | Kabupaten lokasi kebun/hutan.                                       |
| `district`        | `string(100)`   | No       | Kecamatan lokasi kebun/hutan.                                       |
| `village`         | `string(100)`   | No       | Desa/Kelurahan lokasi kebun/hutan.                                  |
| `latitude`        | `decimal(10,7)` | Yes      | Titik koordinat lintang kantor estate.                              |
| `longitude`       | `decimal(10,7)` | Yes      | Titik koordinat bujur kantor estate.                                |
| `status`          | `string(20)`    | No       | Status operasional (`active`, `inactive`).                          |

##### 3. `land_blocks`

Satuan unit terkecil pengelolaan lahan (_management block / forest compartment_).

| Nama Kolom       | Tipe Data           | Nullable | Deskripsi & Aturan                                                                                                      |
| :--------------- | :------------------ | :------- | :---------------------------------------------------------------------------------------------------------------------- |
| `id`             | `uuid`              | No       | Primary Key (UUIDv4).                                                                                                   |
| `estate_id`      | `uuid`              | No       | Foreign Key merujuk ke `estates.id` (onDelete: cascade).                                                                |
| `code`           | `string(50)`        | No       | Kode blok (contoh: `BLK-A01`, `COMP-HTI-05`, `HCV-02`).                                                                 |
| `name`           | `string(255)`       | No       | Nama deskriptif blok.                                                                                                   |
| `ecosystem_type` | `string(50)`        | No       | Klasifikasi ekosistem: `oil_palm`, `natural_forest`, `hti_plantation`, `mangrove`, `peat_swamp_forest`, `agroforestry`. |
| `area_ha`        | `decimal(10,4)`     | No       | Luas efektif blok dalam hektar ($ha$).                                                                                  |
| `geometry`       | `json` / `geometry` | Yes      | Poligon koordinat batas blok (format GeoJSON Feature).                                                                  |
| `soil_type`      | `string(50)`        | No       | Klasifikasi tanah (`mineral`, `peat`, `podzolic`, `alluvium`).                                                          |
| `land_type`      | `string(50)`        | No       | Tipe tutupan lahan (`palm_productive`, `primary_forest`, `secondary_forest`, `hti_timber`, `riparian`, `hcv`).          |
| `status`         | `string(20)`        | No       | Status blok (`active`, `replanting`, `conservation`, `fallow`).                                                         |

##### 4. `crop_cycles`

Siklus pergiliran tanam sawit atau rotasi tegakan hutan budidaya pada blok tertentu.

| Nama Kolom           | Tipe Data      | Nullable | Deskripsi & Aturan                                                                                          |
| :------------------- | :------------- | :------- | :---------------------------------------------------------------------------------------------------------- |
| `id`                 | `uuid`         | No       | Primary Key (UUIDv4).                                                                                       |
| `land_block_id`      | `uuid`         | No       | Foreign Key merujuk ke `land_blocks.id`.                                                                    |
| `crop_type`          | `string(50)`   | No       | Komoditas tanaman (contoh: `Oil Palm`, `Acacia mangium`, `Eucalyptus pellita`, `Rubber`, `Natural Forest`). |
| `planting_date`      | `date`         | Yes      | Tanggal penanaman di lapangan (NULL untuk hutan alam).                                                      |
| `planting_year`      | `integer`      | Yes      | Tahun tanam (contoh: 2012, 2018).                                                                           |
| `initial_population` | `integer`      | Yes      | Jumlah bibit awal yang ditanam.                                                                             |
| `planting_density`   | `decimal(8,2)` | Yes      | Kerapatan populasi tegakan target (pohon/ha).                                                               |
| `variety`            | `string(100)`  | Yes      | Varietas bibit (contoh: DxP Marihat, Socfindo, Klon Akasia).                                                |
| `status`             | `string(20)`   | No       | Status siklus (`active`, `completed`).                                                                      |

##### 5. `palm_stands`

Kondisi populasi tegakan sawit aktual yang hidup pada suatu blok beserta fase mutasi pertumbuhannya.

| Nama Kolom                 | Tipe Data      | Nullable | Deskripsi & Aturan                                                                   |
| :------------------------- | :------------- | :------- | :----------------------------------------------------------------------------------- |
| `id`                       | `uuid`         | No       | Primary Key (UUIDv4).                                                                |
| `crop_cycle_id`            | `uuid`         | No       | Foreign Key merujuk ke `crop_cycles.id`.                                             |
| `tree_count`               | `integer`      | No       | Jumlah pohon berdiri hidup saat sensus.                                              |
| `density_per_ha`           | `decimal(8,2)` | No       | Kepadatan tegakan aktual ($\text{tree\_count} / \text{area\_ha}$).                   |
| `growth_stage`             | `string(30)`   | No       | Fase mutasi pertumbuhan: `TBM_1`, `TBM_2`, `TBM_3`, `TM_MUDA`, `TM_PRIMA`, `TM_TUA`. |
| `average_height_m`         | `decimal(6,2)` | No       | Rata-rata tinggi batang bebas pelepah (meter).                                       |
| `average_stem_diameter_cm` | `decimal(6,2)` | No       | Rata-rata diameter pangkal batang (centimeter).                                      |
| `age_years`                | `integer`      | No       | Umur tegakan sawit saat pendataan (tahun).                                           |

---

#### 2.2 Domain Pengukuran Lapangan & Laboratorium

##### 6. `field_measurements`

Sesi inventarisasi dan pengukuran lapangan pada blok tertentu.

| Nama Kolom           | Tipe Data       | Nullable | Deskripsi & Aturan                                                                               |
| :------------------- | :-------------- | :------- | :----------------------------------------------------------------------------------------------- |
| `id`                 | `uuid`          | No       | Primary Key (UUIDv4).                                                                            |
| `land_block_id`      | `uuid`          | No       | Foreign Key merujuk ke `land_blocks.id`.                                                         |
| `measurement_date`   | `date`          | No       | Tanggal pelaksanaan survei lapangan.                                                             |
| `measurement_year`   | `integer`       | No       | Tahun pengukuran.                                                                                |
| `measurement_method` | `string(50)`    | No       | Metode survei (`sample_plot_census`, `census_100_percent`, `destructive_sample`, `drone_lidar`). |
| `plot_area_m2`       | `decimal(10,2)` | Yes      | Luas plot sensus sampel dalam meter persegi ($m^2$).                                             |
| `status`             | `string(20)`    | No       | Status data (`draft`, `submitted`, `verified`).                                                  |
| `measured_by`        | `uuid`          | No       | Foreign Key merujuk ke `users.id` (surveyor).                                                    |

##### 7. `palm_measurements`

Rincian parameter morfologi sampel pohon sawit hasil survei lapangan.

| Nama Kolom                   | Tipe Data      | Nullable | Deskripsi & Aturan                               |
| :--------------------------- | :------------- | :------- | :----------------------------------------------- |
| `id`                         | `uuid`         | No       | Primary Key (UUIDv4).                            |
| `field_measurement_id`       | `uuid`         | No       | Foreign Key merujuk ke `field_measurements.id`.  |
| `sample_count`               | `integer`      | No       | Jumlah pohon sampel yang diukur.                 |
| `avg_height_m`               | `decimal(6,2)` | No       | Rata-rata tinggi batang sampel ($m$).            |
| `avg_stem_diameter_cm`       | `decimal(6,2)` | No       | Rata-rata diameter pangkal batang sampel ($cm$). |
| `avg_frond_count`            | `decimal(6,2)` | No       | Rata-rata jumlah pelepah hidup per pohon.        |
| `avg_frond_biomass_kg`       | `decimal(8,2)` | Yes      | Rata-rata berat kering 1 pelepah ($kg$).         |
| `avg_fruit_bunch_biomass_kg` | `decimal(8,2)` | Yes      | Rata-rata bobot biomassa TBS pada pohon ($kg$).  |
| `annual_pruned_fronds`       | `decimal(6,2)` | Yes      | Rata-rata pelepah dipangkas per pohon per tahun. |

##### 8. `tree_measurements`

Rincian pengukuran pohon kayu untuk ekosistem Hutan Alam, HTI, dan Mangrove.

| Nama Kolom             | Tipe Data      | Nullable | Deskripsi & Aturan                                                                          |
| :--------------------- | :------------- | :------- | :------------------------------------------------------------------------------------------ |
| `id`                   | `uuid`         | No       | Primary Key (UUIDv4).                                                                       |
| `field_measurement_id` | `uuid`         | No       | Foreign Key merujuk ke `field_measurements.id`.                                             |
| `tree_tag_number`      | `string(50)`   | Yes      | Nomor identifikasi tag pohon pada plot permanen.                                            |
| `botanical_species`    | `string(150)`  | No       | Nama spesies botani (contoh: _Shorea leprosula_, _Acacia mangium_, _Rhizophora apiculata_). |
| `wood_density_g_cm3`   | `decimal(6,3)` | No       | Berat jenis kayu kering tanur ($\rho$, contoh: $0.570$).                                    |
| `dbh_cm`               | `decimal(6,2)` | No       | Diameter batang setinggi dada ($DBH$, $cm$).                                                |
| `total_height_m`       | `decimal(6,2)` | Yes      | Tinggi total pohon ($m$).                                                                   |
| `commercial_height_m`  | `decimal(6,2)` | Yes      | Tinggi bebas cabang ($m$).                                                                  |
| `crown_diameter_m`     | `decimal(6,2)` | Yes      | Diameter tajuk pohon ($m$).                                                                 |

##### 9. `soil_measurements`

Hasil uji laboratorium sifat fisik dan kimia tanah untuk pool SOC (Mineral & Gambut).

| Nama Kolom                 | Tipe Data       | Nullable | Deskripsi & Aturan                                                    |
| :------------------------- | :-------------- | :------- | :-------------------------------------------------------------------- |
| `id`                       | `uuid`          | No       | Primary Key (UUIDv4).                                                 |
| `land_block_id`            | `uuid`          | No       | Foreign Key merujuk ke `land_blocks.id`.                              |
| `measurement_date`         | `date`          | No       | Tanggal pengambilan sampel tanah.                                     |
| `soil_type`                | `string(50)`    | No       | Tekstur/tipe tanah (Mineral Lempung, Gambut Fibrik/Hemik/Saprik).     |
| `depth_cm`                 | `decimal(6,2)`  | No       | Kedalaman horizon sampel tanah ($cm$, misal: $30.0$ atau $100.0$).    |
| `bulk_density`             | `decimal(6,3)`  | No       | Kerapatan lindak tanah ($g/cm^3$ atau $t/m^3$).                       |
| `soil_carbon_percent`      | `decimal(6,3)`  | No       | Persentase C-organik laboratorium ($\%$, misal: $2.45$ atau $48.50$). |
| `coarse_fragments_percent` | `decimal(6,3)`  | Yes      | Persentase fragmen batuan kasar ($>2\text{ mm}$, default $0.0$).      |
| `soil_organic_carbon_t_ha` | `decimal(10,4)` | No       | Hasil perhitungan baku SOC ($t\ C/ha$).                               |
| `laboratory`               | `string(150)`   | No       | Nama laboratorium terakreditasi penguji.                              |

##### 10. `evidence_files`

Dokumen lampiran bukti fisik pengujian lapangan untuk audit.

| Nama Kolom             | Tipe Data     | Nullable | Deskripsi & Aturan                                     |
| :--------------------- | :------------ | :------- | :----------------------------------------------------- |
| `id`                   | `uuid`        | No       | Primary Key (UUIDv4).                                  |
| `land_block_id`        | `uuid`        | Yes      | Foreign Key ke `land_blocks.id`.                       |
| `field_measurement_id` | `uuid`        | Yes      | Foreign Key ke `field_measurements.id`.                |
| `file_name`            | `string(255)` | No       | Nama asli file lampiran.                               |
| `file_path`            | `string(500)` | No       | Path penyimpanan di disk storage.                      |
| `file_type`            | `string(50)`  | No       | Format MIME type (PDF, JPEG, XLSX).                    |
| `hash`                 | `string(64)`  | No       | SHA-256 hash dokumen untuk pencegahan manipulasi file. |

---

#### 2.3 Domain Metodologi & Parameter Terversi

##### 11. `methodologies`

Daftar standar metodologi perhitungan karbon yang diakui secara global dan nasional.

| Nama Kolom    | Tipe Data     | Nullable | Deskripsi & Aturan                                                                                                  |
| :------------ | :------------ | :------- | :------------------------------------------------------------------------------------------------------------------ |
| `id`          | `uuid`        | No       | Primary Key (UUIDv4).                                                                                               |
| `code`        | `string(50)`  | No       | Kode metodologi (contoh: `IPCC_2019_AFOLU`, `CHAVE_2014_FOREST`, `SNI_7724_FOREST`, `ISPO_GHG_V2`, `RSPO_PALMGHG`). |
| `name`        | `string(255)` | No       | Nama lengkap standar metodologi.                                                                                    |
| `description` | `text`        | Yes      | Penjelasan ruang lingkup metodologi.                                                                                |
| `source`      | `string(255)` | No       | Sumber rujukan otoritatif (IPCC, KLHK, BSN, RSPO, Verra).                                                           |
| `status`      | `string(20)`  | No       | Status (`active`, `deprecated`).                                                                                    |

##### 12. `methodology_versions`

Versi revisi berkala dari metodologi perhitungan.

| Nama Kolom           | Tipe Data    | Nullable | Deskripsi & Aturan                             |
| :------------------- | :----------- | :------- | :--------------------------------------------- |
| `id`                 | `uuid`       | No       | Primary Key (UUIDv4).                          |
| `methodology_id`     | `uuid`       | No       | Foreign Key merujuk ke `methodologies.id`.     |
| `version`            | `string(50)` | No       | Nomor versi (contoh: `1.0.0`, `2024.1`).       |
| `effective_from`     | `date`       | No       | Tanggal mulai berlaku.                         |
| `effective_to`       | `date`       | Yes      | Tanggal berakhir (NULL jika masih berlaku).    |
| `formula_definition` | `json`       | No       | Struktur formula algoritma yang diaplikasikan. |
| `status`             | `string(20)` | No       | Status versi (`active`, `superseded`).         |

##### 13. `calculation_parameters`

Definisi variabel dan konstanta matematika dalam suatu metodologi.

| Nama Kolom               | Tipe Data     | Nullable | Deskripsi & Aturan                                                                                                      |
| :----------------------- | :------------ | :------- | :---------------------------------------------------------------------------------------------------------------------- |
| `id`                     | `uuid`        | No       | Primary Key (UUIDv4).                                                                                                   |
| `methodology_version_id` | `uuid`        | No       | Foreign Key merujuk ke `methodology_versions.id`.                                                                       |
| `code`                   | `string(50)`  | No       | Kode parameter (contoh: `CF`, `CO2_TO_C_RATIO`, `CHAVE_EXPONENT`, `ROOT_SHOOT_RATIO_MATURE`, `ROOT_SHOOT_RATIO_YOUNG`). |
| `name`                   | `string(255)` | No       | Nama pembacaan parameter.                                                                                               |
| `unit`                   | `string(50)`  | Yes      | Satuan matematis (fraksi, kg, t/ha, rasio).                                                                             |
| `description`            | `text`        | Yes      | Keterangan rujukan ilmiah parameter.                                                                                    |

##### 14. `parameter_versions`

Nilai besaran numerik aktual parameter pada rentang waktu tertentu.

| Nama Kolom                 | Tipe Data       | Nullable | Deskripsi & Aturan                                                      |
| :------------------------- | :-------------- | :------- | :---------------------------------------------------------------------- |
| `id`                       | `uuid`          | No       | Primary Key (UUIDv4).                                                   |
| `calculation_parameter_id` | `uuid`          | No       | Foreign Key merujuk ke `calculation_parameters.id`.                     |
| `numeric_value`            | `decimal(12,6)` | Yes      | Nilai numerik presisi tinggi (contoh: $0.470000$).                      |
| `value_json`               | `json`          | Yes      | Nilai berbentuk array/matriks jika alometrik bertingkat.                |
| `source`                   | `string(255)`   | No       | Publikasi asal (contoh: Chave et al. 2014, IPCC 2019 Refinement Vol 4). |
| `source_reference`         | `string(255)`   | Yes      | Tabel rujukan / nomor halaman dokumen ilmiah.                           |
| `effective_from`           | `date`          | No       | Tanggal mulai berlakunya nilai.                                         |
| `effective_to`             | `date`          | Yes      | Tanggal kedaluwarsa nilai (NULL jika aktif).                            |

---

#### 2.4 Domain Eksekusi Kalkulasi & Hasil

##### 15. `carbon_pools`

Daftar 5 kolam karbon standar IPCC.

| Nama Kolom    | Tipe Data     | Nullable | Deskripsi & Aturan                                           |
| :------------ | :------------ | :------- | :----------------------------------------------------------- |
| `id`          | `uuid`        | No       | Primary Key (UUIDv4).                                        |
| `code`        | `string(50)`  | No       | Kode unik pool (`AGB`, `BGB`, `DEADWOOD`, `LITTER`, `SOIL`). |
| `name`        | `string(100)` | No       | Nama kolam karbon.                                           |
| `description` | `text`        | Yes      | Penjelasan komponen yang masuk ke kolam ini.                 |
| `active`      | `boolean`     | No       | Status aktif (`true` / `false`).                             |

##### 16. `calculation_runs`

Sesi eksekusi perhitungan karbon pada blok lahan dan periode tertentu.

| Nama Kolom               | Tipe Data       | Nullable | Deskripsi & Aturan                                                 |
| :----------------------- | :-------------- | :------- | :----------------------------------------------------------------- |
| `id`                     | `uuid`          | No       | Primary Key (UUIDv4).                                              |
| `organization_id`        | `uuid`          | No       | Foreign Key ke `organizations.id`.                                 |
| `estate_id`              | `uuid`          | No       | Foreign Key ke `estates.id`.                                       |
| `land_block_id`          | `uuid`          | No       | Foreign Key ke `land_blocks.id`.                                   |
| `methodology_version_id` | `uuid`          | No       | Foreign Key ke `methodology_versions.id`.                          |
| `executed_by`            | `uuid`          | No       | Foreign Key ke `users.id` (eksekutor).                             |
| `ecosystem_type`         | `string(50)`    | No       | Tipe ekosistem yang dihitung pada run ini.                         |
| `period_start`           | `date`          | No       | Awal periode penilaian.                                            |
| `period_end`             | `date`          | No       | Akhir periode penilaian.                                           |
| `area_ha`                | `decimal(10,4)` | No       | Luas blok yang dihitung saat kalkulasi.                            |
| `calculation_type`       | `string(30)`    | No       | Tipe eksekusi (`stock_only`, `sequestration`, `full_accounting`).  |
| `status`                 | `string(30)`    | No       | Alur status (`draft`, `pending_approval`, `approved`, `rejected`). |
| `input_snapshot`         | `json`          | No       | Salinan persis data input lapangan saat kalkulasi.                 |
| `formula_snapshot`       | `json`          | No       | Salinan formula yang dipakai saat kalkulasi.                       |
| `parameter_snapshot`     | `json`          | No       | Salinan nilai parameter numerik saat kalkulasi.                    |
| `calculated_at`          | `timestamp`     | No       | Waktu persis kalkulasi selesai dijalankan.                         |

##### 17. `calculation_inputs`

Tabel normalisasi rincian variabel input individual dari kalkulasi run.

| Nama Kolom           | Tipe Data       | Nullable | Deskripsi & Aturan                                                                                 |
| :------------------- | :-------------- | :------- | :------------------------------------------------------------------------------------------------- |
| `id`                 | `uuid`          | No       | Primary Key (UUIDv4).                                                                              |
| `calculation_run_id` | `uuid`          | No       | Foreign Key ke `calculation_runs.id`.                                                              |
| `input_code`         | `string(50)`    | No       | Kode input (`TREE_HEIGHT`, `DBH`, `WOOD_DENSITY`, `BULK_DENSITY`, `SOC_PCT`, `PALM_GROWTH_STAGE`). |
| `numeric_value`      | `decimal(14,4)` | No       | Besaran angka input yang digunakan.                                                                |
| `unit`               | `string(50)`    | No       | Satuan input ($m$, $cm$, $g/cm^3$, $\%$, $t/ha$).                                                  |
| `source_reference`   | `json`          | Yes      | Rujukan ID record tabel lapangan asal (`field_measurement_id`, dll).                               |

##### 18. `calculation_results`

Hasil agregat utama dari eksekusi perhitungan stok dan penyerapan.

| Nama Kolom                  | Tipe Data       | Nullable | Deskripsi & Aturan                                                     |
| :-------------------------- | :-------------- | :------- | :--------------------------------------------------------------------- |
| `id`                        | `uuid`          | No       | Primary Key (UUIDv4).                                                  |
| `calculation_run_id`        | `uuid`          | No       | Foreign Key ke `calculation_runs.id` (1:1).                            |
| `total_biomass_t`           | `decimal(14,4)` | No       | Total seluruh biomassa kering blok lahan ($ton$).                      |
| `total_carbon_t`            | `decimal(14,4)` | No       | Total cadangan karbon murni blok ($ton\ C$).                           |
| `total_co2e_t`              | `decimal(14,4)` | No       | Total setara emisi karbon dioksida ($ton\ \text{CO}_2\text{e}$).       |
| `carbon_stock_t_ha`         | `decimal(10,4)` | No       | Kepadatan cadangan karbon ($t\ C/ha$).                                 |
| `co2e_stock_t_ha`           | `decimal(10,4)` | No       | Kepadatan setara $\text{CO}_2$ ($t\ \text{CO}_2\text{e}/ha$).          |
| `sequestration_t_co2e_year` | `decimal(10,4)` | Yes      | Laju serapan tahunan ($t\ \text{CO}_2\text{e}/tahun$, jika komparasi). |

##### 19. `carbon_pool_results`

Rincian kontribusi masing-masing dari 5 pool karbon terhadap hasil total.

| Nama Kolom              | Tipe Data       | Nullable | Deskripsi & Aturan                                             |
| :---------------------- | :-------------- | :------- | :------------------------------------------------------------- |
| `id`                    | `uuid`          | No       | Primary Key (UUIDv4).                                          |
| `calculation_result_id` | `uuid`          | No       | Foreign Key ke `calculation_results.id`.                       |
| `carbon_pool_id`        | `uuid`          | No       | Foreign Key ke `carbon_pools.id`.                              |
| `biomass_t`             | `decimal(14,4)` | No       | Biomassa kering pada pool ini ($ton$).                         |
| `carbon_t`              | `decimal(14,4)` | No       | Karbon tersimpan pada pool ini ($ton\ C$).                     |
| `co2e_t`                | `decimal(14,4)` | No       | Setara $\text{CO}_2$ pool ini ($ton\ \text{CO}_2\text{e}$).    |
| `co2e_per_ha`           | `decimal(10,4)` | No       | Setara $\text{CO}_2$ per hektar ($t\ \text{CO}_2\text{e}/ha$). |

##### 20. `calculation_comparisons`

Menghubungkan dua calculation run untuk menghitung delta penyerapan.

| Nama Kolom                    | Tipe Data       | Nullable | Deskripsi & Aturan                                                                |
| :---------------------------- | :-------------- | :------- | :-------------------------------------------------------------------------------- |
| `id`                          | `uuid`          | No       | Primary Key (UUIDv4).                                                             |
| `calculation_run_id`          | `uuid`          | No       | Foreign Key run periode saat ini ($t_2$).                                         |
| `previous_calculation_run_id` | `uuid`          | No       | Foreign Key run periode sebelumnya ($t_1$).                                       |
| `delta_carbon_t`              | `decimal(14,4)` | No       | Selisih total karbon ($\Delta C = C_{t_2} - C_{t_1}$).                            |
| `delta_co2e_t`                | `decimal(14,4)` | No       | Selisih setara $\text{CO}_2$ ($\Delta \text{CO}_2\text{e}$).                      |
| `annualized_co2e_t`           | `decimal(14,4)` | No       | Serapan tahunan normalisasi ($\Delta \text{CO}_2\text{e} / \Delta \text{years}$). |

---

#### 2.5 Domain Persetujuan, Pengguna & Audit

##### 21. `approvals`

Catatan persetujuan berjenjang terhadap calculation run oleh auditor/manajer.

| Nama Kolom           | Tipe Data    | Nullable | Deskripsi & Aturan                                               |
| :------------------- | :----------- | :------- | :--------------------------------------------------------------- |
| `id`                 | `uuid`       | No       | Primary Key (UUIDv4).                                            |
| `calculation_run_id` | `uuid`       | No       | Foreign Key ke `calculation_runs.id`.                            |
| `reviewer_id`        | `uuid`       | No       | Foreign Key ke `users.id` (pejabat peninjau).                    |
| `status`             | `string(30)` | No       | Status keputusan (`approved`, `rejected`, `revision_requested`). |
| `notes`              | `text`       | Yes      | Catatan ulasan audit atau alasan penolakan.                      |
| `reviewed_at`        | `timestamp`  | No       | Waktu persetujuan/penolakan dilakukan.                           |

##### 22. `organization_users`

Pivot multi-tenancy yang mengatur keanggotaan pengguna dan perannya dalam organisasi.

| Nama Kolom        | Tipe Data    | Nullable | Deskripsi & Aturan                                                              |
| :---------------- | :----------- | :------- | :------------------------------------------------------------------------------ |
| `id`              | `uuid`       | No       | Primary Key (UUIDv4).                                                           |
| `organization_id` | `uuid`       | No       | Foreign Key ke `organizations.id`.                                              |
| `user_id`         | `uuid`       | No       | Foreign Key ke `users.id`.                                                      |
| `role`            | `string(50)` | No       | Peran pengguna (`super_admin`, `esg_manager`, `surveyor`, `auditor`, `viewer`). |

##### 23. `audit_logs`

Pencatatan riwayat setiap aksi perubahan data penting dalam sistem.

| Nama Kolom    | Tipe Data     | Nullable | Deskripsi & Aturan                                                              |
| :------------ | :------------ | :------- | :------------------------------------------------------------------------------ |
| `id`          | `uuid`        | No       | Primary Key (UUIDv4).                                                           |
| `user_id`     | `uuid`        | Yes      | Foreign Key ke `users.id` (eksekutor).                                          |
| `action`      | `string(50)`  | No       | Tipe tindakan (`create`, `update`, `delete`, `calculate`, `approve`, `reject`). |
| `entity_type` | `string(100)` | No       | Model entitas yang terpengaruh (contoh: `CalculationRun`).                      |
| `entity_id`   | `uuid`        | No       | ID entitas yang terpengaruh.                                                    |
| `old_values`  | `json`        | Yes      | State nilai data sebelum perubahan.                                             |
| `new_values`  | `json`        | Yes      | State nilai data setelah perubahan.                                             |
| `created_at`  | `timestamp`   | No       | Waktu pencatatan log audit.                                                     |
