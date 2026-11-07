# Peta Jalan Implementasi & Papan Tugas (Implementation Roadmap & Task Board)

## Rencana Eksekusi Bertahap Multi-Ekosistem Berdasarkan Sub-Agent dan Milestone Proyek

---

### 1. Ringkasan Peta Jalan (Milestone Roadmap)

```
[Milestone 1: Fondasi Database Multi-Ekosistem] ──► (db-architect-agent)
                         │
                         ▼
[Milestone 2: Engine Alometrik & Mutasi Sawit]  ──► (carbon-engine-agent + qa-math-tester)
                         │
                         ▼
[Milestone 3: Backend API & Workflow Adaptif]   ──► (backend-controller-agent)
                         │
                         ▼
[Milestone 4: UI Manajemen Sensus Pohon & Sawit]──► (frontend-ui-agent)
                         │
                         ▼
[Milestone 5: Adaptive Calculation Wizard & UI] ──► (frontend-ui-agent)
                         │
                         ▼
[Milestone 6: GIS Spasial Multi-Ekosistem]      ──► (gis-spatial-agent)
                         │
                         ▼
[Milestone 7: Dashboard Portofolio & Pelaporan] ──► (frontend-ui-agent)
                         │
                         ▼
[Milestone 8: QA, Audit Ilmiah & Finalisasi]    ──► (qa-math-tester-agent)
```

---

### 2. Papan Tugas Rinci per Milestone (Task Board)

#### MILESTONE 1: Fondasi Database Multi-Ekosistem, Model & Seeder

- **Penanggung Jawab**: `db-architect-agent`
- **Target Waktu**: Tahap 1
- **Daftar Tugas**:
    - [ ] **TASK-101**: Buat migration untuk 23 tabel relasional sesuai `plans/04_DATABASE_SCHEMA_AND_DATA_DICTIONARY.md` (mencakup `land_blocks.ecosystem_type`, `palm_stands.growth_stage`, `tree_measurements`, dan `palm_measurements`).
    - [ ] **TASK-102**: Buat Eloquent Models di `app/Models/` (`Organization`, `Estate`, `LandBlock`, `CropCycle`, `PalmStand`, `FieldMeasurement`, `PalmMeasurement`, `TreeMeasurement`, `SoilMeasurement`, `EvidenceFile`, `Methodology`, `MethodologyVersion`, `CalculationParameter`, `ParameterVersion`, `CarbonPool`, `CalculationRun`, `CalculationInput`, `CalculationResult`, `CarbonPoolResult`, `CalculationComparison`, `Approval`, `AuditLog`).
    - [ ] **TASK-103**: Definisikan relasi Eloquent (`belongsTo`, `hasMany`, `hasOne`), casts modern, dan helper method pada setiap model.
    - [ ] **TASK-104**: Buat Seeder `MethodologySeeder` (memuat metodologi IPCC 2019 AFOLU, Chave et al. 2014 Hutan Alam, SNI 7724 Hutan Sekunder, ISPO GHG, dan PPKS Alometrik Sawit) dan `ParameterSeeder` (memuat parameter $CF=0.47$, $44/12$, alometrik Khalid et al., Henson logistic, dan koefisien alometrik hutan Chave).
    - [ ] **TASK-105**: Buat Seeder `DemoMultiEcosystemSeeder` (data organisasi contoh, estate/konsesi dengan blok Hutan Alam Konservasi HCV, blok HTI Akasia, blok Mangrove pesisir, dan blok Kelapa Sawit dengan berbagai fase mutasi TBM/TM).
- **Kriteria Penerimaan**:
    - Perintah `php artisan migrate:fresh --seed` berjalan sukses tanpa galat (_zero migration errors_).

---

#### MILESTONE 2: Engine Alometrik Polimorfik & Mutasi Pertumbuhan Sawit

