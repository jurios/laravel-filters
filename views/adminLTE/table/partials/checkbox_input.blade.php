@php($filter_full_name = config('filters.prefix') . '-' . $name)

<div class="form-group">
    <label></label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="{{ $value }}"
               {{ $filters->isFilterApplied($filter_full_name) ? 'checked' : '' }}
               id="input_{{$filter_full_name}}" name="{{$filter_full_name}}">
        <label class="form-check-label" for="input_{{$filter_full_name}}">
            {{ $label }}
        </label>
    </div>
</div>