
require(['core/first'], function() {
    require(['theme_shiny/bootstrap', 'core/log'], function(bootstrap, log) {
        log.debug('Bootstrap initialised');
    });
});
