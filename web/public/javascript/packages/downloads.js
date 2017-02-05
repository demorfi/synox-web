$(function ()
{
    /**
     * Break search elements.
     */
    $(document).on('click', 'form[action="/downloads/search"] [name="break"]', function ()
    {
        $(this).closest('form').trigger('post-recursion-break');
    });

    /**
     * Fetch torrent.
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
            if (response && 'file' in response) {
                $item.addClass('table-success');
                $(this).hide();

                var $download = $(this).closest('td').find('[data-element="download"]');
                $download.val(response.file.name).addClass('btn-success').show();
            } else {
                $item.addClass('table-danger');
                $(this).addClass('btn-danger');
            }
        }, this, $item));

        return (false);
    });

    /**
     * Download torrent file.
     */
    $(document).on('click', '[data-element="download"]', function ()
    {
        var $form = $('<form method="GET" action="' + $(this).data('url') + '">')
            .append($('<input type="hidden" name="name">').val($(this).val()));

        $(this).closest('td').find('.last-action')
            .empty()
            .append($form);

        $form.submit();
        return (false);
    });

    /**
     * Search download elements.
     */
    $(document).on('submit', 'form[action="/downloads/search"]', function ()
    {
        var $table = $('#download-items');
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