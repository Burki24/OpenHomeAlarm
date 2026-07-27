'use strict';

let ohaState = window.OHA_INITIAL_STATE ?? null;
let ohaCodeBuffer = '';
let ohaCodeBusy = false;
let ohaCodeRequestTimer = null;

function ohaTranslate(text) {
    return typeof translate === 'function' ? translate(text) : text;
}

function ohaStateCaption(name) {
    const captions = {
        disarmed: 'Disarmed',
        exit_delay: 'Exit delay',
        armed: 'Armed',
        entry_delay: 'Entry delay',
        alarm: 'Alarm'
    };

    return ohaTranslate(captions[name] ?? 'Unknown state');
}

function ohaModeCaption(name) {
    const captions = {
        none: 'No arming mode',
        home: 'Home',
        away: 'Away',
        night: 'Night'
    };

    return ohaTranslate(captions[name] ?? 'Unknown mode');
}

function ohaReasonCaption(reason) {
    const captions = {
        triggered: 'Triggered',
        unavailable: 'Unavailable',
        active: 'Active'
    };

    return ohaTranslate(captions[reason] ?? reason);
}

function ohaModeButtonCaption(mode) {
    const captions = {
        home: 'Arm Home',
        away: 'Arm Away',
        night: 'Arm Night'
    };

    return ohaTranslate(captions[mode] ?? 'Arm');
}

function ohaStateIcon(name) {
    const icons = {
        disarmed: 'fa-shield',
        exit_delay: 'fa-timer',
        armed: 'fa-lock',
        entry_delay: 'fa-key',
        alarm: 'fa-bell'
    };

    return icons[name] ?? 'fa-shield';
}

function ohaAllModesReady(state) {
    return Boolean(state.Modes?.home?.Ready && state.Modes?.away?.Ready && state.Modes?.night?.Ready);
}

function ohaAnyModeReady(state) {
    return Boolean(state.Modes?.home?.Ready || state.Modes?.away?.Ready || state.Modes?.night?.Ready);
}

function ohaHeroMessage(state) {
    switch (state.State.Name) {
        case 'disarmed':
            if (state.Faults?.Active) {
                return ohaTranslate('Check active system faults before arming.');
            }
            if (ohaAllModesReady(state)) {
                return ohaTranslate('All arming modes are ready.');
            }
            if (ohaAnyModeReady(state)) {
                return ohaTranslate('Choose one of the available arming modes.');
            }
            return ohaTranslate('Resolve the blocking sensors before arming.');

        case 'exit_delay':
            return ohaTranslate('Leave the secured area before the countdown ends.');

        case 'armed':
            return ohaTranslate('Monitoring is active for the selected arming mode.');

        case 'entry_delay':
            return state.Delay?.Source
                ? `${ohaTranslate('Source')}: ${state.Delay.Source}`
                : ohaTranslate('Disarm the system before the countdown ends.');

        case 'alarm':
            if (state.Alarm?.LastSource) {
                return `${ohaTranslate('Source')}: ${state.Alarm.LastSource}`;
            }
            return ohaTranslate('An alarm has been triggered.');

        default:
            return '';
    }
}

function ohaHeroEyebrow(state) {
    if (state.State.Name === 'alarm') {
        return ohaTranslate('Security alarm');
    }
    if (state.State.Name === 'entry_delay' || state.State.Name === 'exit_delay') {
        return ohaTranslate('Countdown');
    }
    return ohaTranslate('Security status');
}

function ohaRenderHero(state) {
    const root = document.getElementById('ohaRoot');
    const stateName = state.State.Name;
    const countdownActive = stateName === 'exit_delay' || stateName === 'entry_delay';

    root.dataset.state = stateName;
    document.getElementById('heroEyebrow').textContent = ohaHeroEyebrow(state);
    document.getElementById('stateLabel').textContent = ohaStateCaption(stateName);
    document.getElementById('heroMessage').textContent = ohaHeroMessage(state);
    document.getElementById('modeLabel').textContent = ohaModeCaption(state.Mode.Name);
    document.getElementById('stateIcon').innerHTML = `<i class="fa-light ${ohaStateIcon(stateName)}" aria-hidden="true"></i>`;

    const countdown = document.getElementById('heroCountdown');
    countdown.hidden = !countdownActive;
    if (countdownActive) {
        document.getElementById('delayRemaining').textContent = String(Math.max(0, Number(state.Delay?.Remaining) || 0));
        document.getElementById('countdownUnit').textContent = ohaTranslate('Seconds');
    }
}

