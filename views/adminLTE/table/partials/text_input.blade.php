@php($filter_full_name = config('filters.prefix') . '-' . $name)

<div class="form-group">
    <label for="input_{{$filter_full_name}}">{{ $label }}</label>

    @if(isset($addon))
        <div class="input-group">
    @endif

                <input type="text" id="input_{{$filter_full_name}}"  class="form-control text-center"
                       name="{{$filter_full_name}}" value="{{ getRequestParam($filter_full_name) }}">

    @if(isset($addon))
                <div class="input-group-addon">{{ $addon }}</div>
        </div>
    @endif


    <div class="invalid-feedback help-block">
        @foreach($errors->get($filter_full_name) as $message)
            {{$message}}
        @endforeach
    </div>
</div>