# Arsitektur Sistem & Analisis Teknologi (System Architecture & Technology Stack)

## Panduan Standar Teknis & Arsitektur Kode Proyek Carbon Calculation

---

### 1. Analisis Komprehensif Teknologi Terpasang (Current Tech Stack Analysis)

Analisis mendalam terhadap repositori ini memastikan seluruh pengembangan tampilan (UI) dan logika pemrograman (Backend) menggunakan konvensi paket terbaru yang sudah terkonfigurasi.

```
┌────────────────────────────────────────────────────────────────────────┐
│                          PRESENTATION LAYER                            │
│  Vue 3.5 (Composition API, <script setup lang="ts">, Single Root Element) │
│  Inertia.js v3 (SPA Client, Instant Visits, Deferred Props, useHttp)   │
│  Tailwind CSS v4 (@theme inline, CSS variables, Dark Mode)            │
│  Reka UI / shadcn-vue (22+ Primitives: Dialog, Card, Sidebar, Sonner)  │
│  Laravel Wayfinder (@/actions, @/routes typed URL generation)         │
├────────────────────────────────────────────────────────────────────────┤
│                          APPLICATION LAYER                             │
│  Laravel 13.x (PHP 8.3 / 8.5)                                          │
│  Laravel Fortify 1.37 (2FA TOTP, WebAuthn Passkeys, Multi-Tenant Auth)  │
│  Polymorphic Strategy Pattern (Ecosystem Biomass Strategies)           │
│  Service Pipeline Architecture (Calculation Engine & Snapshots)        │
├────────────────────────────────────────────────────────────────────────┤
│                           DATA & GIS LAYER                             │
│  SQLite (Local Dev) / PostgreSQL + PostGIS (Production)                │
│  Spatial GeoJSON Polygons (Estates & Land Blocks)                     │
│  Immutable JSON Snapshots (Inputs, Formulas, Parameters)              │
├────────────────────────────────────────────────────────────────────────┤
│                       QUALITY & TESTING LAYER                          │
│  Pest PHP 5.3 (Feature & Unit Tests)                                   │
│  Laravel Pint (PSR-12 / Laravel Code Style)                           │
│  Larastan / PHPStan 3.9 (Type Checking Level 8)                       │
└────────────────────────────────────────────────────────────────────────┘
```

#### Rincian Versi dan Dependensi Inti:

| Kategori                   | Paket / Tooling                                        | Versi Terpasang                    | Peran & Petunjuk Penggunaan                                                                                                                        |
| :------------------------- | :----------------------------------------------------- | :--------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Backend Framework**      | `laravel/framework`                                    | `^13.17` (PHP 8.3/8.5)             | Framework inti, controller, routing, ORM, jobs, policies.                                                                                          |
| **Client-Server Bridge**   | `inertiajs/inertia-laravel` & `@inertiajs/vue3`        | `^3.0.0` (Inertia v3)              | SPA tanpa API REST terpisah. Menggunakan fitur v3: standalone `useHttp`, layout props, deferred props (`Inertia::optional()`, `Inertia::defer()`). |
| **Frontend Framework**     | `vue`                                                  | `^3.5.13`                          | UI framework dengan TypeScript dan Composition API `<script setup lang="ts">`. Wajib single root element.                                          |
| **Styling Engine**         | `tailwindcss` & `@tailwindcss/vite`                    | `^4.1.1` & `^4.1.11` (Tailwind v4) | Menggunakan arsitektur CSS-first v4 (`@theme inline` di `resources/css/app.css`). Tidak menggunakan file `tailwind.config.js` v3 usang.            |
| **UI Component Library**   | `reka-ui` & shadcn-vue preset                          | `^2.9.8`                           | Komponen headless dan accessible di `resources/js/components/ui/` (Card, Button, Dialog, Select, Sonner, dll.).                                    |
| **Icon Library**           | `@lucide/vue`                                          | `^1.17.0`                          | Ikon standar sistem (ekologi, grafik, navigasi, aksi).                                                                                             |
| **Routing Type-Safety**    | `laravel/wayfinder` & `@laravel/vite-plugin-wayfinder` | `^0.1.14`                          | Menghasilkan fungsi TypeScript otomatis dari route Laravel di `@/actions` dan `@/routes`. Bebas hardcoded URL!                                     |
| **Autentikasi & Keamanan** | `laravel/fortify` & `@laravel/passkeys`                | `^1.37.2` & `^0.2.0`               | Autentikasi enterprise, session guard, Two-Factor Authentication (2FA), dan Passkeys WebAuthn.                                                     |
| **Testing Framework**      | `pestphp/pest`                                         | `^5.3`                             | Pest testing framework modern untuk Feature Tests & Unit Tests matematis.                                                                          |
| **Code Formatter**         | `laravel/pint`                                         | `^1.27`                            | Formatter kode otomatis Laravel (`vendor/bin/pint --format agent`).                                                                                |

---

### 2. Pedoman Desain Tampilan (UI/UX Guidelines - Clean Green Modern)

Untuk mencerminkan sistem akuntansi karbon berkelas enterprise, antarmuka mengadopsi tema **"Clean Green Modern Architecture"**:

