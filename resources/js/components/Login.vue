<template>
    <div class="login-page hold-transition dark-mode">
        <div class="login-box">
            <div class="card card-outline card-warning">
                <div class="card-header text-center">
                    <h1 class="h3"><b>Asif</b> Jewelry</h1>
                    <p class="text-muted mb-0">Management System</p>
                </div>
                <div class="card-body">
                    <p class="login-box-msg">Sign in to continue</p>
                    <form @submit.prevent="login">
                        <div class="input-group mb-3">
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-control"
                                placeholder="Email"
                                required
                            />
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                placeholder="Password"
                                required
                            />
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning btn-block" :disabled="loading">
                                    {{ loading ? 'Signing in...' : 'Sign In' }}
                                </button>
                            </div>
                        </div>
                    </form>
                    <p v-if="error" class="text-danger mt-3 mb-0">{{ error }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { setAuth } from '../auth';

export default {
    name: 'Login',
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
            loading: false,
            error: '',
        };
    },
    methods: {
        login() {
            this.loading = true;
            this.error = '';

            axios
                .post('/api/login', this.form)
                .then((res) => {
                    setAuth(res.data.token, res.data.user);
                    axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`;

                    if (res.data.user.role === 'investor') {
                        this.$router.push('/investor');
                    } else {
                        this.$router.push('/dashboard');
                    }
                })
                .catch((err) => {
                    this.error =
                        err.response?.data?.message ||
                        err.response?.data?.errors?.email?.[0] ||
                        'Login failed. Please check your credentials.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1f2d3d;
}
.login-box {
    width: 360px;
}
</style>
