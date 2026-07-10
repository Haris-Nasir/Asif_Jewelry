<template>
    <section class="content">
                <div class="container-fluid">
                    <NewInwardQuality
                        @refreshInwardQualityTable = "refreshInwardQualityTable"
                    />
                    <SMInwardQuality ref="form"/>
                </div>
            </section>
</template>

<script>
import NewInwardQuality from './NewInwardQuality';
import SMInwardQuality from './SMInwardQuality';

export default {
    name: "InwardQualityContainer",
    components: {
        NewInwardQuality,
        SMInwardQuality
    },
    methods: {
        refreshInwardQualityTable: function(event){
            this.$refs.form.getAllInwardQualities();
        }
    }
}
</script>