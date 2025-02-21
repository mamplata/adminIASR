<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import { AnnouncementIcon, DashboardIcon, UserIcon } from '@/Components/Icons/outline'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { TemplateIcon } from '@heroicons/vue/outline'
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

        <SidebarLink title="Announcements" :href="route('announcements.index')" :active="route().current('announcements.index')">
            <template #icon>
                <AnnouncementIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <SidebarCollapsible title="Logs" :active="route().current('logs.*')">
            <template #icon>
                <TemplateIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>

            <SidebarCollapsibleItem :href="route('logs.audit-logs.index')" title="Audit Logs"
                :active="route().current('logs.audit-logs.index')" />
        </SidebarCollapsible>

    </PerfectScrollbar>
</template>
