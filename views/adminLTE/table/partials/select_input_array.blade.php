@php($filter_full_name = config('filters.prefix') . '-' . $name)

<div class="form-group">
    <label for="input_{{$filter_full_name}}">{{ $label }}</label>
    <select name="{{$filter_full_name}}" id="input_{{$filter_full_name}}" class="form-control select2">

        @php($first = true)
        @foreach($options as $option)
            <option value="{{ $option['id'] }}"
                    {{ ($filters->getAppliedFilterValue($filter_full_name) == $option['id']) || (!$filters->isFilterApplied($filter_full_name) && $first) ? 'selected' : '' }}>
                {{ $option['label'] }}
            </option>

            @php($first = false)
        @endforeach

    </select>
    <div class="invalid-feedback help-block">
        @foreach($errors->get($filter_full_name) as $message)
            {{$message}}
        @endforeach
    </div>
</div>