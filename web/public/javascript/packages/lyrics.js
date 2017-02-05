$(function ()
{
    /**
     * Break search elements.
     */
    $(document).on('click', 'form[action="/lyrics/search"] [name="break"]', function ()
    {
        $(this).closest('form').trigger('post-recursion-break');
    });

    /**
     * Fetch lyric.
     */
    $(document).on('click', '[data-element="fetch"]', function ()
    {
        var $item  = $(this).closest('tr'),
            id     = $(this).closest('td').find('[data-element="id"]').val(),
            fields = [
                {
                    name : 'id',
                    value: id
                },
                {
                    name : 'url',
                    value: $(this).val()
                }
            ];

        $item.addClass('table-active');
        $(this).button('loading');

        $.post($(this).data('url'), fields, $.proxy(function ($item, response)
        {
            $item.removeClass('table-active');
            $(this).button('reset');

            // Show download button
            if (response && 'data' in response) {
                $item.addClass('table-success');
                $(this).hide();

                var $show = $(this).closest('td').find('[data-element="show"]');
                $show.data('content', response.data.content).addClass('btn-success').show();
            } else {
                $item.addClass('table-danger');
                $(this).addClass('btn-danger');
            }
        }, this, $item));

        return (false);
    });

    /**
     * Show lyric.
     */
    $(document).on('click', '[data-element="show"]', function ()
    {
        var $modal  = $('#pkg-lyric'),
            content = $(this).data('content') || '<span class="text-danger">Lyric not found!</span>';

        if ($modal.length) {
            var $parent = $(this).closest('tr'),
                title = $parent.find('[data-element="artist"]').text()
                        + ' &mdash; ' + $parent.find('[data-element="title"]').text();

            $modal.find('.modal-title').html(title);
            $modal.find('.modal-body').html(content);
            $modal.modal('show');
        }

        return (false);
    });

    /**
     * Search lyric elements.
     */
    $(document).on('submit', 'form[action="/lyrics/search"]', function ()
    {
        var $table = $('#lyric-items');
        if (!$table.length) {
            return (false);
        }

        // Active indicate new search
        $(this).search('disabled');
        $table.resetTable().form('load');

        // Send search query
        var fields = $(this).serializeArray(),
            url    = $(this).attr('action');
        $.post(url, fields, $.proxy(function ($table, url, fields, response)
        {
            if (response && 'hash' in response) {
                fields.push({name: 'hash', value: response.hash});
                var $search = $.post(url, fields);

                // Send recursion post
                $(this).data('url', $(this).data('urlResults')).data('fields', fields);

                // Callback recursion post
                $(this).data('done', $.proxy(function ($table, response)
                {
                    if (response) {
                        if ('isEnd' in response) {
                            $(this).trigger('post-recursion-break');
                        }

                        if ('chunks' in response) {
                            for (var item in response.chunks) {
                                if (response.chunks.hasOwnProperty(item)) {
                                    $table.addItemToTable(response.chunks[item]);
                                }
                            }
                        }
                    }
                }, this, $table));

                // Callback break recursion post
                $(this).data('break', $.proxy(function ($table, $search)
                {
                    $search.abort();
                    $(this).search('enabled');

                    // Reset status table
                    if (!$table.find('tbody tr:not([hidden])').length) {
                        $table.form('empty');
                    } else {
                        $table.form('reset');
                    }
                }, this, $table, $search)).postRecursion();
            }
        }, this, $table, url, fields));

        return (false);
    });
});