async function loadSidebar() {
	try {
		const response = await fetch('dashboard.html');
		if (!response.ok) throw new Error('Network response was not ok');
		const html = await response.text();

		const sidebarContainer = document.getElementById('sidebar-container');
		sidebarContainer.innerHTML = html;

		// 1. Immediately apply the starting (collapsed) state class
		sidebarContainer.classList.add('sidebar-collapsed');

		// 2. Force the browser to render the current state (trigger reflow)
		void sidebarContainer.offsetWidth;

		// 3. Remove the collapsed class, triggering the CSS transition to the expanded width
		sidebarContainer.classList.remove('sidebar-collapsed');
		initSidebar();
		
		// Setup theme toggle after sidebar is loaded
		setTimeout(() => {
			if (typeof setupThemeToggle === 'function') {
				setupThemeToggle();
			} else {
				setTimeout(() => {
					if (typeof setupThemeToggle === 'function') {
						setupThemeToggle();
					}
				}, 200);
			}
		}, 50);

	} catch (error) {
		console.error('Error loading sidebar:', error);
	}
}
loadSidebar();

