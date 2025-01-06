define(["jquery"], function($) {
    return {
        init: function() {
            $("#id_s_local_alternative_file_system_settings_destino").change(function() {
                $("#adminsettings").submit();
            });
        },
    };
});
