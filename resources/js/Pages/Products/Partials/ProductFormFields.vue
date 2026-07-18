<script setup>
// modify by claude
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    form: { type: Object, required: true },
    categories: { type: Array, required: true },
    units: { type: Array, required: true },
    packagingTypes: { type: Array, required: true },
});

function addVariant() {
    props.form.variants.push({
        id: null,
        unit_id: '',
        packaging_type_id: '',
        sku: '',
        retail_price: '',
        wholesale_price: '',
        wholesale_min_qty: '',
        is_active: true,
    });
}

function removeVariant(index) {
    props.form.variants.splice(index, 1);
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel for="product_category_id" value="Catégorie" />
            <select
                id="product_category_id"
                v-model="form.product_category_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option value="" disabled>— Choisir —</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name_fr }}
                </option>
            </select>
            <InputError :message="form.errors.product_category_id" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <InputLabel for="name_fr" value="Nom (FR)" />
                <TextInput id="name_fr" v-model="form.name_fr" class="mt-1 block w-full" />
                <InputError :message="form.errors.name_fr" class="mt-2" />
            </div>
            <div>
                <InputLabel for="name_en" value="Nom (EN)" />
                <TextInput id="name_en" v-model="form.name_en" class="mt-1 block w-full" />
                <InputError :message="form.errors.name_en" class="mt-2" />
            </div>
        </div>

        <div>
            <InputLabel value="Origine" />
            <div class="mt-2 flex gap-6">
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        value="local"
                        v-model="form.origin"
                        class="text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">Local</span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        value="imported"
                        v-model="form.origin"
                        class="text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">Importé</span>
                </label>
            </div>
            <InputError :message="form.errors.origin" class="mt-2" />
        </div>

        <div>
            <InputLabel for="description" value="Description" />
            <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            ></textarea>
            <InputError :message="form.errors.description" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input
                id="is_active"
                type="checkbox"
                v-model="form.is_active"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            />
            <InputLabel for="is_active" value="Produit actif" class="mb-0 ms-2" />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Variantes (unité / emballage / prix)
                </h3>
                <SecondaryButton type="button" @click="addVariant">
                    + Ajouter une variante
                </SecondaryButton>
            </div>
            <InputError :message="form.errors.variants" class="mb-2" />

            <div
                v-for="(variant, index) in form.variants"
                :key="variant.id ?? `new-${index}`"
                class="mb-4 grid grid-cols-1 gap-4 rounded-md border border-gray-200 p-4 sm:grid-cols-6 dark:border-gray-700"
            >
                <div class="sm:col-span-2">
                    <InputLabel :for="`unit_${index}`" value="Unité" />
                    <select
                        :id="`unit_${index}`"
                        v-model="variant.unit_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="" disabled>— Choisir —</option>
                        <option v-for="unit in units" :key="unit.id" :value="unit.id">
                            {{ unit.name_fr }}
                        </option>
                    </select>
                    <InputError :message="form.errors[`variants.${index}.unit_id`]" class="mt-1" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel :for="`packaging_${index}`" value="Emballage (optionnel)" />
                    <select
                        :id="`packaging_${index}`"
                        v-model="variant.packaging_type_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">— Aucun —</option>
                        <option
                            v-for="packagingType in packagingTypes"
                            :key="packagingType.id"
                            :value="packagingType.id"
                        >
                            {{ packagingType.name_fr }}
                        </option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <InputLabel :for="`sku_${index}`" value="SKU (optionnel)" />
                    <TextInput :id="`sku_${index}`" v-model="variant.sku" class="mt-1 block w-full" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel :for="`retail_${index}`" value="Prix détail (USD)" />
                    <TextInput
                        :id="`retail_${index}`"
                        v-model="variant.retail_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors[`variants.${index}.retail_price`]" class="mt-1" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel :for="`wholesale_${index}`" value="Prix gros (USD, optionnel)" />
                    <TextInput
                        :id="`wholesale_${index}`"
                        v-model="variant.wholesale_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full"
                    />
                </div>

                <div class="sm:col-span-1">
                    <InputLabel :for="`min_qty_${index}`" value="Seuil gros" />
                    <TextInput
                        :id="`min_qty_${index}`"
                        v-model="variant.wholesale_min_qty"
                        type="number"
                        min="1"
                        class="mt-1 block w-full"
                    />
                </div>

                <div class="flex items-end sm:col-span-1">
                    <DangerButton
                        type="button"
                        :disabled="form.variants.length <= 1"
                        @click="removeVariant(index)"
                    >
                        Retirer
                    </DangerButton>
                </div>
            </div>
        </div>
    </div>
</template>
