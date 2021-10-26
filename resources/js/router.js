import Vue from 'vue';
import Router from 'vue-router';

// 引用頁面的 Component
import Home from "./components/views/Home";
// 使用 Vue Router
Vue.use(Router);

// Route 設定
export const routes = [
    { path: '/', component: Home, name:'home'},
];

// 建立 Vue Router instance
const router = new Router({
    mode: 'history',
    routes
});

export default router;
