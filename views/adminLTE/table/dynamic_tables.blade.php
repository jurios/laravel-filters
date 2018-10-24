<script>

    (function() {

        const tables = [];
        
        function bindTableFilterEvent(id) {
            $('#' + id + ' form').submit(function (e) {
                e.preventDefault();
                e.stopPropagation();

                $('#' + id + ' form :input').each(function() {
                    if ($(this).val() === null || $(this).val() === '') {
                        $(this).prop('disabled', true);
                    }
                });

                loadViewTable(id, true);

                redirectToURL(id, $(this).attr('action'), $(this).serialize());
            });
        }

        function loadViewTable(id, status) {

            height = $('#' + id + ' .table-responsive').height();
            width = $('#' + id + ' .table-responsive').width();

            $('#' + id + ' #loading').height(height);
            $('#' + id + ' #loading').width(width);

            $('#' + id + ' .table-responsive').prop('hidden', status);
            $('#' + id + ' #loading').prop('hidden', !status);

        }

        function redirectToURL(id, url, data = null){
            if (data === null) {
                window.location.replace(url);
            } else {
                const separator = url.includes('?') ? '&' : '?';

                window.location.replace(url + separator + data);
            }
        }

        function loadTableSortingModule(id) {
            @if(isset($filters) && ($filters->isFilterApplied(config('filters.prefix') . '-order_desc') || $filters->isFilterApplied(config('filters.prefix') . '-order_asc')))

                const order_by_field = "{{ $filters->getAppliedFilterValue(config('filters.prefix') . '-order_desc') ? $filters->getAppliedFilterValue(config('filters.prefix') . '-order_desc') : $filters->getAppliedFilterValue(config('filters.prefix') . '-order_asc') }}";
                const order_by_direction = "{{ $filters->getAppliedFilterValue(config('filters.prefix') . '-order_desc') ? 'desc' : 'asc' }}";

                if (order_by_field !== '') {

                    $column_header = $('table#' + id + ' th[data-reference="' + order_by_field + '"]');

                    $column_header.removeClass('ascending');
                    $column_header.removeClass('descending');

                    if (order_by_direction === 'asc') {
                        $column_header.addClass('ascending');
                    } else if (order_by_direction === 'desc') {
                        $column_header.addClass('descending');
                    }
                }

            @endif

            $('table#' + id + ' th[data-sortable]').on('click', function () {
                loadViewTable(id, true);
                // We don't use the filter form action url because it doesn't have the GET parameters
                // let url = $('#' + id + ' form').attr('action');
                url = window.location.href;

                const orderBy = $(this).data('reference');

                if($(this).hasClass('ascending') || (!$(this).hasClass('ascending') && !$(this).hasClass('descending'))) {
                    param = 'order_desc';
                    url = updateURLParameter(url, 'DELETE', '{{config('filters.prefix')}}-order_asc', null);
                    url = updateURLParameter(url, 'DELETE', '{{config('filters.prefix')}}-order_desc', null);
                    url = updateURLParameter(url, 'UPDATE', '{{config('filters.prefix')}}-order_desc', orderBy);
                } else {
                    param = 'order_asc';
                    url = updateURLParameter(url, 'DELETE', '{{config('filters.prefix')}}-order_asc', null);
                    url = updateURLParameter(url, 'DELETE', '{{config('filters.prefix')}}-order_desc', null);
                    url = updateURLParameter(url, 'UPDATE', '{{config('filters.prefix')}}-order_asc', orderBy);
                }

                redirectToURL(id, url);

            });
        }

        function updateURLParameter(url, action, param, paramVal = null)
        {
            let TheAnchor = null;
            let newAdditionalURL = "";
            let tempArray = url.split("?");
            let baseURL = tempArray[0];
            let additionalURL = tempArray[1];
            let temp = "";

            if (additionalURL)
            {
                let tmpAnchor = additionalURL.split("#");
                let TheParams = tmpAnchor[0];
                TheAnchor = tmpAnchor[1];
                if(TheAnchor)
                    additionalURL = TheParams;

                tempArray = additionalURL.split("&");

                for (let i=0; i<tempArray.length; i++)
                {
                    if(tempArray[i].split('=')[0] !== param)
                    {
                        newAdditionalURL += temp + tempArray[i];
                        temp = "&";
                    }
                }
            }
            else
            {
                let tmpAnchor = baseURL.split("#");
                let TheParams = tmpAnchor[0];
                TheAnchor  = tmpAnchor[1];

                if(TheParams)
                    baseURL = TheParams;
            }

            if(TheAnchor)
                paramVal += "#" + TheAnchor;

            if (action !== 'DELETE') {
                let rows_txt = temp + "" + param + "=" + paramVal;
                return baseURL + "?" + newAdditionalURL + rows_txt;
            }

            return baseURL + "?" + newAdditionalURL;
        }

        function loadLaravelFilterTables()
        {
            $('table.laravel-filters').each(function() {
                if($(this).attr('id')) {
                    bindTableFilterEvent($(this).attr('id'));
                    loadTableSortingModule($(this).attr('id'));
                    tables.push($(this));
                } else {
                    console.log('There is a table without an id');
                }
            });
        }

        $(document).ready(function () {
            loadLaravelFilterTables();
        });

    })();

</script>