(function () {
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

  let currentMenu = {
    id: Number(form.dataset.initialMenuId || 0),
    min_people: Number(form.dataset.initialMinPeople || 0),
    base_price: Number(form.dataset.initialBasePrice || 0),
    dishes: { entree: [], plat: [], dessert: [] }
  };

  const BORDEAUX = [44.8378, -0.5792];
  const geoCache = Object.create(null);
  let lastGeoQuery = '';
  let lastKm = 0;

  const cityHelp = cityInput?.parentElement?.querySelector('.form-text');
  const distanceLine = document.createElement('div');
  distanceLine.className = 'small text-muted mt-1';
  distanceLine.id = 'distance_line';
  if (cityHelp) cityHelp.insertAdjacentElement('afterend', distanceLine);

  function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const toRad = (v) => v * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
    return 2 * R * Math.asin(Math.sqrt(a));
  }

  async function geocodeCityFr(city) {
    const q = (city || '').trim();
    const key = q.toLowerCase();
    if (!q || key.length < 2) return null;
    if (key === 'bordeaux') return BORDEAUX;
    if (geoCache[key]) return geoCache[key];

    lastGeoQuery = key;
    const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=fr&q=' + encodeURIComponent(q);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
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
    if (!key) return 0;
    if (key === 'bordeaux') return 0;

    const dest = await geocodeCityFr(city);
    if (!dest) return 0;
    return Math.max(0, Math.round(haversineKm(BORDEAUX[0], BORDEAUX[1], dest[0], dest[1])));
  }

  function money(v) {
    return (Math.round(v * 100) / 100).toFixed(2).replace('.', ',') + ' €';
  }

  function setOptions(select, items, placeholder) {
    select.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.textContent = placeholder;
    select.appendChild(ph);

    items.forEach(it => {
      const op = document.createElement('option');
      op.value = String(it.id);
      op.textContent = it.name;
      select.appendChild(op);
    });
  }

  function applyDietFilter() {
    if (!dietFilter || !currentMenu) return;

    const selected = dietFilter.value;
    const filterList = (list) => {
      if (!selected) return list;
      return (list || []).filter(d => {
        const ids = d.diet_ids || [];
        if (!ids || ids.length === 0) return true;
        return ids.map(String).includes(String(selected));
      });
    };

    currentMenu.dishes = {
      entree: filterList(currentMenu.dishesOriginal?.entree || []),
      plat: filterList(currentMenu.dishesOriginal?.plat || []),
      dessert: filterList(currentMenu.dishesOriginal?.dessert || []),
    };

    const prevEntree = entreeSelect.value;
    const prevPlat = platSelect.value;
    const prevDessert = dessertSelect.value;

    setOptions(entreeSelect, currentMenu.dishes.entree || [], 'Choisir une entrée');
    setOptions(platSelect, currentMenu.dishes.plat || [], 'Choisir un plat');
    setOptions(dessertSelect, currentMenu.dishes.dessert || [], 'Choisir un dessert');

    if (prevEntree && (currentMenu.dishes.entree || []).some(d => String(d.id) === String(prevEntree))) entreeSelect.value = prevEntree; else entreePreview.textContent = '';
    if (prevPlat && (currentMenu.dishes.plat || []).some(d => String(d.id) === String(prevPlat))) platSelect.value = prevPlat; else platPreview.textContent = '';
    if (prevDessert && (currentMenu.dishes.dessert || []).some(d => String(d.id) === String(prevDessert))) dessertSelect.value = prevDessert; else dessertPreview.textContent = '';
  }

  function findDishById(cat, id) {
    const arr = (currentMenu.dishes && currentMenu.dishes[cat]) ? currentMenu.dishes[cat] : [];
    return arr.find(d => String(d.id) === String(id)) || null;
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
  }

  function updatePreview(cat, selectEl, previewEl) {
    const dish = findDishById(cat, selectEl.value);
    if (!dish) {
      previewEl.textContent = '';
      return;
    }
    const txt = dish.description ? dish.description : '';
    previewEl.innerHTML = '<strong>' + escapeHtml(dish.name) + '</strong><br><span>' + escapeHtml(txt) + '</span>';
  }

  async function refreshPricing() {
    const min = Number(currentMenu.min_people || 0);
    const base = Number(currentMenu.base_price || 0);

    peopleInput.min = String(min);
    if (Number(peopleInput.value || 0) < min) peopleInput.value = String(min);

    minPeopleTxt.textContent = String(min);
    discountThresholdTxt.textContent = String(min + 5);

    const people = Number(peopleInput.value || 0);
    const unit = (min > 0) ? (base / min) : base;
    const menuPrice = unit * people;
    const discountRate = (people >= (min + 5)) ? 0.10 : 0.0;
    const discountAmount = menuPrice * discountRate;
    const menuAfter = menuPrice - discountAmount;

    let km = 0;
    try {
      km = await estimateKmFromCity(cityInput.value || '');
    } catch (e) {
      km = lastKm || 0;
    }
    lastKm = km;
    distanceHidden.value = String(km);

    if (distanceLine) {
      const cityKey = (cityInput.value || '').trim();
      if (!cityKey) {
        distanceLine.textContent = '';
      } else if (cityKey.toLowerCase() === 'bordeaux') {
        distanceLine.textContent = 'Distance estimée : 0 km (Bordeaux)';
      } else if (km > 0) {
        distanceLine.textContent = 'Distance estimée : ' + km + ' km (depuis Bordeaux)';
      } else {
        distanceLine.textContent = 'Distance estimée : — (ville non reconnue)';
      }
    }

    let delivery = 0;
    const cityKey = (cityInput.value || '').trim().toLowerCase();
    if (cityKey && cityKey !== 'bordeaux' && km > 0) {
      delivery = 5 + 0.59 * km;
    }

    priceMenu.textContent = money(menuAfter);
    priceDelivery.textContent = money(delivery);

    if (discountRate > 0) {
      discountRow.style.display = '';
      priceDiscount.textContent = '- ' + money(discountAmount);
    } else {
      discountRow.style.display = 'none';
      priceDiscount.textContent = '';
    }

    priceTotal.textContent = money(menuAfter + delivery);
  }

  async function loadMenu(menuId) {
    const url = apiBase.replace(/\/$/, '') + '/' + encodeURIComponent(menuId);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
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

    form.action = commanderBase + String(data.id);

    if (dietFilter) {
      dietFilter.innerHTML = '<option value="">Tous les régimes</option>';
      (currentMenu.optionDiets || []).forEach(d => {
        const opt = document.createElement('option');
        opt.value = String(d.id);
        opt.textContent = d.name;
        dietFilter.appendChild(opt);
      });
      dietFilter.value = '';
    }

    applyDietFilter();
    entreePreview.textContent = '';
    platPreview.textContent = '';
    dessertPreview.textContent = '';
    await refreshPricing();
  }

  if (dietFilter) {
    dietFilter.addEventListener('change', applyDietFilter);
  }

  menuSelect.addEventListener('change', () => {
    loadMenu(menuSelect.value).catch(() => {
      alert('Erreur lors du chargement du menu sélectionné.');
    });
  });

  entreeSelect.addEventListener('change', () => updatePreview('entree', entreeSelect, entreePreview));
  platSelect.addEventListener('change', () => updatePreview('plat', platSelect, platPreview));
  dessertSelect.addEventListener('change', () => updatePreview('dessert', dessertSelect, dessertPreview));

  let cityTimer = null;
  peopleInput.addEventListener('input', refreshPricing);
  cityInput.addEventListener('input', () => {
    if (cityTimer) clearTimeout(cityTimer);
    cityTimer = setTimeout(refreshPricing, 450);
  });

  loadMenu(menuSelect.value).catch(refreshPricing);
  form.addEventListener('submit', () => {
    confirmFlag.value = '0';
  });
})();
