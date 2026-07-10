<template>
    <section class="content">
                <div class="container-fluid">
                    <NewSellQuality
                        @refreshSellQualityTable = "refreshSellQualityTable"
                    />
                    <SMSellQuality ref="form"/>
                </div>
            </section>
</template>

<script>
import NewSellQuality from './NewSellQuality';
import SMSellQuality from './SMSellQuality';

export default {
    name: "SellQualityContainer",
    components: {
        NewSellQuality,
        SMSellQuality
    },
    methods: {
        refreshSellQualityTable: function(event){
            this.$refs.form.getAllSellQualities();
        }
    }
}
</script>