<script setup lang="ts">
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps<{ hasil_uji: any }>()

const form = ref({
    terkait: '',
    masalah: '',
    perbaikan: '',
})

function submitAduan() {
    router.post(
        `/customer/hasiluji/aduan/${props.hasil_uji.id}`,
        {
            masalah: form.value.masalah,
            perbaikan: form.value.perbaikan,
            terkait: form.value.terkait,
        }
    )
}
</script>

<template>

    <Head title="Tambah Aduan" />
    <CustomerLayout>
        <div class="p-6">
            <form @submit.prevent="submitAduan" class="space-y-6 max-w-3xl mx-auto">
                <div>
                    <h1 class="text-2xl font-bold mb-4">Tambah Aduan</h1>
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-1">ID Hasil Uji</label>
                        <p class="text-gray-900">{{ props.hasil_uji.kode_hasil_uji }}</p>
                    </div>
                    <h2 class="font-bold text-xl mb-2">Aduan Terkait</h2>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" v-model="form.terkait" value="administrasi" class="accent-blue-500" />
                            <span>Administrasi</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" v-model="form.terkait" value="pengujian" class="accent-blue-500" />
                            <span>Pengujian</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-lg block mb-1">Subjek Aduan</label>
                    <input v-model="form.masalah" type="text" maxlength="100"
                        placeholder="Ringkasan singkat masalah anda" class="w-full border rounded px-3 py-2" />
                    <div class="text-right text-xs text-gray-500">{{ form.masalah.length }}/100 Karakter</div>
                </div>

                <div>
                    <label class="font-bold text-lg block mb-1">Detail Aduan</label>
                    <textarea v-model="form.perbaikan" maxlength="1000" rows="4"
                        placeholder="Jelaskan masalah anda secara detail"
                        class="w-full border rounded px-3 py-2"></textarea>
                    <div class="text-right text-xs text-gray-500">{{ form.perbaikan.length }}/1000 Karakter</div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-green-700 text-white px-6 py-2 rounded-md hover:bg-green-800">
                        Kirim Aduan
                    </button>
                </div>
            </form>
        </div>
    </CustomerLayout>
</template>