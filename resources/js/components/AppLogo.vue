<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const appName = (page.props.name as string) || 'Nusantara Carbon';
</script>

<template>
    <div class="flex items-center gap-2.5">
        <AppLogoIcon class="size-8 shrink-0 rounded-lg shadow-xs" />
        <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold tracking-tight text-foreground">
                {{ appName }}
            </span>
            <span class="truncate text-[11px] text-muted-foreground">
                PT Nusantara Carbon & Agroforestry
            </span>
        </div>
    </div>
</template>
