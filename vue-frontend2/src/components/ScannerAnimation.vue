<template>
    <div>
        <div class="card">
            <div class="card-body">
                <img :src="animation" alt="RFID Scan Animation" class="img-fluid">
            </div>
        </div>
    </div>
</template>
<script>
import animation from '@/assets/img/scanner-animation.gif';
export default {
    data(){
        return {
            animation
        };
    }
}
</script>
<style scoped>

</style>