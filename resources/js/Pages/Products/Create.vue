<script setup>
// modify by claude
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ProductFormFields from './Partials/ProductFormFields.vue';

defineProps({
    categories: { type: Array, required: true },
    units: { type: Array, required: true },
    packagingTypes: { type: Array, required: true },
});

const form = useForm({
    product_category_id: '',
    name_fr: '',
    name_en: '',
    origin: 'local',
    description: '',
    is_active: true,
    variants: [
        {
            id: null,
            unit_id: '',
            packaging_type_id: '',
            sku: '',
            retail_price: '',
            wholesale_price: '',
            wholesale_min_qty: '',
            is_active: true,
        },
    ],
});

function submit() {
    form.post(route('products.store'));
}
</script>

<template>
    <Head title="Nouveau produit" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nouveau produit
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form
                    @submit.prevent="submit"
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                >
                    <ProductFormFields
                        :form="form"
                        :categories="categories"
                        :units="units"
                        :packaging-types="packagingTypes"
                    />

                    <div class="mt-6 flex items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Enregistrer</PrimaryButton>
                        <Link
                            :href="route('products.index')"
                            class="text-sm text-gray-600 hover:underline dark:text-gray-400"
                        >
                            Annuler
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
