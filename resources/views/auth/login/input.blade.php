<div class="form-group row">
    <label for="{{ $name }}" class="col w-full md:w-4/12 text-gray-800">{{ $slot }}</label>

    <div class="col w-full md:w-8/12">
        <input
            class="form-input block w-full @error($name) border-red-600 @enderror"
            id="{{ $name }}"
            type="{{ $type ?? 'text' }}"
            name="{{ $name }}"
            value="{{ old($name) }}"
            autocomplete="{{ $autocomplete ?? 'on' }}"
            {{ !empty($required) ? 'required' : '' }}
            {{ $attributes ?? '' }}
        />

        @error($name)
        <span class="text-red-600" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
