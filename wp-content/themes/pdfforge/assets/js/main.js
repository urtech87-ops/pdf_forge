/* PDFForge — main.js */

document.addEventListener('DOMContentLoaded', function () {

	/* ------------------------------------------------------------------
	   Dark / light toggle
	   ------------------------------------------------------------------ */
	const themeToggle = document.querySelector('.theme-toggle');
	const iconMoon    = themeToggle && themeToggle.querySelector('.icon-moon');
	const iconSun     = themeToggle && themeToggle.querySelector('.icon-sun');

	function applyTheme(theme) {
		document.documentElement.classList.toggle('light', theme === 'light');
		if (iconMoon && iconSun) {
			iconMoon.style.display = theme === 'light' ? 'none'  : '';
			iconSun.style.display  = theme === 'light' ? ''      : 'none';
		}
	}

	const savedTheme = localStorage.getItem('pdfforge-theme') || 'dark';
	applyTheme(savedTheme);

	if (themeToggle) {
		themeToggle.addEventListener('click', function () {
			const next = document.documentElement.classList.contains('light') ? 'dark' : 'light';
			localStorage.setItem('pdfforge-theme', next);
			applyTheme(next);
		});
	}

	/* ------------------------------------------------------------------
	   Header: dropdown
	   ------------------------------------------------------------------ */
	document.querySelectorAll('.dropdown-toggle').forEach(function (btn) {
		const panel = btn.nextElementSibling;

		btn.addEventListener('click', function () {
			const open = btn.getAttribute('aria-expanded') === 'true';
			// Close all other dropdowns
			document.querySelectorAll('.dropdown-toggle[aria-expanded="true"]').forEach(function (b) {
				b.setAttribute('aria-expanded', 'false');
				b.nextElementSibling.classList.remove('open');
			});
			if (!open) {
				btn.setAttribute('aria-expanded', 'true');
				panel.classList.add('open');
			}
		});
	});

	// Close on outside click
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.has-dropdown')) {
			document.querySelectorAll('.dropdown-toggle').forEach(function (btn) {
				btn.setAttribute('aria-expanded', 'false');
				btn.nextElementSibling && btn.nextElementSibling.classList.remove('open');
			});
		}
	});

	/* ------------------------------------------------------------------
	   Header: mobile drawer
	   ------------------------------------------------------------------ */
	const hamburger    = document.querySelector('.hamburger');
	const mobileDrawer = document.querySelector('.mobile-drawer');

	if (hamburger && mobileDrawer) {
		hamburger.addEventListener('click', function () {
			const open = hamburger.getAttribute('aria-expanded') === 'true';
			hamburger.setAttribute('aria-expanded', String(!open));
			mobileDrawer.classList.toggle('open', !open);
			mobileDrawer.setAttribute('aria-hidden', String(open));
		});
	}

	/* ------------------------------------------------------------------
	   Popular Tools: category filter tabs
	   ------------------------------------------------------------------ */
	const tabs      = document.querySelectorAll('.filter-tab');
	const toolCards = document.querySelectorAll('.tool-card');

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			tabs.forEach(function (t) {
				t.classList.remove('active');
				t.setAttribute('aria-selected', 'false');
			});
			tab.classList.add('active');
			tab.setAttribute('aria-selected', 'true');

			const filter = tab.dataset.filter;
			toolCards.forEach(function (card) {
				if (filter === 'all' || card.dataset.category === filter) {
					card.removeAttribute('hidden');
				} else {
					card.setAttribute('hidden', '');
				}
			});
		});
	});

	/* ------------------------------------------------------------------
	   Hero search — filter tool cards in real time
	   ------------------------------------------------------------------ */
	const heroSearchInput   = document.getElementById('heroSearch');
	const headerSearchInput = document.querySelector('.header-search-input');

	function filterTools(query) {
		const q = query.trim().toLowerCase();
		toolCards.forEach(function (card) {
			const name = card.querySelector('.tool-name').textContent.toLowerCase();
			const desc = card.querySelector('.tool-desc').textContent.toLowerCase();
			card.toggleAttribute('hidden', q !== '' && !name.includes(q) && !desc.includes(q));
		});
	}

	if (heroSearchInput) {
		heroSearchInput.addEventListener('input', function () {
			filterTools(this.value);
		});
	}
	if (headerSearchInput) {
		headerSearchInput.addEventListener('input', function () {
			filterTools(this.value);
		});
	}

});
