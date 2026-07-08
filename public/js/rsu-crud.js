(function () {
    const containerSelector = '.js-crud-container';
    let pendingConfirmForm = null;

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('.js-ajax-form');

        if (!form) {
            return;
        }

        event.preventDefault();

        if (form.dataset.confirm && form.dataset.confirmed !== 'true') {
            showInlineConfirm(form, form.dataset.confirm);
            return;
        }

        delete form.dataset.confirmed;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (form.classList.contains('js-personnel-group-form') && form.dataset.groupAvailable !== 'true') {
            showFormErrors(form, {
                message: 'Valida la disponibilidad antes de guardar.',
                errors: {
                    availability: ['El conductor, los ayudantes requeridos y el vehículo deben estar disponibles para los días y turno seleccionados.'],
                },
            });
            return;
        }

        if (form.dataset.requireScheduleValidation === 'true' && form.dataset.scheduleValidated !== 'true') {
            showScheduleValidationResult(form, {
                message: 'Valida el reemplazo antes de guardar.',
                errors: {
                    availability: ['Presiona el botón Validar reemplazo y corrige las observaciones antes de guardar.'],
                },
                suggestions: [],
            }, false);
            return;
        }

        clearFormErrors(form);
        setFormBusy(form, true);

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorPayload = normalizeErrorPayload(payload, response.status);
                showFormErrors(form, errorPayload);
                clearInvalidFileInputs(form, errorPayload);

                if (form.classList.contains('js-personnel-group-form')) {
                    form.dataset.groupAvailable = 'false';
                    form.querySelector('.js-group-submit')?.setAttribute('disabled', 'disabled');
                }

                return;
            }

            const message = payload.message || 'Operación realizada correctamente.';

            await closeOpenModal(form);
            await refreshCrudContainer();
            initCrudEnhancements();
            cleanupModalState();
            showFlash(message, 'success');
        } catch (error) {
            showFlash('No se pudo completar la operación. Intenta nuevamente.', 'danger');
        } finally {
            setFormBusy(form, false);
        }
    });

    document.addEventListener('click', function (event) {
        const confirmButton = event.target.closest('.js-confirm-accept');

        if (confirmButton) {
            if (!pendingConfirmForm) {
                return;
            }

            const form = pendingConfirmForm;
            pendingConfirmForm = null;
            form.dataset.confirmed = 'true';
            clearInlineConfirm();

            if (form.requestSubmit) {
                form.requestSubmit();
                return;
            }

            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            return;
        }

        const cancelButton = event.target.closest('.js-confirm-cancel');

        if (cancelButton) {
            pendingConfirmForm = null;
            clearInlineConfirm();
        }
    });

    document.addEventListener('change', function (event) {
        const brandSelect = event.target.closest('.js-brand-select');

        if (brandSelect) {
            filterModelOptions(brandSelect);
        }

        const contractType = event.target.closest('.js-contract-type');

        if (contractType) {
            toggleContratoEndDate(contractType);
        }

        const vacationInput = event.target.closest('.js-vacation-days, .js-vacation-start');

        if (vacationInput) {
            updateVacationEndDate(vacationInput.closest('form'));
            validateVacationDays(vacationInput.closest('form'));
        }

        const vacationPersonal = event.target.closest('select[data-vacation-eligible]');

        if (vacationPersonal) {
            updateVacationAvailableDays(vacationPersonal.closest('form'));
        }

        const attendanceDate = event.target.closest('input[name="fecha_asistencia"]');

        if (attendanceDate) {
            updateAsistenciaType(attendanceDate.closest('form'));
        }

        const scheduleGroup = event.target.closest('.js-schedule-group-select');

        if (scheduleGroup) {
            if (!window.jQuery || !window.jQuery(scheduleGroup).data('select2')) {
                loadScheduleGroup(scheduleGroup);
            }
        }

        const scheduleWatch = event.target.closest('.js-schedule-watch');

        if (scheduleWatch) {
            syncScheduleHelperFields(scheduleWatch.closest('form'));
            resetScheduleValidation(scheduleWatch.closest('form'));
        }

        const groupWatch = event.target.closest('.js-group-watch');

        if (groupWatch) {
            syncGrupoPersonalHelperFields(groupWatch.closest('form'));
            updateDayOptionState(groupWatch);
            validateGrupoPersonalAvailability(groupWatch.closest('form'));
        }

        const massScheduleFilter = event.target.closest('.js-mass-shift-filter, .js-mass-zone-filter');

        if (massScheduleFilter) {
            filterMassScheduleRows();
        }
    });

    document.addEventListener('change', function (event) {
        const fileInput = event.target.closest('.js-image-input, .js-photo-input');

        if (!fileInput) {
            return;
        }

        renderFilePreview(fileInput);
    });

    document.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.js-remove-selected-file');

        if (!removeButton) {
            return;
        }

        const input = document.querySelector(removeButton.dataset.input);
        const index = Number.parseInt(removeButton.dataset.index, 10);

        if (!input || !Number.isFinite(index)) {
            return;
        }

        removeSelectedFile(input, index);
    });

    document.addEventListener('input', function (event) {
        const picker = event.target.closest('.js-color-picker');

        if (!picker) {
            return;
        }

        const form = picker.closest('form');
        const codeInput = form.querySelector('.js-color-code');
        const preview = document.getElementById(picker.dataset.preview);
        const color = picker.value.toUpperCase();

        codeInput.value = color;
        preview.style.background = color;
        preview.textContent = color;
    });

    document.addEventListener('input', function (event) {
        const input = event.target.closest('.js-color-code');

        if (!input) {
            return;
        }

        const color = input.value.toUpperCase();
        input.value = color;

        if (!/^#[0-9A-F]{6}$/.test(color)) {
            return;
        }

        const preview = document.getElementById(input.dataset.preview);
        const picker = document.getElementById(input.dataset.picker);

        if (preview) {
            preview.style.background = color;
            preview.textContent = color;
        }

        if (picker) {
            picker.value = color;
        }
    });

    document.addEventListener('input', function (event) {
        const attendanceTime = event.target.closest('input[name="hora_asistencia"]');

        if (attendanceTime) {
            updateAsistenciaTurno(attendanceTime.closest('form'));
        }

        const vacationDays = event.target.closest('.js-vacation-days');

        if (vacationDays) {
            updateVacationEndDate(vacationDays.closest('form'));
            validateVacationDays(vacationDays.closest('form'));
        }

        const scheduleWatch = event.target.closest('.js-schedule-watch');

        if (scheduleWatch) {
            syncScheduleHelperFields(scheduleWatch.closest('form'));
            resetScheduleValidation(scheduleWatch.closest('form'));
        }
    });

    document.addEventListener('click', function (event) {
        const suggestionButton = event.target.closest('.js-schedule-apply-suggestion');

        if (suggestionButton) {
            applyScheduleSuggestion(suggestionButton);
            return;
        }

        const resetModalButton = event.target.closest('[data-toggle="modal"][data-target^="#create"]');

        if (resetModalButton) {
            const target = resetModalButton.getAttribute('data-target');
            const form = target ? document.querySelector(`${target} form.js-ajax-form`) : null;

            resetCreateForm(form);
            window.setTimeout(() => resetCreateForm(form), 80);
            window.setTimeout(() => resetCreateForm(form), 250);
        }

        const validateButton = event.target.closest('.js-schedule-validate');

        if (!validateButton) {
            return;
        }

        validateScheduleAvailability(validateButton.closest('form'));
    });

    async function refreshCrudContainer() {
        const currentContainer = document.querySelector(containerSelector);

        if (!currentContainer) {
            return;
        }

        const response = await fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const html = await response.text();
        const documentFragment = new DOMParser().parseFromString(html, 'text/html');
        const nextContainer = documentFragment.querySelector(containerSelector);

        if (nextContainer) {
            currentContainer.replaceWith(nextContainer);
        }
    }

    function initCrudEnhancements() {
        document.querySelectorAll('.js-brand-select').forEach(filterModelOptions);
        document.querySelectorAll('.js-contract-type').forEach(toggleContratoEndDate);
        document.querySelectorAll('.js-vacation-days, .js-vacation-start').forEach((input) => updateVacationEndDate(input.closest('form')));
        document.querySelectorAll('input[name="hora_asistencia"]').forEach((input) => updateAsistenciaTurno(input.closest('form')));
        document.querySelectorAll('.js-attendance-type').forEach((input) => updateAsistenciaType(input.closest('form')));
        document.querySelectorAll('select[data-vacation-eligible]').forEach((select) => updateVacationAvailableDays(select.closest('form')));
        document.querySelectorAll('.js-schedule-form').forEach((form) => {
            syncScheduleHelperFields(form);
            resetScheduleValidation(form);
        });
        document.querySelectorAll('.rsu-day-checkbox').forEach(updateDayOptionState);
        document.querySelectorAll('.js-personnel-group-form').forEach((form) => {
            syncGrupoPersonalHelperFields(form);
            validateGrupoPersonalAvailability(form);
        });
        if (document.querySelector('.js-mass-shift-filter, .js-mass-zone-filter')) {
            filterMassScheduleRows();
        }
        bindCreateFormReset();
        window.RsuZonas?.init();

        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery('.js-select2').each(function () {
                const select = window.jQuery(this);

                if (select.data('select2')) {
                    return;
                }

                const modal = select.closest('.modal');
                const isScheduleModalSelect = Boolean(this.closest('#createProgramacionModal'));
                const options = {
                    width: '100%',
                    theme: 'bootstrap4',
                    language: 'es',
                    placeholder: select.data('placeholder') || 'Seleccione una opción',
                    allowClear: false,
                    minimumResultsForSearch: 0,
                    dropdownParent: isScheduleModalSelect || !modal.length ? window.jQuery(document.body) : modal,
                };

                if (select.hasClass('js-employee-select') && select.data('url')) {
                    options.minimumInputLength = 0;
                    options.ajax = {
                        url: select.data('url'),
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({
                            q: params.term || '',
                            vacation_eligible: select.data('vacation-eligible') ? 1 : 0,
                        }),
                        processResults: (data) => data,
                        cache: true,
                    };
                }

                select.select2(options);

                select.off('select2:select.rsu').on('select2:select.rsu', function (event) {
                    if (!select.data('vacation-eligible')) {
                        return;
                    }

                    const selected = event.params.data || {};
                    const option = this.querySelector(`option[value="${selected.id}"]`);

                    if (option && selected.dias_disponibles !== undefined) {
                        option.dataset.availableDays = selected.dias_disponibles;
                    }

                    updateVacationAvailableDays(this.closest('form'), selected.dias_disponibles);
                });

                select.off('select2:select.rsuAsistencia').on('select2:select.rsuAsistencia', function () {
                    if (!this.dataset.attendanceTypeUrl) {
                        return;
                    }

                    updateAsistenciaType(this.closest('form'));
                });

                select.off('select2:select.rsuScheduleGroup').on('select2:select.rsuScheduleGroup', function () {
                    if (!this.classList.contains('js-schedule-group-select')) {
                        return;
                    }

                    closeSelect2(this);
                    loadScheduleGroup(this);
                });

                select.off('select2:select.rsuWatched select2:clear.rsuWatched').on('select2:select.rsuWatched select2:clear.rsuWatched', function () {
                    closeSelect2(this);

                    if (this.classList.contains('js-group-watch')) {
                        validateGrupoPersonalAvailability(this.closest('form'));
                    }

                    if (this.classList.contains('js-schedule-watch')) {
                        resetScheduleValidation(this.closest('form'));
                    }
                });

                select.off('select2:open.rsuModalPosition');
            });
        }
    }

    function updateAsistenciaTurno(form) {
        if (!form) {
            return;
        }

        const time = form.querySelector('input[name="hora_asistencia"]');
        const shiftName = form.querySelector('.js-attendance-shift-name');
        const shiftId = form.querySelector('.js-attendance-shift-id');

        if (!time || !shiftName || !shiftId || !time.value) {
            return;
        }

        const turnos = JSON.parse(shiftName.dataset.turnos || '[]');
        const shift = turnos.find((item) => {
            if (item.start <= item.end) {
                return time.value >= item.start && time.value <= item.end;
            }

            return time.value >= item.start || time.value <= item.end;
        });

        if (!shift) {
            shiftId.value = '';
            shiftName.value = 'No se encontro turno para la hora ingresada';
            return;
        }

        shiftId.value = shift.id;
        shiftName.value = `${shift.name} (${shift.start} - ${shift.end})`;
    }

    function filterMassScheduleRows() {
        const selectedTurno = String(document.querySelector('.js-mass-shift-filter')?.value || '');
        const selectedZona = String(document.querySelector('.js-mass-zone-filter')?.value || '');
        const rows = Array.from(document.querySelectorAll('.js-mass-group-row'));
        let visible = 0;

        rows.forEach((row) => {
            const matchesTurno = !selectedTurno || String(row.dataset.shiftId || '') === selectedTurno;
            const matchesZona = !selectedZona || String(row.dataset.zoneId || '') === selectedZona;
            const matches = matchesTurno && matchesZona;
            row.classList.toggle('d-none', !matches);

            if (matches) {
                visible++;
            }
        });

        document.querySelectorAll('.js-mass-visible').forEach((target) => {
            target.textContent = String(visible);
        });

        document.querySelectorAll('.js-mass-shift-state').forEach((input) => {
            input.value = selectedTurno;
        });

        document.querySelectorAll('.js-mass-zone-state').forEach((input) => {
            input.value = selectedZona;
        });
    }

    async function updateAsistenciaType(form) {
        if (!form) {
            return;
        }

        const employee = form.querySelector('select[name="personal_id"]');
        const date = form.querySelector('input[name="fecha_asistencia"]');
        const attendanceId = form.querySelector('.js-attendance-id');
        const type = form.querySelector('.js-attendance-type');
        const label = form.querySelector('.js-attendance-type-label');

        if (!employee || !date || !type || !label || !employee.value || !date.value || !employee.dataset.attendanceTypeUrl) {
            return;
        }

        const params = new URLSearchParams({
            personal_id: employee.value,
            fecha_asistencia: date.value,
        });

        if (attendanceId?.value) {
            params.set('attendance_id', attendanceId.value);
        }

        try {
            const response = await fetch(`${employee.dataset.attendanceTypeUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            type.value = payload.type;
            label.value = payload.label;
        } catch (error) {
            // The backend recalculates the type on save; the UI hint can fail silently.
        }
    }

    function filterModelOptions(brandSelect) {
        const form = brandSelect.closest('form');
        const modelSelect = form.querySelector('.js-model-select');

        if (!modelSelect) {
            return;
        }

        const brandId = brandSelect.value;
        let selectedOptionStillVisible = false;

        Array.from(modelSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const visible = option.dataset.brand === brandId;
            option.hidden = !visible;
            option.disabled = !visible;

            if (option.selected && visible) {
                selectedOptionStillVisible = true;
            }
        });

        if (!selectedOptionStillVisible) {
            modelSelect.value = '';
        }

        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery(modelSelect).trigger('change.select2');
        }
    }

    function toggleContratoEndDate(contractType) {
        const form = contractType.closest('form');
        const endDate = form.querySelector('.js-contract-end-date');
        const trial = form.querySelector('.js-contract-trial');
        const isTemporary = contractType.value === 'temporary';

        if (endDate) {
            endDate.disabled = !isTemporary;
            endDate.required = isTemporary;

            if (!isTemporary) {
                endDate.value = '';
            }
        }

        if (trial) {
            trial.disabled = isTemporary;

            if (isTemporary) {
                trial.value = '';
            }
        }
    }

    function updateVacationEndDate(form) {
        if (!form) {
            return;
        }

        const start = form.querySelector('.js-vacation-start');
        const days = form.querySelector('.js-vacation-days');
        const end = form.querySelector('.js-vacation-end');

        if (!start || !days || !end || !start.value || !days.value) {
            return;
        }

        const daysCount = Number.parseInt(days.value, 10);

        if (!Number.isFinite(daysCount) || daysCount < 1) {
            return;
        }

        const date = new Date(`${start.value}T00:00:00`);
        date.setDate(date.getDate() + daysCount - 1);
        end.value = date.toISOString().slice(0, 10);
    }

    function updateVacationAvailableDays(form, selectedAvailableDays = null) {
        if (!form) {
            return;
        }

        const select = form.querySelector('select[data-vacation-eligible]');
        const days = form.querySelector('.js-vacation-days');
        const label = form.querySelector('.js-vacation-available-text');

        if (!select || !days) {
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const availableDays = selectedAvailableDays ?? selectedOption?.dataset.availableDays;

        if (availableDays === undefined || availableDays === null || availableDays === '') {
            if (label) {
                label.textContent = 'Seleccione personal para ver sus días disponibles.';
            }
            days.max = 30;
            validateVacationDays(form);
            return;
        }

        days.max = availableDays;

        if (label) {
            label.textContent = `Días disponibles: ${availableDays}`;
        }

        validateVacationDays(form);
    }

    function validateVacationDays(form) {
        if (!form) {
            return;
        }

        const days = form.querySelector('.js-vacation-days');
        const help = form.querySelector('.js-vacation-days-help');

        if (!days) {
            return;
        }

        const requested = Number.parseInt(days.value, 10);
        const available = Number.parseInt(days.max, 10);

        days.setCustomValidity('');
        days.classList.remove('is-invalid');

        if (help) {
            help.classList.remove('text-danger');
            help.textContent = 'No debe superar los días disponibles del personal.';
        }

        if (!Number.isFinite(requested) || !Number.isFinite(available) || requested <= available) {
            return;
        }

        const message = `El personal seleccionado solo tiene ${available} días disponibles.`;

        days.setCustomValidity(message);
        days.classList.add('is-invalid');

        if (help) {
            help.classList.add('text-danger');
            help.textContent = message;
        }
    }

    function bindCreateFormReset() {
        if (!window.jQuery) {
            return;
        }

        window.jQuery('.modal[id^="create"] form.js-ajax-form, form[data-reset-on-open="true"]').each(function () {
            const form = this;
            const modal = window.jQuery(form).closest('.modal');

            modal.off('show.bs.modal.rsuReset shown.bs.modal.rsuReset').on('show.bs.modal.rsuReset shown.bs.modal.rsuReset', function () {
                resetCreateForm(form);
                window.setTimeout(() => resetCreateForm(form), 80);
                window.setTimeout(() => resetCreateForm(form), 250);
            });
        });
    }

    function resetCreateForm(form) {
        if (!form) {
            return;
        }

        form.reset();
        form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea').forEach((field) => {
            field.value = '';
        });
        form.querySelectorAll('select').forEach((select) => {
            setSelectValue(select, '');
        });
        form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach((field) => {
            field.checked = false;
        });
        form.querySelectorAll('[id$="StatusCreate"], [data-default-checked="true"]').forEach((field) => {
            field.checked = true;
        });
        form.dataset.groupAvailable = 'false';
        form.dataset.scheduleValidated = 'false';
        form.querySelector('.js-group-submit')?.setAttribute('disabled', 'disabled');
        form.querySelector('.js-schedule-submit')?.setAttribute('disabled', 'disabled');
        clearFormErrors(form);
        clearGrupoPersonalAvailability(form);

        form.querySelectorAll('.rsu-day-checkbox').forEach(updateDayOptionState);
        syncGrupoPersonalHelperFields(form);
        syncScheduleHelperFields(form);
        updateAsistenciaTurno(form);
        updateAsistenciaType(form);
        updateVacationEndDate(form);
        validateVacationDays(form);
    }

    function vehicleCapacityFor(select) {
        if (!select?.value) {
            return null;
        }

        const capacities = JSON.parse(select.dataset.vehicleCapacities || '{}');
        const capacity = Number.parseInt(capacities[select.value] || '1', 10);

        return Number.isFinite(capacity) && capacity > 0 ? capacity : 1;
    }

    function expectedHelpersForVehiculo(select) {
        const capacity = vehicleCapacityFor(select);

        return capacity === null ? 0 : Math.max(capacity - 1, 0);
    }

    function helperCapacityText(capacity) {
        if (capacity === null) {
            return 'Seleccione un vehículo para calcular conductor y ayudantes requeridos.';
        }

        const helpers = Math.max(capacity - 1, 0);

        return `Capacidad para 1 conductor + ${helpers} ayudante(s). Total: ${capacity} persona(s).`;
    }

    function syncGrupoPersonalHelperFields(form) {
        if (!form) {
            return;
        }

        const vehicle = form.querySelector('.js-group-vehicle');
        const expected = expectedHelpersForVehiculo(vehicle);
        const capacity = vehicleCapacityFor(vehicle);
        const hint = form.querySelector('.js-group-vehicle-capacity');
        const helperHint = form.querySelector('.js-group-helper-help');
        const teamSection = form.querySelector('.rsu-team-section');
        const hasTurno = Boolean(form.querySelector('[name="turno_id"]')?.value);
        const hasDays = Array.from(form.querySelectorAll('[name="dias_semana[]"]:checked')).length > 0;

        if (hint) {
            hint.textContent = helperCapacityText(capacity);
        }

        if (helperHint) {
            if (capacity === null) {
                helperHint.textContent = 'Seleccione un vehículo para calcular los ayudantes requeridos.';
            } else if (!hasTurno || !hasDays) {
                helperHint.textContent = `Se muestran ${expected} ayudante(s) por la capacidad del vehículo. Complete turno y días para validar disponibilidad.`;
            } else {
                helperHint.textContent = `Se requieren ${expected} ayudante(s). La disponibilidad se validará automáticamente.`;
            }
        }

        if (teamSection) {
            teamSection.classList.toggle('is-pending', capacity !== null && (!hasTurno || !hasDays));
            teamSection.classList.toggle('is-ready', capacity !== null && hasTurno && hasDays);
        }

        syncHelperWrappers(form.querySelectorAll('.js-group-helper-wrapper'), expected);
    }

    function syncScheduleHelperFields(form) {
        if (!form) {
            return;
        }

        const vehicle = form.querySelector('.js-schedule-vehicle');
        const expected = expectedHelpersForVehiculo(vehicle);
        const capacity = vehicleCapacityFor(vehicle);
        const capacityHint = form.querySelector('.js-schedule-vehicle-capacity');
        const helperHint = form.querySelector('.js-schedule-helper-help');

        if (capacityHint) {
            capacityHint.textContent = helperCapacityText(capacity);
        }

        if (helperHint) {
            helperHint.textContent = capacity === null
                ? 'Seleccione un vehículo para calcular los ayudantes requeridos.'
                : `La programación debe contar exactamente con ${expected} ayudante(s).`;
        }

        syncHelperWrappers(form.querySelectorAll('.js-schedule-helper-wrapper'), expected);
    }

    function syncHelperWrappers(wrappers, expected) {
        Array.from(wrappers).forEach((wrapper, index) => {
            const select = wrapper.querySelector('select[name="helper_ids[]"]');
            const visible = index < expected;

            wrapper.classList.toggle('d-none', !visible);

            if (!select) {
                return;
            }

            select.disabled = !visible;
            select.required = visible;

            if (!visible) {
                setSelectValue(select, '');
                wrapper.querySelector('.js-group-helper-availability')?.replaceChildren();
            }
        });
    }

    async function loadScheduleGroup(select) {
        const form = select.closest('form');

        if (!form || !select.value || !form.dataset.groupUrlTemplate) {
            return;
        }

        if (form.dataset.loadingGroupId === String(select.value)) {
            return;
        }

        form.dataset.loadingGroupId = String(select.value);
        closeSelect2(select);
        resetScheduleValidation(form);

        try {
            const response = await fetch(form.dataset.groupUrlTemplate.replace('__GROUP__', select.value), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const group = await response.json();
            setSelectValue(form.querySelector('.js-schedule-shift'), group.turno_id);
            setSelectValue(form.querySelector('.js-schedule-zone'), group.zona_id);
            setSelectValue(form.querySelector('.js-schedule-vehicle'), group.vehiculo_id);
            syncScheduleHelperFields(form);
            setSelectValue(form.querySelector('.js-schedule-driver'), group.conductor_id);
            form.querySelectorAll('.js-schedule-helper-select').forEach((helperSelect, index) => {
                setSelectValue(helperSelect, (group.helper_ids || [])[index] || '');
            });

            form.querySelectorAll('.js-schedule-day').forEach((checkbox) => {
                checkbox.checked = (group.dias_semana || []).map(Number).includes(Number(checkbox.value));
            });

            syncScheduleHelperFields(form);
        } catch (error) {
            showFlash('No se pudo cargar el grupo seleccionado.', 'danger');
        } finally {
            delete form.dataset.loadingGroupId;
            closeSelect2(select);
        }
    }

    async function validateScheduleAvailability(form) {
        if (!form || !form.dataset.validateUrl) {
            return;
        }

        const result = form.querySelector('.js-schedule-validation-result');
        const button = form.querySelector('.js-schedule-validate');

        closeAllSelect2(form);
        resetScheduleValidation(form, false);

        if (!validateScheduleRequiredFields(form)) {
            return;
        }

        if (button) {
            button.disabled = true;
            button.dataset.originalText = button.dataset.originalText || button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Validando';
        }

        try {
            const response = await fetch(form.dataset.validateUrl, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok || !payload.available) {
                showScheduleValidationResult(form, payload, false);
                closeAllSelect2(form);
                return;
            }

            form.dataset.scheduleValidated = 'true';
            form.querySelector('.js-schedule-submit')?.removeAttribute('disabled');
            showScheduleValidationResult(form, payload, true);
            closeAllSelect2(form);
        } catch (error) {
            if (result) {
                result.innerHTML = '<div class="alert alert-danger">No se pudo validar la disponibilidad.</div>';
            }
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText;
            }
        }
    }

    function validateScheduleRequiredFields(form) {
        const errors = [];
        const requiredFields = [
            ['grupo_personal_id', 'Seleccione un grupo de personal.'],
            ['fecha_inicio', 'Ingrese la fecha de inicio.'],
            ['fecha_fin', 'Ingrese la fecha de fin.'],
            ['turno_id', 'Seleccione un turno.'],
            ['zona_id', 'Seleccione una zona.'],
            ['vehiculo_id', 'Seleccione un vehículo.'],
            ['conductor_id', 'Seleccione un conductor.'],
        ];

        requiredFields.forEach(([name, message]) => {
            if (!form.querySelector(`[name="${name}"]`)?.value) {
                errors.push(message);
            }
        });

        const expectedHelpers = expectedHelpersForVehiculo(form.querySelector('.js-schedule-vehicle'));
        const helpers = Array.from(form.querySelectorAll('[name="helper_ids[]"]:not(:disabled)'))
            .map((select) => select.value)
            .filter(Boolean);

        if (helpers.length !== expectedHelpers) {
            errors.push(`Seleccione ${expectedHelpers} ayudante(s) según la capacidad del vehículo.`);
        } else if (new Set(helpers).size !== helpers.length) {
            errors.push('Los ayudantes deben ser personas diferentes.');
        }

        if (!form.querySelectorAll('[name="dias_semana[]"]:checked').length) {
            errors.push('Seleccione al menos un día programable.');
        }

        if (errors.length) {
            showScheduleValidationResult(form, {
                message: 'Completa los datos requeridos antes de validar.',
                errors: { required: errors },
                suggestions: [],
            }, false);

            focusFirstScheduleIssue(form);
            return false;
        }

        return true;
    }

    function focusFirstScheduleIssue(form) {
        const firstEmpty = [
            'grupo_personal_id',
            'fecha_inicio',
            'fecha_fin',
            'turno_id',
            'zona_id',
            'vehiculo_id',
            'conductor_id',
            'helper_ids[]',
        ].map((name) => form.querySelector(`[name="${name}"]`)).find((field) => field && !field.value);

        if (!firstEmpty) {
            return;
        }

        const select2Container = firstEmpty.nextElementSibling?.classList.contains('select2')
            ? firstEmpty.nextElementSibling
            : null;

        (select2Container || firstEmpty).scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showScheduleValidationResult(form, payload, valid) {
        const result = form.querySelector('.js-schedule-validation-result');

        if (!result) {
            return;
        }

        const issues = payload.issues || Object.values(payload.errors || {}).flat();
        const dates = payload.dates || [];
        const suggestions = payload.suggestions || [];
        const warnings = payload.warnings || [];
        const warningBlock = warnings.length
            ? `
                <div class="alert alert-warning mt-2">
                    <strong><i class="fas fa-exclamation-triangle mr-1"></i> Fechas omitidas</strong>
                    <ul class="mb-0 pl-3">
                        ${warnings.map((warning) => `<li>${escapeHtml(warning)}</li>`).join('')}
                    </ul>
                </div>
            `
            : '';

        if (valid) {
            result.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-1"></i>
                    ${escapeHtml(payload.message || 'Disponibilidad validada correctamente.')}
                <div class="small mt-1">Se generarán ${escapeHtml(payload.count || dates.length)} programación(es).</div>
                </div>
            `;
            return;
        }

        result.innerHTML = `
            <div class="alert alert-danger">
                <strong>${escapeHtml(payload.message || 'Se encontraron inconsistencias.')}</strong>
                <ul class="mb-0 pl-3">
                    ${(issues.length ? issues : ['Revisa los datos ingresados.']).map((issue) => `<li>${escapeHtml(issue)}</li>`).join('')}
                </ul>
            </div>
            ${warningBlock}
            ${renderScheduleSuggestions(suggestions)}
        `;
    }

    function renderScheduleSuggestions(suggestions) {
        if (!suggestions.length) {
            return '';
        }

        const blocks = suggestions.map((suggestion) => {
            const replacements = suggestion.replacements || [];
            const replacementItems = replacements.length
                ? replacements.map((person) => `
                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm js-schedule-apply-suggestion"
                        data-role="${escapeHtml(suggestion.role)}"
                        data-index="${escapeHtml(suggestion.index ?? '')}"
                        data-person-id="${escapeHtml(person.id)}"
                    >
                        <i class="fas fa-user-check mr-1"></i> Usar ${escapeHtml(person.label)}
                    </button>
                `).join('')
                : '<span class="text-muted small">No se encontraron reemplazos disponibles para este rol.</span>';

            return `
                <div class="rsu-suggestion-block">
                    <div>
                        <strong>${escapeHtml(suggestion.label)}</strong>
                        <div class="small text-muted">Actual: ${escapeHtml(suggestion.current || '-')}</div>
                    </div>
                    <div class="rsu-suggestion-actions">${replacementItems}</div>
                </div>
            `;
        }).join('');

        return `
            <div class="alert alert-info">
                <strong><i class="fas fa-lightbulb mr-1"></i> Sugerencias</strong>
                <div class="mt-2">${blocks}</div>
            </div>
        `;
    }

    function applyScheduleSuggestion(button) {
        const form = button.closest('form');

        if (!form) {
            return;
        }

        const personId = button.dataset.personId;
        const target = button.dataset.role === 'driver'
            ? form.querySelector('.js-schedule-driver')
            : form.querySelectorAll('.js-schedule-helper-select')[Number.parseInt(button.dataset.index || '0', 10)];

        if (!target || !personId) {
            return;
        }

        setSelectValue(target, personId);
        resetScheduleValidation(form);
        target.closest('.form-group')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function resetScheduleValidation(form, clearResult = true) {
        if (!form || !form.classList.contains('js-schedule-form')) {
            return;
        }

        form.dataset.scheduleValidated = 'false';
        form.querySelector('.js-schedule-submit')?.setAttribute('disabled', 'disabled');

        if (clearResult) {
            const result = form.querySelector('.js-schedule-validation-result');

            if (result) {
                result.innerHTML = '';
            }
        }
    }

    function setSelectValue(select, value) {
        if (!select) {
            return;
        }

        if (Array.isArray(value)) {
            Array.from(select.options).forEach((option) => {
                option.selected = value.map(String).includes(String(option.value));
            });
        } else {
            select.value = value || '';
        }

        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery(select).trigger('change.select2');
        }
    }

    function closeSelect2(select) {
        if (!select || !window.jQuery || !window.jQuery.fn.select2) {
            return;
        }

        const instance = window.jQuery(select);

        if (instance.data('select2')) {
            instance.select2('close');
        }
    }

    function closeAllSelect2(parent) {
        if (!parent || !window.jQuery || !window.jQuery.fn.select2) {
            return;
        }

        parent.querySelectorAll('.js-select2').forEach(closeSelect2);
    }

    async function validateGrupoPersonalAvailability(form) {
        if (!form || !form.classList.contains('js-personnel-group-form') || !form.dataset.validateUrl) {
            return;
        }

        const result = form.querySelector('.js-group-availability-result');
        const submit = form.querySelector('.js-group-submit');
        const driverResult = form.querySelector('.js-group-driver-availability');
        const helperResults = Array.from(form.querySelectorAll('.js-group-helper-wrapper:not(.d-none) .js-group-helper-availability'));
        const expectedHelpers = expectedHelpersForVehiculo(form.querySelector('.js-group-vehicle'));
        const helperValues = selectedGroupHelperValues(form);

        if (helperValues.length === expectedHelpers && new Set(helperValues).size !== helperValues.length) {
            form.dataset.groupAvailable = 'false';
            submit?.setAttribute('disabled', 'disabled');
            clearGrupoPersonalAvailability(form);
            helperResults.forEach((helperResult) => {
                helperResult.innerHTML = '<div class="alert alert-danger py-2 mb-2">Los ayudantes deben ser personas diferentes.</div>';
            });
            return;
        }

        if (!result || !isGroupAvailabilityReady(form)) {
            form.dataset.groupAvailable = 'false';
            submit?.setAttribute('disabled', 'disabled');
            clearGrupoPersonalAvailability(form);
            return;
        }

        form.dataset.groupAvailable = 'false';
        submit?.setAttribute('disabled', 'disabled');
        result.innerHTML = '';

        if (driverResult) {
            driverResult.innerHTML = '<div class="alert alert-info py-2 mb-0"><i class="fas fa-spinner fa-spin mr-1"></i> Validando conductor...</div>';
        }

        helperResults.forEach((helperResult, index) => {
            helperResult.innerHTML = `<div class="alert alert-info py-2 mb-0"><i class="fas fa-spinner fa-spin mr-1"></i> Validando ayudante ${index + 1}...</div>`;
        });

        try {
            const formData = new FormData(form);
            formData.delete('_method');

            const response = await fetch(form.dataset.validateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json().catch(() => ({}));
            const people = payload.people || [];
            const generalIssues = payload.general_issues || Object.values(payload.errors || {}).flat();
            const driver = people.find((person) => person.role === 'driver');
            const helpers = people.filter((person) => person.role === 'helper');

            if (driverResult) {
                driverResult.innerHTML = driver ? renderPersonnelAvailabilityAlert(driver) : '';
            }

            helperResults.forEach((helperResult, index) => {
                helperResult.innerHTML = helpers[index] ? renderPersonnelAvailabilityAlert(helpers[index]) : '';
            });

            result.innerHTML = generalIssues.length
                ? `
                    <div class="alert alert-${response.ok && payload.available ? 'success' : 'danger'}">
                        <strong>${escapeHtml(payload.message || 'Disponibilidad validada.')}</strong>
                        <ul class="mb-0 mt-2 pl-3">${generalIssues.map((issue) => `<li>${escapeHtml(issue)}</li>`).join('')}</ul>
                    </div>
                `
                : '';

            form.dataset.groupAvailable = response.ok && payload.available ? 'true' : 'false';

            if (response.ok && payload.available) {
                submit?.removeAttribute('disabled');
            } else {
                submit?.setAttribute('disabled', 'disabled');
            }
        } catch (error) {
            form.dataset.groupAvailable = 'false';
            submit?.setAttribute('disabled', 'disabled');
            if (driverResult) {
                driverResult.innerHTML = '';
            }
            helperResults.forEach((helperResult) => {
                helperResult.innerHTML = '';
            });
            result.innerHTML = '<div class="alert alert-danger">No se pudo validar la disponibilidad del grupo.</div>';
        }
    }

    function clearGrupoPersonalAvailability(form) {
        form.querySelector('.js-group-availability-result')?.replaceChildren();
        form.querySelector('.js-group-driver-availability')?.replaceChildren();
        form.querySelectorAll('.js-group-helper-availability').forEach((element) => element.replaceChildren());
    }

    function renderPersonnelAvailabilityAlert(person) {
        return `
            <div class="alert alert-${person.available ? 'success' : 'danger'} py-2 mb-2">
                <strong><i class="fas fa-${person.available ? 'check-circle' : 'exclamation-circle'} mr-1"></i>${escapeHtml(person.name)}</strong>
                <div class="small">DNI: ${escapeHtml(person.dni)} | ${escapeHtml(person.type || 'Personal')}</div>
                <div class="small">${escapeHtml(person.message)}</div>
            </div>
        `;
    }

    function updateDayOptionState(input) {
        if (!input?.classList?.contains('rsu-day-checkbox')) {
            return;
        }

        input.closest('.rsu-day-option')?.classList.toggle('is-selected', input.checked);
    }

    function isGroupAvailabilityReady(form) {
        const hasTurno = Boolean(form.querySelector('[name="turno_id"]')?.value);
        const hasVehiculo = Boolean(form.querySelector('[name="vehiculo_id"]')?.value);
        const hasDriver = Boolean(form.querySelector('[name="conductor_id"]')?.value);
        const helperValues = selectedGroupHelperValues(form);
        const expectedHelpers = expectedHelpersForVehiculo(form.querySelector('.js-group-vehicle'));
        const hasHelpers = helperValues.length === expectedHelpers && new Set(helperValues).size === helperValues.length;
        const hasDays = Array.from(form.querySelectorAll('[name="dias_semana[]"]:checked')).length > 0;

        return hasTurno && hasVehiculo && hasDriver && hasHelpers && hasDays;
    }

    function selectedGroupHelperValues(form) {
        return Array.from(form.querySelectorAll('[name="helper_ids[]"]:not(:disabled)'))
            .map((select) => select.value)
            .filter(Boolean);
    }

    function renderFilePreview(fileInput) {
        const preview = document.querySelector(fileInput.dataset.preview);

        if (!preview) {
            return;
        }

        preview.innerHTML = '';
        ensureFileInputId(fileInput);

        Array.from(fileInput.files || []).forEach((file, index) => {
            const card = document.createElement('div');
            const isValid = isValidImageFile(file);

            card.className = `rsu-upload-preview-card ${isValid ? '' : 'is-invalid-file'}`;
            card.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger js-remove-selected-file" data-input="#${fileInput.id}" data-index="${index}" title="Quitar archivo">
                    <i class="fas fa-times"></i>
                </button>
                <div class="rsu-upload-preview-thumb">
                    <i class="${isValid ? 'fas fa-image' : 'fas fa-exclamation-triangle'}"></i>
                </div>
                <div class="rsu-upload-preview-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                <small class="${isValid ? 'text-muted' : 'text-danger'}">${isValid ? formatFileSize(file.size) : 'Formato no permitido'}</small>
            `;
            preview.appendChild(card);

            if (isValid) {
                const reader = new FileReader();
                reader.onload = (readerEvent) => {
                    const thumb = card.querySelector('.rsu-upload-preview-thumb');
                    thumb.innerHTML = '';
                    const image = document.createElement('img');
                    image.src = readerEvent.target.result;
                    image.alt = file.name;
                    image.className = 'rsu-upload-preview-img';
                    thumb.appendChild(image);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function removeSelectedFile(input, indexToRemove) {
        const transfer = new DataTransfer();

        Array.from(input.files || []).forEach((file, index) => {
            if (index !== indexToRemove) {
                transfer.items.add(file);
            }
        });

        input.files = transfer.files;
        renderFilePreview(input);
    }

    function clearInvalidFileInputs(form, payload) {
        const errorKeys = Object.keys(payload.errors || {});
        const hasImageError = errorKeys.some((key) => key === 'images' || key.startsWith('images.'));

        if (!hasImageError) {
            return;
        }

        form.querySelectorAll('.js-image-input').forEach((input) => {
            const validFiles = Array.from(input.files || []).filter(isValidImageFile);
            const transfer = new DataTransfer();
            validFiles.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            renderFilePreview(input);
        });
    }

    function isValidImageFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        const extension = file.name.split('.').pop()?.toLowerCase();
        const maxSize = 2 * 1024 * 1024;

        return allowedTypes.includes(file.type) && allowedExtensions.includes(extension) && file.size <= maxSize;
    }

    function ensureFileInputId(input) {
        if (!input.id) {
            input.id = `file-input-${Math.random().toString(36).slice(2)}`;
        }
    }

    function formatFileSize(size) {
        if (size < 1024) {
            return `${size} B`;
        }

        if (size < 1024 * 1024) {
            return `${(size / 1024).toFixed(1)} KB`;
        }

        return `${(size / (1024 * 1024)).toFixed(1)} MB`;
    }

    function normalizeErrorPayload(payload, status) {
        if (payload.errors || status < 500) {
            return payload;
        }

        return {
            message: payload.message || 'No se pudo completar la operación. Revisa los datos relacionados e intenta nuevamente.',
            errors: {},
        };
    }

    function setFormBusy(form, busy) {
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = busy;
            button.dataset.originalText = button.dataset.originalText || button.innerHTML;
            button.innerHTML = busy
                ? '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando'
                : button.dataset.originalText;
        });
    }

    function clearFormErrors(form) {
        form.querySelectorAll('.js-form-errors').forEach((element) => element.remove());
        form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
    }

    function showFormErrors(form, payload) {
        const message = payload.message || 'Revisa los datos ingresados.';
        const errors = Object.values(payload.errors || {}).flat();
        const listItems = errors
            .map((error) => `<li>${escapeHtml(error)}</li>`)
            .join('');
        const alert = document.createElement('div');

        alert.className = 'alert alert-danger js-form-errors';
        alert.innerHTML = errors.length
            ? `<strong>${escapeHtml(message)}</strong><ul class="mb-0 pl-3">${listItems}</ul>`
            : `<strong>${escapeHtml(message)}</strong>`;

        const modalBody = form.querySelector('.modal-body');
        (modalBody || form).prepend(alert);

        Object.keys(payload.errors || {}).forEach((field) => {
            const input = form.querySelector(`[name="${field}"]`);

            if (input) {
                input.classList.add('is-invalid');
            }
        });
    }

    function showFlash(message, type) {
        const flash = document.querySelector('.js-flash-messages');

        if (!flash) {
            return;
        }

        flash.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-1"></i>
                ${escapeHtml(message)}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
    }

    function showInlineConfirm(form, message) {
        const flash = document.querySelector('.js-flash-messages');

        if (!flash) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
            return;
        }

        pendingConfirmForm = form;
        flash.innerHTML = `
            <div class="alert alert-warning fade show js-inline-confirm d-flex justify-content-between align-items-center flex-wrap" role="alert">
                <span class="mr-3">
                    <i class="fas fa-question-circle mr-1"></i>
                    ${escapeHtml(message)}
                </span>
                <span class="mt-2 mt-sm-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1 js-confirm-cancel">
                        No
                    </button>
                    <button type="button" class="btn btn-sm btn-success js-confirm-accept">
                        Si, continuar
                    </button>
                </span>
            </div>
        `;
    }

    function clearInlineConfirm() {
        document.querySelectorAll('.js-inline-confirm').forEach((alert) => alert.remove());
    }

    function closeOpenModal(form) {
        const modal = form.closest('.modal');

        if (!modal || !window.jQuery) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            const jqueryModal = window.jQuery(modal);

            if (!modal.classList.contains('show')) {
                resolve();
                return;
            }

            jqueryModal.one('hidden.bs.modal', resolve);
            jqueryModal.modal('hide');
            window.setTimeout(resolve, 400);
        });
    }

    function cleanupModalState() {
        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    initCrudEnhancements();
})();
