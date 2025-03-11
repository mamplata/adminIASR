<template>
    <div class="image-wrapper">
        <div class="container-fluid">
            <img :src="pncBg" alt="PNC Background" class="pnc-bg">
            <div class="time-container">
                <h1 class="display-3"><b>{{ time }}</b></h1>
                <h4 class="display-6"><b>{{ date }}</b></h4>
            </div>
        </div>
    </div>
</template>

<script>
import pncBg from '@/assets/img/pnc-bg.jpg';

export default {
    data() {
        return {
            pncBg,
            time:'',
            date:''
        };
    },
    methods: {
        updateTime() {
            const now = new Date();

            // Convert to GMT+8
            const utc = now.getTime() + now.getTimezoneOffset() * 60000;
            const gmt8 = new Date(utc + 8 * 3600000);

            // Format time (HH:MM:SS in 24-hour format)
            let hours = gmt8.getHours().toString().padStart(2, '0');
            let minutes = gmt8.getMinutes().toString().padStart(2, '0');
            let seconds = gmt8.getSeconds().toString().padStart(2, '0');
            this.time = `${hours}:${minutes}:${seconds}`;

            // Format date (Weekday, Day Month Year)
            const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
            this.date = gmt8.toLocaleDateString('en-US', options);
        }
    },
    mounted() {
        this.updateTime(); // Initial call
        setInterval(this.updateTime, 1000); // Update time every second
    }
};
</script>

<style scoped>
.image-wrapper {
    width: 100%;
    height: 150px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.container-fluid {
    width: 100%;
    height: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    position: relative;
    padding: 0;
}

.pnc-bg {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.time-container {
    position: absolute;
    color: white;
    text-align: center;
    padding: 10px 15px;
    border-radius: 10px;
}
</style>