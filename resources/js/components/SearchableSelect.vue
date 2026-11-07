<script setup lang="ts">
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { Check, ChevronDown, Search, X } from '@lucide/vue';

export interface SelectOptionItem {
    value: string | number;
    label: string;
    description?: string;
}

export interface SelectGroupItem {
    group: string;
    options: Array<SelectOptionItem | string>;
}

export type SelectOption = string | SelectOptionItem | SelectGroupItem;

interface Props {
    modelValue?: string | number | null;
    options: SelectOption[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    disabled?: boolean;
    required?: boolean;
    clearable?: boolean;
    size?: 'sm' | 'default';
    id?: string;
    name?: string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: '-- Pilih Opsi --',
    searchPlaceholder: 'Ketik untuk mencari...',
    emptyText: 'Tidak ada hasil ditemukan.',
    disabled: false,
    required: false,
    clearable: false,
    size: 'default',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
    (e: 'change', value: string | number): void;
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = useTemplateRef<HTMLElement>('containerRef');
const searchInputRef = useTemplateRef<HTMLInputElement>('searchInputRef');

// Close dropdown on click outside
onClickOutside(containerRef, () => {
    isOpen.value = false;
});

// Normalize options into groups of SelectOptionItem
interface NormalizedGroup {
    group?: string;
    items: SelectOptionItem[];
}

const normalizedGroups = computed<NormalizedGroup[]>(() => {
    if (!props.options || props.options.length === 0) return [];

    // Check if the options array has groups
    const isGrouped = props.options.some(
        (opt) =>
            typeof opt === 'object' &&
            opt !== null &&
            'group' in opt &&
            'options' in opt,
    );

    if (isGrouped) {
        return (props.options as SelectGroupItem[]).map((g) => ({
            group: g.group,
            items: g.options.map((item) => {
                if (typeof item === 'string') {
                    return { value: item, label: item };
                }
                return item;
            }),
        }));
    }

    // Flat options
    const flatItems: SelectOptionItem[] = (
        props.options as Array<string | SelectOptionItem>
    ).map((item) => {
        if (typeof item === 'string') {
            return { value: item, label: item };
        }
        return item;
    });

    return [{ items: flatItems }];
});

// Filter groups and items based on search query
const filteredGroups = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return normalizedGroups.value;

    return normalizedGroups.value
        .map((g) => {
            const matchedItems = g.items.filter((item) => {
                const labelMatch = item.label.toLowerCase().includes(q);
                const valueMatch = String(item.value).toLowerCase().includes(q);
                const descMatch = item.description?.toLowerCase().includes(q);
                return labelMatch || valueMatch || descMatch;
            });
            return {
                group: g.group,
                items: matchedItems,
            };
        })
        .filter((g) => g.items.length > 0);
});

// Flat list of all items for label lookup
const allItems = computed<SelectOptionItem[]>(() => {
    return normalizedGroups.value.flatMap((g) => g.items);
});

// Currently selected option item
const selectedItem = computed<SelectOptionItem | undefined>(() => {
    if (
        props.modelValue === undefined ||
        props.modelValue === null ||
        props.modelValue === ''
    ) {
        return undefined;
    }
    const val = String(props.modelValue);
    return allItems.value.find((item) => String(item.value) === val);
});

const displayLabel = computed(() => {
    if (selectedItem.value) {
        return selectedItem.value.label;
    }
    if (
        props.modelValue !== undefined &&
        props.modelValue !== null &&
        props.modelValue !== ''
    ) {
        return String(props.modelValue);
    }
    return '';
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
};

const selectOption = (val: string | number) => {
    emit('update:modelValue', val);
    emit('change', val);
    isOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = (e: MouseEvent) => {
    e.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
    searchQuery.value = '';
};

// Keyboard navigation: close on Escape
const onKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        isOpen.value = false;
    }
};

watch(isOpen, (open) => {
    if (!open) {
        searchQuery.value = '';
    }
});
</script>