function ohaRenderMode(modeName, modeState) {
    const button = document.querySelector(`.oha-mode-button[data-mode="${modeName}"]`);
    if (!button) {
        return;
    }

    button.dataset.ready = modeState.Ready ? 'true' : 'false';
    button.dataset.canArm = modeState.CanArm ? 'true' : 'false';
    button.setAttribute('aria-disabled', modeState.CanArm ? 'false' : 'true');

    const title = button.querySelector('[data-role="mode-title"]');
    if (title) {
        title.textContent = ohaModeCaption(modeName);
    }

    const readiness = button.querySelector('[data-role="readiness"]');
    if (readiness) {
        readiness.textContent = ohaTranslate(modeState.Ready ? 'Ready' : 'Not ready');
    }

    const blockers = button.querySelector('[data-role="blockers"]');
    if (blockers) {
        blockers.replaceChildren();
        const items = Array.isArray(modeState.Blockers) ? modeState.Blockers : [];
        blockers.dataset.empty = items.length === 0 ? 'true' : 'false';

        if (items.length === 0) {
            blockers.textContent = ohaTranslate('No blocking sensors');
        } else {
            for (const blocker of items.slice(0, 2)) {
                const line = document.createElement('span');
                line.className = 'oha-blocker';
                line.textContent = `${blocker.Name} · ${ohaReasonCaption(blocker.Reason)}`;
                blockers.appendChild(line);
            }

            if (items.length > 2) {
                const more = document.createElement('span');
                more.className = 'oha-blocker';
                more.textContent = `+${items.length - 2} ${ohaTranslate('more')}`;
                blockers.appendChild(more);
            }
        }
    }

    const action = button.querySelector('[data-role="mode-action"]');
    if (action) {
        if (modeState.CanArm) {
            action.textContent = ohaTranslate('Activate');
        } else if (button.dataset.active === 'true') {
            action.textContent = ohaTranslate('Active');
        } else if (ohaState?.State?.Name === 'disarmed') {
            action.textContent = ohaTranslate('Blocked');
        } else {
            action.textContent = ohaTranslate('Deactivate first to change mode');
        }
    }
}

function ohaRenderArming(state) {
    const section = document.getElementById('armingSection');
    const isDisarmed = state.State.Name === 'disarmed';
    const activeMode = state.Mode?.Name ?? 'none';

    section.hidden = false;
    document.getElementById('armingKicker').textContent = ohaTranslate('Arming modes');
    document.getElementById('armingTitle').textContent = ohaTranslate(isDisarmed ? 'Select security mode' : 'Security zones');
    document.getElementById('armingHint').textContent = isDisarmed
        ? ohaTranslate('Select a ready mode to arm')
        : `${ohaTranslate('Active mode')}: ${ohaModeCaption(activeMode)}`;

    for (const modeName of ['home', 'away', 'night']) {
        const button = document.querySelector(`.oha-mode-button[data-mode="${modeName}"]`);
        if (button) {
            button.dataset.active = !isDisarmed && activeMode === modeName ? 'true' : 'false';
            button.setAttribute('aria-pressed', button.dataset.active);
        }
        ohaRenderMode(modeName, state.Modes[modeName]);
    }
}

