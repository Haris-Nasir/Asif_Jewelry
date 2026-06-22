<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-5 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Add Worker</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" class="form-control" v-model="newWorker.name">
                                    </div>
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" class="form-control" v-model="newWorker.email">
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" v-model="newWorker.password" placeholder="Default: password">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="createWorker">Add Worker</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Worker Permissions</h3>
                                </div>
                                <div class="card-body" v-if="selectedWorker">
                                    <p><strong>{{ selectedWorker.name }}</strong> ({{ selectedWorker.email }})</p>
                                    <div class="row">
                                        <div class="col-md-6" v-for="(label, key) in permissionLabels" :key="key">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" :id="'perm-' + key" :value="key" v-model="selectedPermissions">
                                                <label class="form-check-label" :for="'perm-' + key">{{ label }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body text-muted" v-else>
                                    Select a worker below to edit permissions.
                                </div>
                                <div class="card-footer" v-if="selectedWorker">
                                    <button class="btn btn-primary" @click="savePermissions">Save Permissions</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Workers</h3>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Permissions</th>
                                                <th width="15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="worker in workers" :key="worker.id">
                                                <td>{{ worker.name }}</td>
                                                <td>{{ worker.email }}</td>
                                                <td>{{ (worker.permissions || defaultPermissions).join(', ') }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-primary btn-sm" @click="selectWorker(worker)">Edit Permissions</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
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
        loadWorkers() {
            axios.get('/api/users/workers')
                .then((res) => {
                    this.workers = res.data.data || [];
                    this.permissionLabels = res.data.permission_labels || {};
                })
                .catch(() => toastr['error']('Unable to load workers.'));
        },
        selectWorker(worker) {
            this.selectedWorker = worker;
            this.selectedPermissions = [...(worker.permissions || [])];
            if (!this.selectedPermissions.length) {
                this.defaultPermissions = Object.keys(this.permissionLabels).filter((k) =>
                    ['dashboard', 'purchases', 'sales', 'invoices', 'expenses', 'laboratory', 'stock'].includes(k)
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
                        swal.fire({ title: 'Success', html: "<h5 style='color:#9C9794'>Permissions Updated!</h5>", icon: 'success' });
                        this.loadWorkers();
                    }
                })
                .catch(() => toastr['error']('Failed to update permissions.'));
        },
        createWorker() {
            if (!this.newWorker.name || !this.newWorker.email) {
                toastr['error']('Name and email are required.');
                return;
            }
            axios.post('/api/users/workers', this.newWorker)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({ title: 'Success', html: "<h5 style='color:#9C9794'>Worker Created!</h5>", icon: 'success' });
                        this.newWorker = { name: '', email: '', password: '' };
                        this.loadWorkers();
                    }
                })
                .catch((err) => toastr['error'](err.response?.data?.message || 'Failed to create worker.'));
        },
    },
};
</script>