<template>
    <div
        ref="containerRef"
        class="relative w-full text-left"
        @keydown="onKeyDown"
    >
        <!-- Trigger Button -->
        <button
            type="button"
            :id="id"
            :disabled="disabled"
            @click="toggleDropdown"
            :class="[
                'flex w-full cursor-pointer items-center justify-between gap-2 rounded-lg border bg-white text-left transition-colors dark:bg-neutral-900',
                size === 'sm' ? 'px-2.5 py-1.5 text-xs' : 'px-3 py-2 text-xs',
                disabled
                    ? 'cursor-not-allowed bg-neutral-100 opacity-50 dark:bg-neutral-800'
                    : 'hover:border-neutral-400 dark:hover:border-neutral-600',
                isOpen
                    ? 'border-emerald-600 ring-2 ring-emerald-500/20 dark:border-emerald-500'
                    : 'border-neutral-300 dark:border-neutral-700',
                props.class,
            ]"
        >
            <span
                :class="[
                    'block truncate font-normal',
                    displayLabel
                        ? 'text-neutral-900 dark:text-neutral-100'
                        : 'text-neutral-400 dark:text-neutral-500',
                ]"
            >
                {{ displayLabel || placeholder }}
            </span>

            <div class="flex shrink-0 items-center gap-1 text-neutral-400">
                <button
                    v-if="clearable && modelValue && !disabled"
                    type="button"
                    @click="clearSelection"
                    class="rounded-full p-0.5 hover:bg-neutral-200 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    title="Hapus pilihan"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
                <ChevronDown
                    :class="[
                        'h-4 w-4 transition-transform duration-200',
                        isOpen
                            ? 'rotate-180 text-emerald-600 dark:text-emerald-400'
                            : '',
                    ]"
                />
            </div>
        </button>

        <!-- Hidden input for standard form submission validation -->
        <input
            v-if="required"
            type="text"
            :name="name"
            :value="modelValue"
            required
            tabindex="-1"
            class="sr-only"
        />

        <!-- Popover Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 max-h-72 w-full min-w-[240px] overflow-hidden rounded-xl border border-neutral-200 bg-white p-1.5 shadow-xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <!-- Search Input Box -->
                <div class="relative mb-1 p-1">
                    <Search
                        class="pointer-events-none absolute top-3 left-3 h-3.5 w-3.5 text-neutral-400"
                    />
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full rounded-md border border-neutral-200 bg-neutral-50 py-1.5 pr-2.5 pl-8 text-xs text-neutral-900 placeholder-neutral-400 focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:outline-none dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-100 dark:placeholder-neutral-500 dark:focus:bg-neutral-900"
                    />
                </div>

                <!-- Options List -->
                <div
                    class="max-h-56 space-y-1.5 overflow-y-auto px-0.5 py-1 text-xs"
                >
                    <!-- Groups & Options -->
                    <template v-if="filteredGroups.length > 0">
                        <div
                            v-for="(grp, gIdx) in filteredGroups"
                            :key="grp.group || gIdx"
                            class="space-y-0.5"
                        >
                            <!-- Group Title (if grouped) -->
                            <div
                                v-if="grp.group"
                                class="px-2.5 py-1 text-[10px] font-bold tracking-wider text-neutral-400 uppercase dark:text-neutral-500"
                            >
                                {{ grp.group }}
                            </div>

                            <!-- Group Items -->
                            <button
                                v-for="item in grp.items"
                                :key="item.value"
                                type="button"
                                @click="selectOption(item.value)"
                                :class="[
                                    'flex w-full cursor-pointer items-center justify-between rounded-lg px-2.5 py-2 text-left transition-colors',
                                    String(item.value) === String(modelValue)
                                        ? 'bg-emerald-50 font-semibold text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300'
                                        : 'text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800',
                                ]"
                            >
                                <div class="truncate pr-2">
                                    <div class="truncate text-xs">
                                        {{ item.label }}
                                    </div>
                                    <div
                                        v-if="item.description"
                                        class="truncate text-[11px] text-neutral-400 dark:text-neutral-500"
                                    >
                                        {{ item.description }}
                                    </div>
                                </div>
                                <Check
                                    v-if="
                                        String(item.value) ===
                                        String(modelValue)
                                    "
                                    class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                                />
                            </button>
                        </div>
                    </template>

                    <!-- Empty Results -->
                    <div
                        v-else
                        class="px-3 py-6 text-center text-xs text-neutral-400 dark:text-neutral-500"
                    >
                        {{ emptyText }}
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
