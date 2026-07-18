<script setup>
// modify by claude
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: '',
    is_active: true,
});

function submit() {
    form.post(route('users.store'));
}
</script>

<template>
    <Head title="Nouvel utilisateur" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nouvel utilisateur
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form
                    @submit.prevent="submit"
                    class="space-y-6 bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                >
                    <div>
                        <InputLabel for="name" value="Nom" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Téléphone (optionnel)" />
                        <TextInput id="phone" v-model="form.phone" class="mt-1 block w-full" />
                        <InputError :message="form.errors.phone" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Rôle" />
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="" disabled>— Choisir —</option>
                            <option value="admin">Admin</option>
                            <option value="logisticien">Logisticien</option>
                            <option value="vendeur">Vendeur</option>
                        </select>
                        <InputError :message="form.errors.role" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="password" value="Mot de passe" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Confirmation" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="is_active"
                            type="checkbox"
                            v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <InputLabel for="is_active" value="Compte actif" class="mb-0 ms-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Créer</PrimaryButton>
                        <Link
                            :href="route('users.index')"
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
