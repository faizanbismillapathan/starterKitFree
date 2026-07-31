{{-- Validation summary shown above long forms (12_Form_System.md §15). --}}
@if ($errors->any())
    <x-ui.alert type="danger" :title="__('ui.validation_summary')" class="mb-5">
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->unique() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
