<script setup>
// modify by claude
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    users: { type: Object, required: true },
});

const page = usePage();

const roleLabels = {
    admin: 'Admin',
    vendeur: 'Vendeur',
    logisticien: 'Logisticien',
};
</script>

<template>
    <Head title="Utilisateurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Utilisateurs
                </h2>
                <Link :href="route('users.create')">
                    <PrimaryButton>+ Nouvel utilisateur</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash?.success"
                    class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-300"
                >
                    {{ page.props.flash.success }}
                </div>

                <div class="overflow-x-auto bg-white shadow sm:rounded-lg dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Nom
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Email
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Téléphone
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Rôle
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Statut
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">
                                    {{ user.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.email }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.phone ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        class="mr-1 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700"
                                    >
                                        {{ roleLabels[role] ?? role }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="user.is_active ? 'text-green-600' : 'text-gray-400'">
                                        {{ user.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucun utilisateur pour l'instant.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="users.links.length > 3" class="mt-4 flex flex-wrap gap-1">
                    <Link
                        v-for="(link, index) in users.links"
                        :key="index"
                        :href="link.url ?? '#'"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                            !link.url ? 'pointer-events-none opacity-50' : '',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
