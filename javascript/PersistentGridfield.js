(function ($) {
    $.entwine('ss', function ($) {
        $('.ss-gridfield').entwine({
            onmatch: function () {
                //alert('Gridfield!!');
                var queryString = window.location.search;
                if (queryString) {
                    if (queryString.indexOf('&action_search') > 0) {
                        queryString = queryString.substring(0, queryString.indexOf('&action_search'));
                    }
                    $('#Form_ItemEditForm').each(function () {
                        var action = $(this).attr('action');
                        if (action) {
                            if (action.indexOf('?') < 0) {
                                $(this).attr('action', action + queryString);
                            }
                        }
                    });
                }
            }
        });
    });
})(jQuery);
