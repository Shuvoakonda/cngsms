export function offcanvasCrud(config) {
    return {
        panelOpen: config.openOnLoad ?? false,
        mode: config.initialMode ?? 'create',
        title: config.initialTitle ?? config.createTitle,
        editActionUrl: config.initialEditUrl ?? '',

        init() {
            if (config.deepLinkCreate) {
                this.$nextTick(() => this.openCreate());
            } else if (config.deepLinkEditId && config.recordsById?.[config.deepLinkEditId]) {
                this.$nextTick(() => this.openEdit(config.recordsById[config.deepLinkEditId]));
            } else if (this.panelOpen) {
                this.$nextTick(() => {
                    this.updateAmount();
                    this.syncGuestPurchaseState();
                });
            }
        },

        closePanel() {
            this.panelOpen = false;
        },

        openCreate() {
            this.mode = 'create';
            this.title = config.createTitle;
            this.editActionUrl = '';
            this.panelOpen = true;

            this.$nextTick(() => {
                this.resetForm(config.defaults ?? {});
                this.updateAmount();
                this.syncGuestPurchaseState();
            });
        },

        openEdit(record) {
            this.mode = 'edit';
            this.title = config.editTitle;
            this.editActionUrl = config.updateUrlTemplate.replace('__ID__', record.id);
            this.panelOpen = true;

            this.$nextTick(() => {
                if (this.$refs.editIdInput) {
                    this.$refs.editIdInput.value = record.id;
                }

                if (this.$refs.methodPatch) {
                    this.$refs.methodPatch.disabled = false;
                }

                this.fillForm(record);
                this.updateAmount();
                this.syncGuestPurchaseState();
            });
        },

        resetForm(defaults) {
            const form = this.$refs.form;

            if (!form) {
                return;
            }

            if (this.$refs.editIdInput) {
                this.$refs.editIdInput.value = '';
            }

            if (this.$refs.methodPatch) {
                this.$refs.methodPatch.disabled = true;
            }

            for (const [name, value] of Object.entries(defaults)) {
                this.setFieldValue(form, name, value);
            }
        },

        fillForm(record) {
            const form = this.$refs.form;

            if (!form) {
                return;
            }

            for (const [name, value] of Object.entries(record)) {
                if (name === 'id') {
                    continue;
                }

                this.setFieldValue(form, name, value);
            }
        },

        setFieldValue(form, name, value) {
            const field = form.elements.namedItem(name);

            if (!field) {
                return;
            }

            if (field instanceof RadioNodeList) {
                for (const option of field) {
                    option.checked = option.value === String(value ?? '');
                }

                return;
            }

            field.value = value ?? '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
        },

        calculatedAmount: '0.00',
        guestPurchase: false,

        updateAmount() {
            const form = this.$refs.form;

            if (!form) {
                return;
            }

            const quantity = parseFloat(form.elements.quantity?.value) || 0;
            const rate = parseFloat(form.elements.rate?.value) || 0;
            const baseAmount = quantity * rate;
            const discount = this.adjustmentAmount(baseAmount, form.elements.discount_value?.value, form.elements.discount_type?.value);
            const bonus = this.adjustmentAmount(baseAmount, form.elements.bonus_value?.value, form.elements.bonus_type?.value);
            this.calculatedAmount = Math.max(0, baseAmount - discount + bonus).toFixed(2);
        },

        adjustmentAmount(baseAmount, value, type) {
            const amount = parseFloat(value) || 0;

            if (amount <= 0) {
                return 0;
            }

            return type === 'percent' ? baseAmount * (amount / 100) : amount;
        },

        syncDriverFromVehicle(event) {
            const driverId = event.target.selectedOptions[0]?.dataset.driverId;

            if (!driverId || !this.$refs.driverSelect) {
                return;
            }

            this.$refs.driverSelect.value = driverId;
        },

        onVehicleChange(event) {
            this.guestPurchase = !event.target.value;

            if (this.guestPurchase && this.$refs.driverSelect) {
                this.$refs.driverSelect.value = '';
                return;
            }

            this.syncDriverFromVehicle(event);
        },

        syncGuestPurchaseState() {
            const form = this.$refs.form;

            if (!form) {
                return;
            }

            this.guestPurchase = !form.elements.vehicle_id?.value;
        },
    };
}
