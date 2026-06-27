<div
    x-data="{
        show: false,
        message: '',
        type: 'success',
        notify(detail) {
            this.message = detail.message ?? '';
            this.type = detail.type ?? 'success';
            this.show = true;
            clearTimeout(this._timer);
            this._timer = setTimeout(() => this.show = false, 4000);
        }
    }"
    x-on:notify.window="notify($event.detail)"
    class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex justify-center px-4"
>
    <div
        x-show="show"
        x-transition
        x-text="message"
        class="pointer-events-auto max-w-md rounded-xl px-4 py-3 text-sm font-medium shadow-lg ring-1"
        :class="type === 'error' ? 'bg-red-50 text-red-800 ring-red-200' : 'bg-teal-700 text-white ring-teal-800'"
        style="display: none;"
    ></div>
</div>
