<template>
  <nav class="main-header navbar navbar-expand navbar-dark jewelry-navbar">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars text-lg"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block" v-if="user">
        <span class="nav-link text-md">{{ user.name }} ({{ user.role }})</span>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
      <li class="nav-item">
        <button
          type="button"
          class="btn btn-sm jewelry-theme-btn"
          :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          @click="toggleTheme"
        >
          <i :class="isDark ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill'"></i>
          <span class="jewelry-theme-btn__label">{{ isDark ? 'Dark' : 'Light' }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button @click="logout" class="btn btn-sm btn-danger text-md jewelry-logout-btn">
          <i class="bi bi-box-arrow-left"></i> Logout
        </button>
      </li>
    </ul>
  </nav>
</template>

<script>
import { clearAuth, getUser } from '../../auth';

const THEME_KEY = 'ayub_jewelry_theme';

export default {
    name: "Header",
    data() {
        return {
            isDark: true,
        };
    },
    computed: {
        user() {
            return getUser();
        },
    },
    mounted() {
        const saved = localStorage.getItem(THEME_KEY);
        this.isDark = saved !== 'light';
        this.applyTheme(this.isDark);
    },
    methods: {
        toggleTheme() {
            this.isDark = !this.isDark;
            this.applyTheme(this.isDark);
            localStorage.setItem(THEME_KEY, this.isDark ? 'dark' : 'light');
        },
        applyTheme(isDark) {
            document.body.classList.toggle('dark-mode', isDark);
            document.body.classList.toggle('jewelry-theme-light', !isDark);
        },
        logout() {
            axios.post('/api/logout').finally(() => {
                clearAuth();
                delete axios.defaults.headers.common['Authorization'];
                this.$router.push('/login');
            });
        },
    },
};
</script>

<style scoped>
.jewelry-theme-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-right: 0.5rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    border: 1px solid rgba(212, 175, 55, 0.45);
    background: rgba(212, 175, 55, 0.15);
    color: #f0d875;
    font-weight: 600;
    font-size: 0.82rem;
    line-height: 1.2;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.jewelry-theme-btn:hover {
    background: rgba(212, 175, 55, 0.28);
    color: #fff;
    border-color: rgba(212, 175, 55, 0.65);
}

.jewelry-theme-btn__label {
    letter-spacing: 0.03em;
}

.jewelry-logout-btn {
    margin-left: 0.25rem;
    height: 31px;
}

body.jewelry-theme-light .jewelry-theme-btn {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(184, 134, 11, 0.35);
    color: #5c4a1a;
}

body.jewelry-theme-light .jewelry-theme-btn:hover {
    background: #fff;
    color: #3d3010;
    border-color: rgba(184, 134, 11, 0.55);
}
</style>
