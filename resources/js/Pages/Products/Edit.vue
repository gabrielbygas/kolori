<script setup>
// modify by claude
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ProductFormFields from './Partials/ProductFormFields.vue';

const props = defineProps({
    product: { type: Object, required: true },
    categories: { type: Array, required: true },
    units: { type: Array, required: true },
    packagingTypes: { type: Array, required: true },
});

const form = useForm({
    product_category_id: props.product.product_category_id,
    name_fr: props.product.name_fr,
    name_en: props.product.name_en,
    origin: props.product.origin,
    description: props.product.description,
    is_active: props.product.is_active,
    variants: props.product.variants.map((variant) => ({
        id: variant.id,
        unit_id: variant.unit_id,
        packaging_type_id: variant.packaging_type_id ?? '',
        sku: variant.sku ?? '',
        retail_price: variant.retail_price,
        wholesale_price: variant.wholesale_price,
        wholesale_min_qty: variant.wholesale_min_qty,
        is_active: variant.is_active,
    })),
});

function submit() {
    form.put(route('products.update', props.product.id));
}
</script>

<template>
    <Head title="Modifier le produit" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Modifier le produit — {{ product.name_fr }}
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
