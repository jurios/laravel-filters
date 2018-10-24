@php($filter_full_name = config('filters.prefix') . '-' . $name)

@php($html_filter_full_name = $filter_full_name)

@if(isset($multiple) && $multiple)
    @php($html_filter_full_name = $filter_full_name . '[]')
@endif

<div class="form-group">
    <label for="input_{{$html_filter_full_name}}">{{ $label }}</label>
    <select name="{{$html_filter_full_name}}" id="input_{{$html_filter_full_name}}" class="form-control select2" {{ isset($multiple) && $multiple ? 'multiple' : '' }}>

        @if(!isset($multiple) || $multiple === false)
            <option {{!$filters->isFilterApplied($filter_full_name) ? 'selected' : ''}} value="">
                {{ $select_element }}
            </option>
        @endif

        @foreach($options as $option)
            <option value="{{ $option->$id }}"

                    @if(!isset($multiple) || $multiple === false)
                        {{ ($filters->getAppliedFilterValue($filter_full_name) == $option->$id) ? 'selected' : '' }}>
                    @endif

                    @if(isset($multiple) && $multiple)
                        {{--{{ dd(collect($filters->getAppliedFilterValue($filter_full_name))) }}--}}
                        {{ collect($filters->getAppliedFilterValue($filter_full_name))->contains($option->$id) ? 'selected' : ''}}>
                    @endif

                @if(isset($label_model_function))
                    {{ $option->$label_model_function($language) }}
                @endif
                @if(isset($label_model))
                    {{ $option->$label_model }}
                @endif
            </option>
        @endforeach

    </select>
    <div class="invalid-feedback help-block">
        @foreach($errors->get($html_filter_full_name) as $message)
            {{$message}}
        @endforeach
    </div>
</div>