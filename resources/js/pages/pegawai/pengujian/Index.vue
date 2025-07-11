<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    pengujian: any[];
    unscheduled_pengajuan: any[];
    filter: {
        status: string;
        tanggal: string;
    };
    userRole: string;
}>();

const status = ref(props.filter.status ?? '');
const tanggal = ref(props.filter.tanggal ?? '');

const formatTanggal = (tanggalStr: string) => {
    const date = new Date(tanggalStr);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const handleFilter = () => {
    router.get(
        '/pegawai/pengujian',
        {
            status: status.value || undefined,
            tanggal: tanggal.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilter = () => {
    status.value = '';
    tanggal.value = '';
    router.get(
        '/pegawai/pengujian',
        {},
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Watch for changes and auto-filter
watch([status, tanggal], () => {
    handleFilter();
});

// Modal Delete
const showDeleteModal = ref(false);
const deletingPengujian = ref<any | null>(null);

const openDeleteModal = (item: any) => {
    deletingPengujian.value = item;
    showDeleteModal.value = true;
};
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingPengujian.value = null;
};

const handleDelete = () => {
    if (!deletingPengujian.value) return;
    router.delete(`/pegawai/pengujian/${deletingPengujian.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
        },
    });
};

// Add permissions check
const page = usePage();

const permissions =
    (page.props.auth && Array.isArray((page.props.auth as any).permissions))
        ? ((page.props.auth as any).permissions as string[])
        : [];

const can = (permission: string): boolean => {
    return permissions.includes(permission);
};

const userRole = props.userRole;
</script>

<template>

    <Head title="Daftar Pengujian" />
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-black">DAFTAR PENGUJIAN</h1>
                <Link v-if="can('tambah pengujian')" :href="route('pegawai.pengujian.create')"
                    class="flex items-center gap-2 rounded bg-customDarkGreen px-4 py-2 text-white">
                <span>+</span> Tambah
                </Link>
            </div>

            <!-- Warning for unscheduled pengajuan (khusus admin) -->
            <div v-if="userRole === 'admin' && unscheduled_pengajuan && unscheduled_pengajuan.length > 0" class="mb-4">
                <div class="rounded border-l-4 border-orange-500 bg-orange-100 p-3 text-orange-700">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm">
                                <strong>⚠️ Peringatan:</strong> Ada {{ unscheduled_pengajuan.length }} pengajuan yang
                                belum dijadwalkan untuk
                                pengujian:
                            </p>
                            <ul class="mt-1 list-inside list-disc text-xs">
                                <li v-for="item in unscheduled_pengajuan" :key="item.id">{{ item.kode_pengajuan }} - {{
                                    item.instansi?.nama }}</li>
                            </ul>
                            <p class="text-sm pt-2">
                                <strong>⚠️ Peringatan:</strong> Pengajuan yang <span class="font-semibold">status
                                    jadwalnya masih diproses</span> tidak dapat dibuatkan pengujian:
                            </p>
                            <ul v-if="unscheduled_pengajuan.some(i => i.jadwal && i.jadwal.status !== 'diterima')"
                                class="mt-1 list-inside list-disc text-xs text-red-700">
                                <li v-for="item in unscheduled_pengajuan.filter(i => i.jadwal && i.jadwal.status !== 'diterima')"
                                    :key="item.id">
                                    {{ item.kode_pengajuan }} - {{ item.instansi?.nama }} (Jadwal: {{
                                    item.jadwal?.status }})
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info: Untuk mengubah status pengujian (selalu tampil) -->
            <div v-if="userRole !== 'admin' && unscheduled_pengajuan && unscheduled_pengajuan.length > 0" class="mb-2">
                <div class="rounded border-l-4 border-yellow-400 bg-yellow-50 p-2 text-yellow-800 text-xs">
                    <strong>ℹ️ Info:</strong> Untuk mengubah status pengujian, silakan buka detail pengujian
                    dan lakukan update status di halaman detail.
                </div>
            </div>

            <!-- Filter -->
            <div class="mb-6 flex items-end gap-4">
                <div class="flex w-40 flex-col">
                    <label for="status" class="mb-1 text-sm font-medium text-gray-700">Status</label>
                    <select id="status" v-model="status"
                        class="rounded border-gray-300 bg-customDarkGreen px-2 py-1 text-white">
                        <option value="">Semua Status</option>
                        <option value="selesai">Selesai</option>
                        <option value="diproses">Diproses</option>
                    </select>
                </div>
                <div class="flex w-40 flex-col">
                    <label for="tanggal" class="mb-1 text-sm font-medium text-gray-700">Tanggal</label>
                    <input id="tanggal" type="date" v-model="tanggal"
                        class="rounded border-gray-300 bg-customDarkGreen px-2 py-1 text-white transition-colors focus:bg-white focus:text-black" />
                </div>
                <button @click="resetFilter"
                    class="rounded bg-gray-500 px-3 py-1 text-white transition hover:bg-gray-600">Reset</button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full overflow-hidden rounded-xl bg-white shadow">
                    <thead>
                        <tr class="bg-customDarkGreen text-white">
                            <th class="rounded-tl-xl px-4 py-3 text-left font-semibold">ID Pengujian</th>
                            <th class="px-4 py-3 text-left font-semibold">ID Form Pengajuan</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama Teknisi</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal Pengujian</th>
                            <th class="px-4 py-3 text-left font-semibold">Jam Mulai</th>
                            <th class="px-4 py-3 text-left font-semibold">Jam Selesai</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama Kategori</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="rounded-tr-xl px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in pengujian" :key="item.id"
                            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-200'">
                            <td class="border-b px-4 py-3">{{ item.kode_pengujian ?? '-' }}</td>
                            <td class="border-b px-4 py-3">{{ item.form_pengajuan.kode_pengajuan ?? '-' }}</td>
                            <td class="border-b px-4 py-3">{{ item.user?.nama ?? '-' }}</td>
                            <td class="border-b px-4 py-3">{{ formatTanggal(item.tanggal_uji) ?? '-' }}</td>
                            <td class="border-b px-4 py-3">{{ item.jam_mulai ?? '-' }}</td>
                            <td class="border-b px-4 py-3">{{ item.jam_selesai ?? '-' }}</td>
                            <td class="border-b px-4 py-3">{{ item.kategori?.nama ?? '-' }}</td>
                            <td class="border-b px-4 py-3">
                                <span :class="[
                                    'rounded px-2 py-1 text-xs font-semibold',
                                    item.status === 'selesai' ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white',
                                ]">
                                    {{ item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : '-' }}
                                </span>
                            </td>
                            <td class="border-b px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="route('pegawai.pengujian.detail', item.id)"
                                        class="text-blue-500 hover:text-blue-700" title="Lihat">
                                    <span>👁️</span>
                                    </Link>
                                    <template v-if="userRole !== 'teknisi'">
                                        <Link :href="route('pegawai.pengujian.edit', item.id)"
                                            class="text-yellow-500 hover:text-yellow-700" title="Edit"
                                            :aria-disabled="item.status === 'selesai'"
                                            :tabindex="item.status === 'selesai' ? -1 : 0"
                                            :class="item.status === 'selesai' ? 'pointer-events-none opacity-50' : ''">
                                        <span>✏️</span>
                                        </Link>
                                        <button @click="openDeleteModal(item)" class="text-red-500 hover:text-red-700"
                                            type="button" title="Hapus">
                                            <span>🗑️</span>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Delete Confirmation Modal -->
            <Dialog :open="showDeleteModal" @update:open="closeDeleteModal">
                <DialogContent
                    class="fixed left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 transform rounded-lg bg-white p-6 shadow-xl">
                    <DialogHeader>
                        <DialogTitle class="text-center text-xl font-bold text-gray-400">
                            <div class="flex flex-col items-center">
                                <svg width="64" height="64" viewBox="0 0 94 94" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M53.4152 15.4982C52.7777 14.3582 51.8477 13.4088 50.721 12.748C49.5943 12.0871 48.3118 11.7388 47.0056 11.7388C45.6994 11.7388 44.4169 12.0871 43.2902 12.748C42.1635 13.4088 41.2335 14.3582 40.596 15.4982L12.6721 65.4474C12.0475 66.5647 11.7257 67.8257 11.7387 69.1057C11.7516 70.3856 12.0989 71.6399 12.7461 72.7442C13.3932 73.8485 14.3178 74.7645 15.4281 75.4014C16.5384 76.0383 17.7959 76.3739 19.0758 76.3749H74.9118C76.1923 76.3749 77.4505 76.04 78.5616 75.4036C79.6727 74.7671 80.5981 73.8512 81.246 72.7467C81.8938 71.6422 82.2416 70.3875 82.2549 69.1071C82.2681 67.8267 81.9463 66.5651 81.3215 65.4474L53.4152 15.4982ZM51.406 60.2187C51.406 61.3873 50.9417 62.5081 50.1154 63.3344C49.2891 64.1607 48.1683 64.6249 46.9997 64.6249C45.8311 64.6249 44.7104 64.1607 43.884 63.3344C43.0577 62.5081 42.5935 61.3873 42.5935 60.2187C42.5935 59.0501 43.0577 57.9293 43.884 57.103C44.7104 56.2767 45.8311 55.8124 46.9997 55.8124C48.1683 55.8124 49.2891 56.2767 50.1154 57.103C50.9417 57.9293 51.406 59.0501 51.406 60.2187ZM44.0622 46.9999V32.3124C44.0622 31.5334 44.3717 30.7862 44.9226 30.2353C45.4735 29.6844 46.2206 29.3749 46.9997 29.3749C47.7788 29.3749 48.526 29.6844 49.0768 30.2353C49.6277 30.7862 49.9372 31.5334 49.9372 32.3124V46.9999C49.9372 47.779 49.6277 48.5262 49.0768 49.0771C48.526 49.628 47.7788 49.9374 46.9997 49.9374C46.2206 49.9374 45.4735 49.628 44.9226 49.0771C44.3717 48.5262 44.0622 47.779 44.0622 46.9999Z"
                                        fill="#E94235" />
                                </svg>
                                Peringatan !
                            </div>
                        </DialogTitle>
                    </DialogHeader>

                    <div class="text-center">
                        <p class="font-bold text-gray-900">HAPUS PENGUJIAN {{ deletingPengujian?.kode_pengujian }}</p>
                        <p class="text-gray-600">
                            Apakah Anda yakin ingin menghapus pengujian
                            <span class="font-bold">{{ deletingPengujian?.form_pengajuan?.kode_pengajuan }}</span>?
                        </p>
                    </div>

                    <div class="mt-6 flex justify-center gap-4">
                        <button @click="closeDeleteModal"
                            class="rounded-lg bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300">Batal</button>
                        <button @click="handleDelete"
                            class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">Hapus</button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AdminLayout>
</template>
