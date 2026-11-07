<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 32 32"
        fill="none"
        :class="className"
        v-bind="$attrs"
        aria-hidden="true"
    >
        <!-- Carbon crystal & agroforestry canopy emblem -->
        <rect
            width="32"
            height="32"
            rx="7"
            class="fill-emerald-600 dark:fill-emerald-500"
        />
        <path
            d="M16 6L23.7942 10.5V19.5L16 24L8.20577 19.5V10.5L16 6Z"
            stroke="white"
            stroke-width="1.5"
            stroke-opacity="0.35"
        />
        <path
            d="M16 8C13 12 11 15 11 18C11 20.76 13.24 23 16 23C18.76 23 21 20.76 21 18C21 15 19 12 16 8Z"
            fill="white"
        />
        <path
            d="M16 11V21M16 14L19 12.5M16 17.5L13 16"
            stroke="#059669"
            stroke-width="1.3"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
