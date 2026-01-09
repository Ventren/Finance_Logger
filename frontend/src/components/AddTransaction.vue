<script setup>
import { ref } from 'vue'

const props = defineProps(['categories'])
const emit = defineEmits(['transaction-saved'])

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
      body: JSON.stringify(newTransaction.value)
    })
    
    const result = await response.json()
    if (result.success) {
      alert("Success! Transaction saved.")
      newTransaction.value.description = ''
      newTransaction.value.amount = ''
      // Tell the parent to refresh the list!
      emit('transaction-saved')
    }
  } catch (error) {
    console.error("Submission failed:", error)
  }
}
</script>

<template>
  <div class="form-container">
    <h1>Add Transaction</h1>
    <form @submit.prevent="submitForm">
      <label>Category:</label>
      <select v-model="newTransaction.category_id" required>
        <option value="" disabled>Select a category</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
          {{ cat.category }} ({{ cat.type }})
        </option>
      </select>

      <label>Description:</label>
      <input v-model="newTransaction.description" type="text" placeholder="e.g. Lunch">

      <label>Amount:</label>
      <input v-model="newTransaction.amount" type="number" step="0.01" required>

      <label>Currency:</label>
      <select v-model="newTransaction.currency">
        <option value="MYR">MYR</option>
        <option value="USD">USD</option>
        <option value="SGD">SGD</option>
        <option value="YEN">YEN</option>
      </select>

      <button type="submit">Save Transaction</button>
    </form>
  </div>
</template>