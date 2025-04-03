import { createApp } from "vue";
import "./style.css";
import App from "./App.vue";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "swiper/css";
import { createPinia } from "pinia";
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

const app = createApp(App);
app.use(createPinia());
app.use(Toast);
app.mount("#app");
