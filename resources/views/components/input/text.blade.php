<div {{ $attributes->only('class')->merge(["class" => "flex items-center"]) }}>
    <label for="{{ $name }}" class="w-1/3 my-auto">{{ $label }}</label>

    <div class="input-with-error flex-grow">
        @error($name)
        <span class="input-error">{{ $message }}</span>
        @enderror

        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}"
               value="{{ old($name) }}"
               class="input"
               {{ $attributes->except('class') }}>
    </div>
</div>