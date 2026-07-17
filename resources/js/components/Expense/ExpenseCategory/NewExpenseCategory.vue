<template>
    <div>
        <div class="row">
            <!-- left column -->
            <div class="col-md-12 mt-3">
                <!-- New Expense Category Form Elements -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ $t('expCat.newTitle') }}
                        </h3>
                        <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>
                        </div>
                    </div>
                    <!-- /.card-header -->

                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label
                                    for="category-name-txt"
                                    class="text-md col-form-label"
                                    >{{ $t('expCat.categoryName') }}</label
                                >
                                <span class="required-mark" style="color: red;">*</span>
                            </div>
                            <div class="col-md-4">
                                <input
                                    type="text"
                                    class="form-control text-md"
                                    v-model="expenseCategoryTxt"
                                    :placeholder="$t('expCat.phName')"
                                />
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <!-- card-footer -->
                    <div class="card-footer">
                        <button
                            type="submit"
                            class="btn btn-primary text-md"
                            @click="addCategory"
                        >
                            {{ $t('common.add') }}
                        </button>
                        <button
                            type="reset"
                            class="btn btn-primary ml-3 text-md"
                            @click="resetNewCategoryForm"
                        >
                            {{ $t('common.reset') }}
                        </button>

                        
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.card -->
            </div>
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
    </div>
</template>

<script>
import toastr from "toastr";
import swal from "sweetalert2";

export default {
    name: "NewExpenseCategory",
    data() {
        return {
            expenseCategoryTxt: ""
        };
    },
    methods: {

        addCategory: function() {
            if (this.expenseCategoryTxt == "") {
                toastr.info(this.$t('expCat.required'));
            } else if (this.expenseCategoryTxt.length > 50) {
                toastr.warning(this.$t('expCat.max'));
            } else {
                let payload = {
                    expenseCategory: this.expenseCategoryTxt
                };
                axios
                    .post("/api/expensecategory", payload)
                    .then(res => {
                        if (res.data.status == 1) {
                            swal.fire({
                                title: this.$t('common.success'),
                                html:
                                    "<h5 style='color:#9C9794'>" + this.$t('expCat.added') + "</h5>",
                                icon: "success"
                            }).then(result => {
                                this.expenseCategoryTxt = "";
                                this.$emit("refreashCategoriesTable");
                            });
                        } else if (res.data.status == 0) {
                            toastr.info(res.data.message);
                        } else {
                            toastr.error(res.data.message);
                        }
                    })
                    .catch(err => {
                        toastr.error(this.$t('common.somethingWrong'));
                    });
            }
        },

        resetNewCategoryForm: function() {
            this.expenseCategoryTxt = "";
        },
    }
};
</script>
