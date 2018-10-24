
@if(!isset($has_filters))
    @php($has_filters = false)
@endif

@if(!isset($filters))
    @php($has_filters = false)
@endif

@if(!isset($apply_filters))
    @php($apply_filters = 'Apply filters')
@endif

<div id={{$id}} class="table-container">
    @if($has_filters)

        <div class="box-group" id="accordion-filters">

            <div class="panel box box-primary">

                <div class="box-header with-border">
                    <h4 class="box-title">
                        <a data-toggle="collapse" data-parent="#accordion-filters" href="#filters" aria-expanded="false" class="collapsed">
                            {{ $apply_filters }}
                        </a>
                    </h4>
                </div>

                <div id="filters" class="panel-collapse collapse {{ isset($filters) && $filters->isFiltered() ? 'in' : '' }}"
                     aria-expanded="{{ isset($filters) && $filters->isFiltered() ? 'true' : 'false' }}">

                    <div class="box-body">
                        <div class="col-md-12">
                            <form action="{{$action}}" method="GET">
                                @yield('table-filters-' . $id)
                                <div class="row">
                                    <div class="col-md-4 col-md-offset-4">
                                        <button type="submit" class="btn btn-success btn-block m-l-10 center">
                                            {{ $apply_filters }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    @endif

    <div class="table-responsive">
        <table id="{{ $id }}" class="table table-bordered table-hover table-sm laravel-filters">
            <thead>
            @yield('table-head-' . $id)
            </thead>
            <tbody>
            @yield('table-body-' . $id)
            </tbody>
        </table>
        <div>
            @yield('table-footer-' . $id)
            @if(isset($filters))
                <div>
                    {{ $filters->links() }}
                </div>
            @endif
        </div>
    </div>
    <div id="loading" hidden>
        <div class="col-md-12 text-center">
            <div class="bouncing-loader">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <br>
            <p class="small hint-text">{{ t('Cargando...') }}</p>
        </div>
    </div>
</div>

