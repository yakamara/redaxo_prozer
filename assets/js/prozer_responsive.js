/* https://github.com/github/task_list */

(function($){

    $(document).on('click', '.section1 .design1col > header', function() {
        
        var $container = $(this).parent();

        if ($container.hasClass('is-visible')) {

            $container.removeClass('is-visible');

        } else {
            
            $container.addClass('is-visible');

        }

    });

}(jQuery));
