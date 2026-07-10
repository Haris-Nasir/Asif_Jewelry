<template>
  <div class="jewelry-app">
    <div class="jewelry-app-bg" aria-hidden="true">
      <div class="metal-bg metal-bg--silver"></div>
      <div class="metal-bg metal-bg--gold"></div>
      <div class="jewelry-float jewelry-float--gold"></div>
      <div class="jewelry-float jewelry-float--silver"></div>
    </div>
    <div class="wrapper">
      <Header/>
      <Sidebar/>
      <div class="content-wrapper">
        <transition name="fade" mode="out-in">
          <router-view :key="$route.path"></router-view>
        </transition>
      </div>
      <Footer/>
    </div>
  </div>
</template>

<script>

import Header from "./Header";
import Sidebar from "./Sidebar";
import Footer from "./Footer";
export default {
  name: "Container",
  components: {
    Header,
    Sidebar,
    Footer,
  },
};

</script>
