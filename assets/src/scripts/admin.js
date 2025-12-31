(function ($) {
	"use strict";

	let plugin_name = "fkwdwcrfc";
	let class_name = "." + plugin_name + "-roundup-report";

	const currencyFormatter = new Intl.NumberFormat("en-US", {
		style: "currency",
		currency: "USD",
	});

	$(document).on("click", class_name + "-button", function (e) {
		e.preventDefault();

		var data = {
			action: "roundup_report",
			nonce: fkwdwcrfc_data.nonce,
			month: $("#report_month_id").val(),
		};

		$(class_name + "-button").attr("disabled", "disabled");

		$(class_name + "-button .spinner").addClass("is-active");

		$.ajax({
			url: fkwdwcrfc_data.ajax_url,
			type: "POST",
			data: data,
			dataType: "json",
			success: function (response) {
				console.log(response.data);
				if (response.success == true) {
					$(class_name + "-table").addClass("is-active");

					$(class_name + "-table .total-orders").html(
						response.data.total_orders,
					);
					$(class_name + "-table .total-revenue").html(
						currencyFormatter.format(response.data.total_roundup),
					);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error(
					"Report generation failure: " + textStatus + ": " + errorThrown,
				);
			},
			complete: function () {
				$(class_name + "-button .spinner").removeClass("is-active");
				$(class_name + "-button").prop("disabled", false);
			},
		});
	});
})(jQuery);
