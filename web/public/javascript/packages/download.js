import {packages} from "./packages.js";

$(function () {
    /**
     * Break search elements.
     */
    $(document).on('click', 'form[action="/download/search"] [name="break"]', function () {
        $(this).closest('form').trigger('post-recursion-break');
    });

    /**
     * Fetch torrent.
     */
    $(document).on('click', '[data-element="fetchUrl"]', function () {
        let item = $(this).closest('tr');
        packages.fetch({
            context : item,
            button  : this,
            callback: function (response) {
                let download = $(this).find('[data-element="download"]');
                download.val(response.file.name).addClass('btn-success').show();
            }
        });
        return false;
    });

    /**
     * Download torrent file.
     */
    $(document).on('click', '[data-element="download"]', function () {
        let url = $(this).data('url');
        let form = $(`<form method="GET" action="${url}">`)
            .append($('<input type="hidden" name="name">').val($(this).val()));

        $(this).closest('td').find('.last-action')
            .empty()
            .append(form);

        form.submit();
        return false;
    });

    /**
     * Search download elements.
     */
    $(document).on('submit', 'form[action="/download/search"]', function () {
        let table = $('#download-items');
        if (!table.length) {
            return false;
        }
        packages.search({context: table, form: this});
        return false;
    });
});