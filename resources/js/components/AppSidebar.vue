<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Building2,
    Calculator,
    FileCheck,
    Layers,
    LayoutGrid,
    Map,
    ShieldCheck,
    Sprout,
    TestTube,
    TreePine,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const accountingNav = [
    { title: 'Calculation Runs', href: '/calculations', icon: Calculator },
    { title: 'Approvals Queue', href: '/approvals', icon: ShieldCheck },
];

const dataNav = [
    { title: 'Estates & Consessions', href: '/data/estates', icon: Building2 },
    { title: 'Land Blocks', href: '/data/blocks', icon: Layers },
    { title: 'Forest Tree Census', href: '/data/tree-census', icon: TreePine },
    {
        title: 'Palm Stands & Phenology',
        href: '/data/palm-stands',
        icon: Sprout,
    },
    { title: 'Soil Lab Data', href: '/data/soil', icon: TestTube },
    { title: 'Evidence Vault', href: '/data/evidence', icon: FileCheck },
];

const analyticsNav = [
    { title: 'GIS Spatial Maps', href: '/gis', icon: Map },
    { title: 'Reports & Audits', href: '/reports', icon: BarChart3 },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="gap-2">
            <!-- Main Dashboard -->
            <SidebarGroup class="px-2 py-0">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard())"
                            tooltip="Dashboard"
                        >
                            <Link :href="dashboard()">
                                <LayoutGrid class="size-4" />
                                <span class="font-medium">Dashboard</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- Carbon Accounting -->
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Carbon Accounting</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="item in accountingNav"
                        :key="item.title"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentOrParentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" class="size-4" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- Data Management -->
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Data Management</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in dataNav" :key="item.title">
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentOrParentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" class="size-4" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- Spatial & Analytics -->
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Spatial & Analytics</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="item in analyticsNav"
                        :key="item.title"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentOrParentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" class="size-4" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
