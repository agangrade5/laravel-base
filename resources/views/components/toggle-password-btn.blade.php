@props(['target'])

<button
    type="button"
    class="btn btn-outline-secondary toggle-password"
    data-target="{{ $target }}"
    tabindex="-1"
>
    <i class="bi bi-eye-slash"></i>
</button>
