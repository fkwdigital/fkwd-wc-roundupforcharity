(function ($) {
	"use strict";

	let plugin_name = "fkwdwcrfc";
	let class_name = "." + plugin_name;

	const currencyFormatter = new Intl.NumberFormat("en-US", {
		style: "currency",
		currency: "USD",
	});

	/**
	 * Display a WordPress-style admin notice.
	 *
	 * @since 0.1.0
	 *
	 * @param {string} message - the notice message
	 * @param {string} type - notice type: 'info', 'success', or 'error'
	 */
	function showNotice(message, type) {
		type = type || "info";

		var noticeClass = "notice-info";

		if (type === "error") {
			noticeClass = "notice-error";
		} else if (type === "success") {
			noticeClass = "notice-success";
		}

		var $notice = $("<div>", {
			class: "notice " + noticeClass + " is-dismissible",
		}).append(
			$("<p>").text(message),
			$("<button>", {
				type: "button",
				class: "notice-dismiss",
			}).append(
				$("<span>", {
					class: "screen-reader-text",
					text: "Dismiss this notice.",
				})
			)
		);

		$notice.find(".notice-dismiss").on("click", function () {
			$notice.fadeOut(300, function () {
				$(this).remove();
			});
		});

		$(".wrap h1").first().after($notice);
	}

	$(document).on("click", "#" + plugin_name + "-generate-report", function (e) {
		e.preventDefault();

		var data = {
			action: plugin_name + "_roundup_report",
			nonce: fkwdwcrfc_data.nonce,
			month: $("#" + plugin_name + "-report-month-select-field").val(),
		};

		$(class_name + "-generate-report").attr("disabled", "disabled");
		$(class_name + "-generate-report .spinner").addClass("is-active");

		$.ajax({
			url: fkwdwcrfc_data.ajax_url,
			type: "POST",
			data: data,
			dataType: "json",
		})
			.done(function (response) {
                let resultData = response.data.data;

                console.log(resultData);

				if (response.success === true) {
					$(class_name + "-report-results-table").addClass("is-active");
					$(class_name + "-report-results-table .total-orders").html(
						resultData.total_orders
					);
					$(class_name + "-report-results-table .total-revenue").html(
						currencyFormatter.format(resultData.total_roundup)
					);
					showNotice("Report generated successfully.", "success");
				} else {
					showNotice(response.message || "Report generation failed.", "error");
				}
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				console.error("Report generation failure: " + textStatus + ": " + errorThrown);
				showNotice("Report generation failed. Please try again.", "error");
			})
			.always(function () {
				$(class_name + "-generate-report .spinner").removeClass("is-active");
				$(class_name + "-generate-report").prop("disabled", false);
			});
	});
})(jQuery);