function ohaRenderSummary(state) {
    const activeMode = state.Mode?.Name ?? 'none';
    const activeModeState = state.Modes?.[activeMode] ?? null;
    const monitoringActive = state.State?.Name !== 'disarmed';

    document.getElementById('summaryModeLabel').textContent = ohaTranslate('Arming mode');
    document.getElementById('summaryModeValue').textContent = ohaModeCaption(activeMode);

    document.getElementById('summaryMonitoringLabel').textContent = ohaTranslate('Monitoring');
    document.getElementById('summaryMonitoringValue').textContent = ohaTranslate(
        monitoringActive ? 'Active' : (ohaAnyModeReady(state) ? 'Ready' : 'Not ready')
    );

    document.getElementById('summaryFaultLabel').textContent = ohaTranslate('System status');
    document.getElementById('summaryFaultValue').textContent = ohaTranslate(
        state.Faults?.Active ? 'Fault active' : 'No fault'
    );

    document.getElementById('summaryMemoryLabel').textContent = ohaTranslate('Alarm memory');
    document.getElementById('summaryMemoryValue').textContent = ohaTranslate(
        state.Alarm?.MemoryActive ? 'Stored' : 'Empty'
    );

    const monitoringCard = document.getElementById('summaryMonitoringValue').closest('.oha-status-card');
    if (monitoringCard) {
        monitoringCard.dataset.tone = monitoringActive || activeModeState?.Ready ? 'success' : 'warning';
    }

    const faultCard = document.getElementById('summaryFaultValue').closest('.oha-status-card');
    if (faultCard) {
        faultCard.dataset.tone = state.Faults?.Active ? 'warning' : 'success';
    }

    const memoryCard = document.getElementById('summaryMemoryValue').closest('.oha-status-card');
    if (memoryCard) {
        memoryCard.dataset.tone = state.Alarm?.MemoryActive ? 'warning' : 'neutral';
    }
}

function ohaRenderAlarmMemory(state) {
    const panel = document.getElementById('alarmMemoryPanel');
    const memoryActive = Boolean(state.Alarm?.MemoryActive);
    const alarmActive = state.State.Name === 'alarm';

    panel.hidden = !memoryActive || alarmActive;
    if (panel.hidden) {
        return;
    }

    document.getElementById('alarmMemoryTitle').textContent = ohaTranslate('Alarm memory');
    const parts = [];
    if (state.Alarm?.LastSource) {
        parts.push(state.Alarm.LastSource);
    }
    if (state.Alarm?.LastTime) {
        parts.push(state.Alarm.LastTime);
    }
    document.getElementById('alarmMemoryDetail').textContent = parts.join(' · ') || ohaTranslate('Alarm stored');
}

function ohaRenderFaults(state) {
    const panel = document.getElementById('faultPanel');
    const faults = Array.isArray(state.Faults?.Items) ? state.Faults.Items : [];

    panel.hidden = !state.Faults?.Active;
    if (panel.hidden) {
        return;
    }

    document.getElementById('faultTitle').textContent = ohaTranslate('System fault');
    document.getElementById('faultDetail').textContent = faults.length > 0
        ? faults.map((fault) => `${fault.Name} · ${ohaReasonCaption(fault.Reason)}`).join(', ')
        : ohaTranslate('System fault active');
}

function ohaRenderBypasses(state) {
    const panel = document.getElementById('bypassPanel');
    const bypassed = Array.isArray(state.BypassedSensors) ? state.BypassedSensors : [];

    panel.hidden = bypassed.length === 0;
    if (panel.hidden) {
        return;
    }

    document.getElementById('bypassTitle').textContent = ohaTranslate('Bypassed sensors');
    document.getElementById('bypassDetail').textContent = bypassed.map((sensor) => sensor.Name).join(', ');
}

function ohaCodeInputAllowed() {
    return Boolean(ohaState?.Capabilities?.CodeRequired && ohaState?.Capabilities?.CanDisarm);
}

function ohaRenderInlineCodepad(state) {
    const panel = document.getElementById('inlineCodepad');
    const enabled = Boolean(state.Capabilities?.CodeRequired && state.Capabilities?.CanDisarm);
    const codeRequired = Boolean(state.Capabilities?.CodeRequired);

    panel.dataset.enabled = enabled ? 'true' : 'false';
    document.getElementById('inlineCodepadKicker').textContent = ohaTranslate('Security control');
    document.getElementById('inlineCodepadTitle').textContent = ohaTranslate('Code pad');
    document.getElementById('inlineCodepadState').textContent = ohaTranslate(enabled ? 'Active' : 'Inactive');

    if (enabled) {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Enter the 4 to 8 digit disarm code.');
    } else if (codeRequired) {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Code entry becomes available when disarming is possible.');
    } else {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Code protection is not enabled.');
    }

    document.getElementById('inlineDisarmLabel').textContent = ohaTranslate('Deactivate');
    ohaUpdateCodepad();
}

