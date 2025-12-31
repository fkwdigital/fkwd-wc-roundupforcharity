(function ($) {
	"use strict";

	let plugin_name = "fkwdwcrfc";

	$(document).on(
		"change",
		"#" + plugin_name + "_round_up_fee_input",
		function () {
			let isChecked = $(this).is(":checked") ? "1" : "0";
			localStorage.setItem(plugin_name + "_round_up_fee", isChecked);
			$(document.body).trigger("update_checkout");
		},
	);

	$(document).on("updated_checkout", function () {
		let storedVal = localStorage.getItem(plugin_name + "_round_up_fee");
		if (storedVal === "1") {
			$("#" + plugin_name + "_round_up_fee_input").prop("checked", true);
		}
	});
})(jQuery);
