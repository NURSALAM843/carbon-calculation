# Spesifikasi Tenaga Kerja Sub-Agent (Subagents Workforce Specification)

## Definisi Tim Agen AI Terspesialisasi untuk Proyek Multi-Ekosistem & Mutasi Pertumbuhan Sawit

Untuk memastikan setiap bagian pekerjaan dikerjakan dengan standar arsitektur tertinggi tanpa tumpang tindih, proyek ini membagi pekerjaan ke dalam **6 Sub-Agent Khusus**.

---

### 1. Matriks Peran & Tanggung Jawab Sub-Agent

```
┌────────────────────────────────────────────────────────────────────────┐
│                      SUB-AGENT WORKFORCE ARCHITECTURE                  │
├──────────────────────────┬─────────────────────────────────────────────┤
│ 1. db-architect-agent    │ Migrations, UUIDs, Tree & Palm Measurement  │
│ 2. carbon-engine-agent   │ Polymorphic Strategies, Chave, Palm Phenology│
│ 3. backend-controller    │ Controllers, Adaptive Validation, Wayfinder │
│ 4. frontend-ui-agent     │ Adaptive Wizard, Inertia v3, Vue 3.5, Reka  │
│ 5. gis-spatial-agent     │ GeoJSON, Multi-Ecosystem Choropleth Polygons│
│ 6. qa-math-tester-agent  │ Pest Tests, Forest & Palm Benchmark Audits  │
└──────────────────────────┴─────────────────────────────────────────────┘
```

---

### 2. Rincian Spesifikasi Setiap Sub-Agent

#### SUB-AGENT 1: Database & Migration Architect (`db-architect-agent`)

- **Tujuan Utama**: Membangun fondasi data persisten yang mendukung multi-ekosistem hutan dan perkebunan sawit sesuai `plans/04_DATABASE_SCHEMA_AND_DATA_DICTIONARY.md`.
- **Area Kerja & File Milik**:
    - `database/migrations/*` (23 tabel relasional, mencakup `tree_measurements` dan `palm_measurements`).
    - `app/Models/*` (Semua model Eloquent dengan UUID traits, casts, relationships).
    - `database/factories/*` (Factory data untuk sensus pohon hutan dan sensus tegakan sawit).
    - `database/seeders/*` (Seeder metodologi: Chave 2014, SNI 7724, IPCC AFOLU, ISPO, PPKS Alometrik Sawit, dan seeder data demo multi-ekosistem).
- **Aturan & Batasan Khusus**:
    - Seluruh tabel operasional wajib menggunakan Primary Key `uuid('id')->primary()`.
    - Tabel `land_blocks` wajib memiliki enum/string `ecosystem_type`.
    - Tabel `palm_stands` wajib memiliki kolom `growth_stage` (`TBM_1`, `TBM_2`, `TBM_3`, `TM_MUDA`, `TM_PRIMA`, `TM_TUA`).
- **Input**: `04_DATABASE_SCHEMA_AND_DATA_DICTIONARY.md`.
- **Output**: Migrasi bersih yang sukses dieksekusi `php artisan migrate:fresh --seed` tanpa galat.

---

#### SUB-AGENT 2: Calculation Engine & Domain Logic Specialist (`carbon-engine-agent`)

- **Tujuan Utama**: Mengimplementasikan logika matematika polimorfik untuk pohon hutan biasa (pertumbuhan sekunder kontinyu) dan kelapa sawit (mutasi fase fenologis) sesuai `plans/02_CARBON_CALCULATION_ENGINE_SPEC.md`.
- **Area Kerja & File Milik**:
    - `app/Contracts/Calculation/EcosystemStrategyInterface.php`
    - `app/Services/Calculation/CarbonCalculationEngine.php` (Orchestrator utama)
    - `app/Services/Calculation/Strategies/NaturalForestStrategy.php` (Model Chave et al. 2014 & SNI 7724)
    - `app/Services/Calculation/Strategies/HtiTimberStrategy.php` (Model volume, form factor, BEF)
    - `app/Services/Calculation/Strategies/MangroveStrategy.php` (Model akar tunjang Komiyama / Kauffman)
    - `app/Services/Calculation/Strategies/OilPalmGrowthMutationStrategy.php` (Model alometrik tinggi Khalid et al., model umur Henson, dinamika pelepah pangkasan, mutasi rasio root-to-shoot seiring umur)
    - `app/Services/Calculation/SoilCarbonCalculator.php` (SOC tanah mineral & lahan gambut)
    - `app/Services/Calculation/SequestrationEngine.php` (Delta multi-periode & laju tahunan)
    - `app/Services/Calculation/SnapshotManager.php` (JSON snapshot input, formula, parameter)
- **Aturan & Batasan Khusus**:
    - Seluruh service bersifat _stateless_ dan _pure functions_.
    - Parameter numerik wajib diambil dari `parameter_versions`, bukan nilai mati (_no hardcoded numbers_).
- **Input**: `02_CARBON_CALCULATION_ENGINE_SPEC.md`.
- **Output**: Suite service perhitungan polimorfik yang lulus 100% unit test matematis.

