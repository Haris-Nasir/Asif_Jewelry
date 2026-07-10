<template>
    <section class="content">
                <div class="container-fluid">
                    <NewExpenseCategory
                        @refreashCategoriesTable="refreashCategoriesTable"
                    />
                    <SMExpenseCategory ref="form" />
                </div>
            </section>
</template>

<script>
import NewExpenseCategory from "./NewExpenseCategory";
import SMExpenseCategory from "./SMExpenseCategory";

export default {
    name: "ExpenseCategoryContainer",
    components: {
        NewExpenseCategory,
        SMExpenseCategory
    },
    methods: {
        refreashCategoriesTable: function(event) {
            this.$refs.form.getAllExpenseCategories();
        }
    }
};
</script>
