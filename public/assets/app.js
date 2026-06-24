(function () {
  const form = document.querySelector('[data-menu-filters]');
  const list = document.querySelector('[data-menu-list]');
  if (!form || !list) return;

  const baseHref = document.body?.dataset?.base || '/';

  function withBase(path) {
    // path: "api/menus" ou "menus/1"
    const cleanBase = baseHref.endsWith('/') ? baseHref : baseHref + '/';
    return cleanBase + String(path).replace(/^\//, '');
  }

  async function refresh() {
    const params = new URLSearchParams(new FormData(form));
    const url = withBase('api/menus') + '?' + params.toString();

    let res;
    try {
      res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    } catch (e) {
      list.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des menus.</div>';
      return;
    }

    if (!res.ok) {
      list.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des menus.</div>';
      return;
    }

    const data = await res.json();
    list.innerHTML = '';

    if (!data.items || !data.items.length) {
      list.innerHTML = '<div class="alert alert-secondary">Aucun menu ne correspond aux filtres.</div>';
      return;
    }

    data.items.forEach(m => {
      const card = document.createElement('div');
      card.className = 'col-md-6 col-lg-4 mb-3';
      card.innerHTML = `
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h3 class="h5 card-title">${escapeHtml(m.title)}</h3>
            <p class="card-text text-muted">${escapeHtml(m.short_description)}</p>
            <div class="small">
              <span class="badge text-bg-light">Min. ${m.min_people} pers.</span>
              <span class="badge text-bg-light">${Number(m.base_price).toFixed(2)} €</span>
              <span class="badge text-bg-light">${escapeHtml(m.theme)}</span>
              <span class="badge text-bg-light">${escapeHtml(m.diet)}</span>
            </div>
          </div>
          <div class="card-footer bg-white border-0">
            <a class="btn btn-sm btn-primary" href="${withBase('menus/' + m.id)}">En savoir plus</a>
          </div>
        </div>`;
      list.appendChild(card);
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  form.addEventListener('change', refresh);
  form.addEventListener('submit', e => { e.preventDefault(); refresh(); });
  refresh();
})();

// ==============================
// Page "Commander" (orders/create)
// ==============================
(function () {
  const root = document.querySelector('[data-order-page]');
  if (!root) return;

  const baseHref = document.body?.dataset?.base || '/';
  const cleanBase = baseHref.endsWith('/') ? baseHref : baseHref + '/';
  const withBase = (path) => cleanBase + String(path).replace(/^\//, '');

  const menuSelect = root.querySelector('[data-menu-select]');
  const dishesJsonEl = root.querySelector('[data-menu-dishes-json]');
  const entreeSel = root.querySelector('[data-dish-select="entree"]');
  const platSel = root.querySelector('[data-dish-select="plat"]');
  const dessertSel = root.querySelector('[data-dish-select="dessert"]');
  const peopleInput = root.querySelector('[data-people-count]');
  const minPeopleEl = root.querySelector('[data-min-people]');
  const basePriceEl = root.querySelector('[data-base-price]');
  const kmEl = root.querySelector('[data-distance-km]');
  const feeEl = root.querySelector('[data-delivery-fee]');
  const totalEl = root.querySelector('[data-total]');
  const discountEl = root.querySelector('[data-discount]');
  const warnMinEl = root.querySelector('[data-min-warning]');

  const prevEntree = root.querySelector('[data-preview="entree"]');
  const prevPlat = root.querySelector('[data-preview="plat"]');
  const prevDessert = root.querySelector('[data-preview="dessert"]');

  const hiddenMenuId = root.querySelector('input[name="menu_id"]');
  const hiddenEntree = root.querySelector('input[name="entree_dish_id"]');
  const hiddenPlat = root.querySelector('input[name="plat_dish_id"]');
  const hiddenDessert = root.querySelector('input[name="dessert_dish_id"]');

  function escapeHtml(s) {
    return String(s).replace(/[&<>"]|'/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function parseMenuData() {
    try {
      return JSON.parse(dishesJsonEl.textContent || '{}');
    } catch (e) {
      return {};
    }
  }

  let menuData = parseMenuData();

  function setSelectOptions(select, options) {
    const current = select.value;
    select.innerHTML = '<option value="">— Choisir —</option>';
    options.forEach(d => {
      const opt = document.createElement('option');
      opt.value = String(d.id);
      opt.textContent = d.name;
      opt.dataset.description = d.description || '';
      opt.dataset.category = d.category || '';
      select.appendChild(opt);
    });
    // tente de garder la sélection si possible
    if (current) select.value = current;
    if (!select.value && options.length) select.value = String(options[0].id);
  }

  function updatePreviews() {
    const byId = new Map((menuData.dishes || []).map(d => [String(d.id), d]));
    const fill = (card, dishId) => {
      const d = byId.get(String(dishId));
      if (!d) {
        card.innerHTML = '<div class="text-muted">Aucune sélection</div>';
        return;
      }
      card.innerHTML = `
        <div class="fw-semibold">${escapeHtml(d.name)}</div>
        <div class="small text-muted">${escapeHtml(d.description || '')}</div>
      `;
    };
    fill(prevEntree, entreeSel.value);
    fill(prevPlat, platSel.value);
    fill(prevDessert, dessertSel.value);

    hiddenEntree.value = entreeSel.value || '';
    hiddenPlat.value = platSel.value || '';
    hiddenDessert.value = dessertSel.value || '';
  }

  function computeDeliveryFee(city, km) {
    const c = (city || '').trim().toLowerCase();
    if (!c) return 0;
    if (c === 'bordeaux') return 0;
    const d = isFinite(km) ? km : 0;
    return 5 + 0.59 * Math.max(0, d);
  }

  function updatePricing() {
    const minPeople = Number(menuData.min_people || 0);
    const basePrice = Number(menuData.base_price || 0);
    const people = Math.max(0, Number(peopleInput.value || 0));

    minPeopleEl.textContent = String(minPeople);
    basePriceEl.textContent = basePrice.toFixed(2);

    const pricePerPerson = minPeople > 0 ? (basePrice / minPeople) : 0;
    let menuPrice = pricePerPerson * people;
    let discount = 0;
    if (people >= (minPeople + 5) && basePrice > 0) {
      discount = menuPrice * 0.10;
      menuPrice = menuPrice - discount;
    }

    // Avertissement si en-dessous du minimum (autorisé côté saisie, mais on prévient)
    if (people > 0 && people < minPeople) {
      warnMinEl.classList.remove('d-none');
    } else {
      warnMinEl.classList.add('d-none');
    }

    const city = root.querySelector('[data-city]')?.value || '';
    const km = Number(kmEl.value || 0);
    const fee = computeDeliveryFee(city, km);

    feeEl.textContent = fee.toFixed(2);
    discountEl.textContent = discount.toFixed(2);
    totalEl.textContent = (menuPrice + fee).toFixed(2);
  }

  async function fetchMenuDetails(menuId) {
    const url = withBase('api/menus/' + menuId);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return await res.json();
  }

  async function onMenuChange() {
    const menuId = menuSelect.value;
    if (!menuId) return;
    hiddenMenuId.value = menuId;
    try {
      const data = await fetchMenuDetails(menuId);
      menuData = data;
      // Remplit les sélecteurs par catégorie
      const entrees = (data.dishes || []).filter(d => d.category === 'entree');
      const plats = (data.dishes || []).filter(d => d.category === 'plat');
      const desserts = (data.dishes || []).filter(d => d.category === 'dessert');

      setSelectOptions(entreeSel, entrees);
      setSelectOptions(platSel, plats);
      setSelectOptions(dessertSel, desserts);

      updatePreviews();
      updatePricing();
    } catch (e) {
      console.error(e);
      alert('Impossible de charger le menu sélectionné.');
    }
  }

  // --- Géocodage distance (optionnel) via Nominatim ---
  const addrInput = root.querySelector('[data-address]');
  const cityInput = root.querySelector('[data-city]');
  const geoHint = root.querySelector('[data-geo-hint]');
  let geoTimeout = null;

  function haversineKm(lat1, lon1, lat2, lon2) {
    const toRad = (d) => (d * Math.PI) / 180;
    const R = 6371;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
  }

  async function geocode(query) {
    const u = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
    const r = await fetch(u, { headers: { 'Accept': 'application/json' } });
    if (!r.ok) throw new Error('Geo HTTP ' + r.status);
    const j = await r.json();
    if (!j || !j.length) return null;
    return { lat: Number(j[0].lat), lon: Number(j[0].lon) };
  }

  function normalizeCity(str) {
    return (str || '')
        .toString()
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')  // retire les accents
        .replace(/[^a-z\s-]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function haversineKm(a, b) {
    const toRad = (d) => (d * Math.PI) / 180;
    const R = 6371;
    const dLat = toRad(b.lat - a.lat);
    const dLon = toRad(b.lon - a.lon);
    const lat1 = toRad(a.lat);
    const lat2 = toRad(b.lat);

    const x = Math.sin(dLat/2) * Math.sin(dLat/2)
            + Math.cos(lat1) * Math.cos(lat2)
            * Math.sin(dLon/2) * Math.sin(dLon/2);

    return 2 * R * Math.asin(Math.sqrt(x));
}

/**
 * Distance simplifiée : on se base sur la VILLE (pas l'adresse).
 * - Bordeaux => 0 km
 * - Sinon : calcul approximatif via coordonnées connues
 * - Si ville inconnue : estimation à 50 km (pour ne pas bloquer le formulaire)
 */
function computeDistanceMaybe() {
    const city = normalizeCity(prestationCity.value);
    if (!city) {
        distanceKmInput.value = '0';
        distanceKmDisplay.textContent = '0 km';
        geoHint.textContent = 'Renseigne la ville pour estimer les frais de déplacement.';
        updatePriceUI();
        return;
    }

    const coords = {
        bordeaux: {lat: 44.8378, lon: -0.5792},
        paris: {lat: 48.8566, lon: 2.3522},
        lyon: {lat: 45.7640, lon: 4.8357},
        marseille: {lat: 43.2965, lon: 5.3698},
        toulouse: {lat: 43.6047, lon: 1.4442},
        nice: {lat: 43.7102, lon: 7.2620},
        nantes: {lat: 47.2184, lon: -1.5536},
        lille: {lat: 50.6292, lon: 3.0573},
        strasbourg: {lat: 48.5734, lon: 7.7521},
        rennes: {lat: 48.1173, lon: -1.6778},
        montpellier: {lat: 43.6119, lon: 3.8772},
        grenoble: {lat: 45.1885, lon: 5.7245},
        dijon: {lat: 47.3220, lon: 5.0415},
        tours: {lat: 47.3941, lon: 0.6848},
        angers: {lat: 47.4784, lon: -0.5632},
        reims: {lat: 49.2583, lon: 4.0317},
        le_havre: {lat: 49.4944, lon: 0.1079},
        rouen: {lat: 49.4432, lon: 1.0993},
        orleans: {lat: 47.9029, lon: 1.9093},
        clermont_ferrand: {lat: 45.7772, lon: 3.0870},
        limoges: {lat: 45.8336, lon: 1.2611},
        pau: {lat: 43.2951, lon: -0.3708},
        bayonne: {lat: 43.4929, lon: -1.4748},
        la_rochelle: {lat: 46.1603, lon: -1.1511},
        biarritz: {lat: 43.4832, lon: -1.5586}
    };

    const base = coords.bordeaux;
    let chosen = coords[city];

    // alias rapides
    if (!chosen && city === 'le havre') chosen = coords.le_havre;
    if (!chosen && city === 'clermont ferrand') chosen = coords.clermont_ferrand;

    let km;
    if (chosen) {
        km = Math.round(haversineKm(base, chosen));
        geoHint.textContent = 'Distance estimée à partir de la ville (approx.).';
    } else {
        km = 50;
        geoHint.textContent = 'Ville non reconnue : distance estimée (50 km).';
    }

    // Bordeaux => 0
    if (city === 'bordeaux') km = 0;

    distanceKmInput.value = String(km);
    distanceKmDisplay.textContent = km + ' km';

    updatePriceUI();
}

    geoHint.textContent = 'Calcul de distance…';
    try {
      const target = await geocode(address + ', ' + city);
      const bdx = await geocode('Bordeaux, France');
      if (!target || !bdx) {
        geoHint.textContent = 'Impossible de géocoder. Tu peux saisir la distance manuellement.';
        return;
      }
      const km = haversineKm(target.lat, target.lon, bdx.lat, bdx.lon);
      kmEl.value = km.toFixed(1);
      geoHint.textContent = 'Distance estimée automatiquement (≈ ' + km.toFixed(1) + ' km).';
      updatePricing();
    } catch (e) {
      console.warn(e);
      geoHint.textContent = 'Géocodage indisponible. Tu peux saisir la distance manuellement.';
    }
  }

  function debounceDistance() {
    if (geoTimeout) clearTimeout(geoTimeout);
    geoTimeout = setTimeout(computeDistanceMaybe, 700);
  }

  // Init
  menuSelect.addEventListener('change', onMenuChange);
  entreeSel.addEventListener('change', () => { updatePreviews(); });
  platSel.addEventListener('change', () => { updatePreviews(); });
  dessertSel.addEventListener('change', () => { updatePreviews(); });
  peopleInput.addEventListener('input', updatePricing);
  kmEl.addEventListener('input', updatePricing);
  cityInput.addEventListener('input', () => { updatePricing(); debounceDistance(); });
  addrInput.addEventListener('input', debounceDistance);

  // Première mise en place
  onMenuChange();
})();
