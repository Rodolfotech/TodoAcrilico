(function () {
	// El desplegable de "Catálogo" se abre SOLO con click: sobre el link
	// "Catálogo" y también sobre el botón flecha (.ta-nav-caret-toggle). El
	// link "Catálogo" ya no navega en el primer click (se preventDefault): su
	// función es desplegar la lista; para llegar al catálogo completo está el
	// primer ítem del desplegable ("Todas las piezas"). El mouse NO abre el
	// panel (el CSS solo responde a .is-open, §1.1/§4.57).
	document.addEventListener('DOMContentLoaded', function () {
		var items = document.querySelectorAll('.ta-nav-has-caret');

		// Menú hamburguesa (solo visible <= 861px, ver style.css).
		var header = document.querySelector('.ta-header');
		var navToggle = document.querySelector('.ta-nav-toggle');

		function setNavOpen(open) {
			if (!header || !navToggle) {
				return;
			}
			header.classList.toggle('is-nav-open', open);
			navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			navToggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
		}

		if (navToggle) {
			navToggle.addEventListener('click', function () {
				setNavOpen(!header.classList.contains('is-nav-open'));
			});

			window.addEventListener('resize', function () {
				if (window.innerWidth > 861) {
					setNavOpen(false);
				}
			});
		}

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

		function toggle(li) {
			var isOpen = li.classList.contains('is-open');
			closeAll(li);
			if (isOpen) {
				close(li);
			} else {
				li.classList.add('is-open');
				var toggle = li.querySelector(':scope > .ta-nav-caret-toggle');
				if (toggle) {
					toggle.setAttribute('aria-expanded', 'true');
				}
			}
		}

		items.forEach(function (li) {
			var link = li.querySelector(':scope > a');
			if (link) {
				link.addEventListener('click', function (e) {
					e.preventDefault();
					toggle(li);
				});
			}

			var toggleBtn = li.querySelector(':scope > .ta-nav-caret-toggle');
			if (toggleBtn) {
				toggleBtn.addEventListener('click', function () {
					toggle(li);
				});
			}
		});

		document.addEventListener('click', function (e) {
			if (!e.target.closest('.ta-nav')) {
				document.querySelectorAll('.ta-nav-list li.is-open').forEach(function (li) {
					li.classList.remove('is-open');
					var btn = li.querySelector('.ta-nav-caret-toggle');
					if (btn) {
						btn.setAttribute('aria-expanded', 'false');
					}
				});
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				if (header && header.classList.contains('is-nav-open')) {
					setNavOpen(false);
					navToggle.focus();
				}
				document.querySelectorAll('.ta-nav-list li.is-open').forEach(function (li) {
					li.classList.remove('is-open');
					var btn = li.querySelector('.ta-nav-caret-toggle');
					if (btn) {
						btn.setAttribute('aria-expanded', 'false');
					}
				});
			}
		});
	});
})();