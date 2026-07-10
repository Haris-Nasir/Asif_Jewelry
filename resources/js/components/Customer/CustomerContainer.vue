<template>
  <section class="content">
        <div class="container-fluid">
          <NewCustomer @refreshCustomersTable="refreshCustomersTable" />
          <SMCustomer
            ref="form"
          />
        </div>
      </section>
</template>

<script>
import NewCustomer from "./NewCustomer";
import SMCustomer from "./SMCustomer";

export default {
  name: "CustomerContainer",
  components: {
    NewCustomer,
    SMCustomer,
  },
  methods: {
    refreshCustomersTable: function (event) {
      this.$refs.form.getAllCustomers();
    },
  },
};
</script>
