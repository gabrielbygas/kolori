<script setup>
// modify by claude
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    variants: { type: Array, required: true },
    recentMovements: { type: Array, required: true },
});

const page = usePage();
const formTop = ref(null);
const quantityInput = ref(null);

const form = useForm({
    product_variant_id: '',
    type: 'in',
    quantity: '',
    reason: '',
});

function selectVariant(variantId) {
    form.product_variant_id = variantId;
    formTop.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    quantityInput.value?.focus();
}

function submit() {
    form.post(route('stock.store'), {
        onSuccess: () => form.reset('quantity', 'reason'),
    });
}
</script>

<template>
    <Head title="Stock" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Stock
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash?.success"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-300"
                >
                    {{ page.props.flash.success }}
                </div>

                <!-- Formulaire toujours visible : aucune navigation pour enregistrer un mouvement -->
                <form
                    ref="formTop"
                    @submit.prevent="submit"
                    class="space-y-4 rounded-lg bg-white p-4 shadow sm:p-6 dark:bg-gray-800"
                >
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Nouveau mouvement
                    </h3>

                    <div>
                        <InputLabel for="product_variant_id" value="Produit" />
                        <select
                            id="product_variant_id"
                            v-model="form.product_variant_id"
                            class="mt-1 block w-full rounded-md border-gray-300 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="" disabled>— Choisir —</option>
                            <option v-for="variant in variants" :key="variant.id" :value="variant.id">
                                {{ variant.label }} (stock : {{ variant.current_stock }})
                            </option>
                        </select>
                        <InputError :message="form.errors.product_variant_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Type de mouvement" />
                        <div class="mt-1 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="form.type = 'in'"
                                :class="[
                                    'rounded-md border-2 py-3 text-base font-semibold transition',
                                    form.type === 'in'
                                        ? 'border-green-600 bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                        : 'border-gray-300 text-gray-500 dark:border-gray-700 dark:text-gray-400',
                                ]"
                            >
                                + Entrée
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'out'"
                                :class="[
                                    'rounded-md border-2 py-3 text-base font-semibold transition',
                                    form.type === 'out'
                                        ? 'border-red-600 bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300'
                                        : 'border-gray-300 text-gray-500 dark:border-gray-700 dark:text-gray-400',
                                ]"
                            >
                                − Sortie
                            </button>
                        </div>
                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="quantity" value="Quantité" />
                        <TextInput
                            id="quantity"
                            ref="quantityInput"
                            v-model="form.quantity"
                            type="number"
                            step="0.01"
                            min="0.01"
                            inputmode="decimal"
                            class="mt-1 block w-full py-3 text-base"
                        />
                        <InputError :message="form.errors.quantity" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="reason" value="Motif (optionnel)" />
                        <TextInput
                            id="reason"
                            v-model="form.reason"
                            placeholder="Ex. réception fournisseur, casse, inventaire..."
                            class="mt-1 block w-full py-3 text-base"
                        />
                    </div>

                    <PrimaryButton :disabled="form.processing" class="w-full justify-center py-3 text-base">
                        Enregistrer le mouvement
                    </PrimaryButton>
                </form>

                <!-- Stock actuel -->
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <h3 class="border-b border-gray-200 px-4 py-3 text-base font-semibold text-gray-900 sm:px-6 dark:border-gray-700 dark:text-gray-100">
                        Stock actuel
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="variant in variants"
                            :key="variant.id"
                            :class="[
                                'flex items-center justify-between gap-3 px-4 py-3 sm:px-6',
                                variant.is_low_stock ? 'bg-orange-50 dark:bg-orange-900/20' : '',
                            ]"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm text-gray-900 dark:text-gray-200">
                                    {{ variant.label }}
                                    <span v-if="variant.is_low_stock" class="ml-1 text-xs font-semibold text-orange-600 dark:text-orange-400">
                                        ⚠ Stock bas
                                    </span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Stock :
                                    <span :class="['font-medium', variant.is_low_stock ? 'text-orange-700 dark:text-orange-400' : '']">
                                        {{ variant.current_stock }}
                                    </span>
                                </p>
                            </div>
                            <SecondaryButton type="button" @click="selectVariant(variant.id)">
                                Mouvement
                            </SecondaryButton>
                        </li>
                        <li v-if="variants.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 sm:px-6 dark:text-gray-400">
                            Aucun produit actif pour l'instant.
                        </li>
                    </ul>
                </div>

                <!-- Derniers mouvements (journal, pas de pagination : reste léger) -->
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <h3 class="border-b border-gray-200 px-4 py-3 text-base font-semibold text-gray-900 sm:px-6 dark:border-gray-700 dark:text-gray-100">
                        Derniers mouvements
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="movement in recentMovements" :key="movement.id" class="px-4 py-3 sm:px-6">
                            <div class="flex items-center justify-between gap-3">
                                <p class="min-w-0 truncate text-sm text-gray-900 dark:text-gray-200">
                                    {{ movement.variant_label }}
                                </p>
                                <span
                                    :class="[
                                        'shrink-0 rounded px-2 py-0.5 text-xs font-semibold',
                                        movement.type === 'in'
                                            ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                            : 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    ]"
                                >
                                    {{ movement.type === 'in' ? '+' : '−' }}{{ movement.quantity }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ movement.created_at }}<template v-if="movement.user_name"> — {{ movement.user_name }}</template><template v-if="movement.reason"> — {{ movement.reason }}</template>
                            </p>
                        </li>
                        <li v-if="recentMovements.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 sm:px-6 dark:text-gray-400">
                            Aucun mouvement pour l'instant.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
