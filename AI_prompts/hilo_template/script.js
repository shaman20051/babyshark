// layout
		document.addEventListener('DOMContentLoaded', () => {
			const burgerButton = document.getElementById('burger-button'); // Keep ID for JS hook
			const mainMenu = document.getElementById('main-menu'); // Keep ID for JS hook
			const dropdownToggles = document.querySelectorAll('.dropdown__toggle');
			const langDropdown = document.getElementById('lang-dropdown'); // Keep ID for JS hook
			const langToggleText = langDropdown?.querySelector('.dropdown__text');
			const langOptions = langDropdown?.querySelectorAll('.dropdown__item--lang');
			const themeToggleButton = document.getElementById('theme-toggle'); // Keep ID for JS hook
			const themeLabel = themeToggleButton?.querySelector('.dropdown__theme-label');
			const body = document.getElementById('page-body'); // Use ID on body for easier selection
			const currentThemeDisplay = document.getElementById('current-theme-display');

			const THEME_KEY = 'user-theme-preference';
			const ACTIVE_MENU_MOD = 'menu--active';
			const ACTIVE_BURGER_MOD = 'burger--active';
			const ACTIVE_DROPDOWN_MOD = 'dropdown--active';
			const DARK_THEME_MOD = 'body--dark-theme';
			const SELECTED_ITEM_MOD = 'dropdown__item--selected';


			// Mobile Menu Toggle
			if (burgerButton && mainMenu) {
				burgerButton.addEventListener('click', () => {
					const isActive = mainMenu.classList.contains(ACTIVE_MENU_MOD);
					mainMenu.classList.toggle(ACTIVE_MENU_MOD);
					burgerButton.classList.toggle(ACTIVE_BURGER_MOD);
					burgerButton.setAttribute('aria-expanded', String(!isActive));
					body.style.overflow = mainMenu.classList.contains(ACTIVE_MENU_MOD) ? 'hidden' : '';
				});

				// Close menu if a link inside it is clicked
				mainMenu.querySelectorAll('.menu__link').forEach(link => {
					link.addEventListener('click', () => {
						if (mainMenu.classList.contains(ACTIVE_MENU_MOD)) {
							mainMenu.classList.remove(ACTIVE_MENU_MOD);
							burgerButton.classList.remove(ACTIVE_BURGER_MOD);
							burgerButton.setAttribute('aria-expanded', 'false');
							body.style.overflow = '';
						}
					});
				});
			}

			// Dropdown Toggle
			dropdownToggles.forEach(toggle => {
				toggle.addEventListener('click', (event) => {
					event.stopPropagation();
					const dropdown = toggle.closest('.dropdown');
					if (!dropdown) return;
					const currentlyActive = dropdown.classList.contains(ACTIVE_DROPDOWN_MOD);

					// If the click is on the theme switcher itself, don't close dropdowns
					if (event.target.closest('.dropdown__item--theme-switcher')) {
						// Theme switch logic handles its own state
					} else {
						closeAllDropdowns(); // Close others first
						if (!currentlyActive) {
							dropdown.classList.add(ACTIVE_DROPDOWN_MOD);
							toggle.setAttribute('aria-expanded', 'true');
						}
						// Clicking toggle again will close it via closeAllDropdowns or click outside
					}
				});
			});

			// Close dropdowns when clicking outside
			document.addEventListener('click', (event) => {
				if (!event.target.closest('.dropdown')) {
					closeAllDropdowns();
				}
			});

			function closeAllDropdowns(exceptDropdown = null) {
				document.querySelectorAll('.dropdown.' + ACTIVE_DROPDOWN_MOD).forEach(activeDropdown => {
					if (activeDropdown !== exceptDropdown) {
						activeDropdown.classList.remove(ACTIVE_DROPDOWN_MOD);
						const activeToggle = activeDropdown.querySelector('.dropdown__toggle');
						if (activeToggle) {
							activeToggle.setAttribute('aria-expanded', 'false');
						}
					}
				});
			}

			// Language selection logic
			if (langToggleText && langOptions) {
				langOptions.forEach(button => {
					button.addEventListener('click', (event) => {
						event.stopPropagation();
						const selectedLangCode = button.getAttribute('data-lang');
						const selectedLangText = button.textContent;

						if (langToggleText) {
							langToggleText.textContent = selectedLangCode.toUpperCase();
						}

						langOptions.forEach(btn => btn.classList.remove(SELECTED_ITEM_MOD));
						button.classList.add(SELECTED_ITEM_MOD);

						console.log(`Language changed to: ${selectedLangText} (${selectedLangCode})`);

						closeAllDropdowns();
					});
				});
			}

			// Theme Switching Logic
			const applyTheme = (theme) => {
				if (theme === 'dark') {
					body.classList.add(DARK_THEME_MOD);
					if (themeLabel) themeLabel.textContent = 'Темная тема';
					if (currentThemeDisplay) currentThemeDisplay.textContent = 'Dark';
				} else {
					body.classList.remove(DARK_THEME_MOD);
					if (themeLabel) themeLabel.textContent = 'Светлая тема';
					if (currentThemeDisplay) currentThemeDisplay.textContent = 'Light';
				}
				// Icon visibility is handled by CSS based on body class now
			};

			const toggleTheme = () => {
				const currentThemeIsDark = body.classList.contains(DARK_THEME_MOD);
				const newTheme = currentThemeIsDark ? 'light' : 'dark';
				applyTheme(newTheme);
				localStorage.setItem(THEME_KEY, newTheme);
			};

			if (themeToggleButton) {
				themeToggleButton.addEventListener('click', (event) => {
					event.stopPropagation();
					toggleTheme();
					// Decide if dropdown should remain open or close after theme switch
					// closeAllDropdowns(); // Uncomment to close dropdown after switch
				});
			}

			// Load saved theme on page load
			const savedTheme = localStorage.getItem(THEME_KEY);
			const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			const initialTheme = savedTheme || (prefersDark ? 'dark' : 'light');
			applyTheme(initialTheme);

			// Close dropdown when regular items (links) inside are clicked
			document.querySelectorAll('a.dropdown__item').forEach(item => {
				item.addEventListener('click', () => {
					closeAllDropdowns();
				});
			});

		});
	// END layout

	// Tournament Grid

