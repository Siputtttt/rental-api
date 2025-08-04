<script setup>
import { getCurrentInstance, ref , onMounted , reactive  } from "vue";
import { useStore } from "@/store";
import { useRouter } from "vue-router"; 

const { proxy } = getCurrentInstance();
const router = useRouter(); 
const store = useStore(); 
onMounted(() => { 
    
}) 
</script>
<template>
  <div class="wrapper">
    <div class="header-nav">
      <h3><i class="bi bi-layout-sidebar"></i> <?php echo e($moduleTitle); ?> </h3>
    </div>
    <div class="page">  

    </div>
  </div> 
</template> 
<?php /**PATH /Users/haimac/Documents/jobs/side/rental-api/resources/views/blank/Index.blade.php ENDPATH**/ ?>