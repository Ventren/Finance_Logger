<script setup>
defineProps(['transactions'])
</script>

<template>
  <div class="list-container">
    <h2>History</h2>
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in transactions" :key="t.id">
          <td>{{ t.transaction_date }}</td>
          <td>{{ t.description }}</td>
          <td>{{ t.category }}</td>
          <td :class="t.type === 'income' ? 'income' : 'expense'">
            {{ t.currency }} {{ t.amount }}
          </td>
          <td>
            <button @click="$emit('delete-me', t.id)">Delete</button>
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
  table-layout: fixed; /* This is the secret sauce! */
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