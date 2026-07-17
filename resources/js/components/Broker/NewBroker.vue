<template>
    <div>
        <div class="row">
            <div class="col-md-12 mt-3">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ $t('broker.newTitle') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label for="brokerName" class="text-md col-form-label">{{ $t('broker.name') }} <span
                                        class="required-mark" style="color: red;">*</span></label>
                            </div>

                            <div class="col-md-3">
                                <input type="text" class="form-control" v-model="brokerName" maxlength="70"
                                    :placeholder="$t('broker.phName')">
                            </div>

                            <div class="col-md-2 ml-auto">
                                <label for="contactNo" class="text-md col-form-label">{{ $t('common.contactNo') }}</label>
                            </div>

                            <div class="col-md-3 mr-5">
                                <input type="text" class="form-control" v-model="contactNo" maxlength="11"
                                    :placeholder="$t('broker.phContact')">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" v-on:click="addBroker" class="btn btn-primary text-md">{{ $t('common.add') }}</button>
                        <button type="reset" v-on:click="resetFields"
                            class="btn btn-primary ml-3 text-md">{{ $t('common.reset') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import toastr from 'toastr';
    import swal from 'sweetalert2';

    toastr.options = {
        closeButton: true,
        closeDuration: 200,
        progressBar: true,
        newestOnTop: true,
        positionClass: "toast-top-center",
    };

    export default {
        name: "NewBroker",
        data() {
            return {
                brokerName: '',
                contactNo: '',
                status: null,
                message: null,
                errors: null
            }
        },
        methods: {
            addBroker() {
                if (this.brokerName == '') {
                    toastr["error"](this.$t('broker.nameRequired'));
                } else {
                    let payload = {
                        brokerName: this.brokerName,
                        contactNo: this.contactNo || null
                    };
                    axios
                        .post("../api/broker/insert", payload)
                        .then(response => {
                            if (response.data.status == -1) {
                                var errormsg = response.data.errors;
                                try {
                                    if (errormsg.brokerName)
                                        toastr["error"](errormsg.brokerName);
                                } catch (err) { }

                                try {
                                    if (errormsg.contactNo)
                                        toastr["error"](errormsg.contactNo);
                                } catch (err) { }

                            } else if (response.data.status == 0) {
                                toastr["warning"](response.data.message);
                            } else if (response.data.status == 1) {
                                swal.fire({
                                    title: this.$t('common.success'),
                                    html: "<h5 style='color:#9C9794'>" + this.$t('broker.added') + "</h5>",
                                    icon: 'success'
                                }).then((result) => {
                                    this.$emit("refreshBrokersTable");
                                    this.resetFields();
                                });
                            }
                        })
                        .catch(err => {
                            console.log(err.response.data.message);
                            toastr["error"](this.$t('common.somethingWrong'));
                        })
                }

            },

            resetFields() {
                this.brokerName = '';
                this.contactNo = '';
            }
        }
    }
</script>