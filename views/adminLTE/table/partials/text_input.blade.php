@php($filter_full_name = config('filters.prefix') . '-' . $name)

<div class="form-group">
    <label for="input_{{$filter_full_name}}">{{ $label }}</label>
    <input type="text" id="input_{{$filter_full_name}}"  class="form-control text-center" name="{{$filter_full_name}}" value="{{ getRequestParam($filter_full_name) }}">
    <div class="invalid-feedback help-block">
        @foreach($errors->get($filter_full_name) as $message)
            {{$message}}
        @endforeach
    </div>
</div>