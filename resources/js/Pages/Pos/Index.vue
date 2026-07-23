<script setup>
// modify by claude
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    variants: { type: Array, required: true },
    exchangeRate: { type: Number, required: true },
    pricingMode: { type: String, required: true },
});

const page = usePage();

const search = ref('');
const cart = ref([]);

const filteredVariants = computed(() => {
    if (!search.value.trim()) {
        return [];
    }
    const query = search.value.toLowerCase();
    return props.variants.filter((v) => v.label.toLowerCase().includes(query)).slice(0, 8);
});

function addToCart(variant) {
    const existing = cart.value.find((item) => item.id === variant.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.value.push({
            id: variant.id,
            label: variant.label,
            quantity: 1,
            retail_price: Number(variant.retail_price),
            wholesale_price: variant.wholesale_price !== null ? Number(variant.wholesale_price) : null,
            wholesale_min_qty: variant.wholesale_min_qty,
            manualTier: null,
        });
    }
    search.value = '';
}

function removeFromCart(index) {
    cart.value.splice(index, 1);
}

function suggestedTier(item) {
    if (item.wholesale_price !== null && item.wholesale_min_qty !== null && item.quantity >= item.wholesale_min_qty) {
        return 'wholesale';
    }
    return 'retail';
}

function effectiveTier(item) {
    if (props.pricingMode === 'manual' && item.manualTier) {
        return item.manualTier;
    }
    return suggestedTier(item);
}

function unitPrice(item) {
    return effectiveTier(item) === 'wholesale' ? item.wholesale_price : item.retail_price;
}

function lineSubtotal(item) {
    return item.quantity * unitPrice(item);
}

const totalUsd = computed(() => cart.value.reduce((sum, item) => sum + lineSubtotal(item), 0));
const totalCdf = computed(() => Math.round(totalUsd.value * props.exchangeRate));

const form = useForm({
    items: [],
    payment_currency: 'usd',
    amount_tendered: '',
});

const amountDue = computed(() => (form.payment_currency === 'cdf' ? totalCdf.value : totalUsd.value));
const changeDue = computed(() => (Number(form.amount_tendered) || 0) - amountDue.value);

function formatUsd(value) {
    return Number(value).toFixed(2);
}

function formatCdf(value) {
    return Math.round(value).toLocaleString('fr-FR');
}

function submit() {
    form.items = cart.value.map((item) => ({
        product_variant_id: item.id,
        quantity: item.quantity,
        pricing_tier: props.pricingMode === 'manual' ? effectiveTier(item) : null,
    }));

    form.post(route('pos.store'), {
        onSuccess: () => {
            cart.value = [];
            form.reset('amount_tendered');
        },
    });
}
</script>

