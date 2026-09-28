<form method="POST" action="{{ route('language.switch') }}" class="d-inline-flex align-items-center gap-2">
    @csrf
    <label for="language-select" class="visually-hidden">{{ __('ui.language') }}</label>
    <select id="language-select" name="locale" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="{{ __('ui.language') }}">
        <option value="en" @selected(app()->getLocale() === 'en')>{{ __('ui.english') }}</option>
        <option value="th" @selected(app()->getLocale() === 'th')>{{ __('ui.thai') }}</option>
    </select>
</form>
