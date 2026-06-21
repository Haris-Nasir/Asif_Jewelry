<template>
  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars text-lg"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block" v-if="user">
        <span class="nav-link text-md">{{ user.name }} ({{ user.role }})</span>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item text-md">
        <input type="checkbox" class="text-md" id="changeTheme" onchange="changeTheme" data-size="sm" data-onstyle="light" data-offstyle="secondary" data-on="<i class='bi bi-sun'></i>" data-off="<i class='bi bi-moon'></i>" checked data-toggle="toggle">
      </li>
      <li class="nav-item">
        <button @click="logout" class="btn btn-sm btn-danger text-md" style="margin-left: 20px; height: 31px;">
          <i class="bi bi-box-arrow-left"></i> Logout
        </button>
      </li>
    </ul>
  </nav>
</template>

<script>
import { clearAuth, getUser } from '../../auth';

export default {
    name: "Header",
    computed: {
        user() {
            return getUser();
        },
    },
    methods: {
        logout() {
            axios.post('/api/logout').finally(() => {
                clearAuth();
                delete axios.defaults.headers.common['Authorization'];
                this.$router.push('/login');
            });
        },
    },
};

$(function(){
  $('#changeTheme').bootstrapToggle();
  var theme = "dark";
  $('#changeTheme').change(function(){
    if(theme=="light"){
      $("body").addClass('dark-mode');
      theme = "dark";
    }else{
      $("body").removeClass('dark-mode');
      theme = "light";
    }
  })
});
</script>