- **Penanggung Jawab**: `carbon-engine-agent` & `qa-math-tester-agent`
- **Target Waktu**: Tahap 2
- **Daftar Tugas**:
    - [ ] **TASK-201**: Buat antarmuka `App\Contracts\Calculation\EcosystemStrategyInterface.php` dan DTOs input/output kalkulasi bertipe ketat.
    - [ ] **TASK-202**: Buat `NaturalForestStrategy.php` (menghitung AGB pohon hutan biasa berbasis DBH, tinggi, dan $\rho$ via Chave et al. 2014 & SNI 7724).
    - [ ] **TASK-203**: Buat `HtiTimberStrategy.php` (menghitung biomassa tegakan kayu budidaya seumur berbasis volume dan BEF) dan `MangroveStrategy.php` (menghitung biomassa tajuk dan akar tunjang Komiyama).
    - [ ] **TASK-204**: Buat `OilPalmGrowthMutationStrategy.php` (menghitung alometrik pemanjangan batang Khalid et al., model umur Henson, biomassa pelepah pangkasan ke kolam litter, dan mutasi rasio _root-to-shoot_ seiring umur $0.205 \to 0.180$).
    - [ ] **TASK-205**: Buat `SoilCarbonCalculator.php` (menghitung SOC tanah mineral dan lahan gambut tebal).
    - [ ] **TASK-206**: Buat `CarbonCalculationEngine.php` sebagai orchestrator yang memilih strategi berdasarkan `land_block.ecosystem_type` dan mengagregasi 5 kolam karbon IPCC.
    - [ ] **TASK-207**: Buat `SequestrationEngine.php` (menghitung delta $\Delta C$, $\Delta\text{CO}_2\text{e}$, dan laju tahunan $\text{t CO}_2\text{e}/\text{ha}/\text{tahun}$).
    - [ ] **TASK-208**: Buat Unit Tests Pest `ForestBiomassMathTest.php` dan `PalmGrowthMutationMathTest.php` untuk memvalidasi rumus terhadap benchmark ilmiah.
- **Kriteria Penerimaan**:
    - Seluruh Unit Test matematis lolos dengan presisi numerik tinggi.

---

#### MILESTONE 3: Backend Controller & Validasi Input Adaptif

- **Penanggung Jawab**: `backend-controller-agent`
- **Target Waktu**: Tahap 3
- **Daftar Tugas**:
    - [ ] **TASK-301**: Buat `CalculationRunController.php` (menangani inisiasi wizard, deteksi strategi ekosistem, pratinjau instan, dan penyimpanan run).
    - [ ] **TASK-302**: Buat `ApprovalController.php` (alur persetujuan berjenjang: _Draft_ $\to$ _Pending Approval_ $\to$ _Approved_ / _Rejected_).
    - [ ] **TASK-303**: Buat `MasterDataController.php` untuk pengelolaan Blok, Sensus Pohon Hutan, Sensus Sawit, dan Data Tanah.
    - [ ] **TASK-304**: Buat Form Requests dengan validasi adaptif (validasi DBH & spesies untuk hutan; validasi tinggi batang, pelepah, dan fase mutasi untuk sawit).
    - [ ] **TASK-305**: Implementasikan upload berkas bukti dengan kalkulasi hash SHA-256 otomatis.
    - [ ] **TASK-306**: Buat `AuditLogListener` untuk merekam mutasi entitas krusial secara otomatis.
    - [ ] **TASK-307**: Daftarkan routes di `routes/web.php` dan jalankan sinkronisasi Wayfinder `@laravel/vite-plugin-wayfinder`.
- **Kriteria Penerimaan**:
    - Route Wayfinder tergenerasi di `@/actions` dan `@/routes` tanpa galat TypeScript.

---

#### MILESTONE 4: Layout Antarmuka & Sensus Multi-Ekosistem UI

- **Penanggung Jawab**: `frontend-ui-agent`
- **Target Waktu**: Tahap 4
- **Daftar Tugas**:
    - [ ] **TASK-401**: Perbarui `resources/js/components/AppSidebar.vue` dengan navigasi multi-ekosistem.
    - [ ] **TASK-402**: Buat halaman `resources/js/pages/data/LandBlocks.vue` dengan pemilih tipe ekosistem (`ecosystem_type`).
    - [ ] **TASK-403**: Buat halaman `resources/js/pages/data/TreeCensus.vue` (input sensus pohon hutan: tag pohon, spesies, DBH, tinggi, wood density).
    - [ ] **TASK-404**: Buat halaman `resources/js/pages/data/PalmStands.vue` (input sensus sawit: tinggi batang, jumlah pelepah, selektor fase mutasi TBM/TM).
    - [ ] **TASK-405**: Buat halaman `resources/js/pages/data/SoilData.vue` dan `EvidenceVault.vue`.
- **Kriteria Penerimaan**:
    - Halaman tampil responsif, mendukung dark mode, dan bebas galat konsol.

