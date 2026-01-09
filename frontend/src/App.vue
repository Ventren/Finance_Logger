<script setup>
import { ref, onMounted, computed } from 'vue'
import TransactionList from './components/TransactionList.vue'
import AddTransaction from './components/AddTransaction.vue'

// using ref for real time validation before user clicks the submit button
const categories = ref([])
const transactions = ref([])
const searchQuery = ref('')
const selectedType = ref('All') // filter for income & expense
const selectedCurr = ref('All') // filter for currency
const availableCurrencies = computed(() => {

  // get the currency that are in the list
  const allCurrencies = transactions.value.map(t => t.currency);
  return Array.from(new Set(allCurrencies));
});

//filter for table
const filteredTransactions = computed(() => {
  return transactions.value.filter(t => {
    // 1. Description Match
    const matchesSearch = t.description.toLowerCase().includes(searchQuery.value.toLowerCase())

    // 2. Type Match (Income/Expense)
    const matchesType = selectedType.value === 'All' || t.type === selectedType.value

    // 3. Currency Match
    const matchesCurrency = selectedCurr.value === 'All' || t.currency === selectedCurr.value

    // Only return TRUE if all three are satisfied
    return matchesSearch && matchesType && matchesCurrency
  })
})

// changed from totalBalance to wallet balance to support multiple currency
const walletBalance = computed(() => {
  const totals = {};
  transactions.value.forEach(transaction => {
    const amount = parseFloat(transaction.amount);
    const curr = transaction.currency; // 'MYR', 'USD', etc.

    // if new currency, start at 0
    if (!totals[curr]) {
      totals[curr] = 0;
    }

    //income/expense logic
    if (transaction.type === 'expense') {
      totals[curr] -= amount;
    } else {
      totals[curr] += amount;
    }
  });

  return totals; // Example result: { MYR: 1500.50, USD: -50.00 }
});



const newTransaction = ref({
  category_id: '',
  description: '',
  amount: '',
  currency: 'MYR',
  transaction_date: new Date().toISOString().split('T')[0] //the format date for json following iso.  Defaults to today
})

const fetchTransactions = async () => {
  const res = await fetch('http://localhost/financial-logging-system/backend/get_transaction.php')
  transactions.value = await res.json()
}

const deleteTransaction = async (id) => {
  await fetch('http://localhost/financial-logging-system/backend/delete_transaction.php', {
    method: 'POST',
    body: JSON.stringify({ id })
  });
  await fetchTransactions(); // Refresh the list!
};



onMounted(async () => {
  // Fetch categories (for the form)
  const resCat = await fetch('http://localhost/financial-logging-system/backend/get_categories.php')
  categories.value = await resCat.json()

  // Fetch transactions (for the table)
  fetchTransactions()
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
      await fetchTransactions()
    }

  } catch (error) {
    console.error("Submission failed:", error)
  }
}
</script>

<template>
  <div class="container" style="max-width: 600px; margin: auto; padding: 20px;">

    <div class="summary-grid">
      <div v-for="(balance, currency) in walletBalance" :key="currency"
        :class="['balance-card', balance >= 0 ? 'status-profit' : 'status-loss']">
        <small>{{ currency }} Wallet</small>
        <h2>{{ currency }} {{ balance.toFixed(2) }}</h2>
      </div>
    </div>

  </div>
  <div class="container">
    <AddTransaction :categories="categories" @transaction-saved="fetchTransactions" />
    <hr />
    <div class="filters" style="margin: 20px 0; display: flex; gap: 10px;">
      <input v-model="searchQuery" type="text" placeholder="Search descriptions..." style="flex: 2;" />

      <select v-model="selectedType" style="flex: 1;">
        <option value="All">All Types</option>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
      </select>

      <select v-model="selectedCurr" style="flex: 1;">
        <option value="All">All Currencies</option>
        <option v-for="curr in availableCurrencies" :key="curr" :value="curr">
          {{ curr }}
        </option>
      </select>

    </div>
    <TransactionList :transactions="filteredTransactions" @delete-me="deleteTransaction" />
  </div>
</template>

<style>
label {
  display: block;
  margin-top: 10px;
}

input,
select {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
}

button {
  margin-top: 20px;
  width: 100%;
  padding: 10px;
  background: #42b983;
  color: white;
  border: none;
  cursor: pointer;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.balance-card {
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  transition: background-color 0.3s ease;
  /* Makes the color change smooth */
  border: 2px solid #ddd;
}

.status-profit {
  background-color: #e6fffa;
  border-color: #38b2ac;
  color: #234e52;
}

.status-loss {
  background-color: #fff5f5;
  border-color: #f56565;
  color: #742a2a;
}

.status-neutral {
  background-color: #f7fafc;
  border-color: #edf2f7;
  color: #4a5568;
}
</style>