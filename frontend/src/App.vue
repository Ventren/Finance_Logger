<script setup>
import { ref, onMounted } from 'vue'

// using ref for real time validation before user clicks the submit button
const categories = ref([])
const newTransaction = ref({
  category_id: '',
  description: '',
  amount: '',
  currency: 'MYR',
  transaction_date: new Date().toISOString().split('T')[0] //the format date for json following iso.  Defaults to today
})

//Fetch categories for the dropdown
onMounted(async () => {
  const response = await fetch('http://localhost/financial-logging-system/backend/get_categories.php')
  categories.value = await response.json()
})

//Submit Function
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
      // Reset form
      newTransaction.value.description = ''
      newTransaction.value.amount = ''
    }
  } catch (error) {
    console.error("Submission failed:", error)
  }
}
</script>

<template>
  <div style="max-width: 400px; margin: auto;">
    <h1>Add Transaction</h1>
    
    <form @submit.prevent="submitForm">
      <label>Category:</label>
      <!-- v-model for link with the ref in script -->
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

      <button type="submit">Save Transaction</button>
    </form>
  </div>
</template>

<style>
label { display: block; margin-top: 10px; }
input, select { width: 100%; padding: 8px; margin-top: 5px; }
button { margin-top: 20px; width: 100%; padding: 10px; background: #42b983; color: white; border: none; cursor: pointer; }
</style>