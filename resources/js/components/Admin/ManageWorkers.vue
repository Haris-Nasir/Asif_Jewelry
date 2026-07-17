<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-5 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('admin.addWorker') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>{{ $t('common.name') }} *</label>
                                        <input type="text" class="form-control" v-model="newWorker.name">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.email') }} *</label>
                                        <input type="email" class="form-control" v-model="newWorker.email">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.password') }}</label>
                                        <input type="password" class="form-control" v-model="newWorker.password" :placeholder="$t('common.defaultPassword')">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="createWorker">{{ $t('admin.addWorker') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('admin.workerPerms') }}</h3>
                                </div>
                                <div class="card-body" v-if="selectedWorker">
                                    <p><strong>{{ selectedWorker.name }}</strong> ({{ selectedWorker.email }})</p>
                                    <div class="row">
                                        <div class="col-md-6" v-for="(label, key) in permissionLabels" :key="key">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" :id="'perm-' + key" :value="key" v-model="selectedPermissions">
                                                <label class="form-check-label" :for="'perm-' + key">{{ permLabel(key, label) }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body text-muted" v-else>
                                    {{ $t('admin.selectWorker') }}
                                </div>
                                <div class="card-footer" v-if="selectedWorker">
                                    <button class="btn btn-primary" @click="savePermissions">{{ $t('admin.savePerms') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('admin.workers') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ $t('common.name') }}</th>
                                                <th>{{ $t('common.email') }}</th>
                                                <th>{{ $t('admin.permissions') }}</th>
                                                <th class="text-center">{{ $t('common.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="worker in workers" :key="worker.id">
                                                <td>{{ worker.name }}</td>
                                                <td>{{ worker.email }}</td>
                                                <td>
                                                    <span class="worker-perm-list">{{ formatWorkerPerms(worker) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="table-actions">
                                                        <button
                                                            type="button"
                                                            class="btn btn-primary btn-sm table-actions__label"
                                                            :title="$t('admin.editPerms')"
                                                            @click="selectWorker(worker)"
                                                        >
                                                            <i class="fas fa-user-edit"></i>
                                                            <span class="d-none d-md-inline ml-1">{{ $t('admin.editPerms') }}</span>
                                                            <span class="d-inline d-md-none ml-1">{{ $t('common.edit') }}</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </section>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';

export default {
    name: 'ManageWorkers',
    data() {
        return {
            workers: [],
            permissionLabels: {},
            defaultPermissions: [],
            selectedWorker: null,
            selectedPermissions: [],
            newWorker: { name: '', email: '', password: '' },
        };
    },
    mounted() {
        this.loadWorkers();
    },
    methods: {
        permLabel(key, fallback) {
            const translated = this.$t('perm.' + key);
            return translated === 'perm.' + key ? (fallback || key) : translated;
        },
        formatWorkerPerms(worker) {
            const keys = worker.permissions || this.defaultPermissions || [];
            return keys.map((k) => this.permLabel(k, k)).join(', ');
        },
        loadWorkers() {
            axios.get('/api/users/workers')
                .then((res) => {
                    this.workers = res.data.data || [];
                    this.permissionLabels = res.data.permission_labels || {};
                })
                .catch(() => toastr['error'](this.$t('admin.loadWorkersFail')));
        },
        selectWorker(worker) {
            this.selectedWorker = worker;
            this.selectedPermissions = [...(worker.permissions || [])];
            if (!this.selectedPermissions.length) {
                this.defaultPermissions = Object.keys(this.permissionLabels).filter((k) =>
                    ['dashboard', 'purchases', 'sales', 'invoices', 'expenses', 'laboratory', 'karigar', 'stock'].includes(k)
                );
                this.selectedPermissions = [...this.defaultPermissions];
            }
        },
        savePermissions() {
            axios.put(`/api/users/workers/${this.selectedWorker.id}/permissions`, {
                permissions: this.selectedPermissions,
            })
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({ title: this.$t('common.success'), html: "<h5 style='color:#9C9794'>" + this.$t('admin.permsUpdated') + "</h5>", icon: 'success' });
                        this.loadWorkers();
                    }
                })
                .catch(() => toastr['error'](this.$t('admin.permsFail')));
        },
        createWorker() {
            if (!this.newWorker.name || !this.newWorker.email) {
                toastr['error'](this.$t('admin.nameEmailRequired'));
                return;
            }
            axios.post('/api/users/workers', this.newWorker)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({ title: this.$t('common.success'), html: "<h5 style='color:#9C9794'>" + this.$t('admin.workerCreated') + "</h5>", icon: 'success' });
                        this.newWorker = { name: '', email: '', password: '' };
                        this.loadWorkers();
                    }
                })
                .catch((err) => toastr['error'](err.response?.data?.message || this.$t('common.somethingWrong')));
        },
    },
};
</script>