document.addEventListener('DOMContentLoaded', function() {
    // Автоматическое применение фильтров на десктопе
    const filtersForm = document.querySelector('.tournaments-filters__form');
    const isMobile = window.matchMedia('(max-width: 800px)').matches;
    
    if (!isMobile) {
        const selects = filtersForm.querySelectorAll('.tournaments-filters__select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                filtersForm.submit();
                // Здесь должна быть логика фильтрации без перезагрузки страницы
                console.log('Фильтры применены:', {
                    status: filtersForm.elements.status.value,
                    sort: filtersForm.elements.sort.value
                });
            });
        });
        
        // Скрываем кнопку "Применить" на десктопе
        const applyButton = filtersForm.querySelector('.tournaments-filters__button');
        if (applyButton) {
            applyButton.style.display = 'none';
        }
    }
    
    // Добавляем data-label для мобильных устройств
    if (window.matchMedia('(max-width: 800px)').matches) {
        const cells = document.querySelectorAll('.tournaments-grid__cell');
        cells.forEach(cell => {
            const label = cell.className.match(/--([a-z-]+)/);
            if (label) {
                cell.setAttribute('data-label', label[1].replace('-', ' '));
            }
        });
    }
});
	// END Tournament Grid

	// Секция  ЛИДЕРБОРДА

document.addEventListener('DOMContentLoaded', function() {
    // Добавляем data-label для мобильных устройств
    if (window.matchMedia('(max-width: 800px)').matches) {
        const addLabels = (cells, prefix) => {
            cells.forEach(cell => {
                const label = cell.className.match(/--([a-z]+)/);
                if (label) {
                    cell.setAttribute('data-label', prefix + label[1]);
                }
            });
        };
        
        // Для списка лидербордов
        const leaderboardCells = document.querySelectorAll('.leaderboards-grid__cell');
        addLabels(leaderboardCells, 'leaderboard-');
        
        // Для таблицы лидерборда
        const leaderCells = document.querySelectorAll('.leaderboard-grid__cell');
        addLabels(leaderCells, 'leader-');
        
        // Для таблицы турниров игрока
        const tournamentCells = document.querySelectorAll('.player-tournaments__cell');
        addLabels(tournamentCells, 'tournament-');
    }
    
    // Обработка кнопок "Подробнее" в таблице лидерборда
    const detailButtons = document.querySelectorAll('.leaderboard-grid__details-btn');
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const playerId = this.closest('.leaderboard-grid__row--player').getAttribute('data-player-id');
            const tournamentsRow = document.getElementById(`player-${playerId}-tournaments`);
            
            this.setAttribute('aria-expanded', !isExpanded);
            tournamentsRow.hidden = isExpanded;
            
            // Анимация появления/скрытия
            if (!isExpanded) {
                tournamentsRow.style.display = 'contents';
                tournamentsRow.hidden = false;
            } else {
                setTimeout(() => {
                    tournamentsRow.style.display = 'none';
                }, 200);
            }
        });
    });
});
	// END Секция  ЛИДЕРБОРДА