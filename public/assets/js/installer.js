// assets/js/installer.js
$(function () {
	const BASE_URL =
		window.BASE_URL ||
		window.location.origin + window.location.pathname.replace(/\/[^/]*$/, "");

	$("form").on("submit", function (e) {
		e.preventDefault();

		const form = $(this);
		const data = form.serialize();
		const action = BASE_URL + "/?ajax=check_db";

		$.post(
			action,
			data,
			function (res) {
				if (res.error) {
					alert("Erro na verificação do banco: " + res.error);
					return;
				}

				if (res.exists) {
					if (!confirm("O banco já existe. Deseja continuar mesmo assim?")) {
						return;
					}
				}

				// Define flag localStorage e envia o form normalmente
				localStorage.setItem("instalado", "ok");
				form.off("submit");
				form.submit();
			},
			"json"
		).fail(function () {
			alert("Falha ao verificar o banco de dados. Verifique sua conexão.");
		});
	});

	// Fluxo de continuidade pós-instalação
	if (
		localStorage.getItem("instalado") === "ok" ||
		window.location.hash === "#finalizado"
	) {
		$("#etapa-1, #etapa-2").hide();
		$("#etapa-3").show();
		$("#progress-bar").css("width", "100%").text("Etapa 3/3: Finalizado");
		localStorage.removeItem("instalado");
	}

	// Avançar para Etapa 2
	$("#btn-etapa-1").click(function () {
		$("#etapa-1").hide();
		$("#etapa-2").show();
		$("#progress-bar").css("width", "66%").text("Etapa 2/3");
	});

	// Preencher host automaticamente
	const userHost = location.hostname;
	if (userHost) $("#hostField").val(userHost);
});
