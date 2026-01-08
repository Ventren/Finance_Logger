<script setup>
import { ref, onMounted } from 'vue'

const categories = ref([])

// This function runs as soon as the page loads
onMounted(async () => {
  try {
    const response = await fetch('http://localhost/financial-logging-system/backend/get_categories.php')
    const data = await response.json()
    categories.value = data
    console.log("Categories received:", data)
  } catch (error) {
    console.error("Error fetching categories:", error)
  }
})
</script>

<template>
  <div>
    <h1>Financial Logger</h1>
    <h3>Available Categories:</h3>
    <ul>
      <li v-for="cat in categories" :key="cat.id">
        {{ cat.category }} ({{ cat.type }})
      </li>
    </ul>
  </div>
</template>