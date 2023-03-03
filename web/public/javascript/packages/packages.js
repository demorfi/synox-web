export let packages = {
    fetch({context, button, callback})
    {
        let url = $(button).data('url'),
            id = $(context).find('[data-element="id"]').val(),
            fields = [
                {
                    name : 'id',
                    value: id
                },
                {
                    name : 'url',
                    value: $(button).val()
                }
            ];

        $(context).addClass('table-active');
        $(button).button('loading');

        $.post(url, fields, response => {
            $(context).removeClass('table-active');
            $(button).button('reset');

            if (response && ('file' in response || 'data' in response)) {
                $(context).addClass('table-success');
                $(button).hide();
                callback.call(context, response);
            } else {
                $(context).addClass('table-danger');
                $(button).addClass('btn-danger');
            }
        });
    },
    search({context, form})
    {
        // Active indicate new search
        $(form).search('disabled');
        $(context).resetTable().form('load');

        // Send search query
        let fields = $(form).serializeArray(),
            url = $(form).attr('action');

        $.post(url, fields, response => {
            if (!response || !('hash' in response)) {
                $(form).search('enabled');
                $(context).form('reset');
                return false;
            }

            fields.push({name: 'hash', value: response.hash});
            let search = $.post(url, fields);
            $(form).data('url', $(form).data('urlResults')).data('fields', fields);

            // Callback recursion post
            $(form).data('done', response => {
                if (response) {
                    if ('isEnd' in response && response.isEnd) {
                        $(form).trigger('post-recursion-break');
                    }

                    if ('chunks' in response) {
                        for (let item of response.chunks) {
                            $(context).addItemToTable(item);
                        }
                    }
                }
            });

            // Callback break recursion post
            $(form).data('break', () => {
                search.abort();
                $(form).search('enabled');

                // Reset status table
                if (!$(context).find('tbody tr:not([hidden])').length) {
                    $(context).form('empty');
                } else {
                    $(context).form('reset');
                }
            }).postRecursion();
        });
    }
};