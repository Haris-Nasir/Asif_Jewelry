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
        <select
          class="jewelry-lang-select"
          :value="locale"
          :title="$t('lang.label')"
          @change="onLocaleChange"
        >
          <option value="en">{{ $t('lang.english') }}</option>
          <option value="ur">{{ $t('lang.urdu') }}</option>
        </select>
      </li>
      <li class="nav-item">
        <button
          type="button"
          class="btn btn-sm jewelry-theme-btn"
          :title="isDark ? $t('theme.toLight') : $t('theme.toDark')"
          @click="toggleTheme"
        >
          <i :class="isDark ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill'"></i>
          <span class="jewelry-theme-btn__label">{{ isDark ? $t('theme.dark') : $t('theme.light') }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button @click="logout" class="btn btn-sm btn-danger text-md jewelry-logout-btn">
          <i class="bi bi-box-arrow-left"></i> {{ $t('header.logout') }}
        </button>
      </li>
    </ul>
  </nav>
</template>

<script>
import { clearAuth, getUser } from '../../auth';
import { i18nState, setLocale } from '../../i18n';

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
        locale() {
            return i18nState.locale;
        },
    },
    mounted() {
        const saved = localStorage.getItem(THEME_KEY);
        this.isDark = saved !== 'light';
        this.applyTheme(this.isDark);
    },
    methods: {
        onLocaleChange(event) {
            setLocale(event.target.value);
        },
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
.jewelry-lang-select {
    margin-right: 0.5rem;
    height: 31px;
    padding: 0 0.65rem;
    border-radius: 999px;
    border: 1px solid rgba(212, 175, 55, 0.45);
    background: rgba(212, 175, 55, 0.15);
    color: #f0d875;
    font-weight: 600;
    font-size: 0.82rem;
    line-height: 1.2;
    cursor: pointer;
    outline: none;
    appearance: auto;
}

.jewelry-lang-select:hover,
.jewelry-lang-select:focus {
    background: rgba(212, 175, 55, 0.28);
    border-color: rgba(212, 175, 55, 0.65);
    color: #fff;
}

.jewelry-lang-select option {
    color: #1a1a1a;
    background: #fff;
}

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

body.jewelry-theme-light .jewelry-lang-select,
body.jewelry-theme-light .jewelry-theme-btn {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(184, 134, 11, 0.35);
    color: #5c4a1a;
}

body.jewelry-theme-light .jewelry-lang-select:hover,
body.jewelry-theme-light .jewelry-lang-select:focus,
body.jewelry-theme-light .jewelry-theme-btn:hover {
    background: #fff;
    color: #3d3010;
    border-color: rgba(184, 134, 11, 0.55);
}
</style>
