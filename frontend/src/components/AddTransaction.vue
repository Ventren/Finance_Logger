<script setup>
import { ref } from 'vue'

const props = defineProps(['categories', 'isEditing', 'formData', 'currencies'])
const emit = defineEmits(['transaction-saved'], ['cancel-edit'])
//today date
const today = new Date().toISOString().split('T')[0];
const newTransaction = ref({
  category_id: '',
  description: '',
  amount: '',
  currency: 'MYR',
  transaction_date: new Date().toISOString().split('T')[0]
})

const submitForm = async () => {
  try {
    const response = await fetch('http://localhost/financial-logging-system/backend/save_transaction.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(props.formData)
    })

    const result = await response.json()
    if (result.success) {
      alert("Success! Transaction saved.")
      props.formData.description = ''
      props.formData.amount = ''
      // Tell the parent to refresh the list!
      emit('transaction-saved')
    }
  } catch (error) {
    console.error("Submission failed:", error)
  }
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-slate-800 mb-4">Add New Transaction</h2>

    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
        <input v-model="props.formData.transaction_date" type="date" :max="today" required
          class="w-full p-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none bg-white" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
        <select v-model="props.formData.category_id" required
          class="w-full p-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
          <option value="" disabled>Select</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.category }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <input v-model="props.formData.description" type="text" placeholder="Lunch, Rent, etc."
          class="w-full p-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none" />
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Amount</label>
          <input v-model="props.formData.amount" type="number" step="0.01" required
            class="w-full p-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
          <select v-model="props.formData.currency"
            class="w-full p-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
            <option value="" disabled>Select Currency</option>
            <option v-for="c in currencies" :key="c.id" :value="c.code">
              {{ c.code }}
            </option>
          </select>
        </div>
      </div>

      <button type="submit"
        class="bg-emerald-600 hover:bg-emerald-800 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm cursor-pointer">
        {{ isEditing ? 'Update Transaction' : 'Save Transaction' }}
      </button>
      <button v-if="isEditing" type="button" @click="$emit('cancel-edit')" class="mt-2 text-slate-500 underline">
        {{ isEditing ? 'Cancel Edit' : 'Clear' }}
      </button>
    </form>
  </div>
</template>