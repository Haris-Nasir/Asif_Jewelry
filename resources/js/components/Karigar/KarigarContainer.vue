<template>
  <section class="content">
        <div class="container-fluid">
          <IssueKarigar :karigars="karigarList" @job-changed="refreshAll" />
          <SMKarigarJobs ref="jobsTable" @job-changed="refreshAll" />
          <ManageKarigars @karigars-changed="loadKarigars" />
        </div>
      </section>
</template>

<script>
import IssueKarigar from './IssueKarigar';
import SMKarigarJobs from './SMKarigarJobs';
import ManageKarigars from './ManageKarigars';

export default {
  name: 'KarigarContainer',
  components: { IssueKarigar, SMKarigarJobs, ManageKarigars },
  data() {
    return { karigarList: [] };
  },
  mounted() {
    this.loadKarigars();
  },
  methods: {
    loadKarigars() {
      axios.get('/api/karigar/list').then(res => {
        this.karigarList = res.data || [];
      });
    },
    refreshAll() {
      this.loadKarigars();
      if (this.$refs.jobsTable) {
        this.$refs.jobsTable.loadJobs();
      }
    },
  },
};
</script>
