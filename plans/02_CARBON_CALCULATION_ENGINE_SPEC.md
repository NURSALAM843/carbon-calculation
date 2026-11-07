# Spesifikasi Engine Perhitungan Karbon Polimorfik (Polymorphic Carbon Calculation Engine)

## Multi-Ecosystem Biomass, Soil Carbon, Sequestration & Specialized Palm Growth Mutation

---

### 1. Arsitektur Polimorfik Calculation Engine (Polymorphic Architecture)

Perhitungan biomassa pohon tidak dapat disamaratakan. Pohon hutan berkayu (dikotil/gimnosperma) bertumbuh melalui pembelahan kambium vaskular sejati (_annual ring secondary growth_), sedangkan kelapa sawit adalah monokotil bertunas tunggal yang pertumbuhan biomassanya berjalan melalui **mutasi fase fenologis (growth stage mutations)**.

Oleh karena itu, engine perhitungan dirancang menggunakan **Polymorphic Strategy Pattern**:

```
                              [Calculation Request]
                                        │
                                        ▼
                            [Ecosystem Strategy Resolver]
                                        │
             ┌──────────────────────────┼──────────────────────────┐
             ▼                          ▼                          ▼
    [Strategy A: Hutan Alam]   [Strategy B: HTI & Timber]  [Strategy C: Mangrove]
    - Dicotyledon Secondary    - Volume & Form Factor      - Stilt Roots / Pneumatophore
    - Model Chave et al. 2014  - Wood Density (ρ)          - Sediment Deep Peat SOC
    - Model Brown / SNI 7724   - Monoculture Yield Model   - Komiyama / Kauffman
             │                          │                          │
             └──────────────────────────┼──────────────────────────┘
                                        │
                                        ▼
                       [Strategy D: Kelapa Sawit (Palm Oil)]
                       - Monocotyledon (Tanpa Kambium)
                       - Growth Phase Mutation (TBM 1-3 -> TM Muda -> TM Prima -> TM Tua)
                       - Stem Elongation Alometrics (Khalid et al.) & Henson Logistic
                       - Dynamic Root-to-Shoot Mutation (0.205 -> 0.180)
                       - Frond Pruning Turnover to Litter Pool
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │ Soil Carbon Engine (SOC)      │
                        │ - Mineral Soils               │
                        │ - Tropical Peatlands (Gambut) │
                        └───────────────┬───────────────┘
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │ 5 IPCC Carbon Pools Aggregate │
                        │ (AGB + BGB + Deadwood +       │
                        │  Litter + Soil)               │
                        └───────────────┬───────────────┘
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │ Multi-Period Sequestration    │
                        │ ΔC = C(t2) - C(t1)            │
                        │ Annualized t CO2e/ha/year     │
                        └───────────────┬───────────────┘
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │ Immutable Audit Snapshots     │
                        │ (Input, Parameter, Formula)   │
                        └───────────────────────────────┘
```

---

### 2. Strategi Perhitungan Berdasarkan Tipe Ekosistem

---

#### STRATEGI A: Hutan Alam Tropis (Tropical Natural Forest - Primer & Sekunder)

Pohon hutan alam mengalami pertumbuhan sekunder kontinu melalui kambium vaskular. Variabel utama adalah diameter setinggi dada ($DBH$ atau $D$), tinggi pohon total ($H$), dan massa jenis kayu spesifik (_wood specific gravity_ $\rho$).

1. **Model Alometrik Pantropis Chave et al. (2014)** (_Direkomendasikan IPCC & KLHK_):
   Jika tinggi pohon $H$ diukur:
   $$AGB_{\text{tree}} = 0.0673 \times \left(\rho \times D^2 \times H\right)^{0.976}$$
    - $AGB_{\text{tree}}$: Biomassa atas permukaan per pohon ($kg$).
    - $\rho$: Berat jenis kayu kering tanur ($\text{g/cm}^3$, default hutan tropis Asia Tenggara: $0.57\text{ g/cm}^3$ jika spesies tidak teridentifikasi).
    - $D$: Diameter setinggi dada ($DBH$) dalam $cm$ (diukur pada ketinggian $1.3\text{ m}$).
    - $H$: Tinggi total pohon ($m$).

2. **Model Alometrik Berbasis Indeks Bioklimatik ($E$) Chave et al. (2014)** (_Jika tinggi $H$ tidak diukur_):
   $$AGB_{\text{tree}} = \exp\left(-1.803 - 0.976 \cdot E + 0.976 \cdot \ln(\rho) + 2.673 \cdot \ln(D) - 0.0299 \cdot (\ln(D))^2\right)$$
    - $E$: Indeks stres bioklimatik lingkungan berdasarkan koordinat geospasial (rentang Indonesia: $-0.10$ s/d $+0.05$).

