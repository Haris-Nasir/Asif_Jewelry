<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <NewLabJob @refreshLabJobs="refreshLabJobs" />
                    <SMLabJob ref="labJobsTable" />
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import NewLabJob from './NewLabJob';
import SMLabJob from './SMLabJob';

export default {
    name: 'LabJobContainer',
    components: {
        NewLabJob,
        SMLabJob,
    },
    methods: {
        refreshLabJobs() {
            this.$refs.labJobsTable.loadJobs();
            this.$refs.labJobsTable.loadSummary();
        },
    },
};
</script>
