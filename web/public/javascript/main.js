$(function ()
{
    $('[data-toggle="popover"]').popover();

    /**
     * Supported jQuery hidden attribute on show function.
     *
     * @returns {$}
     */
    $.fn.old_show = $.fn.show;
    $.fn.show = function ()
    {
        $(this).removeAttr('hidden').old_show();
        return (this);
    };

    /**
     * Supported jQuery hidden attribute on hide function.
     *
     * @returns {$}
     */
    $.fn.old_hide = $.fn.hide;
    $.fn.hide = function ()
    {
        $(this).attr('hidden', true).old_hide();
        return (this);
    };

    /**
     * Supported bootstrap button loading.
     *
     * @returns {$}
     */
    $.fn.old_button = $.fn.button;
    $.fn.button = function (method)
    {
        var type      = $(this).is('input') ? 'input' : 'button',
            text      = ($(this).data('loadingText') || $(this).data('loadingText') == false)
                ? $(this).data('loadingText')
                : 'loading...',
            toClass   = $(this).data('loadingClass') ? $(this).data('loadingClass') : '',
            saveClass = $(this).data('loadingClassSave') ? $(this).data('loadingClassSave') : '';

        switch (method) {
            case ('loading'):
                switch (type) {
                    case ('input'):
                        $(this).data('resetText', $(this).val()).val(text);
                        break;

                    case ('button'):
                        $(this).data('resetText', $(this).html()).html(text);
                        break;
                }

                $(this).removeClass(saveClass).addClass(toClass);
                break;

            case ('reset'):
                switch (type) {
                    case ('input'):
                        $(this).val($(this).data('resetText'));
                        break;

                    case ('button'):
                        $(this).html($(this).data('resetText'));
                        break;
                }

                $(this).removeClass(toClass).addClass(saveClass);
                break;

            default:
                $(this).old_button(method);
        }
        return (this);
    };

    /**
     * Ajax settings.
     */
    $.ajaxSetup({
        cache   : false,
        async   : true,
        dataType: 'json',
        complete: function (ajax)
        {
            ajax.then(function (data)
            {
                // Show messages
                var $messages = $('.alert-messages'),
                    $tpl      = $messages.filter('[hidden]:first');

                if (data && ($.isPlainObject(data) && ('error' in data))) {
                    $messages.filter(':not([hidden])').remove();

                    var $message = $tpl.clone().insertAfter($messages);
                    $message.show()
                        .find('.alert').addClass('alert-danger')
                        .find('.message').html(data.error);
                }
            });
        }
    });

    /**
     * Toggle status search field.
     *
     * @returns {$}
     */
    $.fn.search = function (status)
    {
        var $buttons = $(this).find('[name="search"], [name="break"]'),
            $field   = $(this).find('[name="name"]');

        switch (status) {
            case ('enabled'):
                $buttons.hide().filter('[name="search"]').show();
                $field.prop('readonly', false);
                break;

            case ('disabled'):
                $buttons.hide().filter('[name="break"]').show();
                $field.prop('readonly', true);
                break;
        }
        return (this);
    };

    /**
     * Recursion Ajax Post.
     *
     * @returns {$}
     */
    $.fn.postRecursion = function ()
    {
        if ($(this).data('post-recursion-break')) {
            $(this)
                .off('post-recursion-break')
                .removeData('post-recursion-break')
                .removeData('post-recursion');
            return (this);
        }

        if (!$(this).data('post-recursion')) {
            $(this).off('post-recursion-break').on('post-recursion-break', function ()
            {
                $(this).data('post-recursion-break', true);
                if ($.isFunction($(this).data('break'))) {
                    $(this).data('break').apply(this);
                }
                $(this).data('post-recursion').abort();
            });
        }

        $(this).data('post-recursion', $.post($(this).data('url'), $(this).data('fields'))
            .done($.proxy(function (response)
            {
                if ($.isFunction($(this).data('done'))) {
                    $(this).data('done').apply(this, [response]);
                }

                setTimeout($.proxy(function ()
                {
                    $(this).postRecursion();
                }, this), 600);
            }, this)));
        return (this);
    };

    /**
     * Toggle status form.
     *
     * @returns {$}
     */
    $.fn.form = function (method)
    {
        $(this).find('[data-toggle="loading"]').each(function ()
        {
            switch (method) {
                case ('load'):
                    $(this).html($(this).data('load'));
                    break;

                case ('reset'):
                    $(this).html($(this).data('reset'));
                    break;

                case ('empty'):
                    $(this).html($(this).data('empty'));
                    break;
            }
        });
        return (this);
    };

    /**
     * Add new item to table.
     *
     * @returns {$}
     */
    $.fn.addItemToTable = function (item)
    {
        var $tbody = $(this).find('tbody'),
            $tpl   = $tbody.find('tr[hidden]').clone().show(),
            $total = $(this).find('[data-element="total"]');

        $.map(item, function (value, key)
        {
            var $element = $tpl.find('[data-element="' + key + '"]');
            if ($element.length) {
                if ($element.is('a')) {
                    $element.attr('href', value);
                } else {
                    if ($element.is('button') || $element.is('input')) {
                        $element.val(value);
                    } else {
                        $element.html(value);
                    }
                }
            }
        });

        $tpl.appendTo($tbody);
        if ($total.length) {
            $total.text($tbody.find('tr:not([hidden])').length);
        }

        return (this);
    };

    /**
     * Add new item to table.
     *
     * @returns {$}
     */
    $.fn.resetTable = function ()
    {
        var $tbody = $(this).find('tbody'),
            $total = $(this).find('[data-element="total"]');

        $tbody.find('tr:not([hidden])').remove();
        if ($total.length) {
            $total.text(0);
        }

        return (this);
    };

    /**
     * Open auth config.
     */
    $(document).on('show.bs.modal', '#pkg-auth', function (event)
    {
        var $btn = $(event.relatedTarget);
        if (!$(this).hasClass('loaded')) {
            $btn.button('loading');

            // Get settings
            $.post($btn.data('href'), [{name: 'pkg', value: $btn.data('id')}], $.proxy(function ($btn, response)
            {
                $btn.button('reset');
                if (response && response.success) {
                    $(this).find('[name="pkg"]').val($btn.data('id'));
                    $(this).find('[set-value]').val('');

                    // Set settings values
                    if ('settings' in response) {
                        for (var key in response.settings) {
                            if (response.settings.hasOwnProperty(key)) {
                                $(this).find('[name="data[' + key + ']"]')
                                    .val(response.settings[key]).attr('set-value', true);
                            }
                        }
                    }

                    $(this).addClass('loaded');
                    $(this).find('form').off('submit').on('submit', $.proxy(function (event)
                    {
                        var $form = $(event.currentTarget),
                            $btn  = $(this).find('[type="submit"]').button('loading');

                        // Save settings form
                        $.post($form.attr('action'), $form.serializeArray(), $.proxy(function ($btn)
                        {
                            $btn.button('reset');
                            $(this).modal('hide');
                        }, this, $btn));
                        return (false);
                    }, this));
                }
            }, this, $btn));
        }
    }).on('hide.bs.modal', '#pkg-auth', function ()
    {
        $(this).removeClass('loaded');
    });

    /**
     * Toggle of status package.
     */
    $(document).on('change', '[data-toggle="pkg-select"]', function ()
    {
        $.post('/packages/status', [{name: $(this).attr('name'), value: $(this).is(':checked')}]);
        if ($(this).is(':checked')) {
            $(this).closest('.form-check').find('[data-target="#pkg-auth"]').trigger('click');
        }
        return (false);
    });
});