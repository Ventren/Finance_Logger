<script setup>
import { computed } from 'vue';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

const props = defineProps(['transactions', 'categories']);
// rates relative to 1 MYR (Fixed example rates)
const exchangeRates = {
  MYR: 1.0,
  USD: 4.07, // 1 USD = 4.07 MYR
  SGD: 3.17, // 1 SGD = 3.17 MYR
  CNY: 0.58  // 1 CNY = 0.58 MYR
};

const chartData = computed(() => {
  // 1. Filter only expenses
  const expenses = props.transactions.filter(t => t.type === 'expense');

  // 2. Group by category name
  const totals = {};
  expenses.forEach(t => {
    // Find category name from the ID
    const catName = props.categories.find(c => c.id == t.category_id)?.category || 'Other';
    const rate = exchangeRates[t.currency] || 1.0;
    const amountInBaseCurrency = parseFloat(t.amount) * rate;

    totals[catName] = (totals[catName] || 0) + amountInBaseCurrency;
  });

  return {
    labels: Object.keys(totals),
    datasets: [{
      backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
      data: Object.values(totals)
    }]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' }
  }
};
</script>

<template>
  <div class="h-64">
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>