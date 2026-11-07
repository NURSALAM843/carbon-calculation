<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Check,
    CheckCircle2,
    Copy,
    Database,
    FileCheck,
    FileSpreadsheet,
    FileText,
    History,
    Lock,
    Plus,
    ShieldCheck,
    Upload,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface EvidenceItem {
    id: string;
    file_name: string;
    file_path: string;
    file_type: string;
    hash: string;
    created_at: string;
    land_block?: {
        name: string;
        code: string;
        estate?: { name: string };
    };
}

interface BlockOption {
    id: string;
    name: string;
    code: string;
}

interface Props {
    evidences: {
        data: EvidenceItem[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    blocks: BlockOption[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const selectedFile = ref<File | null>(null);
const selectedBlockId = ref('');
const isUploading = ref(false);
const copiedHash = ref<string | null>(null);

const onFileSelect = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        selectedFile.value = input.files[0];
    }
};

const copyToClipboard = (hash: string) => {
    navigator.clipboard.writeText(hash);
    copiedHash.value = hash;
    setTimeout(() => {
        copiedHash.value = null;
    }, 2000);
};

const uploadEvidence = () => {
    if (!selectedFile.value) return;

    isUploading.value = true;
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    if (selectedBlockId.value) {
        formData.append('land_block_id', selectedBlockId.value);
    }

    router.post('/data/evidence', formData, {
        onSuccess: () => {
            showModal.value = false;
            selectedFile.value = null;
            selectedBlockId.value = '';
            isUploading.value = false;
        },
        onError: () => {
            isUploading.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Evidence Vault', href: '/data/evidence' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Brankas Berkas Bukti SHA-256" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <ShieldCheck
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Brankas Berkas Bukti &amp; Sertifikat Uji Lab
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Penyimpanan dokumen pendukung sensus dan uji lab dengan
                    verifikasi integritas hash kriptografis SHA-256 anti-tamper.
                </p>
            </div>
            <Button
                @click="showModal = true"
                class="gap-2 self-start sm:self-auto"
            >
                <Upload class="h-4 w-4" />
                Unggah Dokumen Bukti
            </Button>
        </div>

        <!-- Integrity Banner -->
        <div
            class="flex items-center justify-between rounded-xl border border-neutral-800 bg-neutral-900 p-4 text-xs text-neutral-200"
        >
            <div class="flex items-center gap-3">
                <Lock class="h-5 w-5 shrink-0 text-emerald-400" />
                <div>
                    <span class="font-bold text-white"
                        >Standar Integritas Bukti SRN-PPI &amp; Verifikasi ISO
                        14064-3</span
                    >
                    <p class="mt-0.5 text-neutral-400">
                        Setiap berkas dokumen yang diunggah dikalkulasikan nilai
                        *digest* SHA-256 pada saat transmisi untuk memastikan
                        bahwa bukti fisik tidak dapat dimanipulasi setelah audit
                        disetujui.
                    </p>
                </div>
            </div>
            <Badge
                variant="outline"
                class="hidden border-emerald-500/40 font-mono text-emerald-400 sm:inline-flex"
            >
                SHA-256 Verified
            </Badge>
        </div>

        <!-- Evidence Files Table -->
        <Card
            class="overflow-hidden border border-neutral-200 shadow-sm dark:border-neutral-800"
        >
            <div class="overflow-x-auto">
                <table
                    class="w-full text-left text-sm text-neutral-600 dark:text-neutral-300"
                >
                    <thead
                        class="border-b bg-neutral-50 text-xs font-semibold text-neutral-500 uppercase dark:bg-neutral-800/80 dark:text-neutral-400"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Nama Dokumen</th>
                            <th class="px-4 py-3.5">Blok Lahan Terkait</th>
                            <th class="px-4 py-3.5">Tipe Format</th>
                            <th class="px-4 py-3.5">
                                Hash Kriptografis (SHA-256)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Tanggal Rekam
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="e in evidences.data"
                            :key="e.id"
                            class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="flex items-center gap-2 font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    <FileText
                                        class="h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span>{{ e.file_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <div
                                    class="font-medium text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ e.land_block?.name || 'Konsesi Umum' }}
                                </div>
                                <span
                                    v-if="e.land_block"
                                    class="font-mono text-[11px] text-neutral-400"
                                >
                                    {{ e.land_block?.code }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <Badge
                                    variant="secondary"
                                    class="font-mono text-[10px]"
                                >
                                    {{ e.file_type }}
                                </Badge>
                            </td>
                            <td class="px-4 py-4 font-mono text-xs">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="max-w-[220px] truncate text-neutral-500"
                                        :title="e.hash"
                                    >
                                        {{ e.hash }}
                                    </span>
                                    <button
                                        @click="copyToClipboard(e.hash)"
                                        class="cursor-pointer rounded p-1 text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200"
                                        title="Salin Hash SHA-256"
                                    >
                                        <Check
                                            v-if="copiedHash === e.hash"
                                            class="h-3.5 w-3.5 text-emerald-500"
                                        />
                                        <Copy v-else class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-xs text-neutral-500"
                            >
                                {{
                                    e.created_at
                                        ? new Date(
                                              e.created_at,
                                          ).toLocaleDateString('id-ID')
                                        : '-'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="evidences.total > 0"
                class="flex items-center justify-between border-t px-6 py-4 text-xs text-neutral-500"
            >
                <div>Total {{ evidences.total }} berkas bukti tersimpan</div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="evidences.prev_page_url"
                        :href="evidences.prev_page_url"
                        class="rounded border px-2.5 py-1"
                        >Sebelumnya</Link
                    >
                    <span
                        >Hal {{ evidences.current_page }} dari
                        {{ evidences.last_page }}</span
                    >
                    <Link
                        v-if="evidences.next_page_url"
                        :href="evidences.next_page_url"
                        class="rounded border px-2.5 py-1"
                        >Berikutnya</Link
                    >
                </div>
            </div>
        </Card>

        <!-- MODAL UNGGAH BUKTI -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border bg-white p-6 shadow-2xl dark:bg-neutral-900"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                >
                    <Upload class="h-5 w-5 text-emerald-600" />
                    Unggah Berkas Bukti ke Vault
                </h3>
                <form
                    @submit.prevent="uploadEvidence"
                    class="space-y-4 text-xs"
                >
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Blok Lahan Terkait (Opsional)</label
                        >
                        <select
                            v-model="selectedBlockId"
                            class="w-full rounded-lg border bg-transparent p-2 text-xs"
                        >
                            <option value="">
                                -- Seluruh Konsesi / Tidak Spesifik --
                            </option>
                            <option
                                v-for="b in blocks"
                                :key="b.id"
                                :value="b.id"
                            >
                                [{{ b.code }}] {{ b.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Pilih Dokumen (PDF, XLSX, CSV, JPG, PNG) *</label
                        >
                        <input
                            type="file"
                            @change="onFileSelect"
                            required
                            class="w-full rounded-lg border p-2 text-xs file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:text-emerald-700"
                        />
                        <p class="mt-1 text-[11px] text-neutral-400">
                            Ukuran berkas maksimal 20 MB. Sistem akan
                            menghasilkan hash SHA-256 otomatis.
                        </p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <Button
                            @click="showModal = false"
                            type="button"
                            variant="outline"
                            size="sm"
                            >Batal</Button
                        >
                        <Button
                            :disabled="isUploading || !selectedFile"
                            type="submit"
                            size="sm"
                        >
                            {{
                                isUploading
                                    ? 'Mengunggah & Memvalidasi Hash...'
                                    : 'Unggah ke Vault'
                            }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