3. **Model Alometrik Hutan Sekunder SNI 7724 / Ketterings et al. (2001)**:
   $$AGB_{\text{tree}} = 0.11 \times \rho \times D^{2.62}$$

4. **Agregasi Plot Menjadi Hektar**:
   $$AGB_{\text{forest, t/ha}} = \frac{\sum_{i=1}^{n} AGB_{\text{tree}, i}\ (\text{kg})}{\text{Luas Plot Sensus}\ (\text{m}^2)} \times 10$$

---

#### STRATEGI B: Hutan Tanaman Industri (HTI / Timber Plantations)

Tegakan monokultur pohon berkayu seumur (_even-aged timber stands_), seperti _Acacia mangium_, _Eucalyptus pellita_, atau _Pinus merkusii_.

1. **Model Volume & Biomass Expansion Factor ($BEF$)**:
   $$\text{Volume Batang } (V) = \frac{\pi}{4} \times \left(\frac{DBH}{100}\right)^2 \times H \times f_{\text{form}}$$
    - $f_{\text{form}}$: Faktor bentuk batang silindris (default akasia/eukaliptus: $0.65 - 0.70$).
    - $\text{Biomassa Batang } (W_{\text{stem}}) = V \times \rho \times 1000\ (\text{kg})$.
    - $AGB_{\text{tree}} = W_{\text{stem}} \times BEF$ (di mana $BEF$ adalah rasio biomassa total terhadap biomassa batang, default: $1.25 - 1.40$).

2. **Persamaan Alometrik Langsung Akasia & Eukaliptus**:
    - _Acacia mangium_: $AGB = 0.0837 \times D^{2.54}$ (ton/pohon).
    - _Eucalyptus pellita_: $AGB = 0.0762 \times D^{2.48}$ (ton/pohon).

---

#### STRATEGI C: Hutan Mangrove & Lahan Basah Pesisir

Mangrove memiliki adaptasi morfologi unik berupa sistem perakaran udara (_stilt roots_ pada _Rhizophora_, pneumatofor pensil pada _Avicennia_, atau lutut pada _Bruguiera_) yang menyimpan proporsi karbon sangat tinggi.

1. **Model Alometrik Komiyama et al. (2005) & Kauffman & Donato (2012)**:
    - _Rhizophora apiculata / mucronata_:
      $$W_{\text{top}} = 0.0509 \times \rho \times D^{2.5} \quad (\text{biomassa tajuk \& batang, kg})$$
      $$W_{\text{prop\_root}} = 0.00974 \times \rho \times D^{2.71} \quad (\text{biomassa akar tunjang luar, kg})$$
      $$AGB_{\text{tree}} = W_{\text{top}} + W_{\text{prop\_root}}$$
    - _Avicennia marina / alba_:
      $$AGB_{\text{tree}} = 0.0825 \times \rho \times D^{2.47} \quad (\text{kg})$$

2. **Rasio BGB Mangrove**:
   Karena perakaran lumpur anaerobik yang masif, rasio akar-tajuk mangrove jauh lebih tinggi dibanding hutan daratan:
   $$R_{\text{mangrove}} = 0.35 - 0.49$$

---

#### STRATEGI D: Engine Khusus Mutasi Pertumbuhan Kelapa Sawit (Palm Oil Growth Mutation Engine)

Kelapa sawit (_Elaeis guineensis_) adalah tumbuhan monokotil arboresen tanpa kambium vaskular. Diameternya tidak bertambah setelah pangkal tajuk terbentuk, dan pertumbuhannya terbagi ke dalam **4 mutasi fase fenologis utama**:

