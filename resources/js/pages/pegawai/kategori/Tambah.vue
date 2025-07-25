<script setup lang="ts">
/* eslint-disable */
import { useForm, Head } from '@inertiajs/vue3';
// import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, computed } from 'vue';

interface Parameter {
    id: number;
    kode_parameter: string;
    nama_parameter: string;
    satuan: string;
    harga: '';
}

interface SubKategori {
    id: number;
    kode_subkategori: string;
    nama: string;
}

const props = defineProps<{
    subkategori: SubKategori[];
    parameter: Parameter[];
}>();

const displayValue = ref('');

const form = useForm({
    nama: '',
    harga: '',
    subkategori: [],
    parameter: props.parameter.map((param) => ({
        id: param.id,
        checked: false,
        baku_mutu: '',
    })),
});

const formatCurrency = (value: string | number) => {
    if (!value) return '';
    const num = parseInt(value.toString().replace(/[^\d]/g, ''), 10);
    if (isNaN(num)) return '';

    form.harga = num.toString();
    return 'Rp ' + num.toLocaleString('id-ID');
};

const handleInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const formatted = formatCurrency(target.value);
    displayValue.value = formatted;
};

const formatOnBlur = () => {
    displayValue.value = formatCurrency(form.harga);
};

const isSubkategoriSelected = computed(() => form.subkategori.length > 0);
const isParameterSelected = computed(() => form.parameter.some(p => p.checked));

const submit = () => {
    const filterParam = form.parameter.filter((s) => s.checked);

    if (form.subkategori.length === 0 && filterParam.length === 0) {
        alert('Pilih minimal satu subkategori atau parameter!');
        return;
    }

    form.parameter = filterParam;
    form.post('/pegawai/kategori/store', {});
};
</script>

<template>

    <Head title="Tambah Kategori" />
    <div class="h-screen w-full bg-white lg:grid lg:grid-cols-3">
        <!-- Left Side - Logo Section -->
        <div
            class="hidden h-screen flex-col bg-customDarkGreen lg:col-span-1 lg:flex lg:items-center lg:justify-center">
            <img src="/assets/assetsadmin/logodlh.png" alt="Logo DLH" class="mx-auto h-48 w-auto object-contain" />
            <div class="mt-6 text-center text-white">
                <h2 class="mb-2 border-b border-white pb-2 text-2xl font-bold">SiLanYar</h2>
                <p class="text-sm">Sistem Laboratoruim Karanganyar</p>
            </div>
        </div>

        <!-- Right Side - Form Section -->
        <div class="flex h-screen items-start justify-center overflow-y-auto bg-white lg:col-span-2">
            <form @submit.prevent="submit" class="mx-auto grid w-full max-w-xl gap-6 p-6 md:p-12">
                <div class="grid gap-2 text-center">
                    <h1 class="text-3xl font-bold">Tambah Kategori</h1>
                </div>

                <div class="grid gap-4">
                    <!-- Nama Kategori -->
                    <div class="grid gap-2">
                        <Label for="nama">Nama Kategori</Label>
                        <Input id="nama" v-model="form.nama" type="text" placeholder="Masukkan nama kategori"
                            required />
                        <span v-if="form.errors.nama" class="text-sm text-red-600">
                            {{ form.errors.nama }}
                        </span>
                    </div>

                    <!-- Harga -->
                    <div class="grid gap-2">
                        <Label for="harga">Harga</Label>
                        <input id="harga" v-model="displayValue" @input="handleInput" @blur="formatOnBlur" type="text"
                            placeholder="Harga" required class="w-full rounded border px-3 py-2" />
                        <div v-if="form.errors.harga" class="text-sm text-red-500">
                            {{ form.errors.harga }}
                        </div>
                    </div>

                    <!-- Subkategori -->
                    <div class="grid gap-2">
                        <Label>Subkategori</Label>
                        <div v-for="sub in props.subkategori" :key="sub.id" class="mb-2 flex items-center gap-2">
                            <input type="checkbox" :value="sub.id" v-model="form.subkategori" :id="'sub-' + sub.id"
                                :disabled="isParameterSelected" />
                            <label :for="'sub-' + sub.id" class="text-sm font-semibold">{{ sub.nama }}</label>
                        </div>
                        <div class="text-sm text-red-500">{{ form.errors.subkategori }}</div>
                    </div>

                    <!-- Parameter dan Baku Mutu -->
                    <div class="grid gap-2">
                        <Label>Parameter dan Baku Mutu</Label>
                        <div v-for="(param, index) in form.parameter" :key="param.id"
                            class="mb-2 flex items-center gap-2">
                            <input type="checkbox" v-model="param.checked" :id="'param-' + param.id"
                                :disabled="isSubkategoriSelected" />
                            <label :for="'param-' + param.id" class="text-sm font-semibold">
                                {{ props.parameter[index].nama_parameter }}
                            </label>
                            <input v-model="param.baku_mutu" type="text" class="w-48 rounded border px-3 py-2"
                                :disabled="!param.checked || isSubkategoriSelected" placeholder="Baku Mutu" />
                            <div class="text-sm text-red-500">
                                {{ param.checked ? (form.errors as any)[`parameter.${index}.baku_mutu`] : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="mb-8 w-full rounded bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">Simpan</button>
            </form>
        </div>
    </div>
</template>
