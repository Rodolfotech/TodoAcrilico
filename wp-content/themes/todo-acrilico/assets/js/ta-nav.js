(function () {
	// El botón ".ta-nav-caret-toggle" (aparte del link "Catálogo") abre/cierra
	// el desplegable. Se activa siempre, en cualquier pantalla — nada de
	// detección por matchMedia(hover), que no es confiable en DevTools ni en
	// dispositivos híbridos táctil+mouse. En mouse esto simplemente se suma
	// al hover (que ya revela el menú); el link de texto siempre navega con
	// un solo click/tap, en cualquier dispositivo.
	document.addEventListener('DOMContentLoaded', function () {
		var items = document.querySelectorAll('.ta-nav-has-caret');

		function close(li) {
			li.classList.remove('is-open');
			var toggle = li.querySelector(':scope > .ta-nav-caret-toggle');
			if (toggle) {
				toggle.setAttribute('aria-expanded', 'false');
			}
		}

		function closeAll(except) {
			items.forEach(function (li) {
				if (li !== except) {
					close(li);
				}
			});
		}

		items.forEach(function (li) {
			var toggle = li.querySelector(':scope > .ta-nav-caret-toggle');
			if (!toggle) {
				return;
			}

			toggle.addEventListener('click', function () {
				var isOpen = li.classList.contains('is-open');
				closeAll(li);
				if (isOpen) {
					close(li);
				} else {
					li.classList.add('is-open');
					toggle.setAttribute('aria-expanded', 'true');
				}
			});
		});
document.addEventListener('click', function (e) {
        if (!e.target.closest('.ta-nav')) {
            document.querySelectorAll('.ta-nav-list li.is-open').forEach(function (li) {
                li.classList.remove('is-open');
                const btn = li.querySelector('.ta-nav-caret-toggle');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        }
		});
	});
})();