```
[FASE 1: TBM 1-3 (0-3 Tahun)]
- Tanaman Belum Menghasilkan
- Tinggi batang H ≈ 0 m
- Ekspansi tajuk pelepah muda & akar basal
- Root/Shoot ratio tinggi: R = 0.205
- Biomassa: 12 - 45 kg/pohon
                     │
                     ▼ (Mutasi Umur & Morfologi ke-1)
[FASE 2: TM Muda (4-8 Tahun)]
- Pemanjangan batang pesat (50-75 cm/tahun)
- Mulai panen TBS eksponensial
- Pelepah mulai dipangkas rutin (pruning)
- Root/Shoot ratio bermutasi: R = 0.190
                     │
                     ▼ (Mutasi Umur & Morfologi ke-2)
[FASE 3: TM Prima / Dewasa (9-18 Tahun)]
- Laju tinggi batang stabil (35-45 cm/tahun)
- Diameter batang DBH konstan (tidak membesar)
- Produksi TBS puncak (peak yield)
- Pruning pelepah konstan (36-48 pelepah/pohon/tahun) -> Litter Pool
- Root/Shoot ratio bermutasi: R = 0.180
                     │
                     ▼ (Mutasi Umur & Morfologi ke-3)
[FASE 4: TM Tua / Senescence (>18-25+ Tahun)]
- Pertumbuhan meninggi melambat (<25 cm/tahun)
- Penurunan produktivitas TBS
- Persiapan peremajaan (Replanting turnover)
```

1. **Model Alometrik Pemanjangan Batang (Stem Elongation Alometric - Khalid et al. 1999)**:
   Digunakan pada fase TM jika tinggi batang bebas pelepah ($H$) diukur langsung:
   $$\text{Biomass}_{\text{trunk}}\ (\text{kg/pohon}) = (0.0706 + 0.0976 \times H) \times 1000$$
   _(dengan $H$ dalam meter)_.

2. **Model Komponen Tajuk Pelepah Hidup & Tandan Buah Segar**:
   $$\text{Biomass}_{\text{fronds\_standing}} = N_{\text{fronds\_live}} \times W_{\text{frond\_avg}}$$
    - $N_{\text{fronds\_live}}$: Jumlah pelepah hijau yang dipertahankan di pohon (TBM: 28–32 pelepah; TM Muda: 40–48 pelepah; TM Prima: 36–42 pelepah).
    - $W_{\text{frond\_avg}}$: Berat kering per pelepah ($2.5 - 4.5\text{ kg/pelepah}$).
      $$\text{Biomass}_{\text{bunches}} = N_{\text{bunches}} \times W_{\text{bunch\_avg}} \times 0.53$$

3. **Total Standing AGB per Pohon Sawit**:
   $$AGB_{\text{palm}} = \text{Biomass}_{\text{trunk}} + \text{Biomass}_{\text{fronds\_standing}} + \text{Biomass}_{\text{bunches}}$$

4. **Model Logistik Umur Terpadu (Henson 2005 - Alternatif jika tinggi tidak diukur)**:
   $$AGB_{\text{palm}}(t) = \frac{AGB_{\text{max}}}{1 + \exp\left(-k \cdot (t - t_0)\right)}$$
    - $t$: Umur tanaman sejak tanam lapangan (tahun).
    - $AGB_{\text{max}} = 0.65\text{ ton/pohon}$ ($650\text{ kg}$).
    - $k = 0.28$, $t_0 = 8.5\text{ tahun}$.

5. **Mutasi Dinamika Pelepah Pangkasan ke Kolam Serasah (Pruning Turnover to Litter Pool)**:
   Berbeda dari pohon hutan yang meranggas musiman, kelapa sawit dipangkas rutin setiap panen ($24 - 48\text{ pelepah/pohon/tahun}$). Pelepah pangkasan ditumpuk di gawangan mati (_frond stack / windrow_) dan terdekomposisi menjadi bahan organik tanah:
   $$\text{Biomassa Serasah Pangkasan Tahunan } (\text{t/ha/thn}) = \frac{N_{\text{pruned/thn}} \times W_{\text{frond}} \times \text{Density}_{\text{trees/ha}}}{1000}$$

6. **Mutasi Rasio Akar-Tajuk Berdasarkan Fase Umur**:
    - Jika Umur $< 4$ tahun (Fase TBM): $R = 0.205$
    - Jika Umur $4 - 8$ tahun (Fase TM Muda): $R = 0.190$
    - Jika Umur $> 8$ tahun (Fase TM Prima/Tua): $R = 0.180$

---

### 3. Engine Karbon Tanah (Soil Organic Carbon - SOC)

Berlaku untuk seluruh tipe ekosistem, terbagi atas dua tipe tanah utama:

#### 1. Tanah Mineral (Mineral Soils)

$$SOC_{\text{fraction}} = \frac{SOC_{\%}}{100}$$
$$SOC_{\text{mineral, t C/ha}} = SOC_{\text{fraction}} \times BulkDensity\ (\text{g/cm}^3) \times Depth\ (\text{cm}) \times (1 - CF_{\text{coarse}}) \times 100$$

