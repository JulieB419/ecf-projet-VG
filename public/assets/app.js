// Fichier JS global de l'application.
// Chaque module s'active uniquement si les éléments de sa page existent.
(function () {
  'use strict';

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function money(value) {
    return (Math.round(Number(value || 0) * 100) / 100).toFixed(2).replace('.', ',') + ' €';
  }

  // Page publique : Nos menus
  function initMenuFilters() {
    const form = document.getElementById('menuFilters');
    const list = document.getElementById('menuList');
    const info = document.getElementById('menuInfo');
    if (!form || !list || !info) return;

    const apiUrl = form.dataset.apiUrl;
    const menusUrl = form.dataset.menusUrl;
    if (!apiUrl || !menusUrl) return;

    function renderItems(items) {
      list.innerHTML = '';

      if (!items || items.length === 0) {
        info.innerHTML = '<div class="alert alert-warning mb-0">Aucun menu ne correspond aux filtres.</div>';
        return;
      }

      info.innerHTML = '<div class="alert alert-light border mb-0">Menus trouvés : <strong>' + items.length + '</strong></div>';

      for (const menu of items) {
        const detailUrl = menusUrl.replace(/\/$/, '') + '/' + encodeURIComponent(menu.id);
        list.insertAdjacentHTML('beforeend', `
          <div class="col-md-4 mb-3">
            <div class="card h-100">
              <div class="card-body">
                <h2 class="h5 card-title mb-2">${escapeHtml(menu.title)}</h2>
                <p class="card-text text-muted mb-2">${escapeHtml(menu.short_description ?? '')}</p>
                <ul class="list-unstyled small mb-3">
                  <li><strong>Thème :</strong> ${escapeHtml(menu.theme ?? '')}</li>
                  <li><strong>Régime :</strong> ${escapeHtml(menu.diet ?? '')}</li>
                  <li><strong>Min. personnes :</strong> ${escapeHtml(menu.min_people ?? '')}</li>
                  <li><strong>À partir de :</strong> ${escapeHtml(menu.base_price ?? '')} €</li>
                </ul>
                <a class="btn btn-outline-primary" href="${detailUrl}">En savoir plus</a>
              </div>
            </div>
          </div>
        `);
      }
    }

    async function load(params) {
      const query = params.toString();
      const url = query ? (apiUrl + '?' + query) : apiUrl;

      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        renderItems(data.items || []);
      } catch (error) {
        console.error(error);
        info.innerHTML = '<div class="alert alert-danger mb-0">Erreur lors du chargement des menus.</div>';
        list.innerHTML = '';
      }
    }

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      const params = new URLSearchParams();
      for (const [key, value] of new FormData(form).entries()) {
        const cleanValue = String(value).trim();
        if (cleanValue !== '') params.set(key, cleanValue);
      }
      load(params);
    });

    load(new URLSearchParams());
  }

  // Prévisualisation des régimes/allergènes dans l'administration des plats
  function initDishPreviewBadges() {
    const regimes = document.getElementById('regimes');
    const allergens = document.getElementById('allergens');
    const regimesPreview = document.getElementById('regimesPreview');
    const allergensPreview = document.getElementById('allergensPreview');
    if (!regimes && !allergens) return;

    function asBadges(selectEl) {
      const selected = Array.from(selectEl.selectedOptions)
        .map(option => option.textContent.trim())
        .filter(Boolean);

      if (!selected.length) return '<span class="selection-preview-empty">(aucun)</span>';

      return selected
        .map(label => '<span class="selection-preview-badge">' + escapeHtml(label) + '</span>')
        .join('');
    }

    function render() {
      if (regimes && regimesPreview) regimesPreview.innerHTML = asBadges(regimes);
      if (allergens && allergensPreview) allergensPreview.innerHTML = asBadges(allergens);
    }

    if (regimes) regimes.addEventListener('change', render);
    if (allergens) allergens.addEventListener('change', render);
    render();
  }

  // Page administration : horaires d'ouverture
  function initOpeningHoursForm() {
    document.querySelectorAll('[data-opening-hours-row]').forEach(row => {
      const checkbox = row.querySelector('input[type="checkbox"]');
      const timeInputs = row.querySelectorAll('input[type="time"]');
      if (!checkbox || !timeInputs.length) return;

      function toggle() {
        timeInputs.forEach(input => { input.disabled = checkbox.checked; });
      }

      checkbox.addEventListener('change', toggle);
      toggle();
    });
  }

  // Page Commander
  function initOrderForm() {
    const form = document.getElementById('orderForm');
    if (!form) return;

    const apiBase = form.dataset.apiMenuBase;
    const commanderBase = form.dataset.commanderBase;
    const menuSelect = document.getElementById('menu_id');
    const dietFilter = document.getElementById('diet_filter');
    const entreeSelect = document.getElementById('entree_id');
    const platSelect = document.getElementById('plat_id');
    const dessertSelect = document.getElementById('dessert_id');
    const entreePreview = document.getElementById('entree_preview');
    const platPreview = document.getElementById('plat_preview');
    const dessertPreview = document.getElementById('dessert_preview');
    const peopleInput = document.getElementById('people_count');
    const minPeopleTxt = document.getElementById('min_people_txt');
    const discountThresholdTxt = document.getElementById('discount_threshold_txt');
    const cityInput = document.getElementById('prestation_city');
    const distanceHidden = document.getElementById('distance_km');
    const priceMenu = document.getElementById('price_menu');
    const priceDelivery = document.getElementById('price_delivery');
    const priceDiscount = document.getElementById('price_discount');
    const priceTotal = document.getElementById('price_total');
    const discountRow = document.getElementById('discount_row');
    const confirmFlag = document.getElementById('confirmFlag');

    if (!apiBase || !commanderBase || !menuSelect || !peopleInput) return;

    let currentMenu = {
      id: menuSelect.value,
      min_people: Number(peopleInput.min || peopleInput.value || 0),
      base_price: 0,
      dishesOriginal: { entree: [], plat: [], dessert: [] },
      dishes: { entree: [], plat: [], dessert: [] },
      optionDiets: []
    };

    const bordeaux = [44.8378, -0.5792];
    const geoCache = Object.create(null);
    let lastGeoQuery = '';
    let lastKm = 0;
    let cityTimer = null;

    const cityHelp = cityInput?.parentElement?.querySelector('.form-text');
    const distanceLine = document.createElement('div');
    distanceLine.className = 'small text-muted mt-1';
    distanceLine.id = 'distance_line';
    if (cityHelp) cityHelp.insertAdjacentElement('afterend', distanceLine);

    function haversineKm(lat1, lon1, lat2, lon2) {
      const earthRadius = 6371;
      const toRad = value => value * Math.PI / 180;
      const dLat = toRad(lat2 - lat1);
      const dLon = toRad(lon2 - lon1);
      const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
      return 2 * earthRadius * Math.asin(Math.sqrt(a));
    }

    async function geocodeCityFr(city) {
      const query = (city || '').trim();
      const key = query.toLowerCase();
      if (!query || key.length < 2) return null;
      if (key === 'bordeaux') return bordeaux;
      if (geoCache[key]) return geoCache[key];

      lastGeoQuery = key;
      const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=fr&q=' + encodeURIComponent(query);
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) return null;

      const json = await res.json();
      if (lastGeoQuery !== key) return null;
      if (!Array.isArray(json) || !json[0] || !json[0].lat || !json[0].lon) return null;

      const lat = Number(json[0].lat);
      const lon = Number(json[0].lon);
      if (!Number.isFinite(lat) || !Number.isFinite(lon)) return null;

      geoCache[key] = [lat, lon];
      return geoCache[key];
    }

    async function estimateKmFromCity(city) {
      const key = (city || '').trim().toLowerCase();
      if (!key || key === 'bordeaux') return 0;
      const destination = await geocodeCityFr(city);
      if (!destination) return 0;
      return Math.max(0, Math.round(haversineKm(bordeaux[0], bordeaux[1], destination[0], destination[1])));
    }

    function setOptions(select, items, placeholder) {
      if (!select) return;
      select.innerHTML = '';
      const placeholderOption = document.createElement('option');
      placeholderOption.value = '';
      placeholderOption.textContent = placeholder;
      select.appendChild(placeholderOption);

      (items || []).forEach(item => {
        const option = document.createElement('option');
        option.value = String(item.id);
        option.textContent = item.name;
        select.appendChild(option);
      });
    }

    function applyDietFilter() {
      if (!dietFilter || !currentMenu) return;
      const selected = dietFilter.value;
      const filterList = list => {
        if (!selected) return list || [];
        return (list || []).filter(dish => {
          const ids = dish.diet_ids || [];
          if (!ids || ids.length === 0) return true;
          return ids.map(String).includes(String(selected));
        });
      };

      currentMenu.dishes = {
        entree: filterList(currentMenu.dishesOriginal.entree),
        plat: filterList(currentMenu.dishesOriginal.plat),
        dessert: filterList(currentMenu.dishesOriginal.dessert)
      };

      const previous = {
        entree: entreeSelect?.value,
        plat: platSelect?.value,
        dessert: dessertSelect?.value
      };

      setOptions(entreeSelect, currentMenu.dishes.entree, 'Choisir une entrée');
      setOptions(platSelect, currentMenu.dishes.plat, 'Choisir un plat');
      setOptions(dessertSelect, currentMenu.dishes.dessert, 'Choisir un dessert');

      if (previous.entree && currentMenu.dishes.entree.some(d => String(d.id) === String(previous.entree))) entreeSelect.value = previous.entree;
      if (previous.plat && currentMenu.dishes.plat.some(d => String(d.id) === String(previous.plat))) platSelect.value = previous.plat;
      if (previous.dessert && currentMenu.dishes.dessert.some(d => String(d.id) === String(previous.dessert))) dessertSelect.value = previous.dessert;

      updateAllPreviews();
    }

    function findDishById(category, id) {
      const list = currentMenu.dishes?.[category] || [];
      return list.find(dish => String(dish.id) === String(id)) || null;
    }

    function updatePreview(category, selectEl, previewEl) {
      if (!selectEl || !previewEl) return;
      const dish = findDishById(category, selectEl.value);
      if (!dish) {
        previewEl.textContent = '';
        return;
      }
      previewEl.innerHTML = '<strong>' + escapeHtml(dish.name) + '</strong><br><span>' + escapeHtml(dish.description || '') + '</span>';
    }

    function updateAllPreviews() {
      updatePreview('entree', entreeSelect, entreePreview);
      updatePreview('plat', platSelect, platPreview);
      updatePreview('dessert', dessertSelect, dessertPreview);
    }

    async function refreshPricing() {
      const min = Number(currentMenu.min_people || 0);
      const base = Number(currentMenu.base_price || 0);

      peopleInput.min = String(min);
      if (Number(peopleInput.value || 0) < min) peopleInput.value = String(min);

      if (minPeopleTxt) minPeopleTxt.textContent = String(min);
      if (discountThresholdTxt) discountThresholdTxt.textContent = String(min + 5);

      const people = Number(peopleInput.value || 0);
      const unit = min > 0 ? base / min : base;
      const menuPrice = unit * people;
      const discountRate = people >= (min + 5) ? 0.10 : 0;
      const discountAmount = menuPrice * discountRate;
      const menuAfterDiscount = menuPrice - discountAmount;

      let km = 0;
      try {
        km = await estimateKmFromCity(cityInput?.value || '');
      } catch (error) {
        km = lastKm || 0;
      }
      lastKm = km;
      if (distanceHidden) distanceHidden.value = String(km);

      const cityKey = (cityInput?.value || '').trim();
      if (distanceLine) {
        if (!cityKey) distanceLine.textContent = '';
        else if (cityKey.toLowerCase() === 'bordeaux') distanceLine.textContent = 'Distance estimée : 0 km (Bordeaux)';
        else if (km > 0) distanceLine.textContent = 'Distance estimée : ' + km + ' km (depuis Bordeaux)';
        else distanceLine.textContent = 'Distance estimée : — (ville non reconnue)';
      }

      let delivery = 0;
      if (cityKey && cityKey.toLowerCase() !== 'bordeaux' && km > 0) {
        delivery = 5 + 0.59 * km;
      }

      if (priceMenu) priceMenu.textContent = money(menuAfterDiscount);
      if (priceDelivery) priceDelivery.textContent = money(delivery);
      if (priceTotal) priceTotal.textContent = money(menuAfterDiscount + delivery);

      if (discountRow && priceDiscount) {
        if (discountRate > 0) {
          discountRow.classList.remove('d-none');
          priceDiscount.textContent = '- ' + money(discountAmount);
        } else {
          discountRow.classList.add('d-none');
          priceDiscount.textContent = '';
        }
      }
    }

    async function loadMenu(menuId) {
      const url = apiBase.replace(/\/$/, '') + '/' + encodeURIComponent(menuId);
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      currentMenu = {
        id: data.id,
        min_people: data.min_people,
        base_price: data.base_price,
        dishesOriginal: data.dishes || { entree: [], plat: [], dessert: [] },
        dishes: data.dishes || { entree: [], plat: [], dessert: [] },
        optionDiets: data.option_diets || []
      };

      form.action = commanderBase.replace(/\/$/, '') + '/' + String(data.id);

      if (dietFilter) {
        dietFilter.innerHTML = '<option value="">Tous les régimes</option>';
        currentMenu.optionDiets.forEach(diet => {
          const option = document.createElement('option');
          option.value = String(diet.id);
          option.textContent = diet.name;
          dietFilter.appendChild(option);
        });
        dietFilter.value = '';
      }

      applyDietFilter();
      await refreshPricing();
    }

    if (dietFilter) {
      dietFilter.addEventListener('change', applyDietFilter);
    }

    menuSelect.addEventListener('change', () => {
      loadMenu(menuSelect.value).catch(() => alert('Erreur lors du chargement du menu sélectionné.'));
    });

    entreeSelect?.addEventListener('change', () => updatePreview('entree', entreeSelect, entreePreview));
    platSelect?.addEventListener('change', () => updatePreview('plat', platSelect, platPreview));
    dessertSelect?.addEventListener('change', () => updatePreview('dessert', dessertSelect, dessertPreview));
    peopleInput.addEventListener('input', () => { refreshPricing(); });
    cityInput?.addEventListener('input', () => {
      if (cityTimer) clearTimeout(cityTimer);
      cityTimer = setTimeout(() => { refreshPricing(); }, 450);
    });

    form.addEventListener('submit', () => {
      if (confirmFlag) confirmFlag.value = '0';
    });

    loadMenu(menuSelect.value).catch(() => { refreshPricing(); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMenuFilters();
    initDishPreviewBadges();
    initOpeningHoursForm();
    initOrderForm();
  });
})();
