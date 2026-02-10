define(["jquery", "core_form/changechecker"], function ($, changeChecker) {
    return {
        init: function () {
            let $form = $("#adminsettings");
            let $select = $("#id_s_local_alternative_file_system_storage_destination");

            if (!$form.length || !$select.length) {
                return;
            }

            $select.on("change", function () {
                let formNode = $form.get(0);

                // Prevent the browser "Leave site? Changes may not be saved" warning.
                try {
                    changeChecker.markFormSubmitted(formNode);
                } catch (e) {
                    // Ignore if not available for any reason.
                }

                // Use native submit (not jQuery) to ensure proper submit flow.
                formNode.submit();
            });
        },
    };
});
