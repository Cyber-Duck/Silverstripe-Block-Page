(function($) {
    $.entwine('ss', function($) {
        $('input[name="BlockType"]').entwine({
            onmatch: function() {
            	$('input[name="BlockType"]:first').attr('checked',true);
            }
        });
    });
})(jQuery);