import { createRouter, createWebHistory } from 'vue-router'
import Login    from '../views/Login.vue'
import Register from '../views/Register.vue'
// import Home     from '../views/Home.vue'
import AddKos   from '../views/AddKos.vue'
import KosView  from '../views/KosView.vue'
import KosDetail from '../views/KosDetail.vue'
import OwnerBookings from '../views/OwnerBookings.vue'
import AdminKos from '../views/AdminKos.vue'
import VerifyEmail from '../views/VerifyEmail.vue'
import OwnerDashboard from '../views/OwnerDashboard.vue'
import OwnerKomisi from '../views/OwnerKomisi.vue'
import AdminKomisi from '../views/AdminKomisi.vue'


const routes = [
  // ✅ Public
  { path: '/',          component: KosView },
  { path: '/kos',       component: KosView },
  { path: '/kos/:id', component: KosDetail },
  { path: '/login',     component: Login },
  { path: '/register',  component: Register },
  { path: '/verify-email',  component: VerifyEmail },  

  { path: '/admin/kos', component: AdminKos, meta: { requiresAuth: true, roles: ['admin'] } },


  // ✅ Semua user login
  // { path: '/home',      component: Home, meta: { requiresAuth: true } },

  // ✅ Hanya owner & admin
  { path: '/add-kos',   component: AddKos, meta: { requiresAuth: true, roles: ['owner', 'admin'] } },
  { path: '/owner/bookings', component: OwnerBookings, meta: { requiresAuth: true, roles: ['owner', 'admin'] } },
 { path: '/owner/dashboard',component: OwnerDashboard, meta: { requiresAuth: true, roles: ['owner', 'admin'] } },

{
  path      : '/owner/komisi',
  component : OwnerKomisi,
  meta      : { requiresAuth: true, roles: ['owner'] }
},
{
  path      : '/admin/komisi',
  component : AdminKomisi,
  meta      : { requiresAuth: true, roles: ['admin'] }
},
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  const user  = JSON.parse(localStorage.getItem('user') || '{}')

  // Belum login → ke /login
  if (to.meta.requiresAuth && !token) {
    return { path: '/login' }
  }

  // Sudah login tapi role tidak diizinkan → ke /
  if (to.meta.roles && !to.meta.roles.includes(user.role)) {
    return { path: '/' }
  }

  // Sudah login buka /login atau /register → ke /
  if ((to.path === '/login' || to.path === '/register') && token) {
    return { path: '/' }
  }
  
})

export default router