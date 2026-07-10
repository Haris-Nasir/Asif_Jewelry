<template>
    <section class="content">
                <div class="container-fluid">
                    <NewBroker
                        @refreshBrokersTable="refreshBrokersTable"
                    />
                    <SMBroker ref="form" />
                </div>
            </section>
</template>

<script>
import NewBroker from "./NewBroker";
import SMBroker from "./SMBroker";

export default {
    name: "BrokerContainer",
    components: {
        NewBroker,
        SMBroker
    },
    methods: {
        refreshBrokersTable: function(event) {
            this.$refs.form.getAllBrokers();
        }
    }
};
</script>