<template>
    <Head title="Caisse" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Caisse
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash?.success"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-300"
                >
                    {{ page.props.flash.success }}
                </div>

                <!-- Recherche produit -->
                <div class="rounded-lg bg-white p-4 shadow sm:p-6 dark:bg-gray-800">
                    <InputLabel for="search" value="Ajouter un produit" />
                    <TextInput
                        id="search"
                        v-model="search"
                        placeholder="Tapez le nom du produit..."
                        class="mt-1 block w-full py-3 text-base"
                        autocomplete="off"
                    />
                    <ul v-if="filteredVariants.length > 0" class="mt-2 divide-y divide-gray-200 overflow-hidden rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                        <li v-for="variant in filteredVariants" :key="variant.id">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                @click="addToCart(variant)"
                            >
                                <span class="text-gray-900 dark:text-gray-200">{{ variant.label }}</span>
                                <span class="shrink-0 text-gray-500 dark:text-gray-400">
                                    ${{ formatUsd(variant.retail_price) }}
                                    <template v-if="variant.current_stock <= 0"> — rupture</template>
                                </span>
                            </button>
                        </li>
                    </ul>
                    <p v-else-if="search.trim()" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Aucun produit ne correspond.
                    </p>
                </div>

                <!-- Panier -->
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <h3 class="border-b border-gray-200 px-4 py-3 text-base font-semibold text-gray-900 sm:px-6 dark:border-gray-700 dark:text-gray-100">
                        Panier
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="(item, index) in cart" :key="item.id" class="px-4 py-3 sm:px-6">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm text-gray-900 dark:text-gray-200">{{ item.label }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ${{ formatUsd(unitPrice(item)) }} / unité
                                        <span v-if="effectiveTier(item) === 'wholesale'" class="ml-1 rounded bg-indigo-50 px-1.5 py-0.5 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            Prix de gros
                                        </span>
                                    </p>
                                    <InputError :message="form.errors[`items.${index}.quantity`]" class="mt-1" />
                                </div>
                                <p class="shrink-0 text-sm font-medium text-gray-900 dark:text-gray-200">
                                    ${{ formatUsd(lineSubtotal(item)) }}
                                </p>
                            </div>

                            <div class="mt-2 flex items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-md border border-gray-300 text-lg font-semibold text-gray-600 dark:border-gray-600 dark:text-gray-300"
                                        @click="item.quantity = Math.max(0.01, item.quantity - 1)"
                                    >
                                        −
                                    </button>
                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        class="w-20 rounded-md border-gray-300 py-2 text-center text-base shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    />
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-md border border-gray-300 text-lg font-semibold text-gray-600 dark:border-gray-600 dark:text-gray-300"
                                        @click="item.quantity += 1"
                                    >
                                        +
                                    </button>
                                </div>

                                <div v-if="pricingMode === 'manual' && item.wholesale_price !== null" class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="item.manualTier = 'retail'"
                                        :class="[
                                            'rounded px-2 py-1 text-xs font-medium',
                                            effectiveTier(item) === 'retail'
                                                ? 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100'
                                                : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                        ]"
                                    >
                                        Détail
                                    </button>
                                    <button
                                        type="button"
                                        @click="item.manualTier = 'wholesale'"
                                        :class="[
                                            'rounded px-2 py-1 text-xs font-medium',
                                            effectiveTier(item) === 'wholesale'
                                                ? 'bg-indigo-200 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100'
                                                : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                        ]"
                                    >
                                        Gros
                                    </button>
                                </div>

                                <DangerButton type="button" class="ml-auto" @click="removeFromCart(index)">
                                    Retirer
                                </DangerButton>
                            </div>
                        </li>
                        <li v-if="cart.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 sm:px-6 dark:text-gray-400">
                            Panier vide — recherchez un produit ci-dessus.
                        </li>
                    </ul>
                </div>

                <!-- Total -->
                <div class="rounded-lg bg-white p-4 shadow sm:p-6 dark:bg-gray-800">
                    <div class="flex items-baseline justify-between">
                        <span class="text-base font-semibold text-gray-900 dark:text-gray-100">Total</span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ formatUsd(totalUsd) }}</span>
                    </div>
                    <div class="mt-1 text-right text-sm text-gray-500 dark:text-gray-400">
                        {{ formatCdf(totalCdf) }} CDF
                    </div>
                </div>

                <!-- Paiement -->
                <form
                    @submit.prevent="submit"
                    class="space-y-4 rounded-lg bg-white p-4 shadow sm:p-6 dark:bg-gray-800"
                >
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Paiement</h3>

                    <div>
                        <InputLabel value="Devise reçue" />
                        <div class="mt-1 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="form.payment_currency = 'usd'"
                                :class="[
                                    'rounded-md border-2 py-3 text-base font-semibold transition',
                                    form.payment_currency === 'usd'
                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                        : 'border-gray-300 text-gray-500 dark:border-gray-700 dark:text-gray-400',
                                ]"
                            >
                                USD
                            </button>
                            <button
                                type="button"
                                @click="form.payment_currency = 'cdf'"
                                :class="[
                                    'rounded-md border-2 py-3 text-base font-semibold transition',
                                    form.payment_currency === 'cdf'
                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                        : 'border-gray-300 text-gray-500 dark:border-gray-700 dark:text-gray-400',
                                ]"
                            >
                                CDF
                            </button>
                        </div>
                        <InputError :message="form.errors.payment_currency" class="mt-2" />
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Montant dû :
                        <span class="font-medium text-gray-900 dark:text-gray-200">
                            <template v-if="form.payment_currency === 'cdf'">{{ formatCdf(amountDue) }} CDF</template>
                            <template v-else>${{ formatUsd(amountDue) }}</template>
                        </span>
                    </p>

                    <div>
                        <InputLabel for="amount_tendered" value="Montant reçu" />
                        <TextInput
                            id="amount_tendered"
                            v-model="form.amount_tendered"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            class="mt-1 block w-full py-3 text-base"
                        />
                        <InputError :message="form.errors.amount_tendered" class="mt-2" />
                    </div>

                    <div class="rounded-md bg-gray-50 p-4 text-center dark:bg-gray-900/40">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Monnaie à rendre</span>
                        <p
                            :class="[
                                'text-2xl font-bold',
                                changeDue < 0 ? 'text-red-600' : 'text-green-600',
                            ]"
                        >
                            <template v-if="form.payment_currency === 'cdf'">{{ formatCdf(changeDue) }} CDF</template>
                            <template v-else>${{ formatUsd(changeDue) }}</template>
                        </p>
                    </div>

                    <PrimaryButton
                        :disabled="form.processing || cart.length === 0"
                        class="w-full justify-center py-3 text-base"
                    >
                        Valider la vente
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
