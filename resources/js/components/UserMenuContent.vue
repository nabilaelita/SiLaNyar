<script setup lang="ts">
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';
import { UserIcon, LayoutDashboard, LogOut } from 'lucide-vue-next';

// interface Props {
//     user: User;
// }

// defineProps<Props>();
</script>

<template>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('customer.profile')" as="button">
                <UserIcon class="mr-2 h-4 w-4" />
                Profil
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('customer.dashboard')" as="button">
                <LayoutDashboard class="mr-2 h-4 w-4" />
                Dashboard
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('customer.profile')" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
