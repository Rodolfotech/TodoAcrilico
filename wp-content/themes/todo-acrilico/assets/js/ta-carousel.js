(function () {
	'use strict';

	function initCarousel(root) {
		var slides = root.querySelectorAll('.ta-carousel-slide');
		var counter = root.querySelector('.ta-carousel-counter');
		var prevBtn = root.querySelector('.ta-carousel-nav--prev');
		var nextBtn = root.querySelector('.ta-carousel-nav--next');
		var current = 0;

		function show(index) {
			slides[current].hidden = true;
			current = (index + slides.length) % slides.length;
			slides[current].hidden = false;
			if (counter) {
				counter.textContent = (current + 1) + '/' + slides.length;
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				show(current - 1);
			});
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				show(current + 1);
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-ta-carousel]').forEach(initCarousel);
	});
})();
