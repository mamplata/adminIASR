<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import { AnnouncementIcon, CardIcon, DashboardIcon, DeviceIcon, UserIcon } from '@/Components/Icons/outline'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { TemplateIcon } from '@heroicons/vue/outline'
import { sidebarState } from '@/Composables'
</script>

<template>
    <PerfectScrollbar tagname="nav" aria-label="main" class="relative flex flex-col flex-1 max-h-full gap-4 px-3">
        <SidebarLink title="Dashboard" :href="route('dashboard')" :active="route().current('dashboard')">
            <template #icon>
                <DashboardIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <SidebarLink title="Users" :href="route('users.index')" :active="route().current('users.index')">
            <template #icon>
                <UserIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <SidebarLink title="Announcements" :href="route('announcements.index')"
            :active="route().current('announcements.index')">
            <template #icon>
                <AnnouncementIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <SidebarLink title="Registered Card" :href="route('registered-cards.index')"
            :active="route().current('registered-cards.index')">
            <template #icon>
                <CardIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <SidebarLink title="Device" :href="route('devices.index')" :active="route().current('devices.index')">
            <template #icon>
                <DeviceIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <SidebarCollapsible @click="sidebarState.isOpen = true" title="Logs" :active="route().current('logs.*')">
            <template #icon>
                <TemplateIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>

            <SidebarCollapsibleItem :href="route('logs.audit-logs.index')" title="Audit Logs"
                :active="route().current('logs.audit-logs.index')" />
            <SidebarCollapsibleItem :href="route('logs.entry-logs.index')" title="Entry Logs"
                :active="route().current('logs.entry-logs.index')" />
            <SidebarCollapsibleItem :href="route('logs.unauthorized-logs.index')" title="Unauthorized Logs"
                :active="route().current('logs.unauthorized-logs.index')" />
        </SidebarCollapsible>


    </PerfectScrollbar>
</template>