function ohaRenderDisarm(state) {
    const bar = document.getElementById('controlBar');
    const button = document.getElementById('disarmButton');
    const label = document.getElementById('disarmButtonLabel');
    const codeHint = document.getElementById('codeHint');
    const codeRequired = Boolean(state.Capabilities?.CodeRequired);
    const canDisarm = Boolean(state.Capabilities?.CanDisarm);

    bar.hidden = !canDisarm;
    bar.dataset.codeRequired = codeRequired ? 'true' : 'false';

    if (!canDisarm) {
        ohaCloseCodepad();
        return;
    }

    document.getElementById('controlTitle').textContent = ohaTranslate('System control');
    codeHint.hidden = !codeRequired;
    codeHint.textContent = codeRequired ? ohaTranslate('Code required for disarming') : '';
    button.dataset.enabled = 'true';
    button.setAttribute('aria-disabled', 'false');
    button.dataset.codeRequired = codeRequired ? 'true' : 'false';
    label.textContent = ohaTranslate('Deactivate');
}

function ohaRenderStaticText() {
    document.getElementById('refreshButton').setAttribute('aria-label', ohaTranslate('Refresh'));
    document.getElementById('codepadTitle').textContent = ohaTranslate('Disarm system');
    document.getElementById('codepadHint').textContent = ohaTranslate('Enter the 4 to 8 digit disarm code.');
    document.getElementById('codepadClose').setAttribute('aria-label', ohaTranslate('Cancel code entry'));
    document.getElementById('codepadDelete').setAttribute('aria-label', ohaTranslate('Delete last digit'));
    document.getElementById('codepadClear').setAttribute('aria-label', ohaTranslate('Clear code entry'));
    document.getElementById('codepadConfirm').setAttribute('aria-label', ohaTranslate('Deactivate'));
    document.getElementById('modalDisarmLabel').textContent = ohaTranslate('Deactivate');
    document.getElementById('codepadGrid').setAttribute('aria-label', ohaTranslate('Code pad'));
    document.getElementById('inlineCodepad').setAttribute('aria-label', ohaTranslate('Code pad'));
    document.getElementById('inlineCodepadDelete').setAttribute('aria-label', ohaTranslate('Delete last digit'));
    document.getElementById('inlineCodepadClear').setAttribute('aria-label', ohaTranslate('Clear code entry'));
    document.getElementById('inlineCodepadConfirm').setAttribute('aria-label', ohaTranslate('Deactivate'));
    document.getElementById('inlineCodepadGrid').setAttribute('aria-label', ohaTranslate('Code pad'));
}

function ohaRender() {
    if (!ohaState || Number(ohaState.ApiVersion) !== 1) {
        return;
    }

    ohaRenderStaticText();
    ohaRenderHero(ohaState);
    ohaRenderSummary(ohaState);
    ohaRenderAlarmMemory(ohaState);
    ohaRenderFaults(ohaState);
    ohaRenderBypasses(ohaState);
    ohaRenderArming(ohaState);
    ohaRenderInlineCodepad(ohaState);
    ohaRenderDisarm(ohaState);
}

function ohaRequestAction(ident, value) {
    if (typeof requestAction === 'function') {
        requestAction(ident, value);
    }
}

function ohaHandleModeButton(button) {
    if (!(button instanceof HTMLElement)) {
        return;
    }

    if (button.dataset.canArm === 'true') {
        ohaRequestAction('Arm', button.dataset.mode ?? '');
        return;
    }

    if (ohaState?.State?.Name !== 'disarmed') {
        const hint = document.getElementById('armingHint');
        if (hint) {
            hint.textContent = ohaTranslate('Deactivate first to change mode');
        }
    }
}

function ohaInlineCodepadVisible() {
    const inlineCodepad = document.getElementById('inlineCodepad');
    return inlineCodepad !== null && window.getComputedStyle(inlineCodepad).display !== 'none';
}

function ohaFocusInlineCodepad() {
    if (!ohaCodeInputAllowed() || !ohaInlineCodepadVisible()) {
        return false;
    }

    document.querySelector('#inlineCodepad [data-code-digit="1"]')?.focus();
    return true;
}

