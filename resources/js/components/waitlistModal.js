export default function waitlistModal() {
    return {
        openModal: false,
        updateUrl: '',
        enabled: false,
        value: '',

        open(data) {
            this.openModal = true;
            this.updateUrl = `/entries/${data.id}/waitlist`;

            if (data.current) {
                this.enabled = true;
                this.value = data.current;
            } else {
                this.enabled = false;
                this.value = '';
            }
        },

        close() {
            this.openModal = false;
        }
    };
}
