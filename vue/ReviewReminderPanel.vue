<template>
    <div>
        <list-panel :items="items">
            <pkp-header slot="header">
                <h2>{{title}}</h2>
                <template slot="actions">
                    <pkp-button @click="openAddModal">{{addButtonLabel}}</pkp-button>
                </template>
            </pkp-header>
            <template v-slot:itemActions="{item}">
                <pkp-button @click="openEditModal(item)">{{editButtonLabel}}</pkp-button>
                <pkp-button @click="openDeleteModal(item)" isWarnable>{{deleteButtonLabel}}</pkp-button>
            </template>
        </list-panel>
        <modal v-bind="MODAL_PROPS" name="reviewForm" @closed="formModalClosed">
            <modal-content 
                closeLabel="Close"
                modalName="reviewForm"
                :title="activeFormTitle"
            >
                <pkp-form
                    v-bind="activeForm"
                    @set="updateForm"
                    @success="formSuccess"
                />
            </modal-content>
        </modal>
    </div>
</template>

<style scoped>
    .pkpForm {
        margin: -1rem !important;
        border: none !important;
    }
</style>

<script>
import ListPanel from '@/components/ListPanel/ListPanel.vue';
import PkpHeader from "@/components/Header/Header.vue";
import modal from "@/mixins/modal";
import cloneDeep from 'clone-deep';
import PkpForm from '@/components/Form/Form.vue';

export default {
    components: {
        ListPanel,
        PkpHeader,
        PkpForm
    },
    mixins: [
        modal
    ],
    props: {
        items: {
            type: Array,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        form: {
            type: Object,
            required: true
        },
        addLabel: {
            type: String,
            required: true
        },
        editLabel: {
            type: String,
            required: true
        },
        deleteModalTitle: {
            type: String,
            required: true
        },
        deleteModalMessage: {
            type: String,
            required: true
        },
        addButtonLabel: {
            type: String,
            required: true
        },
        editButtonLabel: {
            type: String,
            required: true
        },
        deleteButtonLabel: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            activeForm: null,
            activeFormTitle: ''
        }
    },
    computed: {
        closeLabel() {
            return this.__('common.close');
        }
    },
    methods: {
        openAddModal() {
            let activeForm = cloneDeep(this.form);
            this.activeForm = activeForm;
            this.activeFormTitle = this.addLabel;
            this.$modal.show("reviewForm");
        },
        openEditModal(item) {
            let activeForm = cloneDeep(this.form);
            activeForm.method = "PUT";
            for (var field of activeForm.fields) {
                field.value = item.raw[field.name];
            }
            activeForm.action += `/${item.id}`;
            this.activeForm = activeForm;
            this.activeFormTitle = this.editLabel;
            this.$modal.show("reviewForm");
        },
        openDeleteModal(item) {
            this.openDialog({
                cancelLabel: this.__("common.no"),
                modalName: "deleteReminder",
                title: this.deleteModalTitle,
                message: this.deleteModalMessage,
                callback: () => {
                    $.ajax({
                        url: this.form.action + `/${item.id}`,
                        type: "POST",
                        headers: {
                            "X-Csrf-Token": pkp.currentUser.csrfToken,
                            "X-Http-Method-Override": "DELETE"
                        },
                        success: (result) => {
                            for (var index in this.items) {
                                if (this.items[index].id == result.id) {
                                    this.items.splice(index, 1);
                                    break;
                                }
                            }
                            this.$modal.hide("deleteReminder");
                        }
                    })
                }
            })
        },
        formModalClosed() {
            this.activeForm = null;
            this.activeFormTitle = '';
        },
        formSuccess(result) {
            if (this.activeForm.method == "POST") {
                this.items.push(result);
            }
            else {
                for (var index in this.items) {
                    if (this.items[index].id == result.id) {
                        this.items[index] = result;
                        break;
                    }
                }
            }
            this.$modal.hide("reviewForm");
        },
        updateForm(formId, data) {
            let activeForm = {...this.activeForm}
            Object.keys(data).forEach(function(key) {
                activeForm[key] = data[key];
            });
            this.activeForm = activeForm;
        },
    }
}
</script>