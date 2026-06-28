<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Company Profile</h2>
        <p class="mt-1 text-sm text-slate-600">These settings apply across the sidebar, login page, reports, exports, and purchase entry forms.</p>
    </header>

    <form
        method="post"
        action="{{ route('admin.settings.company.update') }}"
        enctype="multipart/form-data"
        class="mt-6 grid gap-6 lg:grid-cols-2"
        x-data="{
            preview: null,
            logoName: '',
            savedLogo: @js($company->logoUrl()),
            init() {
                this.preview = this.savedLogo;
            },
        }"
    >
        @csrf
        @method('patch')

        <div class="form-field lg:col-span-2">
            <x-input-label for="company_logo" value="Company Logo" />
            <div class="mt-2 grid gap-4 lg:grid-cols-[1fr_220px]">
                <div class="flex flex-col gap-4 rounded-xl border border-slate-300 bg-slate-50 p-4 sm:flex-row sm:items-start">
                    <div class="relative flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-2">
                        <img
                            x-show="preview"
                            x-cloak
                            :src="preview"
                            alt="Logo preview"
                            class="max-h-20 max-w-20 object-contain"
                        >
                        <div x-show="! preview" class="flex h-full w-full items-center justify-center">
                            <x-application-logo class="h-16 w-16 shrink-0" />
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 space-y-3">
                        <input
                            id="company_logo"
                            name="logo"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-800"
                            @change="
                                logoName = $event.target.files[0]?.name ?? '';
                                preview = $event.target.files[0]
                                    ? URL.createObjectURL($event.target.files[0])
                                    : savedLogo;
                            "
                        >
                        <p class="text-xs text-slate-500">PNG, JPG, or WEBP up to 2 MB. Shown in the sidebar, login page, and report headers.</p>
                        <p x-show="logoName" x-text="logoName ? 'Selected: ' + logoName : ''" class="text-xs font-medium text-teal-700"></p>
                        @if ($company->logo_path)
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="remove_logo"
                                    value="1"
                                    class="rounded border-slate-400 text-teal-700 focus:ring-teal-600"
                                    @change="preview = $event.target.checked ? null : savedLogo"
                                >
                                Remove current logo
                            </label>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sidebar preview</p>
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 ring-1 ring-white/10">
                            <img
                                x-show="preview"
                                x-cloak
                                :src="preview"
                                alt=""
                                class="max-h-7 max-w-7 object-contain"
                            >
                            <x-application-logo x-show="! preview" class="h-7 w-7 shrink-0" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white">{{ $company->name }}</p>
                            <p class="truncate text-xs text-slate-400">Ledger Management</p>
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('logo')" />
        </div>

        <div class="form-field lg:col-span-2">
            <x-input-label for="company_name" value="Company Name" />
            <x-text-input id="company_name" name="name" type="text" :value="old('name', $company->name)" required />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="form-field lg:col-span-2">
            <x-input-label for="company_address" value="Address" />
            <x-textarea-input id="company_address" name="address" rows="3">{{ old('address', $company->address) }}</x-textarea-input>
            <x-input-error :messages="$errors->get('address')" />
        </div>

        <div class="form-field">
            <x-input-label for="currency" value="Currency" />
            <x-text-input id="currency" name="currency" type="text" :value="old('currency', $company->currency)" required />
            <x-input-error :messages="$errors->get('currency')" />
        </div>

        <div class="form-field">
            <x-input-label for="date_format" value="Date Format" />
            <x-text-input id="date_format" name="date_format" type="text" :value="old('date_format', $company->date_format)" required />
            <p class="text-xs text-slate-500">Example: d-m-Y</p>
            <x-input-error :messages="$errors->get('date_format')" />
        </div>

        <div class="form-field">
            <x-input-label for="quantity_unit" value="Quantity Unit" />
            <x-text-input id="quantity_unit" name="quantity_unit" type="text" :value="old('quantity_unit', $company->quantity_unit)" required />
            <p class="text-xs text-slate-500">Example: KG or m3</p>
            <x-input-error :messages="$errors->get('quantity_unit')" />
        </div>

        <div class="flex items-center lg:col-span-2">
            <x-primary-button>Save Company Settings</x-primary-button>
        </div>
    </form>
</section>
