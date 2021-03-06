import router from "./router";
import Vue from "vue";
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)
require('./bootstrap');

// add Liff
Vue.prototype.$liff = window.liff

window.Vue = require('vue').default;

export default new Vue({
    el: '#app',
    router
});
