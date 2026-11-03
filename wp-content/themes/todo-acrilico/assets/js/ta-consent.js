/*
 * Consentimiento de cookies de analítica + carga diferida de Microsoft Clarity.
 * Reglas (SPEC.md, "Microsoft Clarity"):
 * - Clarity se inyecta SOLO después de que el visitante presiona "Aceptar".
 * - La decisión se guarda 12 meses en localStorage (ta_cookie_consent) y se puede
 *   cambiar desde "Preferencias de cookies" del footer.
 * - Todo va en try/catch: un fallo aquí (o de Clarity) no debe afectar al sitio
 *   ni al formulario de cotización.
 */
(function () {
	var config = window.taConsentConfig || {};
	var clarityId = config.clarityId;
	if (!clarityId) {
		return;
	}

	var KEY = 'ta_cookie_consent';
	var MAX_AGE_MS = 365 * 24 * 60 * 60 * 1000;
	var clarityLoaded = false;

	function readConsent() {
		try {
			var data = JSON.parse(localStorage.getItem(KEY) || 'null');
			if (data && typeof data.analytics === 'boolean' && Date.now() - data.date < MAX_AGE_MS) {
				return data;
			}
		} catch (err) {}
		return null;
	}

	function saveConsent(analytics) {
		try {
			localStorage.setItem(KEY, JSON.stringify({ analytics: analytics, date: Date.now(), v: 1 }));
		} catch (err) {}
	}

	function loadClarity() {
		if (clarityLoaded) {
			return;
		}
		clarityLoaded = true;
		try {
			(function (c, l, a, r, i, t, y) {
				c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
				t = l.createElement(r); t.async = 1; t.src = 'https://www.clarity.ms/tag/' + i;
				y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
			})(window, document, 'clarity', 'script', clarityId);
			window.clarity('consent');
		} catch (err) {}
	}

	function deleteClarityCookies() {
		var names = ['_clck', '_clsk', 'CLID', 'ANONCHK', 'MR', 'MUID', 'SM'];
		var host = location.hostname;
		var domains = ['', host, '.' + host, '.' + host.split('.').slice(-2).join('.')];
		names.forEach(function (name) {
			domains.forEach(function (domain) {
				document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + (domain ? '; domain=' + domain : '');
			});
		});
	}

	function init() {
		var banner = document.getElementById('ta-consent');

		// Reserva abajo el alto del banner para que nunca tape contenido: el
		// padding permite llegar al final de la página por encima del banner, y
		// scroll-padding hace que el foco y scrollIntoView (p. ej. el formulario
		// al saltar a un campo con error) dejen libre ese espacio.
		function reserveSpace(px) {
			document.body.style.paddingBottom = px;
			document.documentElement.style.scrollPaddingBottom = px;
		}

		function showBanner() {
			if (!banner) {
				return;
			}
			banner.hidden = false;
			document.body.classList.add('ta-has-consent');
			reserveSpace(banner.offsetHeight + 'px');
		}

		function hideBanner() {
			if (!banner) {
				return;
			}
			banner.hidden = true;
			document.body.classList.remove('ta-has-consent');
			reserveSpace('');
		}

		var consent = readConsent();
		if (consent && consent.analytics) {
			loadClarity();
		} else if (!consent) {
			showBanner();
		}

		if (banner) {
			banner.addEventListener('click', function (e) {
				var btn = e.target.closest('[data-ta-consent]');
				if (!btn) {
					return;
				}
				var accepted = btn.getAttribute('data-ta-consent') === 'accept';
				var previous = readConsent();
				saveConsent(accepted);
				hideBanner();
				if (accepted) {
					loadClarity();
				} else if (clarityLoaded || (previous && previous.analytics)) {
					// Retiro del consentimiento: borrar cookies y recargar para
					// que el script de Clarity ya cargado deje de ejecutarse.
					deleteClarityCookies();
					location.reload();
				}
			});

			// El alto cambia al cargar la tipografía o al girar el teléfono:
			// recalcular siempre el espacio reservado.
			var syncSpace = function () {
				if (!banner.hidden) {
					reserveSpace(banner.offsetHeight + 'px');
				}
			};
			if (window.ResizeObserver) {
				new ResizeObserver(syncSpace).observe(banner);
			}
			window.addEventListener('resize', syncSpace);
		}

		document.addEventListener('click', function (e) {
			if (e.target.closest('[data-ta-consent-open]')) {
				showBanner();
			}
		});
	}

	try {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function () {
				try { init(); } catch (err) {}
			});
		} else {
			init();
		}
	} catch (err) {}
})();
