/**
 * Script Interaktivitas Dashboard Admin Kantin Kejujuran
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Drawer Menu Samping (Mobile)
    const drawerBackdrop = document.getElementById('drawerBackdrop');
    const btnToggleDrawer = document.getElementById('btnToggleDrawer');
    const btnCloseDrawer = document.getElementById('btnCloseDrawer');

    function openDrawer() {
        if (drawerBackdrop) drawerBackdrop.classList.add('show');
    }

    function closeDrawer() {
        if (drawerBackdrop) drawerBackdrop.classList.remove('show');
    }

    if (btnToggleDrawer) btnToggleDrawer.addEventListener('click', openDrawer);
    if (btnCloseDrawer) btnCloseDrawer.addEventListener('click', closeDrawer);
    if (drawerBackdrop) {
        drawerBackdrop.addEventListener('click', (e) => {
            if (e.target === drawerBackdrop) closeDrawer();
        });
    }

    // 2. Popover Notifikasi (Desktop)
    const btnTopNotif = document.getElementById('btnTopNotif');
    const notifPopover = document.getElementById('notifPopover');

    if (btnTopNotif && notifPopover) {
        btnTopNotif.addEventListener('click', (e) => {
            e.stopPropagation();
            notifPopover.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!notifPopover.contains(e.target) && !btnTopNotif.contains(e.target)) {
                notifPopover.classList.remove('show');
            }
        });
    }

    // 3. User Profile Dropdown (Desktop)
    const btnUserProfile = document.getElementById('btnUserProfile');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    if (btnUserProfile && userDropdownMenu) {
        btnUserProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!userDropdownMenu.contains(e.target) && !btnUserProfile.contains(e.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }

    // 4. Toggle Password Visibility
    document.querySelectorAll('.btn-toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                btn.innerHTML = isPassword 
                    ? `<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>`
                    : `<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>`;
            }
        });
    });

    // 5. Live Search & Filter Bar
    const searchInputs = document.querySelectorAll('[data-live-search]');
    searchInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-live-search');
        input.addEventListener('input', () => {
            const query = input.value.toLowerCase().trim();
            document.querySelectorAll(targetSelector).forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // 6. Chip Filter Bar
    const chipGroups = document.querySelectorAll('.chip-group');
    chipGroups.forEach(group => {
        const chips = group.querySelectorAll('.chip');
        chips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                // If it's a link with href, let it navigate, unless handled via JS filter
                if (chip.hasAttribute('data-filter-val')) {
                    e.preventDefault();
                    chips.forEach(c => c.classList.remove('active'));
                    chip.classList.add('active');
                    const val = chip.getAttribute('data-filter-val');
                    const targetSelector = group.getAttribute('data-filter-target');
                    if (targetSelector) {
                        document.querySelectorAll(targetSelector).forEach(item => {
                            if (val === 'semua' || val === '') {
                                item.style.display = '';
                            } else {
                                const itemCategory = item.getAttribute('data-category') || '';
                                item.style.display = (itemCategory.toLowerCase() === val.toLowerCase()) ? '' : 'none';
                            }
                        });
                    }
                }
            });
        });
    });

    // 7. Auto-dismiss Flash Toasts
    document.querySelectorAll('.toast').forEach(toast => {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    });
});

// Modal Dialog Handlers Global
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}
