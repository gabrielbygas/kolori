<script setup>
// modify by claude
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    products: { type: Object, required: true },
});

const page = usePage();
</script>

<template>
    <Head title="Catalogue produits" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Catalogue produits
                </h2>
                <Link :href="route('products.create')">
                    <PrimaryButton>+ Nouveau produit</PrimaryButton>
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
                                    Produit
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Catégorie
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Origine
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Variantes
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Statut
                                </th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="product in products.data" :key="product.id">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">
                                    {{ product.name_fr }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ product.category?.name_fr }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ product.origin === 'local' ? 'Local' : 'Importé' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span
                                        v-for="variant in product.variants"
                                        :key="variant.id"
                                        :class="[
                                            'mr-2 inline-block rounded px-2 py-0.5 text-xs',
                                            variant.is_low_stock
                                                ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300'
                                                : 'bg-gray-100 dark:bg-gray-700',
                                        ]"
                                        :title="variant.is_low_stock ? 'Stock bas' : ''"
                                    >
                                        {{ variant.unit?.name_fr }}<template v-if="variant.packaging_type"> / {{ variant.packaging_type?.name_fr }}</template> — ${{ variant.retail_price }}<template v-if="variant.is_low_stock"> ⚠</template>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="product.is_active ? 'text-green-600' : 'text-gray-400'">
                                        {{ product.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <Link
                                        :href="route('products.edit', product.id)"
                                        class="text-indigo-600 hover:underline dark:text-indigo-400"
                                    >
                                        Modifier
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="products.data.length === 0">
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucun produit pour l'instant.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="products.links.length > 3" class="mt-4 flex flex-wrap gap-1">
                    <Link
                        v-for="(link, index) in products.links"
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