---

#### SUB-AGENT 3: Backend API, Controller & Security Specialist (`backend-controller-agent`)

- **Tujuan Utama**: Menghubungkan presentation layer dengan domain service melalui controller adaptif ekosistem yang aman dan terindeks Wayfinder.
- **Area Kerja & File Milik**:
    - `app/Http/Controllers/CalculationRunController.php` (Wizard kalkulasi multi-ekosistem)
    - `app/Http/Controllers/MasterDataController.php` (Blok, Sensus Pohon Hutan, Sensus Sawit, Tanah)
    - `app/Http/Controllers/ApprovalController.php` (State machine alur approval)
    - `app/Http/Controllers/GisController.php`
    - `app/Http/Controllers/ReportController.php`
    - `app/Http/Requests/*` (Validasi form adaptif berdasarkan `ecosystem_type`)
    - `app/Policies/*` (Otorisasi peran)
    - `routes/web.php` (Sinkronisasi route Wayfinder)
- **Aturan & Batasan Khusus**:
    - Validasi form memeriksa input spesifik: jika blok adalah hutan alam, validasi DBH dan wood density; jika sawit, validasi tinggi batang, jumlah pelepah, dan fase mutasi.
    - Setiap kalkulasi dibungkus `DB::transaction()`.
- **Input**: `05_UI_UX_AND_APPLICATION_FLOW.md` dan Service dari Subagent 2.
- **Output**: Controller adaptif yang terverifikasi pada Wayfinder TypeScript generator.

---

#### SUB-AGENT 4: Frontend UI/UX & Inertia-Vue Specialist (`frontend-ui-agent`)

- **Tujuan Utama**: Membangun antarmuka pengguna SPA adaptif yang elegan, modern, dan mendukung data multi-ekosistem dengan tema "Clean Green Modern Architecture".
- **Area Kerja & File Milik**:
    - `resources/js/pages/Dashboard.vue` (Dashboard analitik dengan tab filter ekosistem)
    - `resources/js/pages/calculations/Wizard.vue` (Wizard adaptif 6 langkah)
    - `resources/js/pages/calculations/Show.vue` (Visualisasi detail run dan 5 pools)
    - `resources/js/pages/data/TreeCensus.vue` & `PalmStands.vue` (Form input sensus adaptif)
    - `resources/js/components/AppSidebar.vue` (Navigasi menu multi-ekosistem)
    - `resources/js/components/calculations/SnapshotInspector.vue`
- **Aturan & Batasan Khusus**:
    - Setiap komponen Vue wajib **Single Root Element**.
    - Menggunakan komponen dari `resources/js/components/ui/` (Reka UI / shadcn-vue).
    - Mengimpor route dari Wayfinder (`@/actions/...` atau `@/routes/...`).
- **Input**: `05_UI_UX_AND_APPLICATION_FLOW.md`.
- **Output**: Antarmuka responsif dan bebas galat tampilan.

---

#### SUB-AGENT 5: GIS & Spatial Mapping Specialist (`gis-spatial-agent`)

- **Tujuan Utama**: Mengembangkan modul pemetaan spasial interaktif blok kebun dan kompartemen hutan dengan pewarnaan tematik cadangan karbon.
- **Area Kerja & File Milik**:
    - `resources/js/components/maps/CarbonMapViewer.vue`
    - `resources/js/pages/gis/EstateMap.vue`
    - `resources/js/pages/gis/CarbonStockMap.vue`
    - `resources/js/pages/gis/SequestrationMap.vue`
- **Aturan & Batasan Khusus**:
    - Pewarnaan poligon membedakan tipe ekosistem (Hutan Alam, HTI, Mangrove, Sawit).
    - Mode choropleth menampilkan gradasi kepadatan stok karbon ($\text{t CO}_2\text{e}/\text{ha}$).
- **Input**: Data GeoJSON dari Subagent 1/3.
- **Output**: Peta interaktif performa tinggi.

---

#### SUB-AGENT 6: QA, Math Verification & Compliance Tester (`qa-math-tester-agent`)

- **Tujuan Utama**: Memverifikasi keabsahan matematis alometrik hutan dan mutasi fenologis sawit terhadap benchmark literatur internasional.
- **Area Kerja & File Milik**:
    - `tests/Unit/ForestBiomassMathTest.php` (Uji model Chave 2014, SNI 7724, Komiyama)
    - `tests/Unit/PalmGrowthMutationMathTest.php` (Uji model Khalid et al., Henson logistic, frond turnover, dan mutasi rasio root-to-shoot TBM vs TM)
    - `tests/Feature/CalculationLifecycleTest.php` (Uji alur Draft $\to$ Pending $\to$ Approved $\to$ Locked)
- **Aturan & Batasan Khusus**:
    - Memastikan seluruh tes Pest lulus (`php artisan test`).
    - Menjalankan `vendor/bin/pint --dirty --format agent`.
- **Input**: Seluruh kode dari Subagent 1 s/d 5.
- **Output**: Laporan pengujian 100% Pass.
