<script setup>
import { ref, onMounted, computed } from 'vue'
import ExpenseChart from './components/ExpenseChart.vue'
import TransactionList from './components/TransactionList.vue'
import AddTransaction from './components/AddTransaction.vue'

// using ref for real time validation before user clicks the submit button
const categories = ref([])
const transactions = ref([])
const searchQuery = ref('')// filter for description
const selectedType = ref('All') // filter for income & expense
const selectedCurr = ref('All') // filter for currency
const filterStartDate = ref('') // start datefilter
const filterEndDate = ref('')   // end datefilter
//for update function
const isEditing = ref(false);
const currentId = ref(null); // To keep track of which ID we are updating

// setting toggle
const showSettings = ref(false)
/*-----------------------------------------Category Management---------------------------------------*/
// Category Management
const newCategoryName = ref('');
const deleteCategory = async (id) => {
  if (!confirm("Delete this category? Transactions using it will show as 'General'.")) return;
  await fetch('http://localhost/financial-logging-system/backend/manage_categories.php', {
    method: 'DELETE',
    body: JSON.stringify({ id })
  });
  // Refresh categories list
  const resCat = await fetch('http://localhost/financial-logging-system/backend/get_categories.php');
  categories.value = await resCat.json();
};

const newCategoryType = ref('expense'); // Default to expense
const addCategory = async () => {
  if (!newCategoryName.value.trim()) return;

  await fetch('http://localhost/financial-logging-system/backend/manage_categories.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      category: newCategoryName.value,
      type: newCategoryType.value
    })
  });

  newCategoryName.value = '';
  // Refresh categories
  const resCat = await fetch('http://localhost/financial-logging-system/backend/get_categories.php');
  categories.value = await resCat.json();
};
/*----------------------------------------- END Category Management---------------------------------------*/

/*------------------------------------------Currency Management---------------------------------------*/
// Currency Management
const dbCurrencies = ref([]); // List from our new table
const newCurrencyCode = ref('');

const fetchCurrencies = async () => {
  const res = await fetch('http://localhost/financial-logging-system/backend/get_currencies.php');
  dbCurrencies.value = await res.json();
};

const addCurrency = async () => {
  if (newCurrencyCode.value.length < 3) return;
  await fetch('http://localhost/financial-logging-system/backend/manage_currencies.php', {
    method: 'POST',
    body: JSON.stringify({ code: newCurrencyCode.value.toUpperCase() })
  });
  newCurrencyCode.value = '';
  fetchCurrencies();
};

const deleteCurrency = async (id) => {
  await fetch('http://localhost/financial-logging-system/backend/manage_currencies.php', {
    method: 'DELETE',
    body: JSON.stringify({ id })
  });
  fetchCurrencies();
};

const availableCurrencies = computed(() => {
  // get the currency that are in the list
  const allCurrencies = transactions.value.map(t => t.currency);
  return Array.from(new Set(allCurrencies));
});
/*-----------------------------------------END Currency Management---------------------------------------*/

/*--------------------------------------------Filters------------------------------------------*/

const resetDateFilters = () => {
  filterStartDate.value = ''
  filterEndDate.value = ''
}

//filter for table
const filteredTransactions = computed(() => {
  return transactions.value.filter(t => {
    // Description Match
    const matchesSearch = t.description.toLowerCase().includes(searchQuery.value.toLowerCase())

    // Type (Income/Expense)
    const matchesType = selectedType.value === 'All' || t.type === selectedType.value

    // Currency 
    const matchesCurrency = selectedCurr.value === 'All' || t.currency === selectedCurr.value
    
    // Date
    const transactionDate = t.transaction_date  // format YYYY-MM-DD
    const matchesStart = !filterStartDate.value || transactionDate >= filterStartDate.value
    const matchesEnd = !filterEndDate.value || transactionDate <= filterEndDate.value
    console.log(`Comparing: DB Date [${transactionDate}] with Filter Start [${matchesStart}]`);
    // Only return TRUE if ALL five are satisfied
    return matchesSearch && matchesType && matchesCurrency && matchesStart && matchesEnd
  })
})

/*-----------------------------------------END Filter---------------------------------------*/
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
  id: null,
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
  fetchCurrencies()
})

