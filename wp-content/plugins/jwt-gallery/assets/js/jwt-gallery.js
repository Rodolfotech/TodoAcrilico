(function () {
	'use strict';

	var STORAGE_KEY = 'jg_session';
	var REST_URL = window.JGGallery ? window.JGGallery.restUrl.replace(/\/$/, '') : '';
	var LOGIN_URL = window.JGGallery ? window.JGGallery.loginUrl : '';
	var PANEL_URL = window.JGGallery ? window.JGGallery.panelUrl : '';
	var PER_PAGE = 9;
	var MAX_IMAGES_PER_POST = 10;

	var EYE_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
	var EYE_OFF_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.6 20.6 0 0 1 5.06-6.06M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a20.6 20.6 0 0 1-4.06 5.06M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
	var PENCIL_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
	var TRASH_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';
	var CHEVRON_LEFT_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>';
	var CHEVRON_RIGHT_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>';

	// ---- session helpers ----------------------------------------------

	function getSession() {
		try {
			var raw = window.localStorage.getItem(STORAGE_KEY);
			if (!raw) return null;
			var session = JSON.parse(raw);
			if (!session || !session.token || !session.expiresAt) return null;
			if (Date.now() >= session.expiresAt) {
				window.localStorage.removeItem(STORAGE_KEY);
				return null;
			}
			return session;
		} catch (e) {
			return null;
		}
	}

	function setSession(session) {
		window.localStorage.setItem(STORAGE_KEY, JSON.stringify(session));
	}

	function clearSession() {
		window.localStorage.removeItem(STORAGE_KEY);
	}

	function authHeaders(session, extra) {
		var headers = { Authorization: 'Bearer ' + session.token };
		if (extra) {
			for (var key in extra) {
				if (Object.prototype.hasOwnProperty.call(extra, key)) headers[key] = extra[key];
			}
		}
		return headers;
	}

	function apiFetch(path, options) {
		return fetch(REST_URL + path, options).then(function (res) {
			return res.json().then(
				function (data) {
					return { ok: res.ok, data: data };
				},
				function () {
					return { ok: false, data: { message: 'Respuesta inválida del servidor.' } };
				}
			);
		});
	}

	// ---- small UI helpers ----------------------------------------------

	function showMessage(el, text) {
		if (!el) return;
		if (!text) {
			el.hidden = true;
			el.textContent = '';
			return;
		}
		el.textContent = text;
		el.hidden = false;
	}

	function formatDate(iso) {
		try {
			return new Date(iso).toLocaleDateString('es-CL', { year: 'numeric', month: 'short', day: 'numeric' });
		} catch (e) {
			return '';
		}
	}

	function capitalize(text) {
		return text ? text.charAt(0).toUpperCase() + text.slice(1) : text;
	}

	function handlePasswordToggles(root) {
		root.querySelectorAll('[data-jg-toggle-password]').forEach(function (btn) {
			var input = btn.previousElementSibling;
			if (!input) return;

			btn.addEventListener('click', function () {
				var shown = input.type === 'text';
				input.type = shown ? 'password' : 'text';
				btn.innerHTML = shown ? EYE_ICON : EYE_OFF_ICON;
				btn.setAttribute('aria-pressed', shown ? 'false' : 'true');
				btn.setAttribute('aria-label', shown ? 'Mostrar contraseña' : 'Ocultar contraseña');
			});
		});
	}

	// ---- reusable pagination component ---------------------------------

	/**
	 * Renders a "Mostrando X-Y de Z" line + Anterior/Siguiente/page-number controls
	 * into `container`. Purely presentational — knows nothing about images or
	 * categories, just page/perPage/total numbers and an onChange(page) callback.
	 */
	function renderPaginationControl(container, opts) {
		container.innerHTML = '';

		var totalPages = Math.max(1, Math.ceil(opts.total / opts.perPage));
		if (opts.total <= 0) return;

		var wrap = document.createElement('div');
		wrap.className = 'jg-pagination';

		var start = (opts.page - 1) * opts.perPage + 1;
		var end = Math.min(opts.page * opts.perPage, opts.total);

		var info = document.createElement('p');
		info.className = 'jg-pagination-info';
		info.textContent = 'Mostrando ' + start + '-' + end + ' de ' + opts.total;
		wrap.appendChild(info);

		if (totalPages > 1) {
			var nav = document.createElement('div');
			nav.className = 'jg-pagination-nav';

			var prevBtn = document.createElement('button');
			prevBtn.type = 'button';
			prevBtn.className = 'jg-pagination-btn';
			prevBtn.textContent = '‹ Anterior';
			prevBtn.disabled = opts.page <= 1;
			prevBtn.addEventListener('click', function () {
				opts.onChange(opts.page - 1);
			});
			nav.appendChild(prevBtn);

			paginationPageList(opts.page, totalPages).forEach(function (entry) {
				if (entry === '…') {
					var ellipsis = document.createElement('span');
					ellipsis.className = 'jg-pagination-ellipsis';
					ellipsis.textContent = '…';
					nav.appendChild(ellipsis);
					return;
				}

				var pageBtn = document.createElement('button');
				pageBtn.type = 'button';
				pageBtn.className = 'jg-pagination-page' + (entry === opts.page ? ' is-active' : '');
				pageBtn.textContent = String(entry);
				pageBtn.addEventListener('click', function () {
					opts.onChange(entry);
				});
				nav.appendChild(pageBtn);
			});

			var nextBtn = document.createElement('button');
			nextBtn.type = 'button';
			nextBtn.className = 'jg-pagination-btn';
			nextBtn.textContent = 'Siguiente ›';
			nextBtn.disabled = opts.page >= totalPages;
			nextBtn.addEventListener('click', function () {
				opts.onChange(opts.page + 1);
			});
			nav.appendChild(nextBtn);

			wrap.appendChild(nav);
		}

		container.appendChild(wrap);
	}

	function paginationPageList(current, total) {
		if (total <= 7) {
			var all = [];
			for (var p = 1; p <= total; p++) all.push(p);
			return all;
		}

		var pages = [1];
		var start = Math.max(2, current - 1);
		var end = Math.min(total - 1, current + 1);

		if (start > 2) pages.push('…');
		for (var i = start; i <= end; i++) pages.push(i);
		if (end < total - 1) pages.push('…');
		pages.push(total);

		return pages;
	}

	// ---- reusable confirm modal ------------------------------------------

	/**
	 * Custom confirmation dialog to replace window.confirm() (which shows an
	 * ugly, unstyled "localhost dice" browser prompt). Returns a Promise that
	 * resolves to true/false — doesn't know anything about images or
	 * categories, just a message and two buttons.
	 *
	 * @param {string} message
	 * @param {{title?: string, confirmLabel?: string, cancelLabel?: string, danger?: boolean}} [options]
	 * @returns {Promise<boolean>}
	 */
	function showConfirmModal(message, options) {
		options = options || {};

		return new Promise(function (resolve) {
			var overlay = document.createElement('div');
			overlay.className = 'jg-modal-overlay';

			var modal = document.createElement('div');
			modal.className = 'jg-modal';
			modal.setAttribute('role', 'alertdialog');
			modal.setAttribute('aria-modal', 'true');

			if (options.title) {
				var title = document.createElement('h3');
				title.className = 'jg-modal-title';
				title.textContent = options.title;
				modal.appendChild(title);
			}

			var text = document.createElement('p');
			text.className = 'jg-modal-message';
			text.textContent = message;
			modal.appendChild(text);

			var actions = document.createElement('div');
			actions.className = 'jg-modal-actions';

			var cancelBtn = document.createElement('button');
			cancelBtn.type = 'button';
			cancelBtn.className = 'jg-button-secondary';
			cancelBtn.textContent = options.cancelLabel || 'Cancelar';

			var confirmBtn = document.createElement('button');
			confirmBtn.type = 'button';
			confirmBtn.className = options.danger ? 'jg-modal-confirm-danger' : '';
			confirmBtn.textContent = options.confirmLabel || 'Aceptar';

			function close(result) {
				document.removeEventListener('keydown', onKeydown);
				overlay.remove();
				resolve(result);
			}

			function onKeydown(e) {
				if (e.key === 'Escape') close(false);
			}

			cancelBtn.addEventListener('click', function () {
				close(false);
			});
			confirmBtn.addEventListener('click', function () {
				close(true);
			});
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) close(false);
			});
			document.addEventListener('keydown', onKeydown);

			actions.appendChild(cancelBtn);
			actions.appendChild(confirmBtn);
			modal.appendChild(actions);
			overlay.appendChild(modal);
			document.body.appendChild(overlay);

			confirmBtn.focus();
		});
	}

	// ---- reusable image-URL repeater --------------------------------------

	/**
	 * A "add up to N image links" form component — used both when uploading a
	 * new publication and when editing one, since both need the same
	 * "one or more photos of the same thing" input. Doesn't know about title,
	 * description, category, or any REST endpoint — just manages a list of
	 * URL inputs and exposes their current values via .getValues().
	 *
	 * @param {string[]} [initialUrls] Pre-fill with these URLs (edit mode).
	 * @returns {HTMLElement} container with a .getValues(): string[] method.
	 */
	function buildImageUrlRepeater(initialUrls) {
		var container = document.createElement('div');
		container.className = 'jg-url-repeater';

		var rowsWrap = document.createElement('div');
		rowsWrap.className = 'jg-url-repeater-rows';
		container.appendChild(rowsWrap);

		var addBtn = document.createElement('button');
		addBtn.type = 'button';
		addBtn.className = 'jg-url-repeater-add';
		addBtn.textContent = '+ Agregar otra imagen';
		container.appendChild(addBtn);

		var countLabel = document.createElement('p');
		countLabel.className = 'jg-url-repeater-count';
		container.appendChild(countLabel);

		function updateState() {
			var rows = rowsWrap.querySelectorAll('.jg-url-repeater-row');
			countLabel.textContent = rows.length + '/' + MAX_IMAGES_PER_POST + ' imágenes';
			addBtn.hidden = rows.length >= MAX_IMAGES_PER_POST;
			rowsWrap.querySelectorAll('.jg-url-repeater-remove').forEach(function (btn) {
				btn.hidden = rows.length <= 1;
			});
		}

		function addRow(value) {
			if (rowsWrap.querySelectorAll('.jg-url-repeater-row').length >= MAX_IMAGES_PER_POST) return;

			var row = document.createElement('div');
			row.className = 'jg-url-repeater-row';

			var input = document.createElement('input');
			input.type = 'url';
			input.placeholder = 'https://ejemplo.com/imagen.jpg';
			input.value = value || '';

			var removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'jg-url-repeater-remove';
			removeBtn.setAttribute('aria-label', 'Quitar esta imagen');
			removeBtn.textContent = '×';
			removeBtn.addEventListener('click', function () {
				row.remove();
				updateState();
			});

			row.appendChild(input);
			row.appendChild(removeBtn);
			rowsWrap.appendChild(row);
			updateState();
		}

		addBtn.addEventListener('click', function () {
			addRow('');
		});

		(initialUrls && initialUrls.length ? initialUrls : ['']).forEach(addRow);

		container.getValues = function () {
			return Array.prototype.slice
				.call(rowsWrap.querySelectorAll('input'))
				.map(function (input) {
					return input.value.trim();
				})
				.filter(function (value) {
					return value;
				});
		};

		return container;
	}

	// ---- LOGIN view ------------------------------------------------------

	function initLoginView(root) {
		if (getSession()) {
			window.location.href = PANEL_URL;
			return;
		}

		var form = root.querySelector('[data-jg-login]');
		handlePasswordToggles(root);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var errorEl = form.querySelector('[data-jg-login-error]');
			showMessage(errorEl, '');

			var email = form.email.value.trim();
			var password = form.password.value;
			var submitBtn = form.querySelector('button[type="submit"]');
			submitBtn.disabled = true;

			apiFetch('/login', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ email: email, password: password }),
			})
				.then(function (result) {
					submitBtn.disabled = false;
					if (!result.ok) {
						showMessage(errorEl, result.data.message || 'No se pudo iniciar sesión.');
						return;
					}
					setSession({
						token: result.data.token,
						expiresAt: Date.now() + result.data.expires_in * 1000,
						user: result.data.user,
					});
					window.location.href = PANEL_URL;
				})
				.catch(function () {
					submitBtn.disabled = false;
					showMessage(errorEl, 'Error de red. Intenta nuevamente.');
				});
		});
	}

	// ---- PANEL view --------------------------------------------------------

	function initPanelView(root) {
		var session = getSession();
		if (!session) {
			window.location.href = LOGIN_URL;
			return;
		}

		var state = {
			categories: [],
			allTotal: 0,
			selectedCategory: 'all',
			page: 1,
			perPage: PER_PAGE,
			images: [],
			total: 0,
		};

		root.querySelector('[data-jg-user]').textContent = session.user.email;

		root.querySelector('[data-jg-logout]').addEventListener('click', function () {
			clearSession();
			window.location.href = LOGIN_URL;
		});

		initCategoryManager(root, state);
		initUploadForm(root, state);

		loadCategories(root, state).then(function () {
			loadImages(root, state);
		});
	}

	// ---- categorías: select, gestor lateral, tabs con contador ---------------

	function loadCategories(root, state) {
		return apiFetch('/categories')
			.then(function (result) {
				if (result.ok) {
					state.categories = result.data;
				}
				renderCategorySelect(root, state.categories);
				renderCategoryManagerList(root, state);
				return apiFetch('/images?per_page=1&page=1');
			})
			.then(function (totalsResult) {
				if (totalsResult && totalsResult.ok) {
					state.allTotal = totalsResult.data.total;
				}
				renderCategoryTabs(root, state);
			});
	}

	function renderCategorySelect(root, categories) {
		root.querySelectorAll('[data-jg-category-select]').forEach(function (select) {
			var current = select.value;
			select.innerHTML = '';

			var noneOpt = document.createElement('option');
			noneOpt.value = '';
			noneOpt.textContent = 'Sin categoría';
			select.appendChild(noneOpt);

			categories.forEach(function (cat) {
				var opt = document.createElement('option');
				opt.value = String(cat.id);
				opt.textContent = capitalize(cat.name);
				select.appendChild(opt);
			});

			if (current) select.value = current;
		});
	}

	function initCategoryManager(root, state) {
		var addForm = root.querySelector('[data-jg-category-add]');
		var errorEl = root.querySelector('[data-jg-category-error]');

		addForm.addEventListener('submit', function (e) {
			e.preventDefault();
			showMessage(errorEl, '');

			var session = getSession();
			if (!session) {
				window.location.href = LOGIN_URL;
				return;
			}

			var name = addForm.name.value.trim();
			if (!name) return;

			apiFetch('/categories', {
				method: 'POST',
				headers: authHeaders(session, { 'Content-Type': 'application/json' }),
				body: JSON.stringify({ name: name }),
			}).then(function (result) {
				if (!result.ok) {
					showMessage(errorEl, result.data.message || 'No se pudo crear la categoría.');
					return;
				}
				addForm.reset();
				loadCategories(root, state);
			});
		});
	}

	function renderCategoryManagerList(root, state) {
		var list = root.querySelector('[data-jg-category-list]');
		var countLabel = root.querySelector('[data-jg-category-count]');
		if (countLabel) {
			countLabel.textContent = state.categories.length + (state.categories.length === 1 ? ' activa' : ' activas');
		}

		list.innerHTML = '';

		state.categories.forEach(function (cat) {
			var li = document.createElement('li');
			li.className = 'jg-category-item';

			var nameSpan = document.createElement('span');
			nameSpan.className = 'jg-category-name';
			nameSpan.textContent = capitalize(cat.name);

			var countSpan = document.createElement('span');
			countSpan.className = 'jg-category-count';
			countSpan.textContent = String(cat.count);

			var renameInput = document.createElement('input');
			renameInput.type = 'text';
			renameInput.className = 'jg-category-rename-input';
			renameInput.maxLength = 60;
			renameInput.value = cat.name;
			renameInput.hidden = true;

			var editBtn = document.createElement('button');
			editBtn.type = 'button';
			editBtn.className = 'jg-category-edit-btn';
			editBtn.setAttribute('aria-label', 'Renombrar ' + cat.name);
			editBtn.innerHTML = PENCIL_ICON;

			var deleteBtn = document.createElement('button');
			deleteBtn.type = 'button';
			deleteBtn.className = 'jg-category-delete-btn';
			deleteBtn.setAttribute('aria-label', 'Eliminar ' + cat.name);
			deleteBtn.innerHTML = TRASH_ICON;

			var saveBtn = document.createElement('button');
			saveBtn.type = 'button';
			saveBtn.className = 'jg-button-small';
			saveBtn.textContent = 'Guardar';
			saveBtn.hidden = true;

			function toEditMode() {
				nameSpan.hidden = true;
				countSpan.hidden = true;
				renameInput.hidden = false;
				editBtn.hidden = true;
				deleteBtn.hidden = true;
				saveBtn.hidden = false;
				renameInput.focus();
			}

			function toViewMode() {
				nameSpan.hidden = false;
				countSpan.hidden = false;
				renameInput.hidden = true;
				editBtn.hidden = false;
				deleteBtn.hidden = false;
				saveBtn.hidden = true;
			}

			editBtn.addEventListener('click', toEditMode);

			deleteBtn.addEventListener('click', function () {
				var warning =
					cat.count > 0
						? 'Las ' + cat.count + ' imágenes que tiene quedarán sin categoría. Esta acción no se puede deshacer.'
						: 'Esta acción no se puede deshacer.';

				showConfirmModal(warning, {
					title: 'Eliminar categoría "' + capitalize(cat.name) + '"',
					confirmLabel: 'Eliminar',
					danger: true,
				}).then(function (confirmed) {
					if (!confirmed) return;

					var session = getSession();
					if (!session) {
						window.location.href = LOGIN_URL;
						return;
					}

					apiFetch('/categories/' + cat.id, {
						method: 'DELETE',
						headers: authHeaders(session),
					}).then(function (result) {
						if (!result.ok) {
							window.alert(result.data.message || 'No se pudo eliminar la categoría.');
							return;
						}
						loadCategories(root, state).then(function () {
							loadImages(root, state);
						});
					});
				});
			});

			saveBtn.addEventListener('click', function () {
				var newName = renameInput.value.trim();
				if (!newName || newName === cat.name) {
					toViewMode();
					return;
				}

				var session = getSession();
				if (!session) {
					window.location.href = LOGIN_URL;
					return;
				}

				apiFetch('/categories/' + cat.id, {
					method: 'PUT',
					headers: authHeaders(session, { 'Content-Type': 'application/json' }),
					body: JSON.stringify({ name: newName }),
				}).then(function (result) {
					if (!result.ok) {
						window.alert(result.data.message || 'No se pudo renombrar la categoría.');
						return;
					}
					loadCategories(root, state).then(function () {
						loadImages(root, state);
					});
				});
			});

			li.appendChild(nameSpan);
			li.appendChild(countSpan);
			li.appendChild(renameInput);
			li.appendChild(editBtn);
			li.appendChild(deleteBtn);
			li.appendChild(saveBtn);
			list.appendChild(li);
		});
	}

	function renderCategoryTabs(root, state) {
		var nav = root.querySelector('[data-jg-category-tabs]');
		if (!nav) return;

		var categorizedSum = state.categories.reduce(function (sum, cat) {
			return sum + cat.count;
		}, 0);
		var uncategorized = Math.max(0, state.allTotal - categorizedSum);

		var tabs = [{ key: 'all', label: 'Todas', count: state.allTotal }];
		state.categories.forEach(function (cat) {
			tabs.push({ key: String(cat.id), label: capitalize(cat.name), count: cat.count });
		});
		if (uncategorized > 0) {
			tabs.push({ key: 'none', label: 'Sin categoría', count: uncategorized });
		}

		nav.innerHTML = '';
		tabs.forEach(function (tab) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'jg-cat-tab' + (tab.key === state.selectedCategory ? ' is-active' : '');

			var label = document.createElement('span');
			label.textContent = tab.label;

			var count = document.createElement('span');
			count.className = 'jg-cat-tab-count';
			count.textContent = String(tab.count);

			btn.appendChild(label);
			btn.appendChild(count);

			btn.addEventListener('click', function () {
				state.selectedCategory = tab.key;
				state.page = 1;
				renderCategoryTabs(root, state);
				loadImages(root, state);
			});

			nav.appendChild(btn);
		});
	}

	function initUploadForm(root, state) {
		var form = root.querySelector('[data-jg-upload]');
		var mount = form.querySelector('[data-jg-upload-images-mount]');
		var repeater = buildImageUrlRepeater();
		mount.appendChild(repeater);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var errorEl = form.querySelector('[data-jg-upload-error]');
			var successEl = form.querySelector('[data-jg-upload-success]');
			showMessage(errorEl, '');
			showMessage(successEl, '');

			var session = getSession();
			if (!session) {
				window.location.href = LOGIN_URL;
				return;
			}

			var imageUrls = repeater.getValues();
			if (!imageUrls.length) {
				showMessage(errorEl, 'Debes indicar al menos un enlace de imagen.');
				return;
			}

			var submitBtn = form.querySelector('button[type="submit"]');
			submitBtn.disabled = true;

			var payload = {
				title: form.title.value,
				description: form.description.value,
				image_urls: imageUrls,
				category_id: form.category_id.value,
			};

			apiFetch('/images', {
				method: 'POST',
				headers: authHeaders(session, { 'Content-Type': 'application/json' }),
				body: JSON.stringify(payload),
			})
				.then(function (result) {
					submitBtn.disabled = false;
					if (!result.ok) {
						showMessage(errorEl, result.data.message || 'No se pudo subir la imagen.');
						return;
					}
					form.reset();
					mount.innerHTML = '';
					repeater = buildImageUrlRepeater();
					mount.appendChild(repeater);
					showMessage(successEl, 'Imagen publicada correctamente.');
					loadCategories(root, state).then(function () {
						loadImages(root, state);
					});
				})
				.catch(function () {
					submitBtn.disabled = false;
					showMessage(errorEl, 'Error de red. Intenta nuevamente.');
				});
		});
	}

	// ---- grilla de imágenes + paginación ------------------------------------

	function loadImages(root, state) {
		var container = root.querySelector('[data-jg-images-grid]');
		container.innerHTML = '<p class="jg-empty">Cargando imágenes…</p>';

		var params = '?per_page=' + state.perPage + '&page=' + state.page;
		if (state.selectedCategory !== 'all') {
			params += '&category_id=' + encodeURIComponent(state.selectedCategory);
		}

		apiFetch('/images' + params).then(function (result) {
			if (!result.ok) {
				container.innerHTML = '';
				var err = document.createElement('p');
				err.className = 'jg-empty';
				err.textContent = 'No se pudieron cargar las imágenes.';
				container.appendChild(err);
				return;
			}

			state.images = result.data.items || [];
			state.total = result.data.total || 0;

			renderImagesGrid(root, state);
			renderPaginationControl(root.querySelector('[data-jg-pagination]'), {
				page: state.page,
				perPage: state.perPage,
				total: state.total,
				onChange: function (page) {
					state.page = page;
					loadImages(root, state);
				},
			});
		});
	}

	function renderImagesGrid(root, state) {
		var container = root.querySelector('[data-jg-images-grid]');
		container.innerHTML = '';

		if (!state.images.length) {
			var empty = document.createElement('p');
			empty.className = 'jg-empty';
			empty.textContent =
				state.selectedCategory === 'all'
					? 'Aún no hay imágenes publicadas.'
					: 'Todavía no hay imágenes en esta categoría.';
			container.appendChild(empty);
			return;
		}

		var grid = document.createElement('div');
		grid.className = 'jg-grid';
		state.images.forEach(function (item) {
			grid.appendChild(buildImageCard(root, state, item));
		});
		container.appendChild(grid);
	}

	function refreshAfterMutation(root, state) {
		loadCategories(root, state).then(function () {
			loadImages(root, state);
		});
	}

	function buildImageCard(root, state, item) {
		var session = getSession();
		var isOwner = !!(session && session.user && Number(session.user.id) === Number(item.author_id));

		var card = document.createElement('article');
		card.className = 'jg-card';

		var view = document.createElement('div');
		view.className = 'jg-card-view';

		var images = item.images && item.images.length ? item.images : [''];
		var currentIndex = 0;

		var figure = document.createElement('div');
		figure.className = 'jg-card-figure';

		var img = document.createElement('img');
		img.loading = 'lazy';
		img.alt = item.title;
		img.src = images[0];
		figure.appendChild(img);

		if (item.category) {
			var badge = document.createElement('span');
			badge.className = 'jg-card-badge';
			badge.textContent = capitalize(item.category.name);
			figure.appendChild(badge);
		}

		if (images.length > 1) {
			var counter = document.createElement('span');
			counter.className = 'jg-card-counter';

			function updateCarousel() {
				img.src = images[currentIndex];
				counter.textContent = currentIndex + 1 + '/' + images.length;
			}

			var prevBtn = document.createElement('button');
			prevBtn.type = 'button';
			prevBtn.className = 'jg-card-nav jg-card-nav--prev';
			prevBtn.setAttribute('aria-label', 'Imagen anterior');
			prevBtn.innerHTML = CHEVRON_LEFT_ICON;
			prevBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				currentIndex = (currentIndex - 1 + images.length) % images.length;
				updateCarousel();
			});

			var nextBtn = document.createElement('button');
			nextBtn.type = 'button';
			nextBtn.className = 'jg-card-nav jg-card-nav--next';
			nextBtn.setAttribute('aria-label', 'Imagen siguiente');
			nextBtn.innerHTML = CHEVRON_RIGHT_ICON;
			nextBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				currentIndex = (currentIndex + 1) % images.length;
				updateCarousel();
			});

			updateCarousel();
			figure.appendChild(prevBtn);
			figure.appendChild(nextBtn);
			figure.appendChild(counter);
		}

		var editForm = null;

		if (isOwner) {
			var icons = document.createElement('div');
			icons.className = 'jg-card-icons';

			var editBtn = document.createElement('button');
			editBtn.type = 'button';
			editBtn.className = 'jg-card-icon-btn jg-card-icon-btn--edit';
			editBtn.setAttribute('aria-label', 'Editar imagen');
			editBtn.innerHTML = PENCIL_ICON;

			var delBtn = document.createElement('button');
			delBtn.type = 'button';
			delBtn.className = 'jg-card-icon-btn jg-card-icon-btn--delete';
			delBtn.setAttribute('aria-label', 'Eliminar imagen');
			delBtn.innerHTML = TRASH_ICON;

			editBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				view.hidden = true;
				editForm.hidden = false;
			});

			delBtn.addEventListener('click', function (e) {
				e.stopPropagation();

				showConfirmModal('Esta acción no se puede deshacer.', {
					title: 'Eliminar imagen "' + item.title + '"',
					confirmLabel: 'Eliminar',
					danger: true,
				}).then(function (confirmed) {
					if (!confirmed) return;

					var currentSession = getSession();
					if (!currentSession) {
						window.location.href = LOGIN_URL;
						return;
					}

					apiFetch('/images/' + item.id, {
						method: 'DELETE',
						headers: authHeaders(currentSession),
					}).then(function (result) {
						if (!result.ok) {
							window.alert(result.data.message || 'No se pudo eliminar la imagen.');
							return;
						}
						refreshAfterMutation(root, state);
					});
				});
			});

			icons.appendChild(editBtn);
			icons.appendChild(delBtn);
			figure.appendChild(icons);
		}

		var body = document.createElement('div');
		body.className = 'jg-card-body';

		var title = document.createElement('h4');
		title.textContent = item.title;

		var desc = document.createElement('p');
		desc.textContent = item.description || '';

		var meta = document.createElement('p');
		meta.className = 'jg-card-meta';
		meta.textContent = (item.author || '') + ' · ' + formatDate(item.date);

		body.appendChild(title);
		if (item.description) body.appendChild(desc);
		body.appendChild(meta);

		view.appendChild(figure);
		view.appendChild(body);
		card.appendChild(view);

		if (isOwner) {
			editForm = buildEditForm(root, state, item, view);
			card.appendChild(editForm);
		}

		return card;
	}

	function buildEditForm(root, state, item, view) {
		var form = document.createElement('form');
		form.className = 'jg-form jg-edit-form';
		form.hidden = true;

		var titleLabel = document.createElement('label');
		titleLabel.textContent = 'Título';
		var titleInput = document.createElement('input');
		titleInput.type = 'text';
		titleInput.required = true;
		titleInput.maxLength = 120;
		titleInput.value = item.title;
		titleLabel.appendChild(titleInput);

		var descLabel = document.createElement('label');
		descLabel.textContent = 'Descripción';
		var descInput = document.createElement('textarea');
		descInput.maxLength = 600;
		descInput.rows = 3;
		descInput.value = item.description || '';
		descLabel.appendChild(descInput);

		var imagesLabel = document.createElement('label');
		imagesLabel.textContent = 'Imágenes (hasta 10)';
		var imagesRepeater = buildImageUrlRepeater(item.images);

		var catLabel = document.createElement('label');
		catLabel.textContent = 'Categoría';
		var catSelect = document.createElement('select');
		var noneOpt = document.createElement('option');
		noneOpt.value = '';
		noneOpt.textContent = 'Sin categoría';
		catSelect.appendChild(noneOpt);
		state.categories.forEach(function (c) {
			var opt = document.createElement('option');
			opt.value = String(c.id);
			opt.textContent = capitalize(c.name);
			if (item.category && Number(item.category.id) === Number(c.id)) opt.selected = true;
			catSelect.appendChild(opt);
		});
		catLabel.appendChild(catSelect);

		var actions = document.createElement('div');
		actions.className = 'jg-edit-actions';

		var saveBtn = document.createElement('button');
		saveBtn.type = 'submit';
		saveBtn.textContent = 'Guardar';

		var cancelBtn = document.createElement('button');
		cancelBtn.type = 'button';
		cancelBtn.className = 'jg-button-secondary';
		cancelBtn.textContent = 'Cancelar';

		actions.appendChild(saveBtn);
		actions.appendChild(cancelBtn);

		var errorEl = document.createElement('p');
		errorEl.className = 'jg-message jg-message--error';
		errorEl.hidden = true;

		form.appendChild(titleLabel);
		form.appendChild(descLabel);
		form.appendChild(imagesLabel);
		form.appendChild(imagesRepeater);
		form.appendChild(catLabel);
		form.appendChild(actions);
		form.appendChild(errorEl);

		cancelBtn.addEventListener('click', function () {
			form.hidden = true;
			view.hidden = false;
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			showMessage(errorEl, '');

			var session = getSession();
			if (!session) {
				window.location.href = LOGIN_URL;
				return;
			}

			var imageUrls = imagesRepeater.getValues();
			if (!imageUrls.length) {
				showMessage(errorEl, 'Debes indicar al menos un enlace de imagen.');
				return;
			}

			var payload = {
				title: titleInput.value,
				description: descInput.value,
				image_urls: imageUrls,
				category_id: catSelect.value,
			};

			apiFetch('/images/' + item.id, {
				method: 'PUT',
				headers: authHeaders(session, { 'Content-Type': 'application/json' }),
				body: JSON.stringify(payload),
			}).then(function (result) {
				if (!result.ok) {
					showMessage(errorEl, result.data.message || 'No se pudo guardar los cambios.');
					return;
				}
				refreshAfterMutation(root, state);
			});
		});

		return form;
	}

	// ---- bootstrap ----------------------------------------------------------

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-jg-root]').forEach(function (root) {
			var view = root.getAttribute('data-jg-view');
			if (view === 'panel') {
				initPanelView(root);
			} else {
				initLoginView(root);
			}
		});
	});
})();
