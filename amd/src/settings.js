define(["jquery"], function($) {
    return {
        init : function() {
            jQuery('#id_s_local_alternative_file_system_settings_destino').change(function() {
                $("#adminsettings").submit();
            });
        },
    };
});
