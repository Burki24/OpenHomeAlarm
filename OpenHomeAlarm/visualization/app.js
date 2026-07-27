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

function ohaRenderMode(modeName, modeState) {
    const card = document.querySelector(`.oha-mode-card[data-mode="${modeName}"]`);
    if (!card) {
        return;
    }

    card.dataset.ready = modeState.Ready ? 'true' : 'false';

    const readiness = card.querySelector('[data-role="readiness"]');
    if (readiness) {
        readiness.textContent = ohaTranslate(modeState.Ready ? 'Ready' : 'Not ready');
    }

    const blockers = card.querySelector('[data-role="blockers"]');
    if (blockers) {
        blockers.replaceChildren();
        const items = Array.isArray(modeState.Blockers) ? modeState.Blockers : [];
        if (items.length === 0) {
            blockers.textContent = ohaTranslate('No blocking sensors');
        } else {
            for (const blocker of items) {
                const line = document.createElement('div');
                line.className = 'oha-blocker';
                line.textContent = `${blocker.Name} · ${ohaReasonCaption(blocker.Reason)}`;
                blockers.appendChild(line);
            }
        }
    }

    const button = card.querySelector('[data-action="arm"]');
    if (button) {
        button.disabled = !modeState.CanArm;
        button.textContent = ohaModeButtonCaption(modeName);
    }
}

function ohaRenderDelay(state) {
    const panel = document.getElementById('delayPanel');
    const title = document.getElementById('delayTitle');
    const detail = document.getElementById('delayDetail');
    const remaining = document.getElementById('delayRemaining');
    const delayActive = state.State.Name === 'exit_delay' || state.State.Name === 'entry_delay';

    panel.hidden = !delayActive;
    if (!delayActive) {
        return;
    }

    title.textContent = ohaStateCaption(state.State.Name);
    remaining.textContent = `${Math.max(0, Number(state.Delay.Remaining) || 0)} s`;
    detail.textContent = state.Delay.Source
        ? `${ohaTranslate('Source')}: ${state.Delay.Source}`
        : ohaTranslate('Waiting for arming');
}

function ohaRenderAlarm(state) {
    const panel = document.getElementById('alarmPanel');
    const title = document.getElementById('alarmTitle');
    const detail = document.getElementById('alarmDetail');
    const alarmActive = state.State.Name === 'alarm';
    const memoryActive = Boolean(state.Alarm.MemoryActive);

    panel.hidden = !alarmActive && !memoryActive;
    if (panel.hidden) {
        return;
    }

    title.textContent = ohaTranslate(alarmActive ? 'Alarm active' : 'Alarm memory');
    const parts = [];
    if (state.Alarm.LastSource) {
        parts.push(`${ohaTranslate('Source')}: ${state.Alarm.LastSource}`);
    }
    if (state.Alarm.LastTime) {
        parts.push(state.Alarm.LastTime);
    }
    detail.textContent = parts.join(' · ') || ohaTranslate('Alarm stored');
}

function ohaRenderFaults(state) {
    const panel = document.getElementById('faultPanel');
    const detail = document.getElementById('faultDetail');
    const faults = state.Faults?.Items ?? [];

    panel.hidden = !state.Faults?.Active;
    if (panel.hidden) {
        return;
    }

    detail.textContent = faults.length > 0
        ? faults.map((fault) => fault.Name).join(', ')
        : ohaTranslate('System fault active');
}

function ohaRenderDisarm(state) {
    const button = document.getElementById('disarmButton');
    const label = document.getElementById('disarmButtonLabel');
    const codeHint = document.getElementById('codeHint');
    const codeRequired = Boolean(state.Capabilities?.CodeRequired);
    const canDisarm = Boolean(state.Capabilities?.CanDisarm);

    button.hidden = !canDisarm;
    codeHint.hidden = !canDisarm || !codeRequired;
    button.disabled = !canDisarm || codeRequired;
    label.textContent = ohaTranslate(codeRequired ? 'Disarm with code' : 'Disarm');
}

function ohaRender() {
    if (!ohaState || Number(ohaState.ApiVersion) !== 1) {
        return;
    }

    const root = document.getElementById('ohaRoot');
    root.dataset.state = ohaState.State.Name;
    document.getElementById('stateLabel').textContent = ohaStateCaption(ohaState.State.Name);
    document.getElementById('modeLabel').textContent = ohaModeCaption(ohaState.Mode.Name);

    ohaRenderDelay(ohaState);
    ohaRenderAlarm(ohaState);
    ohaRenderFaults(ohaState);
    ohaRenderMode('home', ohaState.Modes.home);
    ohaRenderMode('away', ohaState.Modes.away);
    ohaRenderMode('night', ohaState.Modes.night);
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
