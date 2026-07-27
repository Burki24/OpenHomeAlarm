'use strict';

let ohaState = window.OHA_INITIAL_STATE ?? null;

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
    const card = document.querySelector(`.oha-mode-card[data-mode="${modeName}"]`);
    if (!card) {
        return;
    }

    card.dataset.ready = modeState.Ready ? 'true' : 'false';

    const title = card.querySelector('[data-role="mode-title"]');
    if (title) {
        title.textContent = ohaModeCaption(modeName);
    }

    const readiness = card.querySelector('[data-role="readiness"]');
    if (readiness) {
        readiness.textContent = ohaTranslate(modeState.Ready ? 'Ready' : 'Not ready');
    }

    const blockers = card.querySelector('[data-role="blockers"]');
    if (blockers) {
        blockers.replaceChildren();
        const items = Array.isArray(modeState.Blockers) ? modeState.Blockers : [];
        blockers.dataset.empty = items.length === 0 ? 'true' : 'false';

        if (items.length === 0) {
            blockers.textContent = ohaTranslate('No blocking sensors');
        } else {
            for (const blocker of items.slice(0, 3)) {
                const line = document.createElement('div');
                line.className = 'oha-blocker';
                line.textContent = `${blocker.Name} · ${ohaReasonCaption(blocker.Reason)}`;
                blockers.appendChild(line);
            }

            if (items.length > 3) {
                const more = document.createElement('div');
                more.className = 'oha-blocker';
                more.textContent = `+${items.length - 3} ${ohaTranslate('more')}`;
                blockers.appendChild(more);
            }
        }
    }

    const button = card.querySelector('[data-action="arm"]');
    if (button) {
        button.disabled = !modeState.CanArm;
        const label = button.querySelector('[data-role="button-label"]');
        if (label) {
            label.textContent = ohaModeButtonCaption(modeName);
        }
    }
}

function ohaRenderArming(state) {
    const section = document.getElementById('armingSection');
    const isDisarmed = state.State.Name === 'disarmed';

    section.hidden = !isDisarmed;
    if (!isDisarmed) {
        return;
    }

    document.getElementById('armingKicker').textContent = ohaTranslate('Arming modes');
    document.getElementById('armingTitle').textContent = ohaTranslate('Select security mode');
    document.getElementById('armingHint').textContent = ohaTranslate('Select a ready mode to arm');

    ohaRenderMode('home', state.Modes.home);
    ohaRenderMode('away', state.Modes.away);
    ohaRenderMode('night', state.Modes.night);
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

function ohaRenderDisarm(state) {
    const bar = document.getElementById('controlBar');
    const button = document.getElementById('disarmButton');
    const label = document.getElementById('disarmButtonLabel');
    const codeHint = document.getElementById('codeHint');
    const codeRequired = Boolean(state.Capabilities?.CodeRequired);
    const canDisarm = Boolean(state.Capabilities?.CanDisarm);

    bar.hidden = !canDisarm;
    if (!canDisarm) {
        return;
    }

    document.getElementById('controlTitle').textContent = ohaTranslate('System control');
    codeHint.hidden = !codeRequired;
    codeHint.textContent = codeRequired ? ohaTranslate('Code required for disarming') : '';
    button.disabled = codeRequired;
    label.textContent = ohaTranslate(codeRequired ? 'Disarm with code' : 'Disarm');
}

function ohaRenderStaticText() {
    document.getElementById('brandSubtitle').textContent = ohaTranslate('Security control');
    document.getElementById('refreshButton').setAttribute('aria-label', ohaTranslate('Refresh'));
}

function ohaRender() {
    if (!ohaState || Number(ohaState.ApiVersion) !== 1) {
        return;
    }

    ohaRenderStaticText();
    ohaRenderHero(ohaState);
    ohaRenderAlarmMemory(ohaState);
    ohaRenderFaults(ohaState);
    ohaRenderBypasses(ohaState);
    ohaRenderArming(ohaState);
    ohaRenderDisarm(ohaState);
}

function ohaRequestAction(ident, value) {
    if (typeof requestAction === 'function') {
        requestAction(ident, value);
    }
}

function handleMessage(data) {
    if (typeof data === 'string') {
        try {
            ohaState = JSON.parse(data);
        } catch (_error) {
            return;
        }
    } else {
        ohaState = data;
    }

    ohaRender();
}

for (const button of document.querySelectorAll('[data-action="arm"]')) {
    button.addEventListener('click', () => {
        ohaRequestAction('Arm', button.dataset.mode);
    });
}

document.getElementById('disarmButton').addEventListener('click', () => {
    ohaRequestAction('Disarm', '');
});

document.getElementById('refreshButton').addEventListener('click', () => {
    ohaRequestAction('RefreshVisualization', true);
});

ohaRender();
