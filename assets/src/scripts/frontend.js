(function ($) {
	"use strict";

	var plugin_name = "fkwdwcrfc";

	$(document).on("change", "#" + plugin_name + "_round_up_fee_input", function () {
		$(document.body).trigger("update_checkout");
	});
})(jQuery);
