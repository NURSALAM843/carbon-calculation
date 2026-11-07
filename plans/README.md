# Cetak Biru Arsitektur & Rencana Pengembangan Sistem Perhitungan Karbon Multi-Ekosistem

## (Multi-Ecosystem Forest Carbon & Specialized Oil Palm Growth Mutation Accounting System)

Dokumen ini merupakan indeks utama yang merangkum seluruh perencanaan teknis, spesifikasi ilmiah multi-ekosistem, skema basis data, rancangan antarmuka, pembagian sub-agent, dan peta jalan eksekusi untuk membangun aplikasi akuntansi karbon berbasis **Laravel 13**, **Inertia.js v3**, **Vue 3.5**, **Tailwind CSS v4**, dan **Reka UI**.

---

### 1. Fondasi Arsitektur: Mengapa Kelapa Sawit Berbeda dari Hutan Biasa?

Sistem ini dirancang untuk menghitung cadangan dan penyerapan karbon di berbagai tipe ekosistem:

- **Hutan Alam Tropis (Primary & Secondary Forests)**: Menggunakan alometrik pohon dikotil berbasis kambium vaskular sekunder (Chave et al. 2014 & SNI 7724) dengan variabel diameter (DBH), tinggi pohon, dan berat jenis kayu ($\rho$).
- **Hutan Tanaman Industri (HTI - Akasia, Eukaliptus)**: Menggunakan volume tebangan dan _Biomass Expansion Factor_ ($BEF$).
- **Hutan Mangrove & Lahan Basah**: Mengakomodasi sistem perakaran udara (_stilt roots_ / pneumatofor) dan cadangan karbon sedimen/gambut yang sangat dalam.
- **Kelapa Sawit (_Elaeis guineensis_)**: Merupakan **monokotil tanpa kambium sekunder**, di mana pertambahan biomassa tidak kontinyu berdasarkan pembesaran diameter, melainkan berjalan secara diskret melalui **mutasi fase pertumbuhan fenologis**:
    - **Fase TBM 1–3 (0–3 tahun)**: Pertumbuhan tajuk pelepah muda & perakaran basal, $H \approx 0\text{ m}$, rasio akar/tajuk tinggi ($R \approx 0.205$).
    - **Fase TM Muda (4–8 tahun)**: Pemanjangan batang pesat ($50 - 75\text{ cm/tahun}$), panen TBS awal, rasio akar/tajuk bermutasi ($R \approx 0.190$).
    - **Fase TM Prima (9–18 tahun)**: Laju tinggi stabil, diameter konstan, panen TBS puncak, pemangkasan pelepah rutin menyumbang ke kolam serasah (_litter turnover_), rasio akar/tajuk ($R \approx 0.180$).
    - **Fase TM Tua (>18–25+ tahun)**: Pertumbuhan melambat dan persiapan peremajaan (_replanting turnover_).

---

### 2. Struktur Dokumen Perencanaan

|   No   | Dokumen                                                                                                                                                   | Deskripsi                                                                                                                                                                                                                                                                                                                               |
| :----: | :-------------------------------------------------------------------------------------------------------------------------------------------------------- | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **01** | [01_PRD_PRODUCT_REQUIREMENTS_DOCUMENT.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/01_PRD_PRODUCT_REQUIREMENTS_DOCUMENT.md)         | **Product Requirements Document (PRD)**: Visi produk multi-ekosistem, rasional ilmiah mutasi pertumbuhan sawit, kepatuhan regulasi (NEK, ISPO, RSPO, IPCC), persona pengguna, RBAC, serta kebutuhan fungsional.                                                                                                                         |
| **02** | [02_CARBON_CALCULATION_ENGINE_SPEC.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/02_CARBON_CALCULATION_ENGINE_SPEC.md)               | **Spesifikasi Engine Perhitungan Karbon Polimorfik**: Strategi A (Hutan Alam Chave 2014 & SNI), Strategi B (HTI Timber), Strategi C (Mangrove Komiyama), Strategi D (Kelapa Sawit Khalid et al., Henson logistic, dan mutasi rasio akar-tajuk), SOC tanah mineral & gambut, agregasi 5 pool IPCC, dan engine sekuestrasi multi-periode. |
| **03** | [03_SYSTEM_ARCHITECTURE_AND_TECH_STACK.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/03_SYSTEM_ARCHITECTURE_AND_TECH_STACK.md)       | **Arsitektur Sistem & Stack Teknologi**: Implementasi _Polymorphic Strategy Pattern_ di Laravel 13, Inertia v3 SPA, Vue 3.5, Tailwind CSS v4 (@theme inline), Reka UI (shadcn-vue), Laravel Wayfinder, dan Fortify.                                                                                                                     |
| **04** | [04_DATABASE_SCHEMA_AND_DATA_DICTIONARY.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/04_DATABASE_SCHEMA_AND_DATA_DICTIONARY.md)     | **Kamus Data & Skema Database**: 23 tabel relasional yang mencakup `land_blocks.ecosystem_type`, `tree_measurements` (DBH, spesies, $\rho$), `palm_measurements`, `palm_stands.growth_stage`, dan relasi lengkap bersumber dari [erd.png](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/erd.png).                      |
| **05** | [05_UI_UX_AND_APPLICATION_FLOW.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/05_UI_UX_AND_APPLICATION_FLOW.md)                       | **Desain Antarmuka & Alur Aplikasi**: Struktur menu multi-ekosistem, alur Adaptive Calculation Wizard (menyesuaikan form input berdasarkan tipe tutupan lahan), antrean approval, dan peta spasial GIS choropleth.                                                                                                                      |
| **06** | [06_SUBAGENTS_WORKFORCE_SPECIFICATION.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/06_SUBAGENTS_WORKFORCE_SPECIFICATION.md)         | **Spesifikasi Tenaga Kerja Sub-Agent**: Definisi 6 AI Sub-Agent khusus (`db-architect-agent`, `carbon-engine-agent`, `backend-controller-agent`, `frontend-ui-agent`, `gis-spatial-agent`, `qa-math-tester-agent`) dengan cakupan tugas multi-ekosistem.                                                                                |
| **07** | [07_IMPLEMENTATION_ROADMAP_AND_TASK_BOARD.md](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/07_IMPLEMENTATION_ROADMAP_AND_TASK_BOARD.md) | **Peta Jalan Implementasi & Papan Tugas**: Rencana kerja 8 milestone bertahap dengan tiket tugas individual terperinci.                                                                                                                                                                                                                 |

---

### 3. Diagram Acuan Arsitektur Data

- Tautan visual ERD: [erd.png](file:///Users/bidang4/Desktop/belajar/carbon-calculation/plans/erd.png)