//edit function
const startEdit = (transaction) => {
  isEditing.value = true;
  currentId.value = transaction.id;

  // Fill the form with the row's data
  newTransaction.value = {
    id: transaction.id,
    category_id: transaction.category_id,
    description: transaction.description,
    amount: transaction.amount,
    currency: transaction.currency,
    transaction_date: transaction.transaction_date
  };

  // Smooth scroll back to the form
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Add a cancel function too
const cancelEdit = () => {
  isEditing.value = false;
  currentId.value = null;
  // Reset form to defaults
  newTransaction.value = {
    category_id: '',
    description: '',
    amount: '',
    currency: 'MYR',
    transaction_date: new Date().toISOString().split('T')[0]
  };
};

//Submit Function(check if its edit too)
const submitForm = async () => {
  const cleanData = JSON.parse(JSON.stringify(newTransaction.value));

  try {
    const response = await fetch('http://localhost/financial-logging-system/backend/save_transaction.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(cleanData) // Send the clean version
    });

    const result = await response.json();

    if (result.success) {
      alert(isEditing.value ? "Updated!" : "Saved!");
      cancelEdit();
      await fetchTransactions();
    } else {
      alert("Server Error: " + result.error);
    }
  } catch (error) {
    console.error("Fetch Error:", error);
    alert("Connection failed. Check console.");
  }
};

//flags
const flagMap = {
  MYR: 'MY',
  USD: 'US',
  SGD: 'SG',
  EUR: 'EU',
  GBP: 'GB',
  JPY: 'JP',
  CNY: 'CN'
};
/*-----------------------------------------Fixed Exchange rate---------------------------------*/
const exchangeRates = {
  MYR: 1.0,
  USD: 4.07, // 1 USD = 4.07 MYR
  SGD: 3.17, // 1 SGD = 3.17 MYR
  CNY: 0.58  // 1 CNY = 0.58 MYR
};

const totalNetWorth = computed(() => {
  let totalInMYR = 0;

 
  for (const [currency, balance] of Object.entries(walletBalance.value)) {
    const rate = exchangeRates[currency] || 1.0;
    totalInMYR += balance * rate;
  }

  return totalInMYR;
});
/*-----------------------------------------END Fixed Exchange rate---------------------------------*/
</script>

