<script setup>
// modify by claude
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sale: { type: Object, required: true },
});

function formatUsd(value) {
    return Number(value).toFixed(2);
}

function formatCdf(value) {
    return Math.round(value).toLocaleString('fr-FR');
}

function print_() {
    window.print();
}
</script>

<template>
    <Head title="Reçu de vente" />

    <div class="min-h-screen bg-gray-100 py-8 dark:bg-gray-900">
        <div class="mx-auto max-w-sm px-4">
            <div class="mb-4 flex items-center justify-between print:hidden">
                <Link :href="route('pos.index')" class="text-sm text-gray-600 hover:underline dark:text-gray-400">
                    ← Retour à la caisse
                </Link>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800 print:shadow-none">
                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">Kolori</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Reçu de vente — {{ sale.created_at }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Vendeur : {{ sale.user_name }} — Réf. {{ sale.id.slice(0, 8) }}
                </p>

                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="py-1">Produit</th>
                            <th class="py-1 text-right">Qté</th>
                            <th class="py-1 text-right">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in sale.items" :key="index" class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-1 text-gray-900 dark:text-gray-200">
                                {{ item.label }}
                                <span v-if="item.pricing_tier === 'wholesale'" class="text-xs text-gray-500 dark:text-gray-400">(gros)</span>
                            </td>
                            <td class="py-1 text-right text-gray-500 dark:text-gray-400">{{ item.quantity }}</td>
                            <td class="py-1 text-right text-gray-900 dark:text-gray-200">${{ formatUsd(item.subtotal) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 space-y-1 border-t border-gray-200 pt-4 text-sm dark:border-gray-700">
                    <div class="flex justify-between text-base font-bold text-gray-900 dark:text-gray-100">
                        <span>Total USD</span>
                        <span>${{ formatUsd(sale.total_usd) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 dark:text-gray-100">
                        <span>Total CDF</span>
                        <span>{{ formatCdf(sale.total_cdf) }} CDF</span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Devise reçue</span>
                        <span>{{ sale.payment_currency.toUpperCase() }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Montant reçu</span>
                        <span>{{ formatUsd(sale.amount_tendered) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Monnaie rendue</span>
                        <span>{{ formatUsd(sale.change_due) }}</span>
                    </div>
                </div>

                <p class="mt-4 text-center text-xs text-gray-400">
                    Taux appliqué : 1 USD = {{ formatUsd(sale.exchange_rate) }} CDF — Merci de votre achat.
                </p>
            </div>

            <div class="mt-4 flex gap-3 print:hidden">
                <button
                    type="button"
                    class="flex-1 rounded-md bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500"
                    @click="print_"
                >
                    Imprimer
                </button>
                <a
                    :href="route('sales.receipt.pdf', sale.id)"
                    class="flex-1 rounded-md border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Télécharger en PDF
                </a>
            </div>
        </div>
    </div>
</template>
