import {packages} from "./packages.js";

$(function () {
    /**
     * Break search elements.
     */
    $(document).on('click', 'form[action="/lyrics/search"] [name="break"]', function () {
        $(this).closest('form').trigger('post-recursion-break');
    });

    /**
     * Fetch lyric.
     */
    $(document).on('click', '[data-element="fetchUrl"]', function () {
        let item = $(this).closest('tr');
        packages.fetch({
            context : item,
            button  : this,
            callback: function (response) {
                let show = $(this).find('[data-element="show"]');
                show.data('content', response.data.content).addClass('btn-success').show();
            }
        });
        return false;
    });

    /**
     * Show lyric.
     */
    $(document).on('click', '[data-element="show"]', function () {
        let modal = $('#pkg-lyrics');
        if (modal.length) {
            let content = $(this).data('content') || '<span class="text-danger">Lyric not found!</span>',
                item = $(this).closest('tr'),
                title = item.find('[data-element="artist"]').text()
                        + ' &mdash; ' + item.find('[data-element="title"]').text();

            modal.find('.modal-title').html(title);
            modal.find('.modal-body').html(content);
            modal.modal('show');
        }
        return false;
    });

    /**
     * Search lyric elements.
     */
    $(document).on('submit', 'form[action="/lyrics/search"]', function () {
        let table = $('#lyrics-items');
        if (!table.length) {
            return false;
        }
        packages.search({context: table, form: this});
        return false;
    });
});