<template>
  <div class="min-h-screen p-4 md:p-8">
    <div class="max-w-5xl mx-auto">

      <header class="mb-8">
        <h1 class="mb-4 text-4xl font-bold tracking-tight text-heading md:text-5xl lg:text-6xl"> Financial Logger
          Dashboard</h1>
        <p class="text-slate-500">Manage your transactions and multi-currency wallets.</p>
      </header>

      <!-- Total balance card -->
      <div class="mb-6 p-6 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-emerald-100 text-xs font-bold uppercase tracking-widest opacity-80">Total Estimated Net Worth
            </p>
            <h2 class="text-4xl font-black mt-1">
              RM {{ totalNetWorth.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
            </h2>
          </div>
          <div class="text-right relative group"> <span
              class="bg-white/20 px-3 py-1 rounded-full text-xs font-medium cursor-help">
              Base: MYR
            </span>

            <div
              class="absolute right-0 bottom-full mb-2 hidden group-hover:block w-48 p-3 bg-slate-800 text-white text-xs rounded-xl shadow-xl z-50">
              <p class="font-bold border-b border-slate-600 pb-1 mb-2">Exchange Rates (1.00 MYR)</p>
              <ul class="space-y-1">
                <li class="flex justify-between"><span>USD:</span> <span>{{ exchangeRates.USD }}</span></li>
                <li class="flex justify-between"><span>SGD:</span> <span>{{ exchangeRates.SGD }}</span></li>
                <li class="flex justify-between"><span>CNY:</span> <span>{{ exchangeRates.CNY }}</span></li>
              </ul>
              <div class="absolute top-full right-4 border-8 border-transparent border-t-slate-800"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 items-start">

        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="(balance, currency) in walletBalance" :key="currency"
            class="p-6 rounded-2xl border shadow-sm bg-white border-slate-100 hover:shadow-md transition-shadow">

            <div class="flex items-center gap-2 mb-2">
              <img :src="`https://flagcdn.com/w40/${flagMap[currency]?.toLowerCase() || 'un'}.png`" :alt="currency"
                class="w-5 h-auto rounded-sm shadow-xs" />
              <span class="text-xs font-bold uppercase tracking-widest text-slate-400">
                {{ currency }} Wallet
              </span>
            </div>

            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold" :class="balance >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                {{ currency }} {{ balance.toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
          <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Spending Breakdown</h3>
          <ExpenseChart :transactions="filteredTransactions" :categories="categories" />
        </div>

      </div>

      <button @click="showSettings = !showSettings"
        class="text-sm font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full hover:bg-indigo-100 transition-colors">
        {{ showSettings ? 'Close Settings' : '⚙️ Manage Categories & Currencies' }}
      </button>

      <div v-if="showSettings"
        class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-slate-100 rounded-2xl mb-8 border border-slate-200">

        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
          <h3 class="font-bold text-slate-700 mb-3">Categories</h3>
          <div class="flex flex-col gap-2 mb-4">
            <input v-model="newCategoryName" placeholder="Category Name" class="p-2 border rounded-lg text-sm" />
            <div class="flex gap-2">
              <select v-model="newCategoryType" class="flex-grow p-2 border rounded-lg text-sm bg-white">
                <option value="expense">Expense</option>
                <option value="income">Income</option>
              </select>
              <button @click="addCategory"
                class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                Add
              </button>
            </div>
          </div>

          <ul class="max-h-60 overflow-y-auto space-y-2">
            <li v-for="cat in categories" :key="cat.id"
              class="flex justify-between items-center p-2 bg-slate-50 rounded-lg text-sm">
              <div class="flex flex-col">
                <span class="font-medium text-slate-800">{{ cat.category }}</span>
                <span class="text-[10px] uppercase font-bold"
                  :class="cat.type === 'income' ? 'text-emerald-500' : 'text-rose-400'">
                  {{ cat.type }}
                </span>
              </div>
              <button @click="deleteCategory(cat.id)" class="text-slate-400 hover:text-rose-500 p-1">
                ✕
              </button>
            </li>
          </ul>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm">
          <h3 class="font-bold text-slate-700 mb-3">Currencies</h3>
          <div class="flex gap-2 mb-4">
            <input v-model="newCurrencyCode" placeholder="Code (e.g. EUR)"
              class="flex-grow p-2 border rounded-lg text-sm uppercase" maxlength="3" />
            <button @click="addCurrency"
              class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-bold">Add</button>
          </div>
          <ul class="max-h-40 overflow-y-auto">
            <li v-for="curr in dbCurrencies" :key="curr.id"
              class="flex justify-between items-center py-2 border-b text-sm uppercase">
              {{ curr.code }}
              <button @click="deleteCurrency(curr.id)" class="text-rose-500 hover:text-rose-700">✕</button>
            </li>
          </ul>
        </div>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-10">
        <AddTransaction :categories="categories" :isEditing="isEditing" :form-data="newTransaction"
          :currencies="dbCurrencies" @transaction-saved="fetchTransactions" @cancel-edit="cancelEdit" />
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-bottom bg-slate-50/50 flex flex-col md:flex-row gap-4">
          <div class="relative flex-grow">
            <input v-model="searchQuery" type="text" placeholder="Search transactions..."
              class="w-full pl-4 pr-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
          </div>

          <select v-model="selectedType"
            class="px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="All">All Types</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
          </select>

          <select v-model="selectedCurr"
            class="px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="All">All Currencies</option>
            <option v-for="curr in availableCurrencies" :key="curr" :value="curr">
              {{ curr }}</option>
          </select>
        </div>

        <!-- Date filter -->
        <div class="p-4 border-bottom bg-slate-50/50 flex flex-col md:flex-row gap-4 flex-wrap">
          <div class="flex items-center gap-2">
            <input v-model="filterStartDate" type="date"
              class="px-3 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-emerald-500 text-sm" />
            <span class="text-slate-400">to</span>
            <input v-model="filterEndDate" type="date"
              class="px-3 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-emerald-500 text-sm" />
          </div>

          <button v-if="filterStartDate || filterEndDate" @click="resetDateFilters"
            class="text-xs text-slate-500 hover:text-emerald-600 font-medium">
            Clear Dates
          </button>
        </div>
        <!-- Date filter -->

        <TransactionList :transactions="filteredTransactions" @delete-me="deleteTransaction" @edit-me="startEdit" />
      </div>

    </div>
  </div>


  <footer class="mt-20 pb-10 border-t border-slate-200">
    <div class="max-w-5xl mx-auto px-4 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">

      <div class="flex items-center gap-2">
        <span class="text-sm font-semibold text-slate-800 tracking-tight">
          Financial Logger <span class="text-slate-400 font-normal">by Ventren Nair</span>
        </span>
      </div>

      <div class="flex items-center gap-6">
        <div class="flex items-center gap-2">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
        </div>
      </div>
    </div>
  </footer>

</template>
