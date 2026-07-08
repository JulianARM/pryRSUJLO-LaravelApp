(function () {
    function init() {
        document.querySelectorAll('#zoneMap').forEach((element) => {
            if (element.dataset.initialized === '1' || !window.L) {
                return;
            }

            element.dataset.initialized = '1';
            initZonaMap(element);
        });

        document.querySelectorAll('.js-zone-overview-map').forEach((element) => {
            if (element.dataset.initialized === '1' || !window.L || !isVisible(element)) {
                return;
            }

            element.dataset.initialized = '1';
            initZonaOverviewMap(element);
        });
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-zone-preview-id], [data-zone-preview-mode]');

        if (!trigger) {
            return;
        }

        const target = document.querySelector(trigger.dataset.target);
        const map = target?.querySelector('.js-zone-overview-map');

        if (map) {
            map.dataset.selectedZonaId = trigger.dataset.zonePreviewMode === 'all'
                ? 'all'
                : trigger.dataset.zonePreviewId;
        }
    });

    if (window.jQuery) {
        window.jQuery(document)
            .off('shown.bs.modal.rsuZonas')
            .on('shown.bs.modal.rsuZonas', '.modal', function () {
                const map = this.querySelector('.js-zone-overview-map');

                if (!map) {
                    return;
                }

                if (map.dataset.initialized !== '1') {
                    map.dataset.initialized = '1';
                    initZonaOverviewMap(map);
                    return;
                }

                map.rsuLeafletMap?.invalidateSize();
                map.rsuRenderZonaOverview?.();
            });
    }

    function initZonaMap(element) {
        const form = element.closest('form');
        const coordinatesInput = form.querySelector('.js-zone-coordinates-input');
        const manualLatitude = form.querySelector('.js-zone-manual-lat');
        const manualLongitude = form.querySelector('.js-zone-manual-lng');
        const manualError = form.querySelector('.js-zone-manual-error');
        const tableBody = document.querySelector('.js-zone-coordinates-body');
        let coordinates = parseJson(element.dataset.coordinates, []);
        const referenceZonas = parseJson(element.dataset.referenceZonas, []);
        const defaultLat = Number.parseFloat(element.dataset.defaultLat || '-6.767305');
        const defaultLng = Number.parseFloat(element.dataset.defaultLng || '-79.842276');

        const center = coordinates.length
            ? [coordinates[0].lat, coordinates[0].lng]
            : [defaultLat, defaultLng];

        const map = window.L.map(element).setView(center, coordinates.length ? 16 : 14);

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        const currentLayer = window.L.layerGroup().addTo(map);
        const referenceLayer = window.L.layerGroup().addTo(map);
        let polygon = null;

        referenceZonas.forEach((zone) => {
            if (!zone.coordinates || zone.coordinates.length < 3) {
                return;
            }

            const referencePolygon = window.L.polygon(
                zone.coordinates.map((item) => [item.lat, item.lng]),
                {
                    color: '#006DAA',
                    dashArray: '7 5',
                    fillColor: '#0B8FC6',
                    fillOpacity: 0.24,
                    weight: 2,
                }
            ).addTo(referenceLayer);

            referencePolygon.bindTooltip(`Zona referencial: ${zone.name}`);
        });

        map.on('click', (event) => {
            coordinates.push({
                lat: Number(event.latlng.lat.toFixed(7)),
                lng: Number(event.latlng.lng.toFixed(7)),
            });
            render({ fitBounds: true });
        });

        form.querySelector('.js-zone-undo-point')?.addEventListener('click', () => {
            coordinates.pop();
            render({ fitBounds: true });
        });

        form.querySelector('.js-zone-clear-points')?.addEventListener('click', () => {
            if (!coordinates.length || !window.confirm('¿Deseas limpiar todas las coordenadas del perímetro?')) {
                return;
            }

            coordinates = [];
            render({ fitBounds: true });
        });

        form.querySelector('.js-zone-use-location')?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                return;
            }

            navigator.geolocation.getCurrentPosition((position) => {
                const latLng = [position.coords.latitude, position.coords.longitude];
                map.setView(latLng, 17);
            });
        });

        form.querySelector('.js-zone-add-manual-point')?.addEventListener('click', () => {
            addManualCoordinate();
        });

        [manualLatitude, manualLongitude].forEach((input) => {
            input?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                addManualCoordinate();
            });
        });

        tableBody?.addEventListener('click', (event) => {
            const button = event.target.closest('.js-zone-remove-point');

            if (!button) {
                return;
            }

            coordinates.splice(Number.parseInt(button.dataset.index, 10), 1);
            render({ fitBounds: true });
        });

        function addManualCoordinate() {
            clearManualError();

            const latitud = Number.parseFloat(manualLatitude?.value);
            const longitud = Number.parseFloat(manualLongitude?.value);

            if (!Number.isFinite(latitud) || !Number.isFinite(longitud)) {
                showManualError('Ingrese latitud y longitud numericas para agregar el punto.');
                return;
            }

            if (latitud < -90 || latitud > 90) {
                showManualError('La latitud debe estar entre -90 y 90.');
                return;
            }

            if (longitud < -180 || longitud > 180) {
                showManualError('La longitud debe estar entre -180 y 180.');
                return;
            }

            coordinates.push({
                lat: Number(latitud.toFixed(7)),
                lng: Number(longitud.toFixed(7)),
            });

            if (manualLatitude) {
                manualLatitude.value = '';
            }

            if (manualLongitude) {
                manualLongitude.value = '';
            }

            manualLatitude?.focus();
            render();
        }

        function showManualError(message) {
            [manualLatitude, manualLongitude].forEach((input) => input?.classList.add('is-invalid'));

            if (!manualError) {
                return;
            }

            manualError.textContent = message;
            manualError.style.setProperty('display', 'block', 'important');
        }

        function clearManualError() {
            [manualLatitude, manualLongitude].forEach((input) => input?.classList.remove('is-invalid'));

            if (!manualError) {
                return;
            }

            manualError.textContent = '';
            manualError.style.setProperty('display', 'none', 'important');
        }

        function render(options = {}) {
            const shouldFitBounds = options.fitBounds ?? false;

            currentLayer.clearLayers();

            if (polygon) {
                polygon.remove();
                polygon = null;
            }

            coordinates.forEach((coordinate, index) => {
                window.L.marker([coordinate.lat, coordinate.lng], {
                    draggable: true,
                    title: `Punto ${index + 1}`,
                }).addTo(currentLayer).bindTooltip(`Punto ${index + 1}`, {
                    permanent: true,
                    direction: 'top',
                }).on('dragend', (event) => {
                    const latLng = event.target.getLatLng();

                    coordinates[index] = {
                        lat: Number(latLng.lat.toFixed(7)),
                        lng: Number(latLng.lng.toFixed(7)),
                    };

                    render({ fitBounds: false });
                });
            });

            if (coordinates.length >= 3) {
                polygon = window.L.polygon(
                    coordinates.map((item) => [item.lat, item.lng]),
                    {
                        color: '#0E3C67',
                        fillColor: '#0B8FC6',
                        fillOpacity: 0.28,
                        weight: 3,
                    }
                ).addTo(map);
            }

            if (coordinates.length && shouldFitBounds) {
                const bounds = window.L.latLngBounds(coordinates.map((item) => [item.lat, item.lng]));
                map.fitBounds(bounds.pad(0.2));
            }

            coordinatesInput.value = JSON.stringify(coordinates);
            renderTable();
        }

        function renderTable() {
            if (!tableBody) {
                return;
            }

            if (!coordinates.length) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay coordenadas registradas.</td></tr>';
                return;
            }

            tableBody.innerHTML = coordinates.map((coordinate, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td><code>${coordinate.lat}</code></td>
                    <td><code>${coordinate.lng}</code></td>
                    <td class="text-right">
                        <button type="button" class="btn btn-danger btn-xs js-zone-remove-point" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        render({ fitBounds: true });
        setTimeout(() => map.invalidateSize(), 300);
    }

    function initZonaOverviewMap(element) {
        const modal = element.closest('.modal');
        const zonas = parseJson(element.dataset.zonas, []);
        const defaultLat = Number.parseFloat(element.dataset.defaultLat || '-6.767305');
        const defaultLng = Number.parseFloat(element.dataset.defaultLng || '-79.842276');
        const select = modal?.querySelector('.js-zone-overview-select');
        const layer = window.L.layerGroup();
        const map = window.L.map(element).setView([defaultLat, defaultLng], 14);
        const selectedColor = '#0E3C67';
        const selectedFillColor = '#0B8FC6';
        const activeColor = '#16A34A';
        const inactiveColor = '#94A3B8';

        element.rsuLeafletMap = map;

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        layer.addTo(map);

        if (select) {
            select.addEventListener('change', () => {
                element.dataset.selectedZonaId = select.value;
                renderSelectedZona();
            });
        }

        element.rsuRenderZonaOverview = renderSelectedZona;
        renderSelectedZona();
        setTimeout(() => map.invalidateSize(), 300);

        function selectOverviewZona(zoneId) {
            element.dataset.selectedZonaId = String(zoneId);

            if (select) {
                select.value = String(zoneId);
            }

            renderSelectedZona();
        }

        function renderSelectedZona() {
            const selectedValue = element.dataset.selectedZonaId || select?.value || 'all';

            if (selectedValue === 'all') {
                renderAllZonas();
                return;
            }

            const selectedId = Number.parseInt(selectedValue, 10);
            const zone = zonas.find((item) => Number.parseInt(item.id, 10) === selectedId) || zonas[0];

            layer.clearLayers();

            if (!zone) {
                setText(modal, '.js-zone-overview-name', 'No hay zonas registradas');
                setText(modal, '.js-zone-overview-location', '-');
                setText(modal, '.js-zone-overview-points', '0');
                setText(modal, '.js-zone-overview-waste', 'N/A');
                setText(modal, '.js-zone-overview-departamento', '-');
                setText(modal, '.js-zone-overview-area', 'N/A');
                setText(modal, '.js-zone-overview-active-zonas', '0');
                setText(modal, '.js-zone-overview-map-location', 'José Leonardo Ortiz, Chiclayo, Lambayeque');
                setText(modal, '.js-zone-overview-descripcion', 'Registre una zona para visualizarla en el mapa.');
                renderOverviewCoordinates(modal, []);
                renderActiveZonasList(modal, zonas);
                renderMapLegend(modal);
                map.setView([defaultLat, defaultLng], 14);
                return;
            }

            if (select && String(select.value) !== String(zone.id)) {
                select.value = zone.id;
            }

            const coordinates = zone.coordinates || [];
            const area = calculateAreaKm2(coordinates);

            setText(modal, '.js-zone-overview-name', zone.name);
            setText(modal, '.js-zone-overview-location', zone.location || '-');
            setText(modal, '.js-zone-overview-points', coordinates.length);
            setText(modal, '.js-zone-overview-waste', zone.residuos_promedio_kg ? `${formatNumber(zone.residuos_promedio_kg)} Kg` : 'N/A');
            setText(modal, '.js-zone-overview-departamento', zone.departamento || '-');
            setText(modal, '.js-zone-overview-area', area === null ? 'N/A' : `${formatNumber(area)} Km2`);
            setText(modal, '.js-zone-overview-active-zonas', zonas.filter((item) => item.activo).length);
            setText(modal, '.js-zone-overview-map-location', zone.location || 'José Leonardo Ortiz, Chiclayo, Lambayeque');
            setText(modal, '.js-zone-overview-descripcion', zone.descripcion || '-');
            renderOverviewCoordinates(modal, coordinates);
            renderActiveZonasList(modal, zonas);
            renderMapLegend(modal, zone.id);
            renderZonaNavigation(zone.id);

            return;
        }

        function renderZonaNavigation(activeZonaId) {
            const boundsPoints = [];

            zonas.forEach((item, zoneIndex) => {
                const itemCoordinates = item.coordinates || [];
                const isActive = Number.parseInt(item.id, 10) === Number.parseInt(activeZonaId, 10);
                const baseColor = item.activo ? activeColor : inactiveColor;
                const color = isActive ? selectedColor : baseColor;
                const fillColor = isActive ? selectedFillColor : baseColor;

                itemCoordinates.forEach((coordinate, coordinateIndex) => {
                    boundsPoints.push([coordinate.lat, coordinate.lng]);

                    if (isActive) {
                        window.L.marker([coordinate.lat, coordinate.lng], {
                            title: `Punto ${coordinateIndex + 1}`,
                        }).addTo(layer).bindTooltip(String(coordinateIndex + 1), {
                            permanent: itemCoordinates.length <= 12,
                            direction: 'top',
                        });
                    }
                });

                if (itemCoordinates.length >= 3) {
                    const polygon = window.L.polygon(
                        itemCoordinates.map((coordinate) => [coordinate.lat, coordinate.lng]),
                        {
                            color,
                            dashArray: isActive ? null : '7 5',
                            fillColor,
                            fillOpacity: isActive ? 0.34 : 0.16,
                            weight: isActive ? 4 : 2,
                        }
                    ).addTo(layer);

                    polygon.bindTooltip(isActive ? `${item.name} (seleccionada)` : item.name).bindPopup(`
                        <strong>${escapeHtml(item.name)}</strong><br>
                        ${escapeHtml(item.location || '')}<br>
                        ${itemCoordinates.length} puntos
                    `);
                    polygon.on('click', () => selectOverviewZona(item.id));

                    return;
                }

                itemCoordinates.forEach((coordinate) => {
                    const marker = window.L.circleMarker([coordinate.lat, coordinate.lng], {
                        color,
                        fillColor,
                        fillOpacity: isActive ? 0.95 : 0.72,
                        radius: isActive ? 8 : 6,
                        weight: isActive ? 3 : 2,
                    }).addTo(layer);

                    marker.bindTooltip(isActive ? `${item.name} (seleccionada)` : item.name);
                    marker.on('click', () => selectOverviewZona(item.id));
                });
            });

            if (boundsPoints.length) {
                map.fitBounds(window.L.latLngBounds(boundsPoints).pad(0.22));
                return;
            }

            map.setView([defaultLat, defaultLng], 14);
        }

        function renderAllZonas() {
            layer.clearLayers();

            if (select && select.value !== 'all') {
                select.value = 'all';
            }

            if (!zonas.length) {
                setText(modal, '.js-zone-overview-name', 'No hay zonas registradas');
                setText(modal, '.js-zone-overview-location', '-');
                setText(modal, '.js-zone-overview-points', '0');
                setText(modal, '.js-zone-overview-waste', 'N/A');
                setText(modal, '.js-zone-overview-departamento', '-');
                setText(modal, '.js-zone-overview-area', 'N/A');
                setText(modal, '.js-zone-overview-active-zonas', '0');
                setText(modal, '.js-zone-overview-map-location', 'José Leonardo Ortiz, Chiclayo, Lambayeque');
                setText(modal, '.js-zone-overview-descripcion', 'Registre una zona para visualizarla en el mapa.');
                renderOverviewCoordinates(modal, []);
                renderActiveZonasList(modal, zonas);
                renderMapLegend(modal);
                map.setView([defaultLat, defaultLng], 14);
                return;
            }

            const allCoordinates = [];
            const departamentos = [...new Set(zonas.map((zone) => zone.departamento).filter(Boolean))];
            const locations = [...new Set(zonas.map((zone) => zone.location).filter(Boolean))];
            const totalWaste = zonas.reduce((sum, zone) => sum + Number(zone.residuos_promedio_kg || 0), 0);
            const totalArea = zonas.reduce((sum, zone) => sum + Number(calculateAreaKm2(zone.coordinates || []) || 0), 0);
            const activeZonas = zonas.filter((zone) => zone.activo);
            const boundsPoints = [];

            zonas.forEach((zone, zoneIndex) => {
                const coordinates = zone.coordinates || [];
                const color = zone.activo ? activeColor : inactiveColor;

                coordinates.forEach((coordinate, coordinateIndex) => {
                    boundsPoints.push([coordinate.lat, coordinate.lng]);
                    allCoordinates.push({
                        ...coordinate,
                        label: `${zone.name} #${coordinateIndex + 1}`,
                    });
                });

                if (coordinates.length >= 3) {
                    const polygon = window.L.polygon(
                        coordinates.map((coordinate) => [coordinate.lat, coordinate.lng]),
                        {
                            color,
                            fillColor: color,
                            fillOpacity: 0.2,
                            weight: 3,
                        }
                    ).addTo(layer);

                    polygon.bindTooltip(zone.name).bindPopup(`
                        <strong>${escapeHtml(zone.name)}</strong><br>
                        ${escapeHtml(zone.location || '')}<br>
                        ${coordinates.length} puntos
                    `);
                    polygon.on('click', () => selectOverviewZona(zone.id));

                    return;
                }

                coordinates.forEach((coordinate) => {
                    const marker = window.L.circleMarker([coordinate.lat, coordinate.lng], {
                        color,
                        fillColor: color,
                        fillOpacity: 0.85,
                        radius: 7,
                    }).addTo(layer);

                    marker.bindTooltip(zone.name);
                    marker.on('click', () => selectOverviewZona(zone.id));
                });
            });

            setText(modal, '.js-zone-overview-name', 'Todas las zonas');
            setText(modal, '.js-zone-overview-location', `${zonas.length} zonas registradas`);
            setText(modal, '.js-zone-overview-points', allCoordinates.length);
            setText(modal, '.js-zone-overview-waste', totalWaste > 0 ? `${formatNumber(totalWaste)} Kg` : 'N/A');
            setText(modal, '.js-zone-overview-departamento', departamentos.length === 1 ? departamentos[0] : 'Varios');
            setText(modal, '.js-zone-overview-area', totalArea > 0 ? `${formatNumber(totalArea)} Km2` : 'N/A');
            setText(modal, '.js-zone-overview-active-zonas', activeZonas.length);
            setText(modal, '.js-zone-overview-map-location', locations.length === 1 ? locations[0] : 'José Leonardo Ortiz, Chiclayo, Lambayeque');
            setText(modal, '.js-zone-overview-descripcion', 'Vista general de todas las zonas registradas con perímetro disponible.');
            renderOverviewCoordinates(modal, allCoordinates);
            renderActiveZonasList(modal, zonas);
            renderMapLegend(modal);

            if (boundsPoints.length) {
                map.fitBounds(window.L.latLngBounds(boundsPoints).pad(0.22));
                return;
            }

            map.setView([defaultLat, defaultLng], 14);
        }
    }

    function renderActiveZonasList(modal, zonas) {
        const container = modal?.querySelector('.js-zone-overview-active-list');

        if (!container) {
            return;
        }

        const activeZonas = zonas.filter((zone) => zone.activo);

        if (!activeZonas.length) {
            container.innerHTML = '<div class="text-muted small">No hay zonas activas registradas.</div>';
            return;
        }

        container.innerHTML = activeZonas.map((zone) => `
            <div class="rsu-zone-active-item">
                <span><i class="fas fa-map-marker-alt mr-1"></i>${escapeHtml(zone.name)}</span>
                <small>${escapeHtml(zone.location || '-')}</small>
            </div>
        `).join('');
    }

    function renderMapLegend(modal, selectedZonaId = null) {
        const legend = modal?.querySelector('.js-zone-map-legend');

        if (!legend) {
            return;
        }

        const items = [
            ['#16A34A', 'Zonas activas'],
            ['#94A3B8', 'Zonas inactivas'],
        ];

        if (selectedZonaId) {
            items.unshift(['#0B8FC6', 'Zona seleccionada']);
        }

        legend.innerHTML = items.map(([color, label]) => `
            <span class="rsu-zone-legend-item">
                <span class="rsu-zone-legend-swatch" style="background:${color}"></span>
                ${escapeHtml(label)}
            </span>
        `).join('');
    }

    function renderOverviewCoordinates(modal, coordinates) {
        const table = modal?.querySelector('.js-zone-overview-coordinates');

        if (!table) {
            return;
        }

        if (!coordinates.length) {
            table.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay coordenadas registradas.</td></tr>';
            return;
        }

        table.innerHTML = coordinates.map((coordinate, index) => `
            <tr>
                <td>${escapeHtml(coordinate.label || index + 1)}</td>
                <td><code>${coordinate.lat}</code></td>
                <td><code>${coordinate.lng}</code></td>
            </tr>
        `).join('');
    }

    function calculateAreaKm2(coordinates) {
        if (!coordinates || coordinates.length < 3) {
            return null;
        }

        const earthRadiusKm = 6371.0088;
        const averageLatitude = coordinates.reduce((sum, coordinate) => sum + toRadians(coordinate.lat), 0) / coordinates.length;
        const points = coordinates.map((coordinate) => ({
            x: earthRadiusKm * toRadians(coordinate.lng) * Math.cos(averageLatitude),
            y: earthRadiusKm * toRadians(coordinate.lat),
        }));
        let area = 0;

        points.forEach((point, index) => {
            const next = points[(index + 1) % points.length];
            area += point.x * next.y - next.x * point.y;
        });

        return Math.abs(area / 2);
    }

    function toRadians(value) {
        return Number(value) * Math.PI / 180;
    }

    function setText(parent, selector, value) {
        const element = parent?.querySelector(selector);

        if (element) {
            element.textContent = value;
        }
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(Number(value));
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isVisible(element) {
        return Boolean(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
    }

    function parseJson(value, fallback) {
        try {
            return JSON.parse(value || JSON.stringify(fallback));
        } catch (error) {
            return fallback;
        }
    }

    window.RsuZonas = { init };
    document.addEventListener('DOMContentLoaded', init);
})();