---

#### MILESTONE 5: Wizard Kalkulasi Cerdas & Modul Akuntansi Karbon

- **Penanggung Jawab**: `frontend-ui-agent`
- **Target Waktu**: Tahap 5
- **Daftar Tugas**:
    - [ ] **TASK-501**: Buat komponen `AdaptiveCalculationWizard.vue` (menyesuaikan form input secara otomatis ketika user memilih blok hutan vs blok sawit).
    - [ ] **TASK-502**: Buat halaman `resources/js/pages/calculations/Index.vue` dan `Show.vue` (grafik 5 pool karbon, ribbon KPI, dan tombol approval).
    - [ ] **TASK-503**: Buat modal komponen `SnapshotInspector.vue` (inspeksi payload JSON input, parameter, dan formula).
    - [ ] **TASK-504**: Buat halaman `resources/js/pages/calculations/Comparisons.vue` (analisis sekuestrasi delta multi-periode).
    - [ ] **TASK-505**: Buat halaman `resources/js/pages/approvals/Index.vue` (antrean persetujuan auditor).
- **Kriteria Penerimaan**:
    - User dapat menyelesaikan simulasi dan eksekusi kalkulasi blok hutan maupun sawit dengan lancar.

---

#### MILESTONE 6: Modul GIS Spasial & Pemetaan Tematik Multi-Ekosistem

- **Penanggung Jawab**: `gis-spatial-agent`
- **Target Waktu**: Tahap 6
- **Daftar Tugas**:
    - [ ] **TASK-601**: Integrasikan Leaflet / MapLibre-gl di Vue dengan komponen `resources/js/components/maps/CarbonMapViewer.vue`.
    - [ ] **TASK-602**: Render poligon GeoJSON dengan kode warna berdasarkan tipe ekosistem (Hutan Alam, HTI, Mangrove, Sawit).
    - [ ] **TASK-603**: Buat layer tematik _Carbon Stock Choropleth_ ($\text{t CO}_2\text{e}/\text{ha}$).
    - [ ] **TASK-604**: Buat layer tematik _Sequestration Map_ (gradasi hijau untuk net sink, oranye/merah untuk net source).
    - [ ] **TASK-605**: Buat panel samping (_drawer_) interaktif saat poligon diklik.
- **Kriteria Penerimaan**:
    - Peta dapat memuat poligon secara responsif dan menampilkan gradasi warna stok karbon dengan akurat.

---

#### MILESTONE 7: Dashboard Eksekutif Portofolio Karbon & Pelaporan

- **Penanggung Jawab**: `frontend-ui-agent` & `backend-controller-agent`
- **Target Waktu**: Tahap 7
- **Daftar Tugas**:
    - [ ] **TASK-701**: Kembangkan `resources/js/pages/Dashboard.vue` dengan tab filter ekosistem dan matriks fase mutasi sawit.
    - [ ] **TASK-702**: Buat halaman `resources/js/pages/reports/Index.vue` untuk ekspor laporan.
    - [ ] **TASK-703**: Implementasikan ekspor lembar kerja Excel dan dokumen PDF yang memisahkan neraca karbon per tipe ekosistem.
- **Kriteria Penerimaan**:
    - Laporan ekspor tercetak rapi dengan format standar audit (ISPO, RSPO, SRN-PPI).

---

#### MILESTONE 8: QA Menyeluruh, Audit Ilmiah & Finalisasi

- **Penanggung Jawab**: `qa-math-tester-agent` & seluruh tim
- **Target Waktu**: Tahap 8
- **Daftar Tugas**:
    - [ ] **TASK-801**: Tulis Feature Tests Pest untuk seluruh alur kalkulasi multi-ekosistem (`tests/Feature/MultiEcosystemCalculationTest.php`).
    - [ ] **TASK-802**: Uji integritas penguncian data berstatus _approved_.
    - [ ] **TASK-803**: Jalankan `vendor/bin/pint --format agent` pada seluruh berkas PHP.
    - [ ] **TASK-804**: Jalankan `npm run types:check` dan `npm run check`.
    - [ ] **TASK-805**: Uji kinerja kalkulasi dan verifikasi kepatuhan arsitektur.
- **Kriteria Penerimaan**:
    - 100% tes Pest lulus, Pint bersih, dan tidak ada galat TypeScript.
