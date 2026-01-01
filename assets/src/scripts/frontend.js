(function ($) {
	"use strict";

	var plugin_name = "fkwd-checkout-roundupforcharity";

	$(document).on("change", "#" + plugin_name + "_round_up_fee_input", function () {
		$(document.body).trigger("update_checkout");
	});
})(jQuery);
