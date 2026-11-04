/*
 * Consentimiento de cookies de analítica + carga diferida de Microsoft Clarity,
 * Google Analytics 4 (gtag.js directo) y Google Tag Manager.
 * Reglas (SPEC.md, "Analítica: Microsoft Clarity y Google Tag Manager"):
 * - Clarity, GA4 y GTM se inyectan SOLO después de que el visitante presiona "Aceptar".
 * - La decisión se guarda 12 meses en localStorage (ta_cookie_consent) y se puede
 *   cambiar desde "Preferencias de cookies" del footer.
 * - Todo va en try/catch: un fallo aquí (o de Clarity) no debe afectar al sitio
 *   ni al formulario de cotización.
 */
(function () {
	var config = window.taConsentConfig || {};
	var clarityId = config.clarityId;
	var gtmId = config.gtmId;
	var ga4Id = config.ga4Id;
	if (!clarityId && !gtmId && !ga4Id) {
		return;
	}

	var KEY = 'ta_cookie_consent';
	var MAX_AGE_MS = 365 * 24 * 60 * 60 * 1000;
	var clarityLoaded = false;
	var gtmLoaded = false;
	var ga4Loaded = false;
	var consentModeSet = false;

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
		if (!clarityId || clarityLoaded) {
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

	// Modo de consentimiento de Google, una sola vez y ANTES de GA4 o GTM: el
	// visitante aceptó solo analítica, así que la publicidad queda denegada.
	function setGoogleConsentMode() {
		if (consentModeSet) {
			return;
		}
		consentModeSet = true;
		window.dataLayer = window.dataLayer || [];
		window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
		window.gtag('consent', 'default', {
			analytics_storage: 'granted',
			ad_storage: 'denied',
			ad_user_data: 'denied',
			ad_personalization: 'denied'
		});
	}

	function injectScript(src) {
		var t = document.createElement('script');
		t.async = true;
		t.src = src;
		var y = document.getElementsByTagName('script')[0];
		y.parentNode.insertBefore(t, y);
	}

	// Google Analytics 4 directo (snippet oficial de gtag.js).
	function loadGa4() {
		if (!ga4Id || ga4Loaded) {
			return;
		}
		ga4Loaded = true;
		try {
			setGoogleConsentMode();
			window.gtag('js', new Date());
			window.gtag('config', ga4Id);
			injectScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ga4Id));
		} catch (err) {}
	}

	// Snippet oficial de GTM (para etiquetas adicionales; GA4 NO se configura ahí).
	function loadGtm() {
		if (!gtmId || gtmLoaded) {
			return;
		}
		gtmLoaded = true;
		try {
			setGoogleConsentMode();
			window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
			injectScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(gtmId));
		} catch (err) {}
	}

	function loadAnalytics() {
		loadClarity();
		loadGa4();
		loadGtm();
	}

	function deleteAnalyticsCookies() {
		var names = ['_clck', '_clsk', 'CLID', 'ANONCHK', 'MR', 'MUID', 'SM', '_ga', '_gid', '_gat'];
		document.cookie.split(';').forEach(function (c) {
			var name = c.split('=')[0].trim();
			if (/^_ga_/.test(name) || /^_gat_/.test(name)) {
				names.push(name);
			}
		});
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
			loadAnalytics();
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
					loadAnalytics();
				} else if (clarityLoaded || gtmLoaded || ga4Loaded || (previous && previous.analytics)) {
					// Retiro del consentimiento: borrar cookies y recargar para
					// que los scripts ya cargados (Clarity, GTM) dejen de ejecutarse.
					deleteAnalyticsCookies();
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
