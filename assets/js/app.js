/* F1SITE.NL — site behaviour. No dependencies (Chart.js is loaded on chart pages only). */
(function () {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const store = {
    get(key, fallback) { try { const v = localStorage.getItem(key); return v === null ? fallback : JSON.parse(v); } catch (e) { return fallback; } },
    set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* storage unavailable */ } },
  };
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  /* ---------- Theme ---------- */
  function initTheme() {
    $$('[data-theme-toggle]').forEach((btn) => btn.addEventListener('click', () => {
      const next = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
      document.documentElement.dataset.theme = next;
      store.set('theme', next);
      document.dispatchEvent(new CustomEvent('themechange'));
    }));
  }

  /* ---------- Mobile drawer ---------- */
  function initDrawer() {
    const drawer = $('#drawer');
    if (!drawer) return;
    const open = (state) => {
      drawer.classList.toggle('is-open', state);
      drawer.setAttribute('aria-hidden', String(!state));
      document.body.style.overflow = state ? 'hidden' : '';
      $$('[data-drawer-open]').forEach((b) => b.setAttribute('aria-expanded', String(state)));
      if (state) { const f = $('input, a', drawer); f && f.focus(); }
    };
    $$('[data-drawer-open]').forEach((b) => b.addEventListener('click', () => open(true)));
    $$('[data-drawer-close]').forEach((b) => b.addEventListener('click', () => open(false)));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') open(false); });
  }

  /* ---------- Local times ---------- */
  function initLocalTimes() {
    const fmt = {
      datetime: { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' },
      date: { day: 'numeric', month: 'short', year: 'numeric' },
      time: { hour: '2-digit', minute: '2-digit' },
      'weekday-time': { weekday: 'short', hour: '2-digit', minute: '2-digit' },
    };
    $$('time[data-local]').forEach((el) => {
      const d = new Date(el.getAttribute('datetime'));
      if (isNaN(d)) return;
      try {
        el.textContent = new Intl.DateTimeFormat(undefined, fmt[el.dataset.local] || fmt.datetime).format(d);
        el.title = d.toLocaleString(undefined, { dateStyle: 'full', timeStyle: 'short' }) + ' (your time)';
      } catch (e) { /* keep server fallback */ }
    });
    const tz = $('[data-timezone-name]');
    if (tz) { try { tz.textContent = Intl.DateTimeFormat().resolvedOptions().timeZone.replace(/_/g, ' '); } catch (e) {} }
  }

  /* ---------- Countdown ---------- */
  function initCountdown() {
    const el = $('[data-countdown]');
    if (!el) return;
    const target = new Date(el.dataset.countdown).getTime();
    const parts = { d: $('[data-unit="d"]', el), h: $('[data-unit="h"]', el), m: $('[data-unit="m"]', el), s: $('[data-unit="s"]', el) };
    const pad = (n) => String(n).padStart(2, '0');
    const tick = () => {
      const diff = Math.max(0, target - Date.now());
      parts.d.textContent = Math.floor(diff / 864e5);
      parts.h.textContent = pad(Math.floor(diff % 864e5 / 36e5));
      parts.m.textContent = pad(Math.floor(diff % 36e5 / 6e4));
      parts.s.textContent = pad(Math.floor(diff % 6e4 / 1e3));
      if (diff === 0) { clearInterval(timer); el.classList.add('is-live'); }
    };
    const timer = setInterval(tick, 1000);
    tick();
  }

  /* ---------- Tabs ---------- */
  function initTabs() {
    $$('[role="tablist"]').forEach((list) => {
      const tabs = $$('[role="tab"]', list);
      const select = (tab, push) => {
        tabs.forEach((t) => {
          const on = t === tab;
          t.setAttribute('aria-selected', String(on));
          t.tabIndex = on ? 0 : -1;
          const panel = document.getElementById(t.getAttribute('aria-controls'));
          if (panel) panel.hidden = !on;
        });
        if (push && tab.dataset.hash) history.replaceState(null, '', '#' + tab.dataset.hash);
        document.dispatchEvent(new CustomEvent('tabchange'));
      };
      tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => select(tab, true));
        tab.addEventListener('keydown', (e) => {
          const dir = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
          if (!dir) return;
          const next = tabs[(i + dir + tabs.length) % tabs.length];
          next.focus(); select(next, true);
        });
      });
      const fromHash = tabs.find((t) => t.dataset.hash && '#' + t.dataset.hash === location.hash);
      if (fromHash) select(fromHash, false);
    });
  }

  /* ---------- Chip filters & text filters ---------- */
  function initFilters() {
    $$('[data-chip-filter]').forEach((group) => {
      const target = $(group.dataset.chipFilter);
      if (!target) return;
      group.addEventListener('click', (e) => {
        const chip = e.target.closest('[data-value]');
        if (!chip) return;
        $$('[data-value]', group).forEach((c) => c.classList.toggle('is-active', c === chip));
        const v = chip.dataset.value;
        $$('[data-group]', target).forEach((item) => { item.hidden = v !== 'all' && item.dataset.group !== v; });
      });
    });
    $$('[data-text-filter]').forEach((input) => {
      const target = $(input.dataset.textFilter);
      const empty = input.dataset.empty ? $(input.dataset.empty) : null;
      if (!target) return;
      input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        let shown = 0;
        $$('[data-search]', target).forEach((item) => {
          const hit = !q || item.dataset.search.includes(q);
          item.hidden = !hit;
          if (hit) shown++;
        });
        if (empty) empty.hidden = shown > 0;
      });
    });
    $$('select[data-autosubmit]').forEach((s) => s.addEventListener('change', () => s.form.submit()));
  }

  /* ---------- Favourites ---------- */
  const Favs = {
    key: { driver: 'f1_fav_drivers', team: 'f1_fav_teams' },
    list(type) { return store.get(this.key[type], []).map(String); },
    has(type, id) { return this.list(type).includes(String(id)); },
    toggle(type, id) {
      id = String(id);
      const list = this.list(type);
      const on = !list.includes(id);
      store.set(this.key[type], on ? [...list, id] : list.filter((x) => x !== id));
      return on;
    },
  };
  window.F1Favorites = Favs;

  function initFollowButtons() {
    $$('[data-follow]').forEach((btn) => {
      const [type, id] = btn.dataset.follow.split(':');
      const labelEl = $('[data-follow-label]', btn);
      const render = () => {
        const on = Favs.has(type, id);
        btn.classList.toggle('is-following', on);
        btn.setAttribute('aria-pressed', String(on));
        btn.title = on ? 'Unfollow' : 'Follow';
        if (labelEl) labelEl.textContent = on ? 'Following' : 'Follow';
      };
      render();
      btn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); Favs.toggle(type, id); render(); });
    });
  }

  async function initFavouriteStrip() {
    const section = $('[data-favourites]');
    if (!section) return;
    const drivers = Favs.list('driver');
    const teams = Favs.list('team');
    if (!drivers.length && !teams.length) return;
    try {
      const params = new URLSearchParams({ drivers: drivers.join(','), teams: teams.join(',') });
      const res = await fetch(section.dataset.favourites + '?' + params);
      const data = await res.json();
      const items = [...data.drivers, ...data.teams];
      if (!items.length) return;
      $('[data-favourites-list]', section).innerHTML = items.map((it) => `
        <a class="card card-link accent-top fav-item" style="--c:${esc(it.color)}" href="${esc(it.url)}">
          <div class="row">
            ${it.image ? `<img class="avatar" src="${esc(it.image)}" alt="" loading="lazy">` : ''}
            <div class="truncate"><div class="fw-600 truncate">${esc(it.name)}</div>
            <div class="text-xs muted">${it.position ? 'P' + esc(it.position) + ' · ' : ''}${esc(it.points ?? '–')} pts</div></div>
          </div></a>`).join('');
      section.hidden = false;
    } catch (e) { /* ignore */ }
  }

  /* ---------- Weather ---------- */
  async function initWeather() {
    const el = $('[data-weather]');
    if (!el) return;
    const icon = (c) => c === 0 ? '☀️' : c <= 3 ? '⛅' : c <= 48 ? '🌫️' : c <= 67 || (c >= 80 && c <= 82) ? '🌧️' : c <= 86 ? '🌨️' : '⛈️';
    try {
      const res = await fetch(el.dataset.weather);
      const d = await res.json();
      if (d.status !== 'success') return;
      el.innerHTML = `<span class="emoji">${icon(d.weather_code)}</span><span><strong>${d.temp_min}° – ${d.temp_max}°C</strong> · ${d.rain_chance}% chance of rain <span class="subtle">· race day forecast</span></span>`;
      el.hidden = false;
    } catch (e) { /* ignore */ }
  }

  /* ---------- Cookie consent & analytics ---------- */
  function getConsent() {
    const m = document.cookie.match(/(?:^|; )f1_consent=([^;]*)/);
    return m ? decodeURIComponent(m[1]) : null;
  }
  function loadAnalytics() {
    const id = document.body.dataset.ga;
    if (!id || window.gtag) return;
    const s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
  }
  function initConsent() {
    const banner = $('#cookie-banner');
    const consent = getConsent();
    if (consent === 'analytics' || consent === 'all') loadAnalytics();
    const set = (value) => {
      document.cookie = 'f1_consent=' + value + '; max-age=' + 60 * 60 * 24 * 365 + '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
      if (banner) banner.classList.remove('is-visible');
      if (value === 'analytics') loadAnalytics();
    };
    if (banner && !consent) banner.classList.add('is-visible');
    $$('[data-consent]').forEach((b) => b.addEventListener('click', () => set(b.dataset.consent)));
    $$('[data-consent-reset]').forEach((b) => b.addEventListener('click', () => {
      document.cookie = 'f1_consent=; max-age=0; path=/';
      if (banner) banner.classList.add('is-visible');
    }));
  }

  /* ---------- Charts ---------- */
  const charts = [];
  function chartColors() {
    const cs = getComputedStyle(document.documentElement);
    return { grid: cs.getPropertyValue('--chart-grid').trim(), text: cs.getPropertyValue('--chart-text').trim(), font: cs.getPropertyValue('--font-sans').trim() };
  }
  function buildChart(canvas) {
    const cfg = JSON.parse(document.getElementById(canvas.dataset.chart).textContent);
    const c = chartColors();
    Chart.defaults.font.family = c.font;
    Chart.defaults.color = c.text;
    const seenColors = {};
    let config;
    if (cfg.type === 'progression') {
      config = {
        type: 'line',
        data: {
          labels: cfg.labels,
          datasets: cfg.series.map((s) => {
            const dup = seenColors[s.color] = (seenColors[s.color] || 0) + 1;
            return { label: s.name, data: s.points, borderColor: s.color, backgroundColor: s.color, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: .25, borderDash: dup > 1 ? [6, 4] : [] };
          }),
        },
        options: {
          responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'line', boxWidth: 24, padding: 16 } },
            tooltip: { itemSort: (a, b) => b.raw - a.raw, callbacks: { title: (items) => (cfg.races && cfg.races[items[0].dataIndex]) || items[0].label } },
          },
          scales: {
            x: { grid: { color: c.grid }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
            y: { grid: { color: c.grid }, beginAtZero: true, title: { display: true, text: 'Points' } },
          },
        },
      };
    } else if (cfg.type === 'gains') {
      config = {
        type: 'bar',
        data: { labels: cfg.labels, datasets: [{ data: cfg.values, backgroundColor: cfg.values.map((v) => v > 0 ? '#22c55e' : v < 0 ? '#ef4444' : '#8a8f98'), borderRadius: 3, barThickness: 14 }] },
        options: {
          indexAxis: 'y', responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { callbacks: { label: (i) => (i.raw > 0 ? '+' : '') + i.raw + ' places' } } },
          scales: { x: { grid: { color: c.grid }, ticks: { precision: 0 } }, y: { grid: { display: false }, ticks: { autoSkip: false, font: { size: 11 } } } },
        },
      };
    } else if (cfg.type === 'finishes') {
      config = {
        type: 'line',
        data: { labels: cfg.labels, datasets: [
          { label: 'Finish', data: cfg.finish, borderColor: cfg.color, backgroundColor: cfg.color, borderWidth: 2.5, tension: .2, spanGaps: false, pointRadius: 3 },
          { label: 'Grid', data: cfg.grid, borderColor: c.text, backgroundColor: c.text, borderWidth: 1.5, borderDash: [4, 4], pointRadius: 2, tension: .2 },
        ] },
        options: {
          responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
          plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }, tooltip: { callbacks: { title: (items) => cfg.races[items[0].dataIndex], label: (i) => i.dataset.label + ': ' + (i.raw ? 'P' + i.raw : 'DNF') } } },
          scales: { y: { reverse: true, min: 1, suggestedMax: 20, grid: { color: c.grid }, ticks: { precision: 0, callback: (v) => 'P' + v } }, x: { grid: { color: c.grid }, ticks: { maxRotation: 0 } } },
        },
      };
    }
    if (config) charts.push(new Chart(canvas, config));
  }
  function initCharts() {
    const canvases = $$('canvas[data-chart]');
    if (!canvases.length || typeof Chart === 'undefined') return;
    canvases.forEach(buildChart);
    document.addEventListener('themechange', () => {
      const c = chartColors();
      charts.forEach((ch) => {
        Object.values(ch.options.scales || {}).forEach((s) => { if (s.grid && s.grid.color) s.grid.color = c.grid; if (s.ticks) s.ticks.color = c.text; });
        ch.options.plugins.legend.labels && (ch.options.plugins.legend.labels.color = c.text);
        ch.update('none');
      });
    });
    document.addEventListener('tabchange', () => charts.forEach((ch) => ch.resize()));
  }

  /* ---------- Quick search palette (⌘K / Ctrl+K, or "/") ---------- */
  function initPalette() {
    const pal = $('#palette');
    if (!pal) return;
    const input = $('input', pal);
    const list = $('.palette-list', pal);
    let index = null;
    let results = [];
    let active = 0;
    const norm = (s) => String(s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    const render = () => {
      const q = norm(input.value.trim());
      const pool = index || [];
      results = q
        ? pool.filter((it) => norm(it.t + ' ' + it.s).includes(q)).sort((a, b) => norm(a.t).indexOf(q) - norm(b.t).indexOf(q)).slice(0, 12)
        : pool.filter((it) => it.s === 'Page').slice(0, 11);
      active = 0;
      list.innerHTML = results.length
        ? results.map((it, i) => `<li><a href="${esc(it.u)}" class="${i === 0 ? 'is-active' : ''}" role="option">
            ${it.i ? `<img class="avatar" src="${esc(it.i)}" alt="">` : it.c ? `<span class="swatch" style="--c:${esc(it.c)};height:20px"></span>` : ''}
            <span class="fw-600">${esc(it.t)}</span><span class="sub">${esc(it.s)}</span></a></li>`).join('')
        : `<li class="empty" style="padding:24px">Press Enter to search the whole site for “${esc(input.value)}”</li>`;
    };
    const move = (d) => {
      const links = $$('a', list);
      if (!links.length) return;
      links[active].classList.remove('is-active');
      active = (active + d + links.length) % links.length;
      links[active].classList.add('is-active');
      links[active].scrollIntoView({ block: 'nearest' });
    };
    const open = async () => {
      pal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      input.value = '';
      setTimeout(() => input.focus(), 10);
      if (!index) {
        try { index = await (await fetch(pal.dataset.index)).json(); } catch (e) { index = []; }
      }
      render();
    };
    const close = () => { pal.classList.remove('is-open'); document.body.style.overflow = ''; };
    $$('[data-palette-open]').forEach((el) => {
      el.addEventListener(el.tagName === 'INPUT' ? 'focus' : 'click', (e) => { e.preventDefault(); if (el.blur) el.blur(); open(); });
    });
    const isMac = /Mac|iPhone|iPad/.test(navigator.platform);
    $$('[data-kbd]').forEach((k) => { k.textContent = isMac ? '⌘K' : 'Ctrl K'; });
    document.addEventListener('keydown', (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        pal.classList.contains('is-open') ? close() : open();
      } else if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) {
        e.preventDefault();
        open();
      }
    });
    input.addEventListener('input', render);
    input.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
      else if (e.key === 'Escape') close();
      else if (e.key === 'Enter') {
        e.preventDefault();
        const link = $$('a', list)[active];
        location.href = link ? link.href : pal.dataset.searchPage + '?q=' + encodeURIComponent(input.value);
      }
    });
    pal.addEventListener('click', (e) => { if (e.target === pal) close(); });
    if (location.hash === '#search') open();
  }

  /* ---------- Lap chart (loaded when its tab is opened) ---------- */
  function initLapChart() {
    const box = $('[data-laps]');
    if (!box) return;
    let loaded = false;
    const load = async () => {
      if (loaded || box.closest('[hidden]')) return;
      loaded = true;
      try {
        const data = await (await fetch(box.dataset.laps)).json();
        if (!data.laps.length || typeof Chart === 'undefined') {
          box.innerHTML = '<div class="empty">Lap-by-lap data is not available for this race.</div>';
          return;
        }
        box.innerHTML = '<canvas aria-label="Lap-by-lap positions"></canvas>';
        box.style.height = Math.max(440, data.drivers.length * 26) + 'px';
        const c = chartColors();
        const seen = {};
        const chart = new Chart($('canvas', box), {
          type: 'line',
          data: {
            labels: data.laps.map((l) => (l === 0 ? 'Grid' : l)),
            datasets: data.drivers.map((d) => {
              const dup = (seen[d.color] = (seen[d.color] || 0) + 1);
              return { label: d.code, fullName: d.name, data: d.positions, borderColor: d.color, backgroundColor: d.color, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, tension: 0, borderDash: dup > 1 ? [5, 3] : [] };
            }),
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'nearest', intersect: false },
            onHover: (e, els) => {
              const hi = els.length ? els[0].datasetIndex : -1;
              chart.data.datasets.forEach((ds, i) => {
                const base = data.drivers[i].color;
                ds.borderWidth = i === hi ? 4 : hi === -1 ? 2 : 1;
                ds.borderColor = hi === -1 || i === hi ? base : base + '40';
              });
              chart.update('none');
            },
            plugins: {
              legend: { position: window.innerWidth > 760 ? 'right' : 'bottom', labels: { usePointStyle: true, pointStyle: 'line', boxWidth: 18, font: { size: 11 } } },
              tooltip: { callbacks: { title: (i) => (i[0].label === 'Grid' ? 'Starting grid' : 'Lap ' + i[0].label), label: (i) => i.dataset.fullName + ': P' + i.raw } },
            },
            scales: {
              y: { reverse: true, min: 1, max: data.drivers.length, grid: { color: c.grid }, ticks: { stepSize: 1, callback: (v) => 'P' + v } },
              x: { grid: { color: c.grid }, ticks: { maxRotation: 0, autoSkipPadding: 10 }, title: { display: true, text: 'Lap' } },
            },
          },
        });
        charts.push(chart);
        if (data.complete === false) {
          box.insertAdjacentHTML('afterend', '<p class="text-xs subtle mt-2">Showing the first ' + (data.laps.length - 1) + ' laps — the rest is still loading from the data provider. Refresh in a minute for the full race.</p>');
        }
      } catch (e) {
        box.innerHTML = '<div class="empty">Could not load lap data. Please try again later.</div>';
      }
    };
    document.addEventListener('tabchange', load);
    load();
  }

  /* ---------- Daily quiz ---------- */
  function initQuiz() {
    const quiz = $('[data-quiz]');
    if (!quiz) return;
    const day = quiz.dataset.quiz;
    const key = 'f1_quiz_' + day;
    const saved = store.get(key, {});
    const qs = $$('.quiz-q', quiz);
    const finish = () => {
      if (Object.keys(saved).length < qs.length) return;
      const marks = qs.map((q) => saved[q.dataset.q] === q.dataset.answer);
      const score = marks.filter(Boolean).length;
      const stats = store.get('f1_quiz_stats', { played: 0, streak: 0, best: 0, last: null, total: 0 });
      if (stats.last !== day) {
        const yesterday = new Date(new Date(day).getTime() - 864e5).toISOString().slice(0, 10);
        stats.played++;
        stats.total += score;
        stats.streak = stats.last === yesterday ? stats.streak + 1 : 1;
        stats.best = Math.max(stats.best, stats.streak);
        stats.last = day;
        store.set('f1_quiz_stats', stats);
      }
      const grid = marks.map((m) => (m ? '🟩' : '🟥')).join('');
      const res = $('.quiz-result');
      $('.quiz-score', res).textContent = score + '/' + qs.length;
      $('.quiz-grid', res).textContent = grid;
      $('[data-quiz-stats]', res).textContent = `Played ${stats.played} · current streak ${stats.streak} day${stats.streak === 1 ? '' : 's'} · best ${stats.best}`;
      res.hidden = false;
      const share = $('[data-quiz-share]', res);
      share.onclick = async () => {
        const text = `F1SITE.NL daily quiz ${day}\n${grid} ${score}/${qs.length}\n${location.href.split('?')[0]}`;
        try {
          if (navigator.share) await navigator.share({ text });
          else { await navigator.clipboard.writeText(text); share.textContent = 'Copied to clipboard!'; }
        } catch (e) { /* cancelled */ }
      };
    };
    const answer = (q, btn, persist) => {
      $$('.quiz-option', q).forEach((b) => {
        b.disabled = true;
        if (b.dataset.value === q.dataset.answer) b.classList.add('is-correct');
        else if (b === btn) b.classList.add('is-wrong');
      });
      $('.quiz-reveal', q).hidden = false;
      if (persist) { saved[q.dataset.q] = btn.dataset.value; store.set(key, saved); }
      finish();
    };
    qs.forEach((q) => {
      const prev = saved[q.dataset.q];
      const prevBtn = $$('.quiz-option', q).find((b) => b.dataset.value === prev);
      if (prevBtn) answer(q, prevBtn, false);
      $$('.quiz-option', q).forEach((b) => b.addEventListener('click', () => answer(q, b, true)));
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initPalette();
    initLapChart();
    initQuiz();
    initTheme();
    initDrawer();
    initLocalTimes();
    initCountdown();
    initTabs();
    initFilters();
    initFollowButtons();
    initFavouriteStrip();
    initWeather();
    initConsent();
    initCharts();
  });
})();