function ohaHandleDisarmButton() {
    if (!ohaState?.Capabilities?.CanDisarm) {
        return;
    }

    if (ohaState?.Capabilities?.CodeRequired) {
        if (!ohaFocusInlineCodepad()) {
            ohaOpenCodepad();
        }
        return;
    }

    ohaRequestAction('Disarm', '');
}

function ohaClearCodeRequestTimer() {
    if (ohaCodeRequestTimer !== null) {
        window.clearTimeout(ohaCodeRequestTimer);
        ohaCodeRequestTimer = null;
    }
}

function ohaSetCodeError(message) {
    for (const error of document.querySelectorAll('[data-code-error]')) {
        error.hidden = message === '';
        error.textContent = message;
    }
}

function ohaUpdateCodepad() {
    const inputAllowed = ohaCodeInputAllowed();

    for (const display of document.querySelectorAll('[data-code-display]')) {
        const digits = display.querySelectorAll('.oha-code-dot');
        digits.forEach((dot, index) => {
            dot.dataset.filled = index < ohaCodeBuffer.length ? 'true' : 'false';
        });
        display.setAttribute(
            'aria-label',
            `${ohaTranslate('Code entry')}: ${ohaCodeBuffer.length} ${ohaTranslate('digits entered')}`
        );
    }

    const confirmEnabled = inputAllowed && !ohaCodeBusy && ohaCodeBuffer.length >= 4 && ohaCodeBuffer.length <= 8;
    for (const button of document.querySelectorAll('[data-code-confirm]')) {
        button.dataset.enabled = confirmEnabled ? 'true' : 'false';
        button.setAttribute('aria-disabled', confirmEnabled ? 'false' : 'true');
    }

    const editEnabled = inputAllowed && !ohaCodeBusy && ohaCodeBuffer.length > 0;
    for (const button of document.querySelectorAll('[data-code-delete], [data-code-clear]')) {
        button.dataset.enabled = editEnabled ? 'true' : 'false';
        button.setAttribute('aria-disabled', editEnabled ? 'false' : 'true');
    }

    const digitEnabled = inputAllowed && !ohaCodeBusy && ohaCodeBuffer.length < 8;
    for (const button of document.querySelectorAll('[data-code-digit]')) {
        button.dataset.enabled = digitEnabled ? 'true' : 'false';
        button.setAttribute('aria-disabled', digitEnabled ? 'false' : 'true');
    }
}

function ohaResetCodeEntry() {
    ohaClearCodeRequestTimer();
    ohaCodeBuffer = '';
    ohaCodeBusy = false;
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaOpenCodepad() {
    if (!ohaCodeInputAllowed()) {
        return;
    }

    const overlay = document.getElementById('codepadOverlay');
    ohaResetCodeEntry();
    overlay.hidden = false;
    document.body.classList.add('oha-modal-open');

    window.requestAnimationFrame(() => {
        document.querySelector('#codepadOverlay [data-code-digit="1"]')?.focus();
    });
}

function ohaCloseCodepad() {
    const overlay = document.getElementById('codepadOverlay');
    if (overlay) {
        overlay.hidden = true;
    }
    document.body.classList.remove('oha-modal-open');
    ohaResetCodeEntry();
}

function ohaAppendCodeDigit(digit) {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || !/^[0-9]$/.test(digit) || ohaCodeBuffer.length >= 8) {
        return;
    }

    ohaCodeBuffer += digit;
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaDeleteCodeDigit() {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || ohaCodeBuffer.length === 0) {
        return;
    }

    ohaCodeBuffer = ohaCodeBuffer.slice(0, -1);
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaClearCodeEntry() {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || ohaCodeBuffer.length === 0) {
        return;
    }

    ohaCodeBuffer = '';
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaSubmitCode() {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || ohaCodeBuffer.length < 4 || ohaCodeBuffer.length > 8) {
        return;
    }

    const code = ohaCodeBuffer;
    ohaCodeBuffer = '';
    ohaCodeBusy = true;
    ohaSetCodeError('');
    ohaUpdateCodepad();
    ohaRequestAction('DisarmWithCode', code);

    ohaClearCodeRequestTimer();
    ohaCodeRequestTimer = window.setTimeout(() => {
        ohaCodeRequestTimer = null;
        if (ohaCodeBusy) {
            ohaCodeBusy = false;
            ohaSetCodeError(ohaTranslate('No response from the alarm system. Please try again.'));
            ohaUpdateCodepad();
        }
    }, 5000);
}

