<script setup>
defineProps(['transactions'])
defineEmits(['delete-me', 'edit-me'])
//dateformat
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-MY', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
}
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse table-fixed">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="p-4 text-sm font-semibold text-slate-600 w-1/4">Date</th>
          <th class="p-4 text-sm font-semibold text-slate-600 w-1/4">Category</th>
          <th class="p-4 text-sm font-semibold text-slate-600 w-1/4">Description</th>
          <th class="p-4 text-sm font-semibold text-slate-600 w-1/6 text-right">Amount</th>
          <th class="p-4 text-sm font-semibold text-slate-600 w-1/6 text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="t in transactions" :key="t.id" class="hover:bg-slate-50 transition-colors">
          <td class="p-4 text-sm text-slate-600">{{ formatDate(t.transaction_date) }}</td>
          <td class="p-4 text-sm font-medium text-slate-800">{{ t.category || 'General' }}</td>
          <td class="p-4 text-sm text-slate-500 italic">{{ t.description }}</td>
          <td class="p-4 text-sm font-bold text-right" :class="t.type === 'expense' ? 'text-rose-600' : 'text-emerald-600'">
            {{ t.currency }} {{ parseFloat(t.amount).toFixed(2) }}
          </td>
          <td class="p-4 text-center">
            <div class="flex justify-center gap-3">
              <button @click="$emit('edit-me', t)" class="text-indigo-600 hover:text-indigo-900 transition-colors rounded-lg cursor-pointer">
                <span class="text-xs font-bold uppercase">Edit</span>
              </button>
              
              <button @click="$emit('delete-me', t.id)" class="text-rose-500 hover:text-rose-700 transition-colors rounded-lg cursor-pointer">
                <span class="text-xs font-bold uppercase">Delete</span>
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; 
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
  /* Prevent text from wrapping and breaking height */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Assign specific widths to each column */
th:nth-child(1), td:nth-child(1) { width: 25%; } /* Date */
th:nth-child(2), td:nth-child(2) { width: 25%; } /* Category */
th:nth-child(3), td:nth-child(3) { width: 30%; } /* Description */
th:nth-child(4), td:nth-child(4) { width: 20%; } /* Amount */
.income { color: green; font-weight: bold; }
.expense { color: red; font-weight: bold; }
</style>