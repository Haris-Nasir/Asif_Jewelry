<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="col-md-12 mt-3">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Audit Logs</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-row mb-3">
                                    <div class="col-md-2">
                                        <label>From</label>
                                        <input type="date" class="form-control" v-model="filters.from_date" @change="loadLogs">
                                    </div>
                                    <div class="col-md-2">
                                        <label>To</label>
                                        <input type="date" class="form-control" v-model="filters.to_date" @change="loadLogs">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Module</label>
                                        <input type="text" class="form-control" v-model="filters.module" placeholder="e.g. purchase" @change="loadLogs">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Action</label>
                                        <select class="form-control" v-model="filters.action" @change="loadLogs">
                                            <option value="">All</option>
                                            <option value="create">Create</option>
                                            <option value="update">Update</option>
                                            <option value="delete">Delete</option>
                                            <option value="login">Login</option>
                                            <option value="logout">Logout</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>When</th>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Module</th>
                                                <th>Record</th>
                                                <th>Description</th>
                                                <th>IP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="log in logs.data" :key="log.audit_log_id">
                                                <td>{{ formatDate(log.created_at) }}</td>
                                                <td>{{ log.user_name || '-' }}</td>
                                                <td>{{ log.action }}</td>
                                                <td>{{ log.module }}</td>
                                                <td>{{ log.record_id || '-' }}</td>
                                                <td>{{ log.description || '-' }}</td>
                                                <td>{{ log.ip_address || '-' }}</td>
                                            </tr>
                                            <tr v-if="!logs.data || !logs.data.length">
                                                <td colspan="7" class="text-center text-muted">No audit logs found.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3" v-if="logs.data && logs.data.length">
                                    <div class="col-sm-6 offset-5">
                                        <pagination :data="logs" @pagination-change-page="loadLogs"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
</template>

<script>
export default {
    name: 'AuditLogs',
    data() {
        return {
            logs: { data: [] },
            filters: {
                from_date: '',
                to_date: '',
                module: '',
                action: '',
            },
        };
    },
    mounted() {
        const d = new Date();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        this.filters.from_date = `${d.getFullYear()}-${month}-01`;
        this.filters.to_date = `${d.getFullYear()}-${month}-${String(d.getDate()).padStart(2, '0')}`;
        this.loadLogs();
    },
    methods: {
        loadLogs(page = 1) {
            axios.get('/api/audit-logs', {
                params: {
                    page,
                    from_date: this.filters.from_date,
                    to_date: this.filters.to_date,
                    module: this.filters.module || undefined,
                    action: this.filters.action || undefined,
                },
            })
                .then((res) => {
                    this.logs = res.data;
                })
                .catch(() => toastr.error('Unable to load audit logs.'));
        },
        formatDate(value) {
            if (!value) return '-';
            return new Date(value).toLocaleString();
        },
    },
};
</script>
