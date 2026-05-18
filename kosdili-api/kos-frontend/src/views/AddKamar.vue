<template>
  <div>
    <h1>Tambah Kamar</h1>
    <form @submit.prevent="submit">
      <select v-model="kos_id">
        <option v-for="k in kosStore.kosList" :value="k.id">{{ k.nama_kos }}</option>
      </select>
      <input v-model="harga" type="number" placeholder="Harga" />
      <select v-model="status">
        <option value="available">Available</option>
        <option value="booked">Booked</option>
      </select>
      <button type="submit">Tambah Kamar</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useKosStore } from '../store/index.js';

const kosStore = useKosStore();
const kos_id = ref('');
const harga = ref('');
const status = ref('available');

onMounted(() => {
  kosStore.fetchKos();
});

const submit = async () => {
  await kosStore.addKamar({ kos_id: kos_id.value, harga: harga.value, status: status.value });
  alert('Kamar berhasil ditambahkan!');
}
</script>