function ohaHandleInteraction(interaction) {
    if (!interaction || interaction.Type !== 'disarm_code') {
        return;
    }

    ohaClearCodeRequestTimer();
    ohaCodeBusy = false;
    ohaCodeBuffer = '';

    if (interaction.Success === false) {
        ohaSetCodeError(ohaTranslate('Code not accepted. Please try again.'));
    } else {
        ohaSetCodeError('');
    }
    ohaUpdateCodepad();
}

function ohaBindInteractions() {
    const refreshButton = document.getElementById('refreshButton');
    if (refreshButton) {
        refreshButton.onclick = () => ohaRequestAction('RefreshVisualization', true);
    }

    for (const button of document.querySelectorAll('[data-action="arm"]')) {
        button.onclick = () => ohaHandleModeButton(button);
    }

    for (const button of document.querySelectorAll('[data-code-digit]')) {
        button.onclick = () => ohaAppendCodeDigit(button.getAttribute('data-code-digit') ?? '');
    }

    for (const button of document.querySelectorAll('[data-code-delete]')) {
        button.onclick = ohaDeleteCodeDigit;
    }

    for (const button of document.querySelectorAll('[data-code-clear]')) {
        button.onclick = ohaClearCodeEntry;
    }

    for (const button of document.querySelectorAll('[data-code-confirm]')) {
        button.onclick = ohaSubmitCode;
    }

    const disarmButton = document.getElementById('disarmButton');
    if (disarmButton) {
        disarmButton.onclick = ohaHandleDisarmButton;
    }

    const codepadClose = document.getElementById('codepadClose');
    if (codepadClose) {
        codepadClose.onclick = ohaCloseCodepad;
    }
}

function handleMessage(data) {
    let nextState = data;
    if (typeof data === 'string') {
        try {
            nextState = JSON.parse(data);
        } catch (_error) {
            return;
        }
    }

    const previousStateName = ohaState?.State?.Name ?? null;
    const nextStateName = nextState?.State?.Name ?? null;
    ohaState = nextState;

    if (nextStateName === 'disarmed' && previousStateName !== 'disarmed') {
        ohaResetCodeEntry();
    }

    ohaRender();
    ohaHandleInteraction(nextState?.Interaction);
}

document.getElementById('codepadOverlay').addEventListener('click', (event) => {
    if (event.target === event.currentTarget && !ohaCodeBusy) {
        ohaCloseCodepad();
    }
});

document.addEventListener('keydown', (event) => {
    const overlay = document.getElementById('codepadOverlay');
    const inlineCodepad = document.getElementById('inlineCodepad');
    const overlayOpen = !overlay.hidden;
    const inlineFocused = inlineCodepad.contains(document.activeElement);

    if ((!overlayOpen && !inlineFocused) || !ohaCodeInputAllowed()) {
        return;
    }

    if (/^[0-9]$/.test(event.key)) {
        event.preventDefault();
        ohaAppendCodeDigit(event.key);
        return;
    }

    if (event.key === 'Backspace') {
        event.preventDefault();
        ohaDeleteCodeDigit();
        return;
    }

    if (event.key === 'Enter') {
        event.preventDefault();
        ohaSubmitCode();
        return;
    }

    if (event.key === 'Escape' && !ohaCodeBusy) {
        event.preventDefault();
        if (overlayOpen) {
            ohaCloseCodepad();
        } else {
            ohaResetCodeEntry();
        }
    }
});

const ohaDesktopCodepadQuery = window.matchMedia('(min-width: 900px)');
if (typeof ohaDesktopCodepadQuery.addEventListener === 'function') {
    ohaDesktopCodepadQuery.addEventListener('change', (event) => {
        if (event.matches && !document.getElementById('codepadOverlay').hidden) {
            ohaCloseCodepad();
        }
    });
}

ohaBindInteractions();
ohaRender();
