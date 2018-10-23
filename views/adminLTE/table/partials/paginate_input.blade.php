@php($filter_full_name = config('filters.prefix') . '-paginate')
<div class="form-group">
    <label for="input_{{$filter_full_name}}">{{ t('Entradas') }}</label>
    <select name="{{$filter_full_name}}" id="input_{{$filter_full_name}}" class="form-control select2">
        <option value="0" {{ $filters->getPagination() == '0' ? 'selected' : '' }}>
            {{ t('Todas') }}
        </option>
        <option value="10" {{ $filters->getPagination() == '10'  ? 'selected' : '' }}>
            {{ t('10 entradas') }}
        </option>
        <option value="25" {{ $filters->getPagination() == '25' ? 'selected' : '' }}>
            {{ t('25 entradas') }}
        </option>
        <option value="50" {{ $filters->getPagination() == '50' ? 'selected' : '' }}>
            {{ t('50 entradas') }}
        </option>
    </select>
    <div class="invalid-feedback help-block">
        @foreach($errors->get($filter_full_name) as $message)
            {{$message}}
        @endforeach
    </div>
</div>