- $BulkDensity$: Rata-rata $0.9 - 1.4\text{ g/cm}^3$.
- $Depth$: Standar $0 - 30\text{ cm}$ (topsoil) atau $0 - 100\text{ cm}$ (deep profile).
- $CF_{\text{coarse}}$: Fraksi kerikil/batuan kasar ($>2\text{ mm}$, nilai $0 - 1$).

#### 2. Lahan Rawa Gambut (Tropical Peatland Soils)

Lahan gambut memiliki kerapatan lindak yang sangat rendah namun kandungan C-organik sangat pekat ($>40\% - 58\%$):
$$SOC_{\text{peat, t C/ha}} = \frac{C_{\text{peat}\%}}{100} \times BD_{\text{peat}}\ (\text{g/cm}^3) \times Depth_{\text{peat}}\ (\text{cm}) \times 100$$

- $BD_{\text{peat}}$: $0.10 - 0.25\text{ g/cm}^3$.
- $C_{\text{peat}\%}$: $45\% - 56\%$.
- Untuk kedalaman gambut $100\text{ cm}$, cadangan karbon gambut dapat mencapai $450 - 800\text{ t C/ha}$.

---

### 4. Agregasi 5 Kolam Karbon IPCC (IPCC 5 Carbon Pools)

Berlaku universal di semua tipe ekosistem:

$$C_{\text{AGB, t/ha}} = AGB_{\text{t/ha}} \times CF$$
$$C_{\text{BGB, t/ha}} = (AGB_{\text{t/ha}} \times R) \times CF$$
$$C_{\text{Deadwood, t/ha}} = \text{Biomass}_{\text{deadwood, t/ha}} \times CF$$
$$C_{\text{Litter, t/ha}} = \text{Biomass}_{\text{litter, t/ha}} \times CF$$
$$C_{\text{Soil, t/ha}} = SOC_{\text{t C/ha}}$$

$$C_{\text{total, t/ha}} = C_{\text{AGB, t/ha}} + C_{\text{BGB, t/ha}} + C_{\text{Deadwood, t/ha}} + C_{\text{Litter, t/ha}} + C_{\text{Soil, t/ha}}$$

$$\text{CO}_2\text{e}_{\text{stock, t/ha}} = C_{\text{total, t/ha}} \times \frac{44}{12}$$

$$\text{Total CO}_2\text{e}_{\text{block}} = \text{CO}_2\text{e}_{\text{stock, t/ha}} \times \text{Area}_{\text{ha}}$$

---

### 5. Engine Penyerapan Karbon Multi-Periode (Carbon Sequestration Engine)

Menghitung perubahan cadangan karbon bersih antara dua periode kalkulasi yang disetujui ($t_1 \to t_2$):

$$\Delta C = C_{\text{total}}(t_2) - C_{\text{total}}(t_1) \quad (\text{ton } C)$$
$$\Delta\text{CO}_2\text{e} = \Delta C \times \frac{44}{12} \quad (\text{ton } \text{CO}_2\text{e})$$
$$\Delta t\ (\text{tahun}) = \frac{\text{Tanggal}(t_2) - \text{Tanggal}(t_1)}{365.25}$$

$$\text{Annual Sequestration Rate} = \frac{\Delta\text{CO}_2\text{e}}{\Delta t} \quad (\text{t }\text{CO}_2\text{e}/\text{tahun})$$
$$\text{Sequestration Rate per Hektar} = \frac{\text{Annual Sequestration Rate}}{\text{Area}_{\text{ha}}} \quad (\text{t }\text{CO}_2\text{e}/\text{ha}/\text{tahun})$$

- $\text{Rate} > 0$: **Net Carbon Sink** (Penyerap karbon atmosfer).
- $\text{Rate} < 0$: **Net Carbon Source / Loss** (Pelepas emisi, misal penebangan HTI, kebakaran hutan, atau replanting sawit).

---

### 6. Arsitektur Snapshot Audit (Audit Snapshot Payload)

Menjamin auditabilitas permanen untuk sertifikasi karbon (VCS, ISPO, RSPO, SRN-PPI):

1. **`input_snapshot`**: Menyimpan tipe ekosistem (`ecosystem_type`), data sensus pohon hutan (DBH, spesies, $\rho$) atau sensus sawit (tinggi, pelepah, umur/fase), dan data uji lab tanah.
2. **`parameter_snapshot`**: Nilai numerik konstanta yang digunakan ($CF$, koefisien alometrik, $R$, rasio $\text{CO}_2 / C = 44/12$).
3. **`formula_snapshot`**: Formula matematis teks lengkap dan versi strategi engine yang dieksekusi.