1. **Palet Warna & Token Desain (Tailwind v4 Theme)**:
    - Terdefinisi pada `resources/css/app.css` menggunakan variabel HSL:
        - `Primary`: HSL netral gelap/terang (`hsl(0 0% 9%)` pada light, `hsl(0 0% 98%)` pada dark).
        - `Forest Green Accent`: Diterapkan pada metrik karbon, indikator penyerapan (_net sink_), dan status _approved_.
        - `Warning Gold/Amber`: Indikator batas kritis stok dan status _pending review_.
        - `Destructive Coral/Red`: Indikator penurunan biomassa (_net loss/source_) dan status _rejected_.
2. **Tipografi**:
    - Menggunakan font modern **Instrument Sans** (dimuat via Bunny CDN di `vite.config.ts`).
3. **Komponen Konsisten (Reka UI / shadcn-vue)**:
    - Tidak membuat komponen UI ad-hoc tanpa dasar. Gunakan komponen yang ada di `resources/js/components/ui/`:
        - Form input menggunakan `Input.vue`, `Label.vue`, `Select.vue`.
        - Layout data menggunakan `Card.vue`, `CardHeader.vue`, `CardContent.vue`.
        - Modal audit dan dialog konfirmasi menggunakan `Dialog.vue`.
        - Notifikasi menggunakan toast `vue-sonner`.
        - Indikator pemuatan berat menggunakan `Skeleton.vue` dengan animasi pulse.
4. **Dukungan Dark Mode**:
    - Sistem wajib mendukung _Light Mode_ dan _Dark Mode_ secara seamless dengan selector `@custom-variant dark (&:is(.dark *))` yang sudah aktif di `app.css`.

---

### 3. Pedoman Logika Pemrograman Backend (Backend Architecture Guidelines)

#### 3.1 Polimorfisme Engine Perhitungan (Strategy Pattern)

Untuk mengakomodasi perbedaan biologis fundamental antara pohon hutan biasa (pertumbuhan sekunder kambium kontinyu) dan kelapa sawit (mutasi fase fenologis diskret tanpa kambium), backend wajib menerapkan **Strategy Pattern**:

```php
namespace App\Contracts\Calculation;

interface EcosystemStrategyInterface
{
    public function calculateAboveGroundBiomass(CalculationInputDTO $input, array $parameters): BiomassResultDTO;
    public function calculateBelowGroundBiomass(float $agb, int $age, array $parameters): float;
    public function supports(string $ecosystemType): bool;
}
```

Struktur implementasi strategi:

- `App\Services\Calculation\Strategies\NaturalForestStrategy`: Menangani Chave et al. (2014) dan Brown (1997) berbasis DBH, tinggi pohon, dan massa jenis kayu $\rho$.
- `App\Services\Calculation\Strategies\HtiTimberStrategy`: Menangani pohon HTI monokultur seumur (Akasia, Eukaliptus, Pinus) berbasis volume dan BEF.
- `App\Services\Calculation\Strategies\MangroveStrategy`: Menangani mangrove dengan alometrik akar tunjang (_stilt roots_) dan sedimen basah.
- `App\Services\Calculation\Strategies\OilPalmGrowthMutationStrategy`: Menangani spesifik mutasi fase kelapa sawit (TBM 1-3, TM Muda, TM Prima, TM Tua, model alometrik tinggi Khalid et al., model umur Henson, dinamika pelepah pangkasan ke kolam litter, dan pergeseran rasio _root-to-shoot_ seiring umur).

#### 3.2 Struktur Direktori Domain

```
app/
├── Contracts/Calculation/
│   └── EcosystemStrategyInterface.php
├── Services/Calculation/
│   ├── CarbonCalculationEngine.php          (Orchestrator utama)
│   ├── SoilCarbonCalculator.php             (Engine SOC mineral & gambut)
│   ├── SequestrationEngine.php              (Engine delta multi-periode t1 -> t2)
│   ├── SnapshotManager.php                  (Penyusun JSON snapshots)
│   └── Strategies/
│       ├── NaturalForestStrategy.php
│       ├── HtiTimberStrategy.php
│       ├── MangroveStrategy.php
│       └── OilPalmGrowthMutationStrategy.php
└── DTOs/Calculation/
    ├── CalculationInputDTO.php
    ├── BiomassResultDTO.php
    └── CalculationResultDTO.php
```

#### 3.3 Prinsip Determinisme Matematis & Transaksi Database

- Semua kalkulasi karbon bersifat _pure functions_: input yang sama selalu menghasilkan output persis sama.
- Seluruh mutasi calculation run, snapshot, dan pool breakdown wajib dibungkus dalam `DB::transaction()` untuk menjamin integritas ACID.

---

### 4. Pedoman Pengujian (Testing Strategy)

1. **Unit Tests (Pest PHP)**:
    - Pengujian terisolasi untuk masing-masing strategi ekosistem membandingkan hasil perhitungan terhadap _hand-calculated benchmarks_ dari publikasi ilmiah IPCC 2019, Chave 2014, dan PPKS.
2. **Feature Tests (Pest PHP)**:
    - Pengujian siklus hidup kalkulasi dari _Draft_ $\to$ _Pending Approval_ $\to$ _Approved_.
    - Verifikasi penguncian data (_locked state_): memastikan record yang disetujui tidak dapat dimodifikasi.
    - Verifikasi isolasi multi-tenant antar organisasi.
3. **Pint Code Style**:
    - Setiap berkas PHP yang dibuat atau diubah wajib lolos eksekusi `vendor/bin/pint --dirty --format agent`.
