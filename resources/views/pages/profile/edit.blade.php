@php
    use App\Enums\Permission;
    use App\Support\BreadcrumbBuilder;

    $breadcrumbs = BreadcrumbBuilder::forDashboard()
        ->add(__('profile.title'))
        ->toArray();

    $timezoneOptions = collect($timezones)->mapWithKeys(fn (string $tz): array => [$tz => $tz])->all();
@endphp

<x-layouts.app :title="__('profile.title')" :breadcrumbs="$breadcrumbs">
    <x-layout.page-header
        :title="__('profile.heading')"
        :description="__('profile.subheading')"
    />

    <div class="mt-6 space-y-6">
        <x-form.errors />

        {{-- Profile picture --}}
        <x-ui.card>
            <x-form.section
                :title="__('profile.sections.avatar')"
                :description="__('profile.sections.avatar_caption')"
            >
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    <x-ui.avatar :user="$user" size="xl" class="shrink-0" />

                    <div class="min-w-0 flex-1 space-y-3">
                        @can(Permission::MediaUpload->value)
                            <form
                                method="POST"
                                action="{{ route('profile.avatar.store') }}"
                                enctype="multipart/form-data"
                                x-data="fileUpload({
                                    maxSize: {{ (int) config('media.avatar.max_size') }},
                                    accept: @js(config('media.avatar.mime_types')),
                                    messages: {
                                        type: @js(__('profile.errors.avatar_type')),
                                        size: @js(__('profile.errors.avatar_dimensions')),
                                    },
                                })"
                            >
                                @csrf

                                <div
                                    @dragover.prevent="onDragOver()"
                                    @dragleave.prevent="onDragLeave()"
                                    @drop.prevent="onDrop($event)"
                                    :class="dragging
                                        ? 'border-[rgb(var(--color-primary))] bg-[rgb(var(--color-primary-soft))]'
                                        : 'border-[rgb(var(--color-border-strong))]'"
                                    class="flex flex-col items-center justify-center rounded-[var(--radius-md)] border border-dashed px-4 py-6 text-center transition-colors"
                                >
                                    <input
                                        type="file"
                                        name="avatar"
                                        x-ref="input"
                                        @change="onSelect($event)"
                                        accept="{{ implode(',', (array) config('media.avatar.mime_types')) }}"
                                        class="sr-only"
                                    />

                                    <template x-if="! previewUrl">
                                        <div>
                                            <x-ui.icon name="arrow-up-tray" class="mx-auto size-6 text-subtle" />
                                            <p class="mt-2 text-sm text-muted">
                                                {{ __('media.upload.prompt') }}
                                                <button
                                                    type="button"
                                                    @click="browse()"
                                                    class="font-semibold text-[rgb(var(--color-primary))] hover:underline"
                                                >{{ __('media.upload.browse') }}</button>
                                            </p>
                                            <p class="mt-1 text-xs text-subtle">
                                                {{ __('media.upload.hint', ['size' => (int) config('media.avatar.max_size') / 1024 .' MB']) }}
                                            </p>
                                        </div>
                                    </template>

                                    <template x-if="previewUrl">
                                        <div class="flex items-center gap-3">
                                            <img :src="previewUrl" alt="" class="size-14 rounded-full object-cover ring-1 ring-[rgb(var(--color-border))]" />
                                            <div class="text-left">
                                                <p class="text-sm font-medium text-default" x-text="filename"></p>
                                                <button
                                                    type="button"
                                                    @click="reset()"
                                                    class="text-xs font-medium text-[rgb(var(--color-danger))] hover:underline"
                                                >{{ __('media.upload.remove') }}</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs font-medium text-[rgb(var(--color-danger))]" role="alert"></p>

                                @error('avatar')
                                    <p class="mt-2 text-xs font-medium text-[rgb(var(--color-danger))]" role="alert">{{ $message }}</p>
                                @enderror

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <x-ui.button type="submit" size="sm" icon="arrow-up-tray" x-bind:disabled="! filename">
                                        {{ __('profile.actions.upload_avatar') }}
                                    </x-ui.button>
                                </div>
                            </form>
                        @endcan

                        @can(Permission::MediaDelete->value)
                            @if ($user->avatar_url)
                                <x-modal.confirm
                                    :title="__('profile.actions.remove_avatar')"
                                    :message="__('profile.sections.avatar_caption')"
                                    :action="route('profile.avatar.destroy')"
                                    method="DELETE"
                                    :confirm-label="__('profile.actions.remove_avatar')"
                                >
                                    <x-slot:trigger>
                                        <x-ui.button variant="danger-soft" size="sm" icon="trash">
                                            {{ __('profile.actions.remove_avatar') }}
                                        </x-ui.button>
                                    </x-slot:trigger>
                                </x-modal.confirm>
                            @endif
                        @endcan
                    </div>
                </div>
            </x-form.section>
        </x-ui.card>

        {{-- Personal information --}}
        <x-ui.card>
            <form
                method="POST"
                action="{{ route('profile.update') }}"
                x-data="dirtyForm(@js(__('You have unsaved changes.')))"
            >
                @csrf
                @method('PUT')

                <x-form.section
                    :title="__('profile.sections.personal')"
                    :description="__('profile.sections.personal_caption')"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.field :label="__('auth.fields.first_name')" for="first_name" required>
                            <x-form.input name="first_name" id="first_name" :value="$user->first_name" autocomplete="given-name" required />
                        </x-form.field>

                        <x-form.field :label="__('auth.fields.last_name')" for="last_name" required>
                            <x-form.input name="last_name" id="last_name" :value="$user->last_name" autocomplete="family-name" required />
                        </x-form.field>

                        <x-form.field
                            :label="__('auth.fields.email')"
                            for="email"
                            required
                            class="sm:col-span-2"
                            :help="config('starter_kit.auth.email_verification_required') ? __('Changing your email requires re-verification.') : null"
                        >
                            <x-form.input name="email" type="email" id="email" icon="envelope" :value="$user->email" autocomplete="email" required />
                        </x-form.field>

                        <x-form.field :label="__('auth.fields.phone')" for="phone" optional>
                            <x-form.input name="phone" type="tel" id="phone" :value="$user->phone" autocomplete="tel" />
                        </x-form.field>
                    </div>
                </x-form.section>

                <hr class="my-6 border-default" />

                <x-form.section
                    :title="__('profile.sections.preferences')"
                    :description="__('profile.sections.preferences_caption')"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.field :label="__('profile.fields.timezone')" for="timezone" optional>
                            <x-form.select
                                name="timezone"
                                id="timezone"
                                :options="$timezoneOptions"
                                :value="$user->timezone"
                                :placeholder="__('ui.actions.search')"
                            />
                        </x-form.field>

                        <x-form.field :label="__('profile.fields.locale')" for="locale" optional>
                            <x-form.select
                                name="locale"
                                id="locale"
                                :options="['en' => 'English']"
                                :value="$user->locale ?? 'en'"
                            />
                        </x-form.field>
                    </div>
                </x-form.section>

                <div class="mt-6 flex justify-end border-t border-default pt-5">
                    <x-ui.button type="submit">{{ __('profile.actions.save') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
