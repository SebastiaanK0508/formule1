(function () {
    const KEY_DRIVERS = 'f1_fav_drivers';
    const KEY_TEAMS = 'f1_fav_teams';

    function safeGet(key) {
        try {
            return JSON.parse(localStorage.getItem(key) || '[]');
        } catch (e) {
            return [];
        }
    }
    function safeSet(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) { /* storage unavailable, ignore */ }
    }

    window.F1Favorites = {
        isFavoriteDriver: (id) => safeGet(KEY_DRIVERS).includes(String(id)),
        isFavoriteTeam: (id) => safeGet(KEY_TEAMS).includes(String(id)),
        toggleDriver: function (id) {
            id = String(id);
            let list = safeGet(KEY_DRIVERS);
            const isFav = list.includes(id);
            list = isFav ? list.filter(x => x !== id) : [...list, id];
            safeSet(KEY_DRIVERS, list);
            return !isFav;
        },
        toggleTeam: function (id) {
            id = String(id);
            let list = safeGet(KEY_TEAMS);
            const isFav = list.includes(id);
            list = isFav ? list.filter(x => x !== id) : [...list, id];
            safeSet(KEY_TEAMS, list);
            return !isFav;
        },
        getDrivers: () => safeGet(KEY_DRIVERS),
        getTeams: () => safeGet(KEY_TEAMS),
    };

    function initFollowButtons() {
        document.querySelectorAll('[data-follow-driver]').forEach(btn => {
            const id = btn.getAttribute('data-follow-driver');
            const iconOnly = btn.hasAttribute('data-icon-only');
            const render = () => {
                const active = window.F1Favorites.isFavoriteDriver(id);
                btn.classList.toggle('is-following', active);
                btn.innerHTML = iconOnly ? (active ? '★' : '☆') : (active ? '★ Following' : '☆ Follow Driver');
            };
            render();
            btn.addEventListener('click', (e) => { e.preventDefault(); window.F1Favorites.toggleDriver(id); render(); });
        });
        document.querySelectorAll('[data-follow-team]').forEach(btn => {
            const id = btn.getAttribute('data-follow-team');
            const iconOnly = btn.hasAttribute('data-icon-only');
            const render = () => {
                const active = window.F1Favorites.isFavoriteTeam(id);
                btn.classList.toggle('is-following', active);
                btn.innerHTML = iconOnly ? (active ? '★' : '☆') : (active ? '★ Following' : '☆ Follow Team');
            };
            render();
            btn.addEventListener('click', (e) => { e.preventDefault(); window.F1Favorites.toggleTeam(id); render(); });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFollowButtons);
    } else {
        initFollowButtons();
    }